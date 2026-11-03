<?php
/** Serve the bundled HTML demonstrations through the portfolio's own WordPress URL. */
defined('ABSPATH') || exit;

function monty_portfolio_demo_root($slug)
{
    static $roots;
    if (null === $roots) {
        $roots = array();
        $parent = realpath(get_template_directory() . '/projects');
        foreach (monty_portfolio_catalog() as $project) {
            if (empty($project['path']) || !$parent) {
                continue;
            }
            $parts = explode('/', $project['path']);
            $key = $parts[1] ?? '';
            $root = realpath($parent . '/' . $key);
            if ($root && 0 === strpos($root, $parent . DIRECTORY_SEPARATOR) && is_file($root . '/index.html')) {
                $roots[$key] = $root;
            }
        }
    }
    return is_string($slug) ? ($roots[$slug] ?? false) : false;
}

function monty_portfolio_demo_url($slug, $page = 'index.html', $query = '', $fragment = '')
{
    $args = array();
    if ($query) {
        wp_parse_str($query, $args);
    }
    $args['monty_demo'] = $slug;
    unset($args['monty_demo_page']);
    if ('index.html' !== $page) {
        $args['monty_demo_page'] = $page;
    }
    return add_query_arg($args, home_url('/')) . ($fragment ? '#' . rawurlencode(rawurldecode($fragment)) : '');
}

function monty_portfolio_demo_page($root, $requested)
{
    if (!is_string($requested) || strlen($requested) > 240 || strpbrk($requested, "\0\\") !== false || !preg_match('/\.html?$/i', $requested)) {
        return false;
    }
    $file = realpath($root . '/' . $requested);
    return $file && 0 === strpos($file, $root . DIRECTORY_SEPARATOR) && is_file($file) ? $file : false;
}

function monty_portfolio_rewrite_demo_links($html, $slug, $page, $root)
{
    return preg_replace_callback('/(<a\b[^>]*\bhref\s*=\s*)(["\'])(.*?)\2/isu', function ($match) use ($slug, $page, $root) {
        $href = html_entity_decode($match[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $parts = wp_parse_url($href);
        if (false === $parts || isset($parts['scheme']) || isset($parts['host']) || 0 === strpos($href, '/')) {
            return $match[0];
        }
        $relative = rawurldecode($parts['path'] ?? '');
        $target = $relative ? ('index.html' === $page ? '' : dirname($page) . '/') . $relative : $page;
        $file = monty_portfolio_demo_page($root, $target);
        if (!$file) {
            return $match[0];
        }
        $target = str_replace(DIRECTORY_SEPARATOR, '/', substr($file, strlen($root) + 1));
        $url = monty_portfolio_demo_url($slug, $target, $parts['query'] ?? '', $parts['fragment'] ?? '');
        return $match[1] . $match[2] . esc_url($url) . $match[2];
    }, $html);
}

function monty_portfolio_serve_demo()
{
    if (!isset($_GET['monty_demo'])) {
        return;
    }
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (!in_array($method, array('GET', 'HEAD'), true)) {
        header('Allow: GET, HEAD');
        wp_die(esc_html__('Portfolio demonstrations do not accept form submissions.', 'monty-portfolio'), '', array('response' => 405));
    }
    $slug = is_string($_GET['monty_demo']) ? sanitize_key(wp_unslash($_GET['monty_demo'])) : '';
    if (preg_match('/^upload-([1-9][0-9]*)$/D', $slug, $uploaded)) {
        $post_id = (int) $uploaded[1];
        $demo = monty_portfolio_uploaded_demo($post_id);
        if (!$demo) {
            wp_die('This project demo is not available.', 'Demo not found', array('response' => 404));
        }
        $page = isset($_GET['monty_demo_page']) && is_string($_GET['monty_demo_page']) ? wp_unslash($_GET['monty_demo_page']) : $demo['entry'];
        if (!preg_match('/\.html?$/i', $page)) {
            wp_die('This project page could not be found.', 'Demo not found', array('response' => 404));
        }
        monty_portfolio_send_uploaded($post_id, $demo['revision'], $page);
    }
    $root = monty_portfolio_demo_root($slug);
    $page = isset($_GET['monty_demo_page']) ? (is_string($_GET['monty_demo_page']) ? wp_unslash($_GET['monty_demo_page']) : '') : 'index.html';
    $file = $root ? monty_portfolio_demo_page($root, $page) : false;
    if (!$file) {
        wp_die(esc_html__('This portfolio demonstration could not be found.', 'monty-portfolio'), esc_html__('Demo not found', 'monty-portfolio'), array('response' => 404));
    }
    $html = file_get_contents($file);
    if (false === $html) {
        wp_die(esc_html__('The demonstration is temporarily unavailable.', 'monty-portfolio'), '', array('response' => 503));
    }
    $page = str_replace(DIRECTORY_SEPARATOR, '/', substr($file, strlen($root) + 1));
    $asset_root = trailingslashit(get_template_directory_uri()) . 'projects/' . rawurlencode($slug) . '/';
    $config = array('home' => home_url('/#work'), 'entry' => monty_portfolio_demo_url($slug), 'slug' => $slug, 'page' => $page, 'assetRoot' => $asset_root);
    $head = '<base href="' . esc_url($asset_root . implode('/', array_map('rawurlencode', explode('/', $page)))) . '">' . "\n";
    $head .= '<meta name="robots" content="noindex, nofollow">' . "\n";
    $head .= '<script>window.MONTY_DEMO = ' . wp_json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';</script>' . "\n";
    $html = monty_portfolio_rewrite_demo_links($html, $slug, $page, $root);
    $html = preg_replace_callback('/<head\b[^>]*>/i', static function ($match) use ($head) {
        return $match[0] . "\n" . $head; }, $html, 1);
    status_header(200);
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Robots-Tag: noindex, nofollow', true);
    if ('HEAD' !== $method) {
        // Trusted, bundled HTML only. The route never executes or includes a requested file.
        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
    exit;
}
add_action('template_redirect', 'monty_portfolio_serve_demo', 0);
