<?php
/**
 * Plugin Name: Quickified
 * Description: Decreases number of requests and is the most lightweight plugin on the web.
 * Version: 1.0
 * Author: Adam Norbäcker, Felix Lundquist
 * Author URI: 127.0.0.1
 * Plugin URI: 127.0.0.1
 *
 * @author    Quickified
 * @copyright 2017-2018 Quickified (127.0.0.1)
 *
 */
defined( 'ABSPATH' ) or die( 'You are not allowed here.' );

// Both admin and public area
include_once 'logging/error_logging.php';
include_once 'tables/tables.php';
include_once 'admin/pages/admin.php';
include_once 'image_optimization/upload_handler/upload_handler.php';
function setup()
  {
    include_once 'minify/minify_setup.php';
  }
function uninstall()
  {
    include_once 'minify/minify_uninstall.php';
  }

// Security setup is not included in the activation handler because of WIP and has to be activated manually.
function security_setup()
  {
    if ( get_option( 'security_setup_once' ) != '1' )
      {
        include_once 'security/security_setup.php';
        update_option( 'security_setup_once', '1' );
      }
  }
function security_uninstall()
  {
    if ( get_option( 'security_uninstall' ) != '1' )
      {
        include_once 'security/security_uninstall.php';
        update_option( 'security_uninstall', '1' );
      }
  }
if ( $security == 1 )
  {
    include_once 'security/security.php';
    delete_option( 'security_uninstall' );
    add_action( 'admin_init', 'security_setup' );
  }
else
  {
    delete_option( 'security_setup_once' );
    add_action( 'admin_init', 'security_uninstall' );
  }
if ( $imageOptimization == 1 )
  {
    include_once 'image_optimization/exif.php';
  }
if ( $minify == 1 )
  {
    include_once 'minify/minify_main.php';
  }

// Triggers setup when plugin is activated and uninstall when plugin is deactivated to remove leftovers.
register_activation_hook( __FILE__, 'setup' );
register_deactivation_hook( __FILE__, 'uninstall' );

// Non Admin area
if ( !is_admin() && !wp_doing_ajax() || !is_admin() )
  {
    $currenturl = ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] === 'on' ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    if ( stripos( $currenturl, wp_login_url() ) !== false )
      {
        return;
      }
    
    include_once 'utils/main/remove_queries.php';
    include_once 'utils/main/remove_fonts.php';
    include_once 'utils/main/defer_js.php';
    include_once 'utils/main/clean_wp.php';
    include_once 'utils/main/jquery_migrate_disable.php';
    include_once 'utils/main/heartbeat.php';
    
    if ( $minify == 1 )
      {
        include_once 'minify/inline/inlinescripts.php';
        include_once 'html_pages/html_pages.php';
        include_once 'minify/minify.php';
      }
    
    if ( $imageOptimization == 1 )
      {
        include_once 'image_optimization/scaledimgs.php';
      }
    
    
    if ( $lazyLoad == 1 )
      {
        include_once 'lazyload/lazyload.php';
      }
  }
