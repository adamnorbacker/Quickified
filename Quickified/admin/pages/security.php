<?php
defined('ABSPATH') or die('You are not allowed here.');

function quickified_settings_security_page()
{
    //HTTPS
    add_settings_section("HTTPS", "", null, "security_functions_section");
    add_settings_field("https-checkbox", "Activate HTTPS", "https_checkbox_display", "security_functions_section", "HTTPS");
    register_setting("security_functions", "https-checkbox");
}
add_action("admin_init", "quickified_settings_security_page");

function https_checkbox_display()
{
    ?>
			<input type="checkbox" name="https-checkbox" value="1" <?php checked(1, get_option('https-checkbox'), true);?> />
 <?php
}

function quickified_admin_menu_security_options()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    ?>
        <div id="quickified_container" class="wrap">
<div id="innerpageindex">
<ul>
<li class="active navbutton" id="security_overview_button">Overview</li><li class="navbutton" id="security_settings_button">Settings</li>
</ul>
</div>
<div class="innerblockstyle">
    <h2>Security settings</h2>
    <p>All the security options for Optimal Lazy Load.</p>
    <form method="post" action="options.php">
    <?php
    settings_fields("security_functions");

    do_settings_sections("security_functions_section");

    submit_button();
    ?>
    </form>
</div>
<div id="quickified_footer" class="innerblockstyle"><a href="#" target="_blank" class="madeby">Quickified is made by Quickified Systems™</a></div>
</div>
    <?php
}

function quickified_security()
{
    add_submenu_page('optimal_lazy_load_menu', 'Optimal Lazy Load Security', 'Security', 'administrator', 'security_subpage', 'quickified_admin_menu_security_options');
}
add_action('admin_menu', 'quickified_security');

//Variable declarations
$isHttpsActivated = get_option('https-checkbox');

function setup_https()
{
    if (get_option('setup_https') != '1') {
        require_once __DIR__ . '/../webbx_security/security_setup.php';

        update_option('setup_https', '1');
    }
}

function deactivate_https()
{
    if (get_option('deactivate_https') != '1') {
        require_once __DIR__ . '/../webbx_security/security_setup.php';

        update_option('deactivate_https', '1');
    }
}

if ($isHttpsActivated == 1) {
    delete_option('deactivate_https');
    add_action('admin_init', 'setup_https');
}

if ($isHttpsActivated != 1) {
    delete_option('setup_https');
    add_action('admin_init', 'deactivate_https');
}