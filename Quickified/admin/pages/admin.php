<?php
defined( 'ABSPATH' ) or die( 'You are not allowed here.' );
function quickified_admin_menu()
  {
    add_menu_page( 'Quickified Settings', 'Quickified', 'manage_options', 'optimal_lazy_load_menu', 'quickified_admin_menu_options', plugin_dir_url( __FILE__ ) . '../assets/images/logo/quickified_icon.png' );
  }
add_action( 'admin_menu', 'quickified_admin_menu' );

function quickified_settings_page()
  {
    $createWebp  = get_option( 'create_webp-checkbox' );
    $compressJpg = get_option( 'compress_jpg-checkbox' );
    // Minify
    add_settings_section( "Minify", "Minify", null, "set_functions_section" );
    add_settings_field( "minify-checkbox", "Activate Minify", "minify_checkbox_display", "set_functions_section", "Minify" );
    register_setting( "set_functions", "minify-checkbox" );
    // Security
    // add_settings_section("Security", "Security", null, "set_functions_section");
    // add_settings_field("security-checkbox", "Activate Security", "security_checkbox_display", "set_functions_section", "Security");
    // register_setting("set_functions", "security-checkbox");
    // Lazy load
    add_settings_section( "lazy_load", "Lazy load", null, "set_functions_section" );
    add_settings_field( "lazy_load-checkbox", "Activate LazyLoad", "lazy_load_checkbox_display", "set_functions_section", "lazy_load" );
    register_setting( "set_functions", "lazy_load-checkbox" );
    // Lazy Load settings
    add_settings_section( "Lazy_load_settings", "Lazy Load Settings", null, "set_functions_lazyload_section" );
    add_settings_field( "activate_images-checkbox", "Activate LazyLoad for images", "activate_images_checkbox_display", "set_functions_lazyload_section", "Lazy_load_settings" );
    add_settings_field( "activate_bg_images-checkbox", "Activate LazyLoad for background-images", "activate_bg_images_checkbox_display", "set_functions_lazyload_section", "Lazy_load_settings" );
    register_setting( "set_functions_lazyload", "activate_images-checkbox" );
    register_setting( "set_functions_lazyload", "activate_bg_images-checkbox" );
    // DB Optimization
    add_settings_section( "db_optimization", "DB Optimization", null, "set_functions_section" );
    add_settings_field( "db_optimization-checkbox", "Activate DB Optimization", "db_optimization_checkbox_display", "set_functions_section", "db_optimization" );
    register_setting( "set_functions", "db_optimization-checkbox" );
    // Fonts Optimization
    add_settings_section( "fonts_optimization", "Fonts Optimization", null, "set_functions_section" );
    add_settings_field( "fonts_optimization-checkbox", "Activate Fonts Optimization", "fonts_optimization_checkbox_display", "set_functions_section", "fonts_optimization" );
    register_setting( "set_functions", "fonts_optimization-checkbox" );
    // Image Optimization
    add_settings_section( "image_optimization", "Image Optimization", null, "set_functions_section" );
    add_settings_field( "image_optimization-checkbox", "Activate Image Optimization", "image_optimization_checkbox_display", "set_functions_section", "image_optimization" );
    register_setting( "set_functions", "image_optimization-checkbox" );
    
    //IMAGE Optimization SETTINGS
    add_settings_section( "compress_images", "Image Optimizations", null, "set_functions_images_section" );
    add_settings_field( "compress_jpg-checkbox", "Compress JPG", "compress_jpg_checkbox_display", "set_functions_images_section", "compress_images" );
    add_settings_field( "compress_png-checkbox", "Compress PNG", "compress_png_checkbox_display", "set_functions_images_section", "compress_images" );
    add_settings_field( "create_webp-checkbox", "Create WEBP", "create_webp_checkbox_display", "set_functions_images_section", "compress_images" );
    if ( $compressJpg )
      {
        add_settings_field( "jpg_compression-textinput", "JPG Compression value 1-100", "jpg_compression_textinput_display", "set_functions_images_section", "compress_images" );
      }
    if ( $createWebp )
      {
        add_settings_field( "webp_compression-textinput", "WEBP Compression value 1-100", "webp_compression_textinput_display", "set_functions_images_section", "compress_images" );
      }
    register_setting( "set_functions_images", "compress_jpg-checkbox" );
    register_setting( "set_functions_images", "compress_png-checkbox" );
    register_setting( "set_functions_images", "create_webp-checkbox" );
    if ( $compressJpg )
      {
        register_setting( "set_functions_images", "jpg_compression-textinput" );
      }
    if ( $createWebp )
      {
        register_setting( "set_functions_images", "webp_compression-textinput" );
      }
    
  }
