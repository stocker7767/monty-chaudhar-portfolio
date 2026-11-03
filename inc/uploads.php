<?php
/** Owner-only ZIP import and persistent static-demo storage. */
defined('ABSPATH') || exit;
require_once __DIR__ . '/upload-archive.php';

function monty_portfolio_upload_table()
{
    global $wpdb;
    return $wpdb->prefix . 'monty_demo_files';
}

function monty_portfolio_upload_schema()
{
    global $wpdb;
    if ('1' === get_option('monty_demo_schema')) {
        return true;
    }
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $table = monty_portfolio_upload_table();
    $charset = $wpdb->get_charset_collate();
    dbDelta("CREATE TABLE $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        project_id bigint(20) unsigned NOT NULL,
        revision varchar(32) NOT NULL,
        file_path varchar(400) NOT NULL,
        path_hash char(64) NOT NULL,
        mime varchar(80) NOT NULL,
        bytes int(10) unsigned NOT NULL,
        encoding varchar(16) NOT NULL,
        payload longtext NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY project_file (project_id,revision,path_hash),
        KEY project_id (project_id)
    ) $charset;");
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table))) !== $table) {
        return new WP_Error('storage_unavailable', 'WordPress could not prepare project storage. Ask your host to allow this theme to create its project table.');
    }
    update_option('monty_demo_schema', '1', false);
    return true;
}

function monty_portfolio_can_upload($post_id)
{
    return 'monty_project' === get_post_type($post_id) && current_user_can('manage_options') && current_user_can('upload_files') && current_user_can('edit_post', $post_id);
}

function monty_portfolio_upload_authorized($post_id, $nonce)
{
    return monty_portfolio_can_upload($post_id) && is_string($nonce) && wp_verify_nonce($nonce, 'monty_demo_' . $post_id);
}

function monty_portfolio_remove_uploaded($post_id, $expected)
{
    global $wpdb;
    if (!monty_portfolio_can_upload($post_id)) {
        return new WP_Error('forbidden', 'Only a site administrator can remove a project demo.');
    }
    $lock = 'monty_demo_lock_' . absint($post_id);
    $time = (int) get_option($lock);
    if ($time && $time < time() - 300) {
        delete_option($lock);
    }
    if (!add_option($lock, time(), '', false)) {
        return new WP_Error('upload_busy', 'An upload is still running for this project. Wait for it to finish.');
    }
    try {
        $old = get_post_meta($post_id, '_monty_uploaded_demo', true);
        if ((is_array($old) ? $old['revision'] ?? '' : '') !== $expected) {
            return new WP_Error('upload_conflict', 'This demo changed in another tab. Reload the editor first.');
        }
        if ($old && !delete_post_meta($post_id, '_monty_uploaded_demo')) {
            return new WP_Error('storage_remove', 'WordPress could not remove the demo. Please try again.');
        }
        if (get_option('monty_demo_schema')) {
            $wpdb->delete(monty_portfolio_upload_table(), array('project_id' => $post_id), array('%d'));
        }
        return true;
    } finally {
        delete_option($lock);
    }
}

function monty_portfolio_uploaded_demo($post_id, $check_access = true)
{
    $post = get_post($post_id);
    if (!$post || 'monty_project' !== $post->post_type || 'trash' === $post->post_status) {
        return false;
    }
    if ($check_access && 'publish' !== $post->post_status && !monty_portfolio_can_upload($post_id)) {
        return false;
    }
    $demo = get_post_meta($post_id, '_monty_uploaded_demo', true);
    return is_array($demo) && preg_match('/^[a-f0-9]{32}$/D', $demo['revision'] ?? '') ? $demo : false;
}

function monty_portfolio_upload_url($post_id, $revision, $path = '')
{
    // index.php PATH_INFO also works with ordinary WordPress permalink settings.
    $url = home_url('/index.php/monty-demo/' . absint($post_id) . '/' . $revision . '/');
    return $url . implode('/', array_map('rawurlencode', explode('/', $path)));
}

