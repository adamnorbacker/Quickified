<?php
defined('ABSPATH') or die('You are not allowed here.');
//add_action('admin_init', 'Scan_fonts');
function Scan_fonts($oncleancache = false)
{
    if(get_option('fonts_optimization-checkbox')){
        include_once plugin_dir_path(__DIR__) . 'api/apirequestor.php';
        $wp_content_dir = get_home_path() . "wp-content/";
        $searchDir = new RecursiveDirectoryIterator($wp_content_dir);
        $iterator = new RecursiveIteratorIterator($searchDir);
        $font_search_pattern = new RegexIterator($iterator, '/^.+(.ttf)$/i', RecursiveRegexIterator::GET_MATCH);
        foreach ($font_search_pattern as $name => $font_search_pattern) {
            $fontfamily = getFontFamily($name);
            $path_to_font_file_no_extension = substr($name, 0, strrpos($name, "."));
            $woff2 = file_exists("$path_to_font_file_no_extension.woff2");
            $woff = file_exists("$path_to_font_file_no_extension.woff");
            // $the_fonts_url = strstr($name, '/wp-content');
            // $the_fonts_url_no_extension = strstr($path_to_font_file_no_extension, '/wp-content');
            if (!$oncleancache) {
                if ($woff2 && $woff) {
                    echo "<tr>
                <td data-title='Font Family'>$fontfamily</td>
                <td data-title='Has optimized files'>Yes</td>
                </tr>";
                } else {
                    echo "<tr>
                <td data-title='Font Family'>$fontfamily</td>
                <td data-title='Has optimized files'>No</td>
                </tr>";
                }
            } else {
                if (!$woff2) {
                    processFont($name, 'woff2');
                }
                if (!$woff) {
                    processFont($name, 'woff');
                }
            }
        }
        if($oncleancache)
            logError("Genererat fonts");
    }
    return true;
}

add_action('wp_ajax_Get_Zipped_fonts', 'Get_Zipped_fonts');
function Get_Zipped_fonts()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "quickified_fonts";
    $table_id = $_POST['font_id'];
    $font_files = $wpdb->get_var("SELECT filefontname FROM $table_name WHERE ID = $table_id");
    if ($font_files) {
        $path_to_font_file_no_extension = substr($font_files, 0, strrpos($font_files, "."));
        $font_filename = pathinfo($path_to_font_file_no_extension)['basename'];
        $the_zip_url = wp_upload_dir()['baseurl'] . "/fonts/$font_filename.zip";
        if (!file_exists("$path_to_font_file_no_extension.zip")) {
            $generate_zip = Generate_zip("$font_filename.ttf", "$font_filename.woff", "$font_filename.woff2");
            if ($generate_zip) {
                echo $the_zip_url;
            } else {
                logError("Error generating zip for $font_filename");
            }
        } else {
            echo $the_zip_url;
        }
    }
    wp_die();
}

add_action('wp_ajax_Get_Generated_Font_css', 'Get_Generated_Font_css');
function Get_Generated_Font_css()
{

    global $wpdb;
    $table_name = $wpdb->prefix . "quickified_fonts";
    $table_id = $_POST['font_id'];
    $font_files = $wpdb->get_results("SELECT filefontname,thefontname FROM $table_name WHERE ID = $table_id");

    if ($font_files) {
        foreach ($font_files as $font) {
            $path_to_font_file_no_extension = substr($font->filefontname, 0, strrpos($font->filefontname, "."));
            $font_filename = pathinfo($path_to_font_file_no_extension)['basename'];
            $generate_css = Generate_css("modules", "/var/www/html/wp-content/uploads/fonts/15c18dd38dabfa.ttf");
            if ($generate_css) {
                echo $generate_css;
            }
        }

    }
    wp_die();
}

function Generate_zip($ttf, $woff, $woff2)
{
    $zip = new ZipArchive();
    $path_to_font_file_no_extension = substr($ttf, 0, strrpos($ttf, "."));
    $wp_uploadsurl = wp_upload_dir()['basedir'] . '/fonts/';
    $filename = $wp_uploadsurl . "$path_to_font_file_no_extension.zip";
    if ($zip->open($filename, ZipArchive::CREATE) !== true) {
        logError("cannot create <$filename>\n");
        $zip->close();
        return false;
        exit();
    }
    $zip->addFile($wp_uploadsurl . $ttf, $ttf);
    $zip->addFile($wp_uploadsurl . $woff, $woff);
    $zip->addFile($wp_uploadsurl . $woff2, $woff2);
    $zip->close();
    return true;
}

function Generate_css($fontname, $fontfilename)
{
    if (empty($fontname) || empty($fontfilename)) {
        return false;
    }
    $path_to_font_file_no_extension = substr($fontfilename, 0, strrpos($fontfilename, "."));
    $fontfilename = pathinfo($path_to_font_file_no_extension)['basename'];
    $the_fonts_url = wp_upload_dir()['baseurl'] . "/fonts/$fontfilename";
    $the_fonts_url = strstr($the_fonts_url, '/wp-content');
    $generated_css = "@font-face {
        font-family: '$fontname';
        src: local('$fontname'),
                url('$the_fonts_url.woff2') format('woff2'),
                url('$the_fonts_url.woff') format('woff'),
                url('$the_fonts_url.ttf') format('truetype');
        font-weight: 400;
        font-display: swap;
        font-style: normal;
    }";
    return $generated_css;
}

function Generate_fonts($path_to_font_file)
{
    include_once plugin_dir_path(__DIR__) . 'api/apirequestor.php';
    $fontfamily = getFontFamily($path_to_font_file);
    $path_to_font_file_no_extension = substr($path_to_font_file, 0, strrpos($path_to_font_file, "."));

    include_once plugin_dir_path(__DIR__) . 'api/apirequestor.php';
    processFont($path_to_font_file);
    $compressed_woff_files = file_exists("$path_to_font_file_no_extension.woff");
    $compressed_woff2_files = file_exists("$path_to_font_file_no_extension.woff2");
    if (!$compressed_woff_files) {
        logError('Failed to generate woff fonts.');
    }
    if (!$compressed_woff2_files) {
        logError('Failed to generate woff2 fonts.');
    }
    if ($compressed_woff2_files && $compressed_woff_files) {
        Insert_fonts($path_to_font_file, $fontfamily);
    }
}

function Insert_fonts($filefontname, $thefontname)
{
    global $wpdb;
    $table_name = $wpdb->prefix . "quickified_fonts";
    $time_zone = get_option('timezone_string');
    date_default_timezone_set($time_zone);
    //Date format: 2019-05-25 11:35:07
    $date = date('Y-m-d H:i:s');
    $charset_collate = $wpdb->get_charset_collate();

    $wpdb->query($wpdb->prepare("INSERT INTO $table_name(time, filefontname, thefontname) VALUES(%s, %s, %s)", $date, $filefontname, $thefontname));
}
