<?php
/** Posts and archive fallback. The portfolio homepage uses front-page.php. */
defined('ABSPATH') || exit;
get_header();
?>
<main id="main" class="wp-content-shell">
    <h1><?php echo esc_html(is_home() ? __('Journal', 'monty-portfolio') : (is_search() ? __('Search results', 'monty-portfolio') : get_the_archive_title())); ?>
    </h1>
    <?php if (have_posts()): ?>
        <?php while (have_posts()):
            the_post(); ?>
            <article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <p class="entry-meta"><?php echo esc_html(get_the_date()); ?></p>
                <?php the_excerpt(); ?>
            </article>
        <?php endwhile; ?>
        <?php the_posts_pagination(); ?>
    <?php else: ?>
        <p><?php esc_html_e('Nothing here yet. You can explore the portfolio instead.', 'monty-portfolio'); ?></p>
        <a class="button button-lime"
            href="<?php echo esc_url(home_url('/#work')); ?>"><?php esc_html_e('Explore my work', 'monty-portfolio'); ?>
            ↗</a>
    <?php endif; ?>
</main>
<?php get_footer(); ?>