add_action( "admin_init", "quickified_settings_page" );

//Various checkboxes for admin area toggling settings.

function minify_checkbox_display()
  {
?>
<input type="checkbox" name="minify-checkbox" value="1" <?php
    checked( 1, get_option( 'minify-checkbox' ), true );
?> />
<?php
  }

function security_checkbox_display()
  {
?>
<input type="checkbox" name="security-checkbox" value="1" <?php
    checked( 1, get_option( 'security-checkbox' ), true );
?> />
<?php
  }

function lazy_load_checkbox_display()
  {
?>
<input type="checkbox" name="lazy_load-checkbox" value="1" <?php
    checked( 1, get_option( 'lazy_load-checkbox' ), true );
?> />
<?php
  }

function db_optimization_checkbox_display()
  {
?>
<input type="checkbox" name="db_optimization-checkbox" value="1" <?php
    checked( 1, get_option( 'db_optimization-checkbox' ), true );
?> />
<?php
  }

function fonts_optimization_checkbox_display()
  {
?>
<input type="checkbox" name="fonts_optimization-checkbox" value="1" <?php
    checked( 1, get_option( 'fonts_optimization-checkbox' ), true );
?> />
<?php
  }

function image_optimization_checkbox_display()
  {
?>
<input type="checkbox" name="image_optimization-checkbox" value="1" <?php
    checked( 1, get_option( 'image_optimization-checkbox' ), true );
?> />
<?php
  }

function compress_jpg_checkbox_display()
  {
?>
<input type="checkbox" name="compress_jpg-checkbox" value="1" <?php
    checked( 1, get_option( 'compress_jpg-checkbox' ), true );
?> />
<?php
  }
function compress_png_checkbox_display()
  {
?>
<input type="checkbox" name="compress_png-checkbox" value="1" <?php
    checked( 1, get_option( 'compress_png-checkbox' ), true );
?> />
<?php
  }
function create_webp_checkbox_display()
  {
?>
<input type="checkbox" name="create_webp-checkbox" value="1" <?php
    checked( 1, get_option( 'create_webp-checkbox' ), true );
?> />
<?php
  }
function jpg_compression_textinput_display()
  {
    //jpg_compression-textinput
    $jpg_compression_value = get_option( 'jpg_compression-textinput' );
?>
<input type="range" id="rangejpg" min="1" max="100" name="jpg_compression-textinput"
    oninput="rangevaluejpg.value = rangejpg.value" value="<?php
    if ( $jpg_compression_value )
      {
        echo $jpg_compression_value;
      }
    else
      {
        echo "85";
      }
?>" />
<output class="rangevalue" id="rangevaluejpg">
    <?php
    if ( $jpg_compression_value )
      {
        echo $jpg_compression_value;
      }
    else
      {
        echo "85";
      }
?>
</output>
<?php
  }
function webp_compression_textinput_display()
  {
    //webp_compression-textinput
    $webp_compression_value = get_option( 'webp_compression-textinput' );
?>
<input type="range" id="rangewebp" min="1" max="100" name="webp_compression-textinput"
    oninput="rangevaluewebp.value = rangewebp.value" value="<?php
    if ( $webp_compression_value )
      {
        echo $webp_compression_value;
      }
    else
      {
        echo "85";
      }
?>" />
<output class="rangevalue" id="rangevaluewebp">
    <?php
    if ( $webp_compression_value )
      {
        echo $webp_compression_value;
      }
    else
      {
        echo "85";
      }
?>
</output>
<?php
  }

// Lazyload stuff
function activate_images_checkbox_display()
  {
?>
<input type="checkbox" name="activate_images-checkbox" value="1" <?php
    checked( 1, get_option( 'activate_images-checkbox' ), true );
?> />
<?php
  }

function activate_bg_images_checkbox_display()
  {
?>
<input type="checkbox" name="activate_bg_images-checkbox" value="1" <?php
    checked( 1, get_option( 'activate_bg_images-checkbox' ), true );
?> />
<?php
  }
