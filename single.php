<?php
/** Regular WordPress posts. */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" class="wp-content-shell">
<?php while ( have_posts() ) : the_post(); ?>
    <article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
        <h1><?php the_title(); ?></h1>
        <p class="entry-meta"><?php echo esc_html( get_the_date() ); ?></p>
        <?php the_content(); ?>
        <?php wp_link_pages(); ?>
    </article>
    <?php the_post_navigation(); ?>
    <?php if ( comments_open() || get_comments_number() ) { comments_template(); } ?>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
