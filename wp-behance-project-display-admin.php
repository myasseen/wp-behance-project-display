<?php
// Register and enqueue admin stylesheet
wp_register_style('fuyuko-behance-wp-admin-style', plugins_url('/css/fuyuko_behance_admin_style.css', __FILE__));
wp_enqueue_style('fuyuko-behance-wp-admin-style');

if (!empty($_POST['wp_behance_project_setting_updated']) && $_POST['wp_behance_project_setting_updated'] === 'Y') {
    $behance_username = sanitize_text_field($_POST['behance_username']);
    $behance_api = sanitize_text_field($_POST['behance_api']);
    update_option('behance_username', $behance_username);
    update_option('behance_api', $behance_api);
    echo '<div class="updated"><p><strong>' . __('Setting saved.') . '</strong></p></div>';
} else {
    $behance_username = get_option('behance_username');
    $behance_api = get_option('behance_api');
}

if (!empty($_POST['wp_behance_project_update_project']) && $_POST['wp_behance_project_update_project'] === 'Y') {
    $behance_username = get_option('behance_username');
    $behance_api = get_option('behance_api');
    $behance_url = "https://api.behance.net/v2/users/" . esc_attr($behance_username) . "/projects?api_key=" . esc_attr($behance_api);
    $response = wp_remote_get($behance_url);
    if (!is_wp_error($response)) {
        $file_content = wp_remote_retrieve_body($response);
        update_option('wp_behance_projects', $file_content);
        echo '<div class="updated"><p><strong>' . __('Your Projects are updated.') . '</strong></p></div>';
    } else {
        echo '<div class="error"><p><strong>' . __('API error: ') . esc_html($response->get_error_message()) . '</strong></p></div>';
    }
}
?>

<div id="fuyuko-behance-wp" class="wrap">
    <div class="main-content">
        <?php echo "<h1>" . __('WP Behance Project Display Settings', 'fuyuko_net') . "</h1>"; ?>
        <p>This plugin will extract the specified user's project information (public data only) through Behance API and display in a page or post.</p>
        <?php echo "<h2>" . __('How To Display Your Behance Projects', 'fuyuko_net') . "</h2>"; ?>
        <p>Place the following shortcode in a post or a page where you want to display Behance projects:</p>
        <p><pre>[wpbehance]</pre></p>
        <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
            <input type="hidden" name="wp_behance_project_setting_updated" value="Y">
            <?php echo "<h2>" . __('Plugin Setting', 'fuyuko_net') . "</h2>"; ?>
            <p><label><?php _e("Behance API Key: "); ?></label>
               <input type="text" name="behance_api" value="<?php echo esc_attr($behance_api); ?>" size="40">
               <a href="https://www.behance.net/dev/register" target="_blank">Register API</a>
            </p>
            <p><label><?php _e("Behance Username: "); ?></label>
               <input type="text" name="behance_username" value="<?php echo esc_attr($behance_username); ?>" size="40">
            </p>
            <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Update Plugin Setting', 'fuyuko_net') ?>" />
            </p>
        </form>
        <?php echo "<h2>" . __('Sync Behance Projects', 'fuyuko_net') . "</h2>"; ?>
        <p>Your projects are synchronized on daily basis automatically. If you would like to update the projects immediately, press "Sync Projects".</p>
        <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
            <input type="hidden" name="wp_behance_project_update_project" value="Y">
            <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Sync Projects', 'fuyuko_net') ?>" />
            </p>
        </form>
        <!-- Documentation & links as before -->
    </div>
</div>