function monty_portfolio_import_zip($post_id, $path, $name, $expected = null)
{
    global $wpdb;
    if (!monty_portfolio_can_upload($post_id)) {
        return new WP_Error('forbidden', 'Only a site administrator can upload project ZIPs.');
    }
    $lock = 'monty_demo_lock_' . absint($post_id);
    $time = (int) get_option($lock);
    if ($time && $time < time() - 300) {
        delete_option($lock);
    }
    if (!add_option($lock, time(), '', false)) {
        return new WP_Error('upload_busy', 'Another upload for this project is still running. Wait for it to finish, then try again.');
    }
    $reader = null;
    $revision = '';
    $committed = false;
    try {
        $old = get_post_meta($post_id, '_monty_uploaded_demo', true);
        if (null !== $expected && (is_array($old) ? $old['revision'] ?? '' : '') !== $expected) {
            return new WP_Error('upload_conflict', 'This project changed in another tab. Reload the editor before uploading again.');
        }
        $reader = Monty_Portfolio_Archive::open($path);
        if (is_wp_error($reader)) {
            return $reader;
        }
        $schema = monty_portfolio_upload_schema();
        if (is_wp_error($schema)) {
            return $schema;
        }
        $revision = str_replace('-', '', wp_generate_uuid4());
        $total = 0;
        foreach ($reader->files() as $file) {
            $contents = $reader->read($file);
            if (is_wp_error($contents)) {
                return $contents;
            }
            $total += strlen($contents);
            $encoding = function_exists('gzencode') ? 'gzip-base64' : 'base64';
            $encoded = 'gzip-base64' === $encoding ? gzencode($contents, 6) : $contents;
            if (
                false === $encoded || false === $wpdb->insert(monty_portfolio_upload_table(), array(
                    'project_id' => $post_id,
                    'revision' => $revision,
                    'file_path' => $file['path'],
                    'path_hash' => hash('sha256', $file['path']),
                    'mime' => $file['mime'],
                    'bytes' => $file['bytes'],
                    'encoding' => $encoding,
                    'payload' => base64_encode($encoded),
                ), array('%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s'))
            ) {
                return new WP_Error('storage_write', 'WordPress could not save the website. Your previous demo has been kept.');
            }
        }
        $demo = array('revision' => $revision, 'entry' => $reader->entry(), 'files' => count($reader->files()), 'bytes' => $total, 'name' => sanitize_file_name($name), 'uploaded' => current_time('mysql', true));
        if (!update_post_meta($post_id, '_monty_uploaded_demo', $demo)) {
            return new WP_Error('storage_commit', 'WordPress could not finish saving the website. Your previous demo has been kept.');
        }
        $committed = true;
        $wpdb->query($wpdb->prepare('DELETE FROM ' . monty_portfolio_upload_table() . ' WHERE project_id = %d AND revision <> %s', $post_id, $revision));
        return $demo;
    } finally {
        if ($reader instanceof Monty_Portfolio_Archive) {
            $reader->close();
        }
        if ($revision && !$committed) {
            $wpdb->delete(monty_portfolio_upload_table(), array('project_id' => $post_id, 'revision' => $revision), array('%d', '%s'));
        }
        delete_option($lock);
    }
}

function monty_portfolio_upload_record($post_id, $revision, $path)
{
    global $wpdb;
    if (false === Monty_Portfolio_Archive::safe_path($path)) {
        return false;
    }
    $row = $wpdb->get_row($wpdb->prepare('SELECT file_path,mime,bytes,encoding,payload FROM ' . monty_portfolio_upload_table() . ' WHERE project_id = %d AND revision = %s AND path_hash = %s LIMIT 1', $post_id, $revision, hash('sha256', $path)), ARRAY_A);
    if (!$row || $row['file_path'] !== $path || (int) $row['bytes'] > Monty_Portfolio_Archive::MAX_FILE) {
        return false;
    }
    $data = base64_decode($row['payload'], true);
    if (false !== $data && 'gzip-base64' === $row['encoding']) {
        $data = function_exists('gzdecode') ? gzdecode($data, Monty_Portfolio_Archive::MAX_FILE + 1) : false;
    }
    if (false === $data || strlen($data) !== (int) $row['bytes']) {
        return false;
    }
    return array('contents' => $data, 'mime' => $row['mime']);
}