function quickified_admin_menu_options()
  {
    if ( !current_user_can( 'manage_options' ) )
      {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
      }
    
?>
<div id="quickified_container" class="wrap">
    <div id="innerpageindex">
        <ul>
            <li class="active navbutton" id="mainpage_overview_button"><a href="#main_overview">Overview</a></li>
            <li class="navbutton" id="mainpage_settings_button"><a href="#settings_main_overview">Settings</a></li>
        </ul>
    </div>
    <div id="main_overview" class="activepage q_pages">
        <div class="innerblockstyle">
            <h1>Quickified</h1>
            <p>Text here.</p>
        </div>
    </div>

    <div id="settings_main_overview" class="q_pages">
        <div class="innerblockstyle">
            <h1>Quickified</h1>
            <h2>Settings</h2>
            <p>All the main functions for Quickified.</p>
            <form method="post" action="options.php">
                <?php
    settings_fields( "set_functions" );
    
    do_settings_sections( "set_functions_section" );
    
    submit_button();
?>
           </form>
        </div>
    </div>
    <div id="quickified_footer" class="innerblockstyle">
        <a href="#" target="_blank" class="madeby">Quickified is made by Quickified Systems™</a>
    </div>
</div>
<?php
  }

//Variable declarations
$minify            = get_option( 'minify-checkbox' );
$security          = get_option( 'security-checkbox' );
$lazyLoad          = get_option( 'lazy_load-checkbox' );
$dbOptimization    = get_option( 'db_optimization-checkbox' );
$fontsOptimization = get_option( 'fonts_optimization-checkbox' );
$imageOptimization = get_option( 'image_optimization-checkbox' );

// Load relevant pages & settings if activated.
if ( $security == 1 )
  {
    //Security is WIP and is not activated unless you activate it here.
    //include_once 'security.php';
  }

if ( $lazyLoad == 1 )
  {
    include_once 'lazyload.php';
  }

if ( $dbOptimization == 1 )
  {
    include_once 'db.php';
  }

if ( $fontsOptimization == 1 )
  {
    include_once 'fonts.php';
  }

if ( $imageOptimization == 1 )
  {
    include_once 'image.php';
  }

function load_db_statics()
  {
    //Enque css & JS
    $screen = get_current_screen();
    if ( stripos( $screen->id, 'optimal_lazy_load_menu' ) !== false )
      {
        wp_enqueue_style( 'db-optimize', plugin_dir_url( __FILE__ ) . 'css/db_optimization.css' );
        wp_enqueue_script( 'db-optimize', plugin_dir_url( __FILE__ ) . 'js/db_optimizations.js', array(
             'jquery' 
        ), '1.0', true );
      }
    if ( stripos( $screen->id, 'security_subpage' ) !== false )
      {
        wp_enqueue_style( 'db-optimize', plugin_dir_url( __FILE__ ) . 'css/db_optimization.css' );
        wp_enqueue_script( 'db-optimize', plugin_dir_url( __FILE__ ) . 'js/db_optimizations.js', array(
             'jquery' 
        ), '1.0', true );
      }
    if ( stripos( $screen->id, 'db_subpage' ) !== false )
      {
        wp_enqueue_style( 'db-optimize', plugin_dir_url( __FILE__ ) . 'css/db_optimization.css' );
        wp_enqueue_script( 'db-optimize', plugin_dir_url( __FILE__ ) . 'js/db_optimizations.js', array(
             'jquery' 
        ), '1.0', true );
      }
    if ( stripos( $screen->id, 'fonts_subpage' ) !== false )
      {
        wp_enqueue_style( 'db-optimize', plugin_dir_url( __FILE__ ) . 'css/db_optimization.css' );
        wp_enqueue_script( 'db-optimize', plugin_dir_url( __FILE__ ) . 'js/db_optimizations.js', array(
             'jquery' 
        ), '1.0', true );
        wp_localize_script( 'ajax-script', 'generate_zip_font_ajax', array(
             'ajax_url' => admin_url( 'admin-ajax.php' ) 
        ) );
      }
    if ( stripos( $screen->id, 'image_subpage' ) !== false )
      {
        wp_enqueue_style( 'db-optimize', plugin_dir_url( __FILE__ ) . 'css/db_optimization.css' );
        wp_enqueue_script( 'db-optimize', plugin_dir_url( __FILE__ ) . 'js/db_optimizations.js', array(
             'jquery' 
        ), '1.0', true );
      }
    if ( stripos( $screen->id, 'lazyload_subpage' ) !== false )
      {
        wp_enqueue_style( 'db-optimize', plugin_dir_url( __FILE__ ) . 'css/db_optimization.css' );
        wp_enqueue_script( 'db-optimize', plugin_dir_url( __FILE__ ) . 'js/db_optimizations.js', array(
             'jquery' 
        ), '1.0', true );
      }
  }
add_action( 'admin_enqueue_scripts', 'load_db_statics' );
