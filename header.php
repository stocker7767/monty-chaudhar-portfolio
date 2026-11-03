<?php
/** Document head and shared navigation. */
defined('ABSPATH') || exit;
$monty_name = monty_portfolio_setting('name');
$monty_brand_parts = explode(' ', $monty_name, 2);
?>
<!doctype html>
<html <?php language_attributes(); ?> data-theme="dark">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#151713">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <a class="skip-link" href="#main"><?php esc_html_e('Skip to content', 'monty-portfolio'); ?></a>
    <?php if (is_front_page()): ?>
        <div class="reading-progress" aria-hidden="true"><span id="reading-progress"></span></div><?php endif; ?>
    <header class="site-header">
        <a href="<?php echo esc_url(home_url('/#top')); ?>" class="brand"
            aria-label="<?php echo esc_attr($monty_name . ' home'); ?>"><span
                class="brand-mark">m<span>↗</span></span><span
                class="brand-name"><?php echo esc_html(strtoupper($monty_brand_parts[0])); ?><?php if (isset($monty_brand_parts[1])): ?><br><?php echo esc_html(strtoupper($monty_brand_parts[1])); ?><?php endif; ?></span></a>
        <nav aria-label="<?php esc_attr_e('Main navigation', 'monty-portfolio'); ?>" id="navigation"><a
                href="<?php echo esc_url(home_url('/#work')); ?>">Work
                <sup><?php echo esc_html((string) count(monty_portfolio_projects())); ?></sup></a><a
                href="<?php echo esc_url(home_url('/#about')); ?>">About</a><a
                href="<?php echo esc_url(home_url('/#playground')); ?>">Playground</a></nav>
        <div class="header-actions"><?php if (is_front_page()): ?><button class="command-key" id="command-open"
                    aria-label="Open quick navigation">⌘ / Ctrl K</button><button class="icon-button" id="theme-toggle"
                    aria-label="Switch to light theme">◐</button><?php endif; ?><a class="header-contact"
                href="<?php echo esc_url(home_url('/#contact')); ?>">Let’s talk <span>↗</span></a></div>
    </header>