function monty_portfolio_upload_paths($post_id, $revision)
{
    global $wpdb;
    static $cache = array();
    $key = $post_id . ':' . $revision;
    if (!isset($cache[$key])) {
        $paths = $wpdb->get_col($wpdb->prepare('SELECT file_path FROM ' . monty_portfolio_upload_table() . ' WHERE project_id = %d AND revision = %s', $post_id, $revision));
        $cache[$key] = array_fill_keys($paths, true);
    }
    return $cache[$key];
}

function monty_portfolio_upload_root_references($text, $post_id, $revision)
{
    $paths = monty_portfolio_upload_paths($post_id, $revision);
    $rewrite = static function ($value) use ($paths, $post_id, $revision) {
        $parts = wp_parse_url($value);
        if (!is_array($parts) || isset($parts['host']) || isset($parts['scheme'])) {
            return $value;
        }
        $path = ltrim(rawurldecode($parts['path'] ?? ''), '/');
        if ('' !== $path && !isset($paths[$path])) {
            return $value;
        }
        return monty_portfolio_upload_url($post_id, $revision, $path) . (isset($parts['query']) ? '?' . $parts['query'] : '') . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    };
    $text = preg_replace_callback('~(["\'])(/(?!/)[^"\'\s<>]*)\1~u', static function ($match) use ($rewrite) {
        return $match[1] . $rewrite($match[2]) . $match[1]; }, $text);
    $text = preg_replace_callback('~\bsrcset\s*=\s*(["\'])(.*?)\1~isu', static function ($match) use ($rewrite) {
        $value = preg_replace_callback('~(^|[\s,])(/(?!/)[^\s,]+)~u', static function ($item) use ($rewrite) {
            return $item[1] . $rewrite($item[2]); }, $match[2]);
        return 'srcset=' . $match[1] . $value . $match[1];
    }, $text);
    return preg_replace_callback('~\burl\(\s*(/(?!/)[^)\s]+)\s*\)~i', static function ($match) use ($rewrite) {
        return 'url(' . $rewrite($match[1]) . ')'; }, $text);
}

function monty_portfolio_uploaded_html($html, $post_id, $demo, $page)
{
    $html = monty_portfolio_upload_root_references($html, $post_id, $demo['revision']);
    $html = preg_replace('~<base\b[^>]*>~i', '', $html);
    $asset_root = monty_portfolio_upload_url($post_id, $demo['revision']);
    $config = array('home' => home_url('/#work'), 'entry' => monty_portfolio_demo_url('upload-' . $post_id), 'slug' => 'upload-' . $post_id, 'page' => $page, 'assetRoot' => $asset_root, 'allowForms' => true);
    $head = '<base href="' . esc_url(monty_portfolio_upload_url($post_id, $demo['revision'], $page)) . '">' . "\n";
    $head .= '<meta name="robots" content="noindex, nofollow">' . "\n";
    $head .= '<script>window.MONTY_DEMO = ' . wp_json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';</script>' . "\n";
    $head .= '<link rel="stylesheet" href="' . esc_url(get_theme_file_uri('assets/css/demo.css')) . '">' . "\n";
    $head .= '<script defer src="' . esc_url(get_theme_file_uri('assets/js/demo.js')) . '"></script>' . "\n";
    if (preg_match('/<head\b[^>]*>/i', $html)) {
        return preg_replace_callback('/<head\b[^>]*>/i', static function ($match) use ($head) {
            return $match[0] . "\n" . $head; }, $html, 1);
    }
    return '<!doctype html><html><head>' . $head . '</head><body>' . $html . '</body></html>';
}

