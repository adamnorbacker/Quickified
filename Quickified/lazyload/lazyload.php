<?php
defined( 'ABSPATH' ) or die( 'You are not allowed here.' );
// Loading of LazyLoad
function lazyLoad_scripts()
  {
    wp_enqueue_script( 'lazyLoad_script', plugin_dir_url( __FILE__ ) . 'assets/js/Quickified.min.js', array(), '2.0', true );
    wp_enqueue_style( 'lazyLoad_style', plugin_dir_url( __FILE__ ) . 'assets/css/Quickified.min.css' );
  }
add_action( 'wp_enqueue_scripts', 'lazyLoad_scripts', 0 );

// Lazyloading
function lazyLoad( $content )
  {
    if ( is_feed() || is_preview() || stripos( $_SERVER[ 'REQUEST_URI' ], "sitemap" ) !== false )
      {
        return $content;
      }
    ob_start( function( $content )
      {
        $dom = new DOMDocument( '1.0', 'UTF-8' );
        @$dom->loadHTML( $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        $imgplaceholder = plugin_dir_url( __FILE__ ) . 'assets/images/lazyload/loader.gif';
        if ( ( isset( $_SERVER[ 'HTTP_ACCEPT' ] ) === true ) && ( strstr( $_SERVER[ 'HTTP_ACCEPT' ], 'image/apng' ) !== false ) )
          {
            // Ifall apng, Funkar bara för chrome, måste hitta check för övriga webbläsare...
            $imgplaceholder = plugin_dir_url( __FILE__ ) . 'assets/images/lazyload/loader.apng';
          }
        $tags = $dom->getElementsByTagName( 'img' );
        foreach ( $tags as $tag )
          {
            if ( stripos( $tag->getAttribute( 'class' ), 'noload' ) !== false )
              {
              }
            else
              {
                if ( !$tag->hasAttribute( 'height' ) || !$tag->hasAttribute( 'width' ) )
                  {
                    $sdata      = $tag->getAttribute( 'src' );
                    $imgid      = get_attachment_ID( $sdata );
                    $image_data = wp_get_attachment_image_src( $imgid, "full" );
                    $checksvg   = strtolower( pathinfo( $sdata, PATHINFO_EXTENSION ) );
                    if ( $checksvg === 'svg' || $checksvg === 'svgz' )
                      {
                        $svgfile    = simplexml_load_file( $sdata );
                        $width      = $svgfile[ width ];
                        $height     = $svgfile[ height ];
                        $viewboxdef = $svgfile[ viewBox ];
                        if ( !empty( $width ) || !empty( $height ) )
                          {
                            $tag->setAttribute( 'height', $height . 'px' );
                            $tag->setAttribute( 'width', $width . 'px' );
                          }
                        elseif ( !empty( $viewboxdef ) )
                          {
                            list( $x_pos, $y_pos, $x_width, $y_height ) = explode( ' ', $svgfile[ 'viewBox' ] );
                            $tag->setAttribute( 'height', $y_height . 'px' );
                            $tag->setAttribute( 'width', $x_width . 'px' );
                          }
                        else
                          {
                            $tag->setAttribute( 'height', 'auto' );
                            $tag->setAttribute( 'width', '100%' );
                          }
                      }
                    else
                      {
                        $tag->setAttribute( 'width', $image_data[ 1 ] . 'px' );
                        $tag->setAttribute( 'height', $image_data[ 2 ] . 'px' );
                      }
                  }
                if ( $tag->hasAttribute( 'class' ) )
                  {
                    if ( !$tag->hasAttribute( 'data-src' ) )
                      {
                        if ( $tag->hasAttribute( 'src' ) )
                          {
                            $clonednode = $tag->cloneNode();
                            $no_script  = $dom->createElement( 'noscript' );
                            $no_script->appendChild( $clonednode );
                            $tag->parentNode->insertBefore( $no_script, $tag );
                            $sdata      = $tag->getAttribute( 'src' );
                            $srcsetdata = $tag->getAttribute( 'srcset' );
                            $sizesdata  = $tag->getAttribute( 'sizes' );
                            if ( ( isset( $_SERVER[ 'HTTP_ACCEPT' ] ) === true ) && ( strstr( $_SERVER[ 'HTTP_ACCEPT' ], 'image/webp' ) !== false ) )
                              {
                                if ( stripos( $sdata, ".jpg" ) )
                                  {
                                    $sdata = str_replace( ".jpg", ".webp", $sdata );
                                  }
                                if ( stripos( $srcsetdata, ".jpg" ) )
                                  {
                                    $srcsetdata = str_replace( ".jpg", ".webp", $srcsetdata );
                                  }
                              }
                            $tag->setAttribute( 'data-src', $sdata );
                            $tag->setAttribute( 'data-srcset', $srcsetdata );
                            $tag->setAttribute( 'data-sizes', $sizesdata );
                            $tag->removeAttribute( 'sizes' );
                            $tag->removeAttribute( 'srcset' );
                            $tag->setAttribute( 'src', $imgplaceholder );
                            if ( $tag->hasAttribute( 'class' ) )
                              {
                                $tag->setAttribute( 'class', trim( $tag->getAttribute( 'class' ) . ' oll' ) );
                              }
                            else
                              {
                                $tag->setAttribute( 'class', 'oll' );
                              }
                          }
                      }
                  }
              }
          }
        $bgs = $dom->getElementsByTagName( 'div' );
        foreach ( $bgs as $bg )
          {
            if ( $bg->hasAttribute( 'style' ) )
              {
                $checkbg = $bg->getAttribute( 'style' );
                if ( preg_match( '/\bbackground\b(?![\w-])/i', $checkbg ) || preg_match( '/\bbackground-image\b/i', $checkbg ) )
                  {
                    if ( $bg->hasAttribute( 'class' ) )
                      {
                        $bg->setAttribute( 'class', trim( $bg->getAttribute( 'class' ) . ' bgoll' ) );
                      }
                    else
                      {
                        $bg->setAttribute( 'class', 'bgoll' );
                      }
                  }
              }
          }
        $content = $dom->saveHTML();
        $content = html_entity_decode( $content, ENT_QUOTES, 'UTF-8' );
        return $content;
      } );
  }
add_action( 'wp_loaded', 'lazyLoad', 99999999999 );
