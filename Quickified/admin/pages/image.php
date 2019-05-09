<?php
defined('ABSPATH') or die('You are not allowed here.');
error_reporting(E_ALL);
ini_set('display_errors', 1);

function Quickified_Admin_Menu_Image_options()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }


    ?>
<!-- Overview tab -->
<div id="quickified_container" class="wrap">
<div id="innerpageindex">
<ul>
<li class="active navbutton" id="image_overview_button"><a href="#image_overview">Overview</a></li><li class="navbutton" id="image_uploaded_image_button"><a href="#settings_image_tab">Image Settings</a></li><li class="navbutton" id="image_image_scanner_button"><a href="#image_scanner_tab">Image Scanner</a></li>
</ul>
</div>
<div id="image_overview" class="activepage q_pages">
<div class="innerblockstyle">
    <h1>Image Optimization overview</h1>
    <p>Quickified optimizes your images automatically when you upload them to your media library, it uses advanced optimization algorithms to make sure the image looks good while also loading really quick!</p>
</div>
</div>
<!-- Settings image tab -->
<div id="settings_image_tab" class="q_pages">
<div class="innerblockstyle">
<h1>Image Settings</h1>
    <p>Quckified automatically sets the best settings for you, but if you want to have more controll, then you can change everything here.</p>
    <form method="post" action="options.php">
    <?php
settings_fields("set_functions_images");

    do_settings_sections("set_functions_images_section");

    submit_button();
    ?>
    </form>
</div>
</div>
<!-- Bulk optimize image tab -->
<div id="image_scanner_tab" class="q_pages">
<div class="innerblockstyle">
<div id="image_scanner_descriptor">
<h1>Image Bulk Optimizer</h1>
<p>Image Bulk Optimizer scans your whole front-end for image files and looks if it can optimize any of them. It will then do the neccesary changes so other users benefit of it.</p>
<p>As you might have already noticed, Quickifed does everything for you. No work is needed on your end. Now, have a cup of coffee and enjoy the time you saved!</p>
<?php
global $wpdb;
    $optimized_images = array();
    $table_name = $wpdb->prefix . "quickified_optimized_images";
    $getUrl = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    foreach ($getUrl as $row) {
        $optimized_images[] = $row['url'];
    }
    $query_images_args = array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
    );

    $query_images = new WP_Query($query_images_args);

    $images = array();
    $images_path = array();
    $images_original_size = array();
    $images_optimized_size = array();

    foreach ($query_images->posts as $image) {
        $attachment_url = wp_get_attachment_url($image->ID);
        $attachment_parse = parse_url($attachment_url, PHP_URL_PATH);
        $attachment_path = $_SERVER['DOCUMENT_ROOT'] . $attachment_parse;
        $attachment_title = get_the_title($image->ID);
        $images_path[] = $attachment_path;
    }
    $unoptimized_images = array_diff($images_path, $optimized_images);
    $Semi_Final_optimized_images = array_intersect($images_path, $optimized_images);
    $Final_optimized_images = array();
    foreach ($unoptimized_images as $image) {
        $name = basename($image);
        $images[] = "<tr>
        <td data-title='Image Name'>$name</td>
        <td data-title='Is Optimized'>No</td>
        <td data-title='Saved Size'>0%</td>
        </tr>";
    }
    foreach ($Semi_Final_optimized_images as $insert_other_keys) {
        $getFullRow = $wpdb->get_results("SELECT * FROM $table_name WHERE url LIKE '%$insert_other_keys%'", ARRAY_A);
        foreach ($getFullRow as $row) {
            $Final_optimized_images[] = $row;
        }
    }

    function getSavedSize($originalSize, $optimizedSize)
    {
        $differenceValue = $originalSize - $optimizedSize;

        @$calculatedvalue = ($differenceValue / $originalSize) * 100;
        $finalvalue = (int) number_format($calculatedvalue, 0);
        if (is_nan($finalvalue)) {
            return "0";
        } else {
            return $finalvalue;
        }
    }

    foreach ($Final_optimized_images as $image) {
        $name = basename($image['url']);
        $compressedSize = getSavedSize($image['original_size'], $image['optimized_size']);
        $images_original_size[] = $image['original_size'];
        $images_optimized_size[] = $image['optimized_size'];
        $images[] = "<tr>
        <td data-title='Image Name'>$name</td>
        <td data-title='Is Optimized'>Yes</td>
        <td data-title='Saved Size'>$compressedSize%</td>
        </tr>";
    }
    $optimizedSize = getSavedSize(array_sum($images_original_size), array_sum($images_optimized_size));

    ?>
</div>
<div class="table-responsive-vertical shadow-z-1">
<table id="table" class="table table-hover table-mc-light-blue">
<thead>
<tr>
<th>Image Name</th>
<th>Is Optimized</th>
<th>Saved Size</th>
</tr>
</thead>
<tbody>
<?php
foreach ($images as $image) {
        echo $image;
    }
    ?>
</tbody>
</table>
</div>
<div><p id="savedimagesize">Quickified has reduced your images with <?php echo $optimizedSize; ?>%!</p></div>
<a id="optimize_images_button" class="quickified_buttons" href="#">Optimize Images</a>
</div>
</div>
<div id="quickified_footer" class="innerblockstyle"><a href="#" target="_blank" class="madeby">Quickified is made by Quickified Systems™</a></div>
</div>
    <?php

}

function Quickified_image()
{
    add_submenu_page('optimal_lazy_load_menu', 'Optimal Lazy Load Image', 'Image', 'administrator', 'image_subpage', 'Quickified_Admin_Menu_Image_options');
}
add_action('admin_menu', 'Quickified_image');

//Variable declarations
// $isFontsActivated = get_option('images-checkbox');
// if ($isFontsActivated == 1) {
//     delete_option('deactivate_images');
//     add_action('admin_init', 'setup_images');
// }

// if ($isFontsActivated != 1) {
//     delete_option('setup_images');
//     add_action('admin_init', 'deactivate_images');
// }