function monty_portfolio_send_uploaded($post_id, $revision, $page)
{
    $demo = monty_portfolio_uploaded_demo($post_id);
    if (!$demo || $demo['revision'] !== $revision) {
        wp_die('This project demo is not available.', 'Demo not found', array('response' => 404));
    }
    $page = '' === $page ? $demo['entry'] : $page;
    $record = monty_portfolio_upload_record($post_id, $revision, $page);
    if (!$record) {
        wp_die('This project file could not be found.', 'File not found', array('response' => 404));
    }
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (!in_array($method, array('GET', 'HEAD'), true)) {
        header('Allow: GET, HEAD');
        wp_die('These are static project files.', '', array('response' => 405));
    }
    $contents = $record['contents'];
    $mime = $record['mime'];
    if ('text/html' === $mime) {
        $contents = monty_portfolio_uploaded_html($contents, $post_id, $demo, $page);
    } elseif (in_array($mime, array('text/css', 'text/javascript', 'application/json', 'application/manifest+json', 'model/gltf+json', 'image/svg+xml'), true)) {
        $contents = monty_portfolio_upload_root_references($contents, $post_id, $revision);
    }
    status_header(200);
    header('Content-Type: ' . $mime . (0 === strpos($mime, 'text/') ? '; charset=UTF-8' : ''));
    header('X-Content-Type-Options: nosniff');
    header('X-Robots-Tag: noindex, nofollow');
    if ('publish' !== get_post_status($post_id) || 'text/html' === $mime) {
        nocache_headers();
    } else {
        $etag = '"' . hash('sha256', $revision . ':' . $page) . '"';
        header('ETag: ' . $etag);
        header('Cache-Control: public, max-age=31536000, immutable');
        if (trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
            status_header(304);
            exit;
        }
    }
    if ('HEAD' !== $method) {
        echo $contents;
    } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    exit;
}

function monty_portfolio_uploaded_route()
{
    $request = wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $home = trailingslashit(wp_parse_url(home_url('/'), PHP_URL_PATH) ?: '/');
    if (!is_string($request) || 0 !== strpos($request, $home)) {
        return;
    }
    $relative = substr($request, strlen($home));
    if (preg_match('~^(?:index\.php/)?monty-demo/([1-9][0-9]*)/([a-f0-9]{32})/(.*)$~D', $relative, $match)) {
        monty_portfolio_send_uploaded((int) $match[1], $match[2], rawurldecode($match[3]));
    }
}
add_action('template_redirect', 'monty_portfolio_uploaded_route', -1);

function monty_portfolio_upload_ajax()
{
    $post_id = isset($_POST['project_id']) && is_scalar($_POST['project_id']) ? absint($_POST['project_id']) : 0;
    $nonce = isset($_POST['nonce']) && is_string($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (!monty_portfolio_upload_authorized($post_id, $nonce)) {
        wp_send_json_error(array('message' => 'You do not have permission to change this project demo. Reload the editor and try again.'), 403);
    }
    $expected = isset($_POST['expected']) && is_string($_POST['expected']) ? sanitize_text_field(wp_unslash($_POST['expected'])) : '';
    if ('monty_demo_remove' === ($_POST['action'] ?? '')) {
        $removed = monty_portfolio_remove_uploaded($post_id, $expected);
        if (is_wp_error($removed)) {
            wp_send_json_error(array('message' => $removed->get_error_message()), 409);
        }
        wp_send_json_success(array('message' => 'Uploaded demo removed. Any selected bundled demo or external URL will be used instead.', 'revision' => ''));
    }
    $file = $_FILES['project_zip'] ?? array();
    if (!is_array($file) || !isset($file['error'], $file['tmp_name'], $file['name']) || !is_int($file['error']) || UPLOAD_ERR_OK !== $file['error'] || !is_string($file['tmp_name']) || !is_string($file['name']) || !is_uploaded_file($file['tmp_name'])) {
        wp_send_json_error(array('message' => 'Choose a ZIP within your site’s upload limit and try again.'), 400);
    }
    if ('zip' !== strtolower(pathinfo($file['name'], PATHINFO_EXTENSION))) {
        wp_send_json_error(array('message' => 'Choose a .zip file containing your finished website.'), 400);
    }
    $result = monty_portfolio_import_zip($post_id, $file['tmp_name'], $file['name'], $expected);
    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()), 400);
    }
    wp_send_json_success(array('message' => sprintf('Uploaded %d files. Click Publish or Update to save the project details.', $result['files']), 'revision' => $result['revision'], 'name' => $result['name'], 'url' => monty_portfolio_demo_url('upload-' . $post_id)));
}
add_action('wp_ajax_monty_demo_upload', 'monty_portfolio_upload_ajax');
add_action('wp_ajax_monty_demo_remove', 'monty_portfolio_upload_ajax');

function monty_portfolio_delete_uploaded($post_id)
{
    global $wpdb;
    if ('monty_project' === get_post_type($post_id) && get_option('monty_demo_schema')) {
        $wpdb->delete(monty_portfolio_upload_table(), array('project_id' => $post_id), array('%d'));
    }
}
add_action('before_delete_post', 'monty_portfolio_delete_uploaded');
