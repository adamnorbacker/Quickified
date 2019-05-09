<?php
defined( 'ABSPATH' ) or die( 'You are not allowed here.' );
//Remove exclusion dirs
$base_cache_folder = ABSPATH . 'wp-content/cache/opt_minify';
delete_files( $base_cache_folder );
$base_cache_folder = ABSPATH . 'wp-content/cache/opt_html';
delete_files( $base_cache_folder );
function delete_files( $target )
  {
    if ( is_dir( $target ) )
      {
        $files = glob( $target . '*', GLOB_MARK );
        
        foreach ( $files as $file )
          {
            delete_files( $file );
          }
        
        @rmdir( $target );
      }
    elseif ( is_file( $target ) )
      {
        unlink( $target );
      }
    else
      {
        logError( 'minify uninstall: Failed to remove folder/folders' );
      }
  }
$error_log = ABSPATH . 'wp-content/plugins/Quickified/error_logs/OLL.log';
delete_file( $error_log );
function delete_file( $file )
  {
    if ( file_exists( $file ) && is_file( $file ) )
      {
        unlink( $file );
      }
  }
function delete_tables()
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'quickified_minify';
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
    $table_name = $wpdb->prefix . 'quickified_pages';
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
    $table_name = $wpdb->prefix . 'quickified_crawler';
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
    $table_name = $wpdb->prefix . 'quickified_fonts';
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
    $table_name = $wpdb->prefix . 'quickified_security';
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
  }
delete_tables();
function clear_htaccess()
  {
    $htaccess       = ABSPATH . ".htaccess";
    $file_data      = file_get_contents( $htaccess );
    $findQuickified = '/(?=\# BEGIN QUICKIFIED REWRITES)(.*)(?<=\# END QUICKIFIED REWRITES)\s*/is';
    $file_data      = preg_replace( $findQuickified, '', $file_data );
    file_put_contents( $htaccess, $file_data );
  }
clear_htaccess();
