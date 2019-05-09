<?php
defined( 'ABSPATH' ) or die( 'You are not allowed here.' );
//Setup exclusion dirs
$base_cache_folder = ABSPATH . 'wp-content/cache/';
//echo $excluded_css_dir;
if ( is_writable( ABSPATH . 'wp-content/cache' ) )
  {
    mkdir( $base_cache_folder . 'opt_minify', 0755, true );
    mkdir( $base_cache_folder . 'opt_html', 0755, true );
  }
else
  {
    logError( 'The cache folder is not writeable' );
  }
