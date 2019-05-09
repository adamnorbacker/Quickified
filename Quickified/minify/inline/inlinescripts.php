<?php
defined( 'ABSPATH' ) or die( 'You are not allowed here.' );

function start_inline_crawler()
  {
    global $wpdb;
    $table_name = $wpdb->prefix . "quickified_minify";
    
    if ( $wpdb->get_var( "SELECT is_inlined FROM $table_name" ) == 0 )
      {
        $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET `is_inlined` = '1' WHERE `id`=%d", 1 ) );
        return true;
      }
    return false;
  }

add_action( 'shutdown', 'crawlInline' );
function crawlInline()
  {
    if ( start_inline_crawler() )
      {
        $pages    = get_pages();
        $numPages = count( $pages );
        
        if ( !empty( $numPages ) )
          {
            for ( $i = 0; $i < $numPages; $i++ )
              {
                $page    = $pages[ $i ];
                $pageurl = get_page_link( $page->ID );
                wp_remote_get( "$pageurl", array(
                     'user-agent' => 'Quickified Page Crawler 1.0',
                    'timeout' => 10 + $i,
                    'sslverify' => false,
                    'cookies' => array(
                         new WP_Http_Cookie( array(
                             'name' => 'inline_crawl',
                            'value' => 'asdasd' 
                        ) ) 
                    ) 
                ) );
              }
          }
      }
  }

getInlinePages();
function getInlinePages()
  {
    
    //Kolla först ifall det kommer från crawlern...
    $userAgent = $_SERVER[ 'HTTP_USER_AGENT' ];
    if ( $userAgent == 'Quickified Page Crawler 1.0' && isset( $_COOKIE[ 'inline_crawl' ] ) )
      {
        function ic_callback( $data )
          {
            if ( !isset( $GLOBALS[ 'final_inline' ] ) )
                $GLOBALS[ 'final_inline' ] = '';
            $GLOBALS[ 'final_inline' ] .= $data;
            return $data;
          }
        function ic_buffer_start()
          {
            ob_start( 'ic_callback' );
          }
        function ic_buffer_end()
          {
            if ( ob_get_length() )
              {
                ob_end_clean();
              }
            
            $output    = $GLOBALS[ 'final_inline' ];
            $time_zone = get_option( 'timezone_string' );
            date_default_timezone_set( $time_zone );
            $currenturl = ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] === 'on' ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            if ( empty( $output ) )
              {
                logError( "Empty, trying again: $currenturl" );
                wp_remote_get( "$currenturl", array(
                     'user-agent' => 'Quickified Page Crawler 1.0',
                    'timeout' => 10,
                    'sslverify' => false,
                    'cookies' => array(
                         new WP_Http_Cookie( array(
                             'name' => 'inline_crawl',
                            'value' => 'asdasd' 
                        ) ) 
                    ) 
                ) );
                return;
              }
            $pages = get_pages();
            include_once substr( plugin_dir_path( __DIR__ ), 0, -7 ) . 'api/apirequestor.php';
            foreach ( $pages as $page )
              {
                $pageurl   = get_page_link( $page->ID );
                $pagetitle = $page->post_title;
                
                if ( $currenturl == $pageurl )
                  {
                    $data = processInline( $output );
                    addInlineData( $data );
                    logError( "Crawled and extracted inline data $pageurl" );
                    clearstatcache();
                    if ( $page === end( $pages ) )
                      {
                        global $wpdb;
                        $table_name_crawler = $wpdb->prefix . "quickified_crawler";
                        $wpdb->query( "DELETE FROM $table_name_crawler" );
                      }
                  }
              }
          }
        add_action( 'wp_loaded', 'ic_buffer_start' );
        add_action( 'shutdown', 'ic_buffer_end' );
      }
  }

function addInlineData( $inputdata )
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'quickified_minify';
    if ( !empty( $wpdb->get_results( "SELECT inline_js FROM $table_name WHERE 'inline_js' IS NOT NULL" ) ) )
      {
        $savedscriptstoarray = implode( '', $wpdb->get_row( "SELECT inline_js FROM $table_name", ARRAY_A ) );
        $data                = unserialize( $savedscriptstoarray );
        foreach ( $inputdata as $input )
            $data[] = $input;
        $savedInline = serialize( $data );
        
        $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET `inline_js` = %s", $savedInline ) );
      }
  }

function removeInlineCallback( $buffer )
  {
    include_once substr( plugin_dir_path( __DIR__ ), 0, -7 ) . 'api/apirequestor.php';
    
    $currenturl = ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] === 'on' ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    global $wpdb;
    $table_name = $wpdb->prefix . "quickified_pages";
    $js         = $wpdb->get_var( "SELECT jsurl FROM $table_name WHERE url = '$currenturl'" );
    if ( $js == null )
      {
        $js = '';
      }
    
    $buffer = replaceInline( $buffer, $js );
    
    return $buffer;
    
  }

function removeInlineBuffer_start()
  {
    ob_start( "removeInlineCallback" );
  }
function removeInlineBuffer_end()
  {
    if ( ob_get_length() )
      {
        ob_end_clean();
      }
  }

add_action( 'init', 'main_handle_inline' );
function main_handle_inline()
  {
    if ( current_user_can( 'administrator' ) )
      {
        add_action( 'wp_loaded', 'removeInlineBuffer_start', 9999999 );
        add_action( 'shutdown', 'removeInlineBuffer_end' );
      }
  } 
