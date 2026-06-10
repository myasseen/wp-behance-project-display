<?php
/*
Plugin Name: WP Behance Project Display
Plugin URI: https://github.com/myasseen/wp-behance-project-display
Description: Display your Behance projects using a shortcode.
Author: Fuyuko Gratton (Updated v2025: Perplexity AI)
Version: 0.5.0
Author URI: http://fuyuko-net/
*/

// Activation: Setup
register_activation_hook(__FILE__, 'wp_behance_project_display_activate');
function wp_behance_project_display_activate() {
    update_option('behance_api', '');
    if (!wp_next_scheduled('wp_behance_project_display_daily_event')) {
        wp_schedule_event(time(), 'daily', 'wp_behance_project_display_daily_event');
    }
}
add_action('wp_behance_project_display_daily_event', 'wp_behance_project_display_do_daily');

function wp_behance_project_display_do_daily() {
    $behance_username = get_option('behance_username');
    $behance_api = get_option('behance_api');

    if (!$behance_username || !$behance_api) return;

    $behance_url = "https://api.behance.net/v2/users/" . esc_attr($behance_username) . "/projects?api_key=" . esc_attr($behance_api);

    $response = wp_remote_get($behance_url);
    if (!is_wp_error($response)) {
        $file_content = wp_remote_retrieve_body($response);
        update_option('wp_behance_projects', $file_content);
    } else {
        error_log('Behance API Error: ' . $response->get_error_message());
    }
}

// Deactivation: Cleanup
register_deactivation_hook(__FILE__, 'wp_behance_project_display_deactivate');
function wp_behance_project_display_deactivate() {
    wp_clear_scheduled_hook('wp_behance_project_display_daily_event');
}

// Plugin page links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wp_behance_project_display_action_links');
function wp_behance_project_display_action_links($links) {
    $links[] = '<a href="' . esc_url(admin_url('options-general.php?page=wp-behance-project-display')) . '">Settings</a>';
    return $links;
}

// Admin setup
add_action('admin_menu', 'wp_behance_project_display_admin_actions');
function wp_behance_project_display_admin() {
    include('wp-behance-project-display-admin.php');
}
function wp_behance_project_display_admin_actions() {
    add_options_page("WP Behance Project Display", "Behance Project Display", "manage_options", "wp-behance-project-display", "wp_behance_project_display_admin");
}

// Stylesheet
add_action('wp_enqueue_scripts', 'wp_behance_project_display_style_stylesheet');
function wp_behance_project_display_style_stylesheet() {
    wp_register_style('wp-behance-project-display-style', plugins_url('/css/wp-behance-project-display.css', __FILE__));
    wp_enqueue_style('wp-behance-project-display-style');
}

// Shortcode
add_shortcode('wpbehance', 'wp_behance_project_display_shortcode');
function wp_behance_project_display_shortcode($atts) {
    wp_enqueue_style('font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css');

    $atts = shortcode_atts(array('display' => 'default'), $atts);
    $display = esc_attr($atts['display']);

    $string = get_option('wp_behance_projects');
    $json_data = json_decode($string, true);

    if (!isset($json_data['projects']) || !is_array($json_data['projects'])) {
        return '<div>No Behance projects found or API data is not available.</div>';
    }

    $output = '<section id="behance-projects" class="' . $display . '">' . "\n";
    foreach ($json_data['projects'] as $project) {
        $cover_img = isset($project['covers']['404']) ? esc_url($project['covers']['404']) : '';
        $output .= '<article class="behanceProject">' . "\n";
        $output .= '<figure class="projectThumbnail"><a href="' . esc_url($project['url']) . '" target="_blank" ><img src="' . $cover_img . '" alt="' . esc_attr($project['name']) . '"/></a></figure>' . "\n";
        $output .= '<h1 class="projectName"><a href="' . esc_url($project['url']) . '" target="_blank">' . esc_html($project['name']) . '</a></h1>' . "\n";
        $output .= '<footer class="projectStat">';
        $output .= '<div class="projectStatView"><i class="fa fa-eye"></i> ' . intval($project['stats']['views']) . "</div>\n";
        $output .= '<div class="projectStatAppreciation"><i class="fa fa-thumbs-o-up"></i> ' . intval($project['stats']['appreciations']) . "</div>\n";
        $output .= '<div class="projectStatComment"><i class="fa fa-comments"></i> ' . intval($project['stats']['comments']) . "</div>\n";
        $output .= "</footer>\n";
        $output .= '</article>' . "\n";
    }
    $output .= '</section>' . "\n";

    return $output;
}
?>
