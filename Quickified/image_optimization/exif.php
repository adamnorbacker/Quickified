<?php
defined('ABSPATH') or die('You are not allowed here.');
//TODO Kolla extensions för bulkoptimerare
//Ta bort "optimerad" fil ifall den är större än originalet...

function bulkOptimizeImages()
{
    include_once plugin_dir_path(__DIR__).'api/apirequestor.php';
    global $wpdb;
    $table_name = $wpdb->prefix . "quickified_optimized_images";
    $upload_dir = wp_upload_dir()['basedir'];
    $all_upload_files = listFolderFiles($upload_dir);
    $fail = false;
    $optimized = false;
    foreach ($all_upload_files as $file) {
        if (is_file($file) && @exif_imagetype($file) !== false) {
            $isOptimized = $wpdb->get_var("SELECT optimized FROM $table_name WHERE url = '$file'");
            if ($isOptimized != 1) {
                if(!processImage($file)){
                    logError("Error processing $file");
                    $fail = true;
                } else {
                    clearstatcache();
                    $optimized = true;
                }
            }
        }
    }
    if($fail){
        echo 'An error occured while optimizing images!';
    } else {
        echo $optimized ? 'Images have been optimized!' : 'No images needed to be optimized!';
    }
    wp_die();
}

// Default quality for jpeg images
function jpeg_quality()
{
    return 70;
}
add_filter('jpeg_quality', 'jpeg_quality');

function listFolderFiles($dir)
{
    $files = dir_scan($dir . '/*');
    return $files;
}
function dir_scan($folder)
{
    $files = glob($folder);
    foreach ($files as $f) {
        if (is_dir($f)) {
            $files = array_merge($files, dir_scan($f . '/*')); // scan subfolder
        }
    }
    return $files;
}

//svg
function cc_mime_types($mimes)
{
    $new_filetypes = array();
    $new_filetypes['svg'] = 'image/svg+xml';
    $mimes = array_merge($mimes, $new_filetypes);

    return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');

function custom_svg_thumb()
{
    $css = '';

    $css = '.wp-core-ui .attachment .thumbnail img[src$=".svg"] { width: 100% !important; height: auto !important; }';

    echo '<style type="text/css">' . $css . '</style>';
}
add_action('admin_head', 'custom_svg_thumb');

function ignore_upload_ext($checked, $file, $filename, $mimes)
{
    if (!$checked['type']) {
        $wp_filetype = wp_check_filetype($filename, $mimes);
        $ext = $wp_filetype['ext'];
        $type = $wp_filetype['type'];
        $proper_filename = $filename;
        if ($type && 0 === strpos($type, 'image/') && $ext !== 'svg') {
            $ext = $type = false;
        }

        $checked = compact('ext', 'type', 'proper_filename');
    }

    return $checked;
}
add_filter('wp_check_filetype_and_ext', 'ignore_upload_ext', 10, 4);
