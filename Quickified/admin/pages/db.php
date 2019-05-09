<?php
defined('ABSPATH') or die('You are not allowed here.');

//Variable declarations
$Is_Db_activated = get_option('db-checkbox');
$Is_Revisions_activated = get_option('db-checkbox-revisions');
$Is_Trash_activated = get_option('db-checkbox-trash');
$Is_Draft_activated = get_option('db-checkbox-draft');
$Is_Spam_activated = get_option('db-checkbox-spam');
$Is_Orphans_activated = get_option('db-checkbox-orphans');
$Is_optimize_tables_activated = get_option('db-checkbox-optimizeTables');
$Is_optimize_All_tables_activated = get_option('db-checkbox-optimizeAllTables');
if ($Is_Db_activated == 1) {
    add_action('scheduleOptimizeTables', 'optimizeTables');
}
add_action('wp_ajax_optimizeTables', 'optimizeTables');

register_activation_hook(__FILE__, 'activateScheduleDBOptimize');
function activateScheduleDBOptimize()
{
    if (! wp_next_scheduled('scheduleOptimizeTables')) {
        wp_schedule_event(time(), 'daily', 'scheduleOptimizeTables');
    }
}



function optimizeTables()
{
    global $wpdb;
    $posts = $wpdb->prefix . "posts";
    $postmeta = $wpdb->prefix . "postmeta";
    $comments = $wpdb->prefix . "comments";
    $term_relationships = $wpdb->prefix . "term_relationships";
    $term_taxonomy = $wpdb->prefix . "term_taxonomy";
    $Is_Db_activated = get_option('db-checkbox');
    $Is_Revisions_activated = get_option('db-checkbox-revisions');
    $Is_Trash_activated = get_option('db-checkbox-trash');
    $Is_Draft_activated = get_option('db-checkbox-draft');
    $Is_Spam_activated = get_option('db-checkbox-spam');
    $Is_Orphans_activated = get_option('db-checkbox-orphans');
    $Is_optimize_tables_activated = get_option('db-checkbox-optimizeTables');
    $Is_optimize_All_tables_activated = get_option('db-checkbox-optimizeAllTables');
    if ($Is_Db_activated == 1) {
        if ($Is_Revisions_activated == 1) {
            $revisionsql = $wpdb->get_results("SELECT * FROM $posts WHERE post_type = 'revision'");
            if ($revisionsql) {
                $delete = $wpdb->query("DELETE FROM $posts WHERE post_type = 'revision'");
                if ($delete) {
                    //print_r("Revision delete succeded");
                }
            }
        }
        if ($Is_Trash_activated == 1) {
            $trashsql = $wpdb->get_results("SELECT * FROM $posts WHERE post_status = 'trash'");
            if ($trashsql) {
                $delete = $wpdb->query("DELETE FROM $posts WHERE post_status = 'trash'");
                if ($delete) {
                    //print_r("Trash delete succeded");
                }
            }
        }
        if ($Is_Draft_activated == 1) {
            $autodraftsql = $wpdb->get_results("SELECT * FROM $posts WHERE post_status = 'auto-draft'");
            if ($autodraftsql) {
                $delete = $wpdb->query("DELETE FROM $posts WHERE post_status = 'auto-draft'");
                if ($delete) {
                    //print_r("Auto-draft delete succeded");
                }
            }
        }
        if ($Is_Spam_activated == 1) {
            $spamsql = $wpdb->get_results("SELECT * FROM $comments WHERE comment_approved = 'spam'");
            if ($spamsql) {
                $delete = $wpdb->query("DELETE FROM $comments WHERE comment_approved = 'spam'");
                if ($delete) {
                    //print_r("Spam delete succeded");
                }
            }
        }
        if ($Is_Orphans_activated == 1) {
            $deleteOrphans = $wpdb->query("DELETE meta FROM $postmeta meta LEFT JOIN $posts nullpost ON nullpost.ID = meta.post_id WHERE nullpost.ID IS NULL");
            if ($deleteOrphans) {
                //print_r("Orphans delete succeded");
            }
        }
        if ($Is_optimize_tables_activated == 1) {
            $optimizeTables = $wpdb->get_col("SHOW TABLES");
            foreach ($optimizeTables as $table) {
                if ($Is_optimize_All_tables_activated == 0) {
                    if (strpos($table, $wpdb->prefix) !== false) {
                        $optimize = $wpdb->query("OPTIMIZE TABLE $table");
                        if ($optimize) {
                            //print_r("$table is optimized.");
                        }
                    }
                } elseif ($Is_optimize_All_tables_activated == 1) {
                    $optimize = $wpdb->query("OPTIMIZE TABLE $table");
                    if ($optimize) {
                        //print_r("$table is optimized.");
                    }
                }
            }
        }
    }
    wp_die();
}

