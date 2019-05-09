<?php
// Heartbeat
// Wordpress heartbeat API, sets the amount of ajax calls on admin-area. Affects draft backups, autosaved content and so on.
// https://developer.wordpress.org/plugins/javascript/heartbeat-api/
function heartbeat_optimized_frequency($settings)
{
    $settings['interval'] = 120; //Interval between 15-120s
    return $settings;
}
add_filter('heartbeat_settings', 'heartbeat_optimized_frequency');

function deactivate_heartbeat()
{
    wp_deregister_script('heartbeat');
}
//add_action('init', 'deactivate_heartbeat');

// Deactivate heartbeat everywhere except on pages / posts
function deactivate_heartbeat_posts()
{
    $baseurl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
    $currenturl = $baseurl . $_SERVER['REQUEST_URI'];
    if (strpos($currenturl, 'wp-admin/post.php') === false || strpos($currenturl, 'wp-admin/post-new.php') === false) {
        wp_deregister_script('heartbeat');
    }
}
add_action('init', 'deactivate_heartbeat_posts');
