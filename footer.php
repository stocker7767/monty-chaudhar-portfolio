<?php
/** Shared footer and the portfolio dialogs. */
defined('ABSPATH') || exit;
?>
<div class="signature-footer" aria-hidden="true">
    <span><?php echo esc_html(strtoupper(monty_portfolio_setting('first_name'))); ?><span
            class="signature-dot">.</span></span></div>
<footer class="site-footer shell"><span>© <?php echo esc_html(wp_date('Y')); ?>
        <?php echo esc_html(monty_portfolio_setting('name')); ?></span><span>ONE DEVELOPER. A LOT OF
        POSSIBILITIES.</span>
    <div><?php if (is_front_page()): ?><button id="motion-toggle" aria-pressed="false">Motion:
                on</button><?php endif; ?><a href="<?php echo esc_url(home_url('/#top')); ?>"
            aria-label="Back to top">Back to top ↑</a></div>
</footer>
<?php
if (is_front_page()) {
    get_template_part('template-parts/dialogs');
}
wp_footer();
?>
</body>

</html>