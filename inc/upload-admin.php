<?php
/** The ZIP uploader in the native Projects editor. */
defined('ABSPATH') || exit;

function monty_portfolio_upload_box_register()
{
    if (current_user_can('manage_options') && current_user_can('upload_files')) {
        add_meta_box('monty-project-upload', __('Upload project website ZIP', 'monty-portfolio'), 'monty_portfolio_upload_box', 'monty_project', 'normal', 'high');
    }
}
add_action('add_meta_boxes', 'monty_portfolio_upload_box_register', 5);

function monty_portfolio_upload_box($post)
{
    $demo = monty_portfolio_uploaded_demo($post->ID, false);
    $limit = min(Monty_Portfolio_Archive::MAX_ZIP, wp_max_upload_size());
    ?>
    <div class="monty-project-uploader" data-project="<?php echo esc_attr((string) $post->ID); ?>"
        data-nonce="<?php echo esc_attr(wp_create_nonce('monty_demo_' . $post->ID)); ?>"
        data-revision="<?php echo esc_attr($demo['revision'] ?? ''); ?>"
        data-limit="<?php echo esc_attr((string) $limit); ?>">
        <p>Upload your finished HTML/CSS/JavaScript website. Its ZIP needs <strong>index.html</strong> and its assets; one
            enclosing folder is fine. For React or Vite, ZIP the built <strong>dist</strong> or <strong>build</strong>
            folder.</p>
        <p class="monty-demo-current">
            <?php echo $demo ? esc_html($demo['name'] . ' · ' . $demo['files'] . ' files') : 'No uploaded demo yet. Your existing bundled demo or live URL is still available.'; ?>
        </p>
        <?php if (class_exists('ZipArchive')): ?>
            <label class="screen-reader-text" for="monty-project-zip">Choose project ZIP</label>
            <input id="monty-project-zip" class="monty-project-zip" type="file" accept=".zip,application/zip">
            <p class="description">Maximum ZIP: <?php echo esc_html(size_format($limit)); ?>. Each file: up to 8 MB. An
                uploaded demo takes priority over the bundled-demo selector and external live URL.</p>
            <div class="monty-upload-actions"><button class="button button-primary monty-upload-button" type="button">Upload
                    ZIP</button><button class="button monty-remove-button" type="button" <?php disabled(!$demo); ?>>Remove
                    uploaded demo</button></div>
        <?php else: ?>
            <p>Your host needs the PHP ZIP extension enabled before project ZIPs can be uploaded.</p>
        <?php endif; ?>
        <p class="monty-upload-status" role="status" aria-live="polite"></p>
        <?php if ($demo): ?><a class="monty-demo-preview"
                href="<?php echo esc_url(monty_portfolio_demo_url('upload-' . $post->ID)); ?>" target="_blank"
                rel="noopener noreferrer">Preview uploaded website ↗</a><?php else: ?><a class="monty-demo-preview"
                target="_blank" rel="noopener noreferrer" hidden>Preview uploaded website ↗</a><?php endif; ?>
        <p class="description">After uploading, click <strong>Publish</strong> or <strong>Update</strong> to save your
            project details. Draft projects stay hidden from visitors. Future theme updates keep your uploaded demos.</p>
    </div>
    <?php
}

function monty_portfolio_upload_admin_assets()
{
    $screen = get_current_screen();
    if (!$screen || 'monty_project' !== $screen->post_type || !in_array($screen->base, array('post', 'post-new'), true) || !current_user_can('manage_options')) {
        return;
    }
    wp_enqueue_style('monty-upload-admin', get_theme_file_uri('assets/css/upload-admin.css'), array(), monty_portfolio_asset_version('assets/css/upload-admin.css'));
    wp_enqueue_script('monty-upload-admin', get_theme_file_uri('assets/js/upload-admin.js'), array(), monty_portfolio_asset_version('assets/js/upload-admin.js'), true);
    wp_localize_script('monty-upload-admin', 'MONTY_UPLOAD', array('ajaxUrl' => admin_url('admin-ajax.php')));
}
add_action('admin_enqueue_scripts', 'monty_portfolio_upload_admin_assets');
