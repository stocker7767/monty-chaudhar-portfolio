<?php
defined('ABSPATH') || exit;
if (post_password_required()) {
    return;
}
?>
<section id="comments" class="comments-area">
    <?php if (have_comments()): ?>
        <h2><?php esc_html_e('Comments', 'monty-portfolio'); ?></h2>
        <ol class="comment-list">
            <?php wp_list_comments(array('style' => 'ol', 'short_ping' => true, 'avatar_size' => 40)); ?></ol>
        <?php the_comments_navigation(); ?>
    <?php endif; ?>
    <?php if (!comments_open() && get_comments_number()): ?>
        <p><?php esc_html_e('Comments are closed.', 'monty-portfolio'); ?></p><?php endif; ?>
    <?php comment_form(); ?>
</section>