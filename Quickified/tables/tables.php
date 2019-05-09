<?php
defined('ABSPATH') or die('You are not allowed here.');
function createTableOptimizedImages()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "quickified_optimized_images";
    $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                time datetime NOT NULL,
                url text NOT NULL,
                optimized INT NOT NULL,
                original_size INT NOT NULL,
                optimized_size INT NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";
    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    maybe_create_table($table_name, $sql);
}
createTableOptimizedImages();

function createMinifyTables()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . "quickified_minify";
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime NOT NULL,
        the_scripts text NOT NULL,
        CDATA text NOT NULL,
        inline_js text NOT NULL,
        is_inlined tinyint NOT NULL DEFAULT 0,
        is_running tinyint NOT NULL DEFAULT 0,
        PRIMARY KEY  (id)
      ) $charset_collate;";
    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    maybe_create_table($table_name, $sql);
    if($wpdb->get_var("SELECT id FROM $table_name") == 0)
        $wpdb->query($wpdb->prepare("INSERT INTO $table_name(id, is_inlined) VALUES(%d, %d)", 1, 1));
}
createMinifyTables();

function createHtmlPagesTables(){
    global $wpdb;
    $table_name = $wpdb->prefix . "quickified_pages";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			url text NOT NULL,
            pagefile text NOT NULL,
            relativeurl text NOT NULL,
            jsurl text NOT NULL,
            jspagefile text NOT NULL,
			crawled INT NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    maybe_create_table($table_name, $sql);

    $table_name = $wpdb->prefix . "quickified_crawler";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
            num INT NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

    maybe_create_table($table_name, $sql);
    if($wpdb->get_var("SELECT num FROM $table_name") == 0)
    $wpdb->query($wpdb->prepare("INSERT INTO $table_name(num) VALUES(%d)", 1));
}
createHtmlPagesTables();

function createFontsTables(){
    global $wpdb;
    $table_name = $wpdb->prefix . "quickified_fonts";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            filefontname text NOT NULL,
            thefontname text NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    maybe_create_table($table_name, $sql);
}

createFontsTables();

function createSecurityTables(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'quickified_security';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        tid text NOT NULL,
        ip text NOT NULL,
        username text NOT NULL,
        tries_site text NOT NULL,
        tries_login text NOT NULL,
        banned_site text NOT NULL,
        banned_login text NOT NULL,
        remaining_time_site text NOT NULL,
        remaining_time_login text NOT NULL,
        reseturl_site text NOT NULL,
        reseturl_login text NOT NULL,
        wrong_username text NOT NULL,
        UNIQUE KEY id (id)) $charset_collate;";
    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    maybe_create_table($table_name, $sql);
}
createSecurityTables();