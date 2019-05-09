<?php
defined('ABSPATH') or die('You are not allowed here.');

function quickified_admin_menu_lazyload_options()
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
    <h2>LazyLoad settings</h2>
    <p>All the LazyLoad options for Quickified.</p>
    <form method="post" action="options.php">
    <?php
    settings_fields("security_functions");

    do_settings_sections("set_functions_lazyload_section");

    submit_button();
    ?>
    </form>
</div>
<div id="quickified_footer" class="innerblockstyle"><a href="#" target="_blank" class="madeby">Quickified is made by Quickified Systems™</a></div>
</div>
    <?php
}

function quickified_lazyload()
{
    add_submenu_page('optimal_lazy_load_menu', 'Quickified Lazyload', 'Lazyload', 'administrator', 'lazyload_subpage', 'quickified_admin_menu_lazyload_options');
}
add_action('admin_menu', 'quickified_lazyload');