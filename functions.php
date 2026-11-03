<?php
/** Theme setup and front-end assets. */
defined('ABSPATH') || exit;

require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/projects.php';
require_once get_template_directory() . '/inc/demos.php';
require_once get_template_directory() . '/inc/uploads.php';
require_once get_template_directory() . '/inc/upload-admin.php';

function monty_portfolio_setup()
{
    load_theme_textdomain('monty-portfolio', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('responsive-embeds');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor.css');
}
add_action('after_setup_theme', 'monty_portfolio_setup');

function monty_portfolio_asset_version($relative)
{
    $path = get_theme_file_path($relative);
    return file_exists($path) ? (string) filemtime($path) : '1.0.0';
}

function monty_portfolio_assets()
{
    wp_enqueue_style('monty-foundations', get_theme_file_uri('assets/css/styles.css'), array(), monty_portfolio_asset_version('assets/css/styles.css'));
    wp_enqueue_style('monty-personality', get_theme_file_uri('assets/css/personality.css'), array('monty-foundations'), monty_portfolio_asset_version('assets/css/personality.css'));
    wp_enqueue_style('monty-wordpress', get_stylesheet_uri(), array('monty-personality'), monty_portfolio_asset_version('style.css'));
    if (!is_front_page()) {
        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
        return;
    }
    wp_enqueue_script('monty-interactions', get_theme_file_uri('assets/js/app.js'), array(), monty_portfolio_asset_version('assets/js/app.js'), array('strategy' => 'defer', 'in_footer' => true));
    $hero = monty_portfolio_hero_projects();
    $config = array(
        'email' => monty_portfolio_setting('email'),
        'home' => home_url('/'),
        'heroProject' => $hero ? $hero[0]['id'] : '',
    );
    $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
    wp_add_inline_script('monty-interactions', 'window.MONTY_PROJECTS = ' . wp_json_encode(monty_portfolio_projects(), $flags) . '; window.MONTY_CONFIG = ' . wp_json_encode($config, $flags) . ';', 'before');
}
add_action('wp_enqueue_scripts', 'monty_portfolio_assets');

function monty_portfolio_meta()
{
    if (is_front_page() && !defined('WPSEO_VERSION') && !defined('RANK_MATH_VERSION') && !defined('AIOSEO_VERSION')) {
        echo '<meta name="description" content="' . esc_attr(monty_portfolio_setting('hero_description')) . '">' . "\n";
    }
    if (!has_site_icon()) {
        echo '<link rel="icon" href="' . esc_url(get_theme_file_uri('assets/favicon.svg')) . '" type="image/svg+xml">' . "\n";
    }
}
add_action('wp_head', 'monty_portfolio_meta', 2);

function monty_portfolio_hero_projects()
{
    $projects = monty_portfolio_projects();
    $hero = array();
    foreach (array('chirpy', 'abhika-aura', 'royal-barber') as $id) {
        foreach ($projects as $project) {
            if ($project['id'] === $id && $project['path']) {
                $hero[$id] = $project;
            }
        }
    }
    foreach ($projects as $project) {
        if (count($hero) >= 3) {
            break;
        }
        if ($project['path'] && !isset($hero[$project['id']])) {
            $hero[$project['id']] = $project;
        }
    }
    return array_values($hero);
}
