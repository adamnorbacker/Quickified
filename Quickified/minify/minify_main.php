<?php
defined( 'ABSPATH' ) or die( 'You are not allowed here.' );
require_once 'xmlmini/xmlmini.php';
//Clean cache button
function cleancachebutton( $wp_admin_bar )
  {
    global $wp_admin_bar;
    $menu_id = 'quickified_cache';
    $wp_admin_bar->add_menu( array(
         'id' => $menu_id,
        'title' => __( 'Clean Cache' ),
        'href' => '?clean_cache=1',
        'meta' => array(
             'class' => 'cleancachebutton' 
        ) 
    ) );
    if ( function_exists( 'opcache_get_status' ) )
        if ( opcache_get_status() !== false )
            $wp_admin_bar->add_menu( array(
                 'parent' => $menu_id,
                'title' => __( 'Clean OPCache' ),
                'href' => '?clean_opcache=1',
                'meta' => array(
                     'class' => 'cleancachebutton' 
                ) 
            ) );
  }
if ( !empty( $_GET[ 'clean_cache' ] ) )
  {
    cleancache();
  }

if ( !empty( $_GET[ 'clean_opcache' ] ) )
  {
    if ( function_exists( 'opcache_get_status' ) )
        if ( opcache_get_status() !== false )
            opcache_reset();
  }

//Clean cache function
function cleancache()
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'quickified_minify';
    if ( $wpdb->get_var( "SELECT is_running FROM $table_name" ) == 0 )
      {
        if ( get_option( 'fonts_optimization-checkbox' ) )
          {
            Scan_fonts( true );
          }
        if ( get_option( 'minify-checkbox' ) )
          {
            $dir = stripos( $_SERVER[ 'REQUEST_URI' ], 'wp-admin' ) !== false ? '../wp-content/cache/opt_minify' : 'wp-content/cache/opt_minify';
            foreach ( glob( $dir . '/style-*.min.css' ) as $filename )
              {
                if ( file_exists( $filename ) )
                  {
                    unlink( $filename );
                  }
              }
            foreach ( glob( $dir . '/script-*.min.js' ) as $filename )
              {
                if ( file_exists( $filename ) )
                  {
                    unlink( $filename );
                  }
              }
            //Minimera htaccess
            include_once plugin_dir_path( __DIR__ ) . 'api/apirequestor.php';
            processHtaccess();
            
            $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET `is_inlined` = '0', `is_running` = 1 WHERE `id`=%d", 1 ) );
            
            $table_name = $wpdb->prefix . "quickified_pages";
            
            $getrows = $wpdb->get_results( "SELECT * FROM $table_name" );
            foreach ( $getrows as $row )
              {
                $pagefile = $row->pagefile;
                @unlink( $pagefile );
                $inlinejs = $row->jspagefile;
                @unlink( $inlinejs );
                //logError("Removed file: $pagefile");
              }
            $wpdb->query( "DELETE FROM $table_name" );
          }
      }
  }

//Clean cache if theme/plugin/or WP version is updated.
function wp_upe_upgrade_completed( $upgrader_object, $options )
  {
    if ( $options[ 'action' ] == 'update' && $options[ 'type' ] == 'plugin' && !empty( $options[ 'plugins' ] ) )
      {
        foreach ( $options[ 'plugins' ] as $plugin )
          {
            set_transient( 'plugins_has_been_updated', 1 );
          }
      }
    if ( $options[ 'action' ] == 'update' && $options[ 'type' ] == 'theme' )
      {
        set_transient( 'themes_has_been_updated', 1 );
      }
    if ( $options[ 'action' ] == 'update' && $options[ 'type' ] == 'core' )
      {
        set_transient( 'core_has_been_updated', 1 );
      }
  }

//Admin update notices
function wp_upe_display_update_notice()
  {
    if ( get_transient( 'plugins_has_been_updated' ) )
      {
        cleancache();
        echo '<div class=\'notice notice-success\'>' . __( 'Quickified: A plugin was updated, cache has been cleaned!', 'wp-phbu' ) . '</div>';
        delete_transient( 'plugins_has_been_updated' );
      }
    if ( get_transient( 'themes_has_been_updated' ) )
      {
        cleancache();
        echo '<div class=\'notice notice-success\'>' . __( 'Quickified: A theme was updated, cache has been cleaned!', 'wp-thbu' ) . '</div>';
        delete_transient( 'themes_has_been_updated' );
      }
    if ( get_transient( 'core_has_been_updated' ) )
      {
        cleancache();
        echo '<div class=\'notice notice-success\'>' . __( 'Quickified: Wordpress Core was updated, cache has been cleaned!', 'wp-chbu' ) . '</div>';
        delete_transient( 'core_has_been_updated' );
      }
    if ( !empty( $_GET[ 'cleancache' ] ) )
      {
        echo '<div class=\'notice notice-success\'>' . __( 'Quickified: Cache has been cleaned!', 'wp-chbc' ) . '</div>';
      }
  }

//Functions container
if ( get_option( 'minify-checkbox' ) )
  {
    add_action( 'admin_bar_menu', 'cleancachebutton', 999 );
    add_action( 'upgrader_process_complete', 'wp_upe_upgrade_completed', 10, 2 );
    add_action( 'admin_notices', 'wp_upe_display_update_notice' );
  }