function quickified_settings_db_page()
{
    //Activate DB optimizations
    add_settings_section("db", "", null, "db_functions_section");
    add_settings_field("db-checkbox", "Activate DB Optimizations", "db_checkbox_display", "db_functions_section", "db");
    register_setting("db_functions", "db-checkbox");
}
add_action("admin_init", "quickified_settings_db_page");
function quickified_settings_db_page_checkboxes()
{
    $Is_Db_activated = get_option('db-checkbox');
    $Is_optimize_tables_activated = get_option('db-checkbox-optimizeTables');
    //Activate DB revisions
    if ($Is_Db_activated == 1) {
        add_settings_section("db", "", null, "db_functions_inner_section");
        add_settings_field("db-checkbox-revisions", "Remove/Optimize Revisions", "db_checkbox_revisions_display", "db_functions_inner_section", "db");
        register_setting("db_functions_inner", "db-checkbox-revisions");
        //Activate DB trash
        add_settings_field("db-checkbox-trash", "Remove/Optimize trash", "db_checkbox_trash_display", "db_functions_inner_section", "db");
        register_setting("db_functions_inner", "db-checkbox-trash");
        //Activate DB auto-drafs
        add_settings_field("db-checkbox-draft", "Remove/Optimize auto-drafts", "db_checkbox_draft_display", "db_functions_inner_section", "db");
        register_setting("db_functions_inner", "db-checkbox-draft");
        //Activate DB spam
        add_settings_field("db-checkbox-spam", "Remove/Optimize spam", "db_checkbox_spam_display", "db_functions_inner_section", "db");
        register_setting("db_functions_inner", "db-checkbox-spam");
        //Activate DB orphans
        add_settings_field("db-checkbox-orphans", "Remove/Optimize orphans", "db_checkbox_orphans_display", "db_functions_inner_section", "db");
        register_setting("db_functions_inner", "db-checkbox-orphans");
        //Activate DB Optimize-tables
        add_settings_field("db-checkbox-optimizeTables", "Optimize tables", "db_checkbox_optimizeTables_display", "db_functions_inner_section", "db");
        register_setting("db_functions_inner", "db-checkbox-optimizeTables");
        //Activate DB optimizeAllTables
        if ($Is_optimize_tables_activated == 1) {
            add_settings_field("db-checkbox-optimizeAllTables", "Optimize all the tables", "db_checkbox_optimizeAllTables_display", "db_functions_inner_section", "db");
            register_setting("db_functions_inner", "db-checkbox-optimizeAllTables");
        }
    }
}
add_action("admin_init", "quickified_settings_db_page_checkboxes");


function db_checkbox_display()
{
    ?>
<input id="enable_db_opt" type="checkbox" name="db-checkbox" value="1" <?php checked(1, get_option('db-checkbox'), true); ?> />
    <?php
}
function db_checkbox_revisions_display()
{
    ?>
<input type="checkbox" name="db-checkbox-revisions" value="1" <?php checked(1, get_option('db-checkbox-revisions'), true); ?> />
    <?php
}
function db_checkbox_trash_display()
{
    ?>
<input type="checkbox" name="db-checkbox-trash" value="1" <?php checked(1, get_option('db-checkbox-trash'), true); ?> />
    <?php
}
function db_checkbox_draft_display()
{
    ?>
<input type="checkbox" name="db-checkbox-draft" value="1" <?php checked(1, get_option('db-checkbox-draft'), true); ?> />
    <?php
}
function db_checkbox_spam_display()
{
    ?>
<input type="checkbox" name="db-checkbox-spam" value="1" <?php checked(1, get_option('db-checkbox-spam'), true); ?> />
    <?php
}
function db_checkbox_orphans_display()
{
    ?>
<input type="checkbox" name="db-checkbox-orphans" value="1" <?php checked(1, get_option('db-checkbox-orphans'), true); ?> />
    <?php
}
function db_checkbox_optimizeTables_display()
{
    ?>
<input type="checkbox" name="db-checkbox-optimizeTables" value="1" <?php checked(1, get_option('db-checkbox-optimizeTables'), true); ?> />
    <?php
}
function db_checkbox_optimizeAllTables_display()
{
    ?>
<input type="checkbox" name="db-checkbox-optimizeAllTables" value="1" <?php checked(1, get_option('db-checkbox-optimizeAllTables'), true); ?> />
    <?php
}

