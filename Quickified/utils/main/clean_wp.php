<?php
defined( 'ABSPATH' ) or die( 'You are not allowed here.' );

// Remove unnecessary WP functions
function WP_CLEAN()
  {
    remove_action( 'wp_head', 'wlwmanifest_link' );
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wp_shortlink_wp_head' );
    // Remove emoji
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    // Disable WP comments for posts
    remove_action( 'set_comment_cookies', 'wp_set_comment_cookies' );
    //Remove Avatars
    add_filter( 'bp_core_fetch_avatar', "disableFilter" );
    add_filter( 'get_avatar', "disableFilter" );
    add_filter( 'bp_get_signup_avatar', "disableFilter" );
  }
add_action( 'wp_loaded', 'WP_CLEAN' );

// WP embed stuff
// add_action('init', 'Disable_Embed_functions');
// add_action('wp_enqueue_scripts', 'Disable_Embed_js');

// Remove WP-embed functions that is not needed
function Disable_Embed_functions()
  {
    remove_action( 'rest_api_init', 'wp_oembed_register_route' );
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
    remove_action( 'wp_head', 'wp_oembed_add_host_js' );
    remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 8 );
    add_filter( 'embed_oembed_discover', '__return_false' );
    add_filter( 'rewrite_rules_array', 'disable_embeds_rewrites' );
  }

function Disable_Embed_js()
  {
    wp_dequeue_script( 'wp-embed' );
  }
