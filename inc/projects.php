<?php
/** Native WordPress project management; no additional plugin is required. */
defined('ABSPATH') || exit;

function monty_portfolio_catalog()
{
    static $catalog;
    if (null === $catalog) {
        $contents = file_get_contents(get_template_directory() . '/inc/project-catalog.json');
        $catalog = json_decode($contents ?: '[]', true);
        $catalog = is_array($catalog) ? $catalog : array();
    }
    return $catalog;
}

function monty_portfolio_register_projects()
{
    if (post_type_exists('monty_project')) {
        return;
    }
    register_post_type('monty_project', array(
        'labels' => array('name' => __('Projects', 'monty-portfolio'), 'singular_name' => __('Project', 'monty-portfolio'), 'add_new_item' => __('Add project', 'monty-portfolio'), 'edit_item' => __('Edit project', 'monty-portfolio')),
        'public' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'has_archive' => false,
        'rewrite' => false,
        'menu_icon' => 'dashicons-portfolio',
        'menu_position' => 21,
        'supports' => array('title', 'editor', 'excerpt', 'page-attributes'),
        'map_meta_cap' => true,
    ));
}
add_action('init', 'monty_portfolio_register_projects');

function monty_portfolio_seed_projects()
{
    if (get_option('monty_portfolio_catalog_installed')) {
        return;
    }
    monty_portfolio_register_projects();
    $complete = true;
    foreach (monty_portfolio_catalog() as $order => $project) {
        $existing = get_posts(array('post_type' => 'monty_project', 'post_status' => 'any', 'numberposts' => 1, 'meta_key' => '_monty_catalog_id', 'meta_value' => $project['id']));
        if ($existing) {
            continue;
        }
        $post_id = wp_insert_post(array('post_type' => 'monty_project', 'post_status' => 'publish', 'post_title' => $project['name'], 'post_name' => $project['id'], 'post_content' => $project['description'], 'post_excerpt' => $project['line'], 'menu_order' => $order), true);
        if (is_wp_error($post_id)) {
            $complete = false;
            continue;
        }
        $project['demo'] = $project['path'] ? explode('/', $project['path'])[1] : '';
        $project['live_url'] = '';
        $project['code_url'] = 'https://github.com/stocker7767/' . rawurlencode($project['repo']);
        update_post_meta($post_id, '_monty_catalog_id', $project['id']);
        update_post_meta($post_id, '_monty_project', wp_slash($project));
    }
    if ($complete) {
        update_option('monty_portfolio_catalog_installed', '1', false);
    }
}
add_action('after_switch_theme', 'monty_portfolio_seed_projects');