function quickified_admin_menu_db_options()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    $Is_Db_activated = get_option('db-checkbox'); ?>
<div id="quickified_container" class="wrap">
<div id="innerpageindex">
<ul>
<li class="active navbutton" id="db_overview_button"><a href="#db_optimization">Overview</a></li><li class="navbutton" id="db_settings_button"><a href="#db_settingsPage">Settings</a></li>
</ul>
</div>
<div id="db_optimization" class="activepage q_pages">
<div class="table-responsive-vertical shadow-z-1">
<table id="table" class="table table-hover table-mc-light-blue">
<thead>
<tr>
<th>ID</th>
<th>Table name</th>
<th>Size</th>
</tr>
</thead>
<tbody>
    <?php
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT
table_schema as `Database`,
table_name AS `Table`,
round(((data_length + index_length) / 1024 / 1024), 2) `Size in MB`
FROM information_schema.TABLES
ORDER BY (data_length + index_length) DESC;";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $sumMB = 0;
        $id = 1;
        global $wpdb;

        while ($rows = $result->fetch_assoc()) {
            if ($rows['Database'] !== 'information_schema') {
                if (stripos($rows['Table'], $wpdb->prefix) !== false) {
                    ?>
<tr>
<td data-title="ID"><?php echo $id++; ?></td>
<td data-title="table name"><?php echo $rows['Table']; ?></td>
<td data-title="size"><?php echo $rows['Size in MB'] . 'MB'; ?></td>
</tr>
                        <?php
                        $sumMB += $rows['Size in MB'];
                }
            }
        }
    } else {
        echo "0 results";
    }
    $conn->close(); ?>
</tbody>
</table>
</div>
<div id="db_result_table" class="table-responsive-vertical shadow-z-1">
<table id="table" class="table table-hover table-mc-light-blue">
<thead>
<th class="result">Total Size:</th>
</thead>
<div>
<tbody>
<tr>
<td class="result" data-title="Total Size:"><?php echo $sumMB . 'MB'; ?></td>
</tr>
</tbody>
</div>
</table>
<a id="optimize_db_button" class="quickified_buttons" href="#">Optimize DB</a>
</div>
</div>
<div id="db_settingsPage" class="q_pages">
    <div class="innerblockstyle">
    <h1>DB Settings</h1>
    <p>Enable the settings you need by clicking on the checkbox.</p>
    <form method="post" action="options.php">
    <?php
    settings_fields("db_functions");
    do_settings_sections("db_functions_section");
    settings_fields("db_functions_inner");
    do_settings_sections("db_functions_inner_section");
    submit_button(); ?>
    </form>
    </div>
</div>
<div id="quickified_footer" class="innerblockstyle"><a href="#" target="_blank" class="madeby">Quickified is made by Quickified Systems™</a></div>
</div>
    <?php
}

add_action('wp_ajax_optimizeImages', 'optimizeImages');
function optimizeImages()
{
    bulkOptimizeImages();
}

add_action('wp_ajax_optimizeFonts', 'optimizeFonts');
function optimizeFonts()
{
    if(Scan_fonts(true)){
        echo "Fonts have been optimized!";
    } else {
        echo "Fonts have not been optimized!";
    }
    wp_die();
}

function quickified_db()
{
    add_submenu_page('optimal_lazy_load_menu', 'Optimal Lazy Load db', 'DB Optimizations', 'administrator', 'db_subpage', 'quickified_admin_menu_db_options');
}
add_action('admin_menu', 'quickified_db');
