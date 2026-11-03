<?php
defined('ABSPATH') || exit;
get_header();
?>
<main id="main" class="wp-content-shell">
    <p class="eyebrow">404 / PAGE NOT FOUND</p>
    <h1><?php esc_html_e('Let’s get you back to the work.', 'monty-portfolio'); ?></h1>
    <p><?php esc_html_e('This page could not be found.', 'monty-portfolio'); ?></p>
    <a class="button button-lime"
        href="<?php echo esc_url(home_url('/#work')); ?>"><?php esc_html_e('Explore my work', 'monty-portfolio'); ?>
        ↗</a>
</main>
<?php get_footer(); ?>