function monty_portfolio_projects()
{
    static $projects;
    if (null !== $projects) {
        return $projects;
    }
    $projects = array();
    $posts = get_posts(array('post_type' => 'monty_project', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => array('menu_order' => 'ASC', 'ID' => 'ASC')));
    $catalog = array_column(monty_portfolio_catalog(), null, 'id');
    if (!get_option('monty_portfolio_catalog_installed') && !$posts) {
        foreach ($catalog as $item) {
            $item['demo'] = $item['path'] ? explode('/', $item['path'])[1] : '';
            $item['codeUrl'] = 'https://github.com/stocker7767/' . rawurlencode($item['repo']);
            $item['path'] = $item['demo'] ? monty_portfolio_demo_url($item['demo']) : null;
            $projects[] = $item;
        }
        return $projects;
    }
    foreach ($posts as $post) {
        $meta = get_post_meta($post->ID, '_monty_project', true);
        $meta = is_array($meta) ? $meta : array();
        $catalog_id = get_post_meta($post->ID, '_monty_catalog_id', true);
        $base = $catalog[$catalog_id] ?? array();
        $item = wp_parse_args($meta, $base + array('repo' => '', 'category' => 'business', 'type' => 'Website project', 'featured' => false, 'tags' => array(), 'note' => '', 'colour' => '#d9edc6', 'ink' => '#244c33', 'wordmark' => $post->post_title, 'caption' => 'WEBSITE PROJECT', 'demo' => '', 'live_url' => '', 'code_url' => ''));
        $item['id'] = sanitize_key($catalog_id ?: 'project-' . $post->ID);
        $item['name'] = html_entity_decode(wp_strip_all_tags(get_the_title($post)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $item['type'] = trim($item['type']) ?: 'Website project';
        $item['caption'] = trim($item['caption']) ?: 'WEBSITE PROJECT';
        $item['wordmark'] = trim($item['wordmark']) ?: $item['name'];
        $item['description'] = wp_strip_all_tags($post->post_content);
        $item['line'] = wp_strip_all_tags($post->post_excerpt ?: wp_trim_words($post->post_content, 18));
        $item['featured'] = (bool) $item['featured'];
        $item['tags'] = is_array($item['tags']) ? array_values($item['tags']) : array();
        $item['codeUrl'] = $item['code_url'] ?: ($item['repo'] ? 'https://github.com/stocker7767/' . rawurlencode($item['repo']) : '');
        $uploaded = monty_portfolio_uploaded_demo($post->ID);
        $item['path'] = $uploaded ? monty_portfolio_demo_url('upload-' . $post->ID) : (!empty($item['live_url']) ? esc_url_raw($item['live_url']) : (monty_portfolio_demo_root($item['demo']) ? monty_portfolio_demo_url($item['demo']) : null));
        $projects[] = $item;
    }
    return $projects;
}

function monty_portfolio_project_boxes()
{
    add_meta_box('monty-project-details', __('Portfolio display and links', 'monty-portfolio'), 'monty_portfolio_project_box', 'monty_project', 'normal', 'high');
}
add_action('add_meta_boxes', 'monty_portfolio_project_boxes');

function monty_portfolio_project_box($post)
{
    $data = get_post_meta($post->ID, '_monty_project', true);
    $data = is_array($data) ? $data : array();
    wp_nonce_field('monty_save_project', 'monty_project_nonce');
    echo '<p>' . esc_html__('Use the title for the project name, the main editor for the description, and Excerpt for the short gallery line. Published projects appear in the portfolio; draft or trash a project to remove it. Page Attributes → Order controls its position.', 'monty-portfolio') . '</p>';
    $categories = array('wordpress' => 'WordPress', 'business' => 'Business websites', 'interactive' => 'Interactive', 'practice' => 'Early work');
    echo '<p><label for="monty-category"><strong>Category</strong></label><br><select name="monty_project[category]" id="monty-category">';
    foreach ($categories as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($data['category'] ?? 'business', $value, false) . '>' . esc_html($label) . '</option>';
    }
    $featured = metadata_exists('post', $post->ID, '_monty_project') ? !empty($data['featured']) : true;
    echo '</select></p><p><label><input type="checkbox" name="monty_project[featured]" value="1" ' . checked($featured, true, false) . '> Show in Featured</label></p>';
    $fields = array('type' => 'Project type / context', 'tags' => 'Technologies (separate with commas)', 'code_url' => 'Source code URL (https://...)', 'live_url' => 'External live URL (optional; leave empty to use the bundled demo)', 'colour' => 'Card background colour (hex)', 'ink' => 'Card text colour (hex)', 'wordmark' => 'Loading-cover wordmark', 'caption' => 'Loading-cover caption', 'note' => 'Project status / demo note');
    foreach ($fields as $key => $label) {
        $value = $data[$key] ?? '';
        if ('tags' === $key && is_array($value)) {
            $value = implode(', ', $value);
        }
        echo '<p><label for="monty-' . esc_attr($key) . '"><strong>' . esc_html($label) . '</strong></label><br>';
        if (in_array($key, array('wordmark', 'note'), true)) {
            echo '<textarea rows="3" class="widefat" id="monty-' . esc_attr($key) . '" name="monty_project[' . esc_attr($key) . ']">' . esc_textarea($value) . '</textarea>';
        } else {
            $type = in_array($key, array('code_url', 'live_url'), true) ? 'url' : 'text';
            echo '<input class="widefat" type="' . esc_attr($type) . '" id="monty-' . esc_attr($key) . '" name="monty_project[' . esc_attr($key) . ']" value="' . esc_attr($value) . '">';
        }
        echo '</p>';
    }
    echo '<p><label for="monty-demo"><strong>Bundled website demo</strong></label><br><select class="widefat" name="monty_project[demo]" id="monty-demo"><option value="">No bundled demo</option>';
    foreach (monty_portfolio_catalog() as $project) {
        if (!$project['path']) {
            continue;
        }
        $slug = explode('/', $project['path'])[1];
        echo '<option value="' . esc_attr($slug) . '" ' . selected($data['demo'] ?? '', $slug, false) . '>' . esc_html($project['name']) . '</option>';
    }
    echo '</select></p>';
}

function monty_portfolio_save_project($post_id)
{
    if (!isset($_POST['monty_project_nonce']) || !is_string($_POST['monty_project_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['monty_project_nonce'])), 'monty_save_project') || !current_user_can('edit_post', $post_id) || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }
    $input = isset($_POST['monty_project']) && is_array($_POST['monty_project']) ? wp_unslash($_POST['monty_project']) : array();
    $data = array();
    foreach (array('type', 'caption') as $key) {
        $data[$key] = sanitize_text_field(is_scalar($input[$key] ?? '') ? $input[$key] : '');
    }
    foreach (array('wordmark', 'note') as $key) {
        $data[$key] = sanitize_textarea_field(is_scalar($input[$key] ?? '') ? $input[$key] : '');
    }
    foreach (array('code_url', 'live_url') as $key) {
        $data[$key] = esc_url_raw(is_scalar($input[$key] ?? '') ? $input[$key] : '', array('http', 'https'));
    }
    $data['category'] = in_array($input['category'] ?? '', array('wordpress', 'business', 'interactive', 'practice'), true) ? $input['category'] : 'business';
    $data['featured'] = !empty($input['featured']);
    $tags = sanitize_text_field(is_scalar($input['tags'] ?? '') ? $input['tags'] : '');
    $data['tags'] = array_values(array_filter(array_map('trim', explode(',', $tags))));
    $data['colour'] = sanitize_hex_color(is_scalar($input['colour'] ?? '') ? $input['colour'] : '') ?: '#d9edc6';
    $data['ink'] = sanitize_hex_color(is_scalar($input['ink'] ?? '') ? $input['ink'] : '') ?: '#244c33';
    $demo = sanitize_key(is_scalar($input['demo'] ?? '') ? $input['demo'] : '');
    $data['demo'] = monty_portfolio_demo_root($demo) ? $demo : '';
    update_post_meta($post_id, '_monty_project', wp_slash($data));
}
add_action('save_post_monty_project', 'monty_portfolio_save_project');
