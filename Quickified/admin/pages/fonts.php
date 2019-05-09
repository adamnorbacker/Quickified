<?php
defined('ABSPATH') or die('You are not allowed here.');
require_once __DIR__ . '/../font_optimization/fonts.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
$wp_fontsurl = wp_upload_dir()['basedir'] . '/fonts/';
if (!file_exists($wp_fontsurl)) {
    mkdir($wp_fontsurl, 0755);
}
function Quickified_Settings_Fonts_page()
{
    //Fonts
    add_settings_section("Fonts", "", null, "fonts_functions_section");
    add_settings_field("fonts-checkbox", "Upload font: ", "Fonts_Checkbox_display", "fonts_functions_section", "Fonts");
    register_setting("fonts_functions", "fonts-checkbox", "Handle_Font_upload");
}
add_action("admin_init", "Quickified_Settings_Fonts_page");

function Fonts_Checkbox_display()
{
    ?>
    <input type="file" name="font-file" accept=".ttf" />
    <?php echo get_option('font-file'); ?>
    <?php
}

function Handle_Font_upload($option)
{
    if (!empty($_FILES['font-file']['tmp_name'])) {
        $file = $_FILES['font-file']['name'];

        $checkextension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($checkextension === 'ttf') {
            $wp_uploadsurl = wp_upload_dir()['basedir'];
            $newFontName = uniqid(true);
            $target = $wp_uploadsurl . "/fonts/$newFontName.$checkextension";
            $uploadfont = move_uploaded_file($_FILES['font-file']['tmp_name'], $target);
            if ($uploadfont) {
                Generate_fonts($target);
            }
        } else {
            logError("Uploaded wrong format for file $file, the extension is supposed to be .ttf");
        }
    }

    return $option;
}

function Quickified_Admin_Menu_Fonts_options()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    ?>
<!-- Overview tab -->
<div id="quickified_container" class="wrap">
<div id="innerpageindex">
<ul>
<li class="active navbutton" id="fonts_overview_button"><a href="#font_overview">Overview</a></li><li class="navbutton" id="fonts_uploaded_fonts_button"><a href="#uploaded_fonts_tab">Uploaded Fonts</a></li><li class="navbutton" id="fonts_font_scanner_button"><a href="#font_scanner_tab">Font Scanner</a></li>
</ul>
</div>
<div id="font_overview" class="activepage q_pages">
<div class="innerblockstyle">
    <h2>Fonts overview</h2>
    <p>Upload your fonts here to optimize them, The uploaded fonts will be available in the "uploaded fonts" tab.</p>
    <form method="post" enctype="multipart/form-data" action="options.php">
    <?php
settings_fields("fonts_functions");
    do_settings_sections("fonts_functions_section");
    submit_button();
    ?>
    </form>
</div>
</div>
<!-- Uploaded fonts tab -->
<div id="uploaded_fonts_tab" class="q_pages">
<div class="innerblockstyle">
<div class="table-responsive-vertical shadow-z-1">
<table id="table" class="table table-hover table-mc-light-blue">
<thead>
<tr>
<th>ID</th>
<th>Font name</th>
<th>Download/Generate CSS</th>
</tr>
</thead>
<tbody>
    <?php
global $wpdb;
    $table_name = $wpdb->prefix . "quickified_fonts";
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = $wpdb->get_results("SELECT * FROM $table_name");
    foreach ($sql as $row) {

        ?>
        <tr>
        <td data-title="ID"><?php echo $row->id; ?></td>
        <td data-title="Font name"><?php echo $row->thefontname ?></td>
        <td data-title="Download/Generate CSS"><div class="dropdowncontainer"><div class="text">Download/Generate CSS</div><ul class="dropdownlist"><li data-id="<?php echo $row->id; ?>" class="generate_css">Generate CSS</li><li data-id="<?php echo $row->id; ?>" class="download_files">Download files</li></ul></div></td>
        </tr>
        <?php
}
    $conn->close();?>
</tbody>
</table>
</div>
</div>
</div>
<!-- Bulk optimize fonts tab -->
<div id="font_scanner_tab" class="q_pages">
<div class="innerblockstyle">
<div id="font_scanner_descriptor">
<h1>Font Scanner</h1>
<p>Font Scanner scans your whole front-end for font files and looks if it can optimize any of them. It will then do the neccesary changes so other users benefit of it.</p>
<p>As you might have already noticed, Quickifed does everything for you. No work is needed on your end. Now, have a cup of coffee and enjoy the time you saved!</p>
</div>
<div class="table-responsive-vertical shadow-z-1">
<table id="table" class="table table-hover table-mc-light-blue">
<thead>
<tr>
<th>Font Family</th>
<th>Has optimized files</th>
</tr>
</thead>
<tbody>
<?php
Scan_fonts();
    ?>
</tbody>
</table>
</div>
<a id="optimize_fonts_button" class="quickified_buttons" href="#">Optimize Fonts</a>
</div>
</div>
<div id="popup_generated_css"><div class="container"><pre id="generated_css_content"></pre><span id="close_gen_css" class="dashicons dashicons-no-alt"></span></div></div>
<div id="quickified_footer" class="innerblockstyle"><a href="#" target="_blank" class="madeby">Quickified is made by Quickified Systems™</a></div>
</div>
    <?php

}

function Quickified_fonts()
{
    add_submenu_page('optimal_lazy_load_menu', 'Optimal Lazy Load Fonts', 'Fonts', 'administrator', 'fonts_subpage', 'Quickified_Admin_Menu_Fonts_options');
}
add_action('admin_menu', 'Quickified_fonts');

//Variable declarations
// $isFontsActivated = get_option('fonts-checkbox');
// if ($isFontsActivated == 1) {
//     delete_option('deactivate_fonts');
//     add_action('admin_init', 'setup_fonts');
// }

// if ($isFontsActivated != 1) {
//     delete_option('setup_fonts');
//     add_action('admin_init', 'deactivate_fonts');
// }