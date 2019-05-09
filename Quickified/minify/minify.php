<?php
defined('ABSPATH') or die('You are not allowed here.');
$dir = 'wp-content/cache/opt_minify';
// Date format: 2019-05-06-13:53:35
$currenttime = current_time('Y-m-d-H:i:s');
$cssfilename = 'wp-content/cache/opt_minify/style-' . $currenttime . '.min.css';

function Load_last()
{
    if ($_SERVER['HTTP_USER_AGENT'] == 'Quickified Page Crawler 1.0') {
        Load_Minify();
    }

}
add_action('wp_loaded', 'Load_last');
function Load_Minify()
{
    $dir = 'wp-content/cache/opt_minify';
    $dirfile = '';
    foreach (glob($dir . '/style-*.min.css') as $filename) {
        $dirfile = $filename;
    }
    if (file_exists($dirfile)) {
        if (!is_admin()) {
            add_action('wp_enqueue_scripts', 'my_assets');
            function my_assets()
            {
                foreach (glob('wp-content/cache/opt_minify/style-*.min.css') as $filename) {
                    $dirfile = '/' . $filename;
                }
                foreach (glob('wp-content/cache/opt_minify/script-*.min.js') as $jsfilename) {
                    $jsdirfile = '/' . $jsfilename;
                }
                wp_enqueue_style('combined-css', $dirfile);
                wp_enqueue_script('combined-js', $jsdirfile, array(), '1.0.0', true);
            }
            add_action('wp_enqueue_scripts', 'remove_all_styles_and_add_styles');
            function remove_all_styles_and_add_styles()
            {
                global $wp_styles;
                $wp_styles->queue = array(
                    'combined-css',
                    'admin-bar',
                );
            }
            add_action('wp_enqueue_scripts', 'ScriptEnqueue', 99999);
            function ScriptEnqueue()
            {
                
                global $wp_styles;
                global $wp_scripts;

                
                if (is_user_logged_in()) {
                    $styles_to_keep = array(
                        'admin-bar',
                        'combined-css',
                        'dashicons',
                        'yoast-seo-adminbar',
                    );
                    $scripts_to_keep = array(
                        'admin-bar',
                        'combined-js',
                    );
                } else {
                    $styles_to_keep = array(
                        'combined-css',
                    );
                    $scripts_to_keep = array(
                        'combined-js',
                    );
                }
                foreach ($wp_styles->registered as $handle => $data) {
                    if (in_array($handle, $styles_to_keep)) {
                        continue;
                    }

                    wp_deregister_style($handle);
                    wp_dequeue_style($handle);
                }
                foreach ($wp_scripts->registered as $handle => $data) {
                    if (!in_array($handle, $scripts_to_keep)) {
                        wp_dequeue_script($handle);
                    }
                }
            }
            add_action('wp_enqueue_scripts', 'remove_all_scripts_and_add_scripts', 9998);
            function remove_all_scripts_and_add_scripts()
            {
                global $wp_scripts;
                $result = array();
                $alldatadepstominfile = 'combined-js';
                foreach ($wp_scripts->queue as $script) {
                    $result['scripts'][] = $wp_scripts->registered[$script]->src;
                }
                $allscripts = $result['scripts'];
                $allregisteredjs = $wp_scripts->registered;
                global $wpdb;
                $table_name = $wpdb->prefix . 'quickified_minify';
                $getthescripts = $wpdb->get_row('SELECT CDATA FROM ' . $table_name, ARRAY_A);
                $savedscriptstoarray = implode('', unserialize(implode('', $getthescripts)));
                $allregisteredjs['combined-js']->extra['data'] = $savedscriptstoarray;
                if ($_SERVER['HTTP_USER_AGENT'] == 'Quickified Page Crawler 1.0') {
                    $strscripts = serialize($allscripts);
                    $checkrows = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                    if ($checkrows == 0) {
                        $wpdb->query($wpdb->prepare("INSERT INTO $table_name(time, the_scripts, id) VALUES(%s, %s, %d)", current_time('mysql'), $strscripts, 1));
                    } else {
                        $wpdb->query($wpdb->prepare("UPDATE $table_name SET `time` = %s, `the_scripts` = %s WHERE `id`=%d", current_time('mysql'), $strscripts, 1));
                    }
                }
            }
        } else {
            add_action('save_post', 'my_save_post_function', 10, 3);
            function my_save_post_function($post_ID, $post, $update)
            {
                foreach (glob($dir . '/style-*.min.css') as $filename) {
                    $dirfile = $filename;
                }
                unlink($dirfile);
            }
        }
    } else {
        if (!is_admin()) {
            function fetch_scripts_and_styles()
            {
                $result = array();
                $result['scripts'] = array();
                $result['scriptsarray'] = array();
                $result['styles'] = array();
                $result['iestyles'] = array();
                global $wp_scripts;
                foreach ($wp_scripts->done as $script) {
                    $result['scriptsdone'][] = $wp_scripts->registered[$script]->src;
                }

                foreach ($wp_scripts->queue as $script) {
                    $result['scripts'][] = $wp_scripts->registered[$script]->src;
                    $result['scriptsarray'][] = $wp_scripts->registered[$script]->extra;
                }
                global $wp_styles;
                foreach ($wp_styles->queue as $style) {
                    $result['iestyles'][] = $wp_styles->registered[$style]->extra;
                    $result['styles'][] = $wp_styles->registered[$style]->src;
                }
                return $result;
            }
            add_action('wp_head', 'process_scripts_and_styles');
            function process_scripts_and_styles()
            {
                $scripts_and_styles = fetch_scripts_and_styles();
                $allscripts = $scripts_and_styles['scripts'];
                $scriptsdone = $scripts_and_styles['scriptsdone'];
                $alldatascripts = $scripts_and_styles['scriptsarray'];
                $allstyles = $scripts_and_styles['styles'];
                $alliestyles = $scripts_and_styles['iestyles'];
                $filterresults = array();
                foreach ($alliestyles as $key => $value) {
                    if (!empty($value)) {
                        $filterresults = array_filter($alliestyles);
                        break;
                    }
                }
                $excludeallcss = array();
                foreach ($allstyles as $allstyle) {
                    $pos1 = strpos($allstyle, 'fonts');
                    if ($pos1 !== false) {
                        array_push($excludeallcss, $allstyle);
                    }
                }
                global $wpdb;
                $table_name = $wpdb->prefix . 'quickified_minify';
                $doestableexist = $wpdb->get_results('SELECT the_scripts FROM ' . $table_name . ' WHERE \'the_scripts\' IS NOT NULL');
                if (!empty($doestableexist)) {
                    $getthescripts = $wpdb->get_row('SELECT the_scripts FROM ' . $table_name . '', ARRAY_A);
                    $savedscriptstoarray = implode('', $getthescripts);
                    $arrayscripts = unserialize($savedscriptstoarray);
                $filteredresults = array_diff_key($allstyles, $filterresults);
                $filteredcss = array_diff($filteredresults, $excludeallcss);
                $findmecss = 'style-' . $currenttime . '.min.css';
                $dir = plugin_dir_path(__FILE__) . 'cssmini/';
                $stylestominify = array();
                foreach ($filteredcss as $styleitem) {
                    $pos1 = stripos($styleitem[0], $findmecss);
                    if ($pos1 === false) {
                        if (strpos($styleitem, "dashicons") === false && strpos($styleitem, "adminbar") === false) {
                            $stylestominify[$styleitem] = file_get_contents($styleitem);
                        }
                    }
                }
                include_once plugin_dir_path(__DIR__) . 'api/apirequestor.php';
                $minicss = processCss($stylestominify);
                $cssfilename = 'wp-content/cache/opt_minify/style-' . $currenttime . '.min.css';
                file_put_contents($cssfilename, $minicss);
                $js_files = array();
                $excludeallthis = array();
                $scripts_to_check = array_merge($allscripts, $arrayscripts);
                $excludearray = array(
                    'wp-includes',
                    'wp-admin',
                    'wp-content/cache/opt_minify/',
                );
                foreach ($scripts_to_check as $newallscript) { 
                    foreach ($excludearray as $excludetoken) {
                        if (stripos($newallscript, 'jquery') === false) {
                            if (stripos($newallscript, $excludetoken) !== false) {
                                array_push($excludeallthis, $newallscript);
                            }
                        }
                    }
                }

                $remove_empty_scriptsdone = array_filter($scriptsdone);
                $remove_empty_arrayscripts = array_filter(empty($arrayscripts) ? $allscripts : $arrayscripts);
                $final_array = array_unique(array_merge($remove_empty_scriptsdone, $remove_empty_arrayscripts));
                $finalresults = array_diff($final_array, $excludeallthis);

                foreach ($finalresults as $finalresult) {
                    if (strpos($finalresult, '/wp-includes') === 0) {
                        $finalresult = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$finalresult";
                    } elseif (strpos($finalresult, '/wp-content') === 0) {
                        $finalresult = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$finalresult";
                    }
                    $js_files[$finalresult] = file_get_contents($finalresult);
                }
                $minijs = processJs($js_files);
                file_put_contents('wp-content/cache/opt_minify/script-' . $currenttime . '.min.js', $minijs);
            }
            if ($_SERVER['HTTP_USER_AGENT'] == 'Quickified Page Crawler 1.0') {
                ob_start();
                add_action('shutdown', function () {
                    $final = '';
                    $levels = ob_get_level();
                    for ($i = 0; $i < $levels; $i++) {
                        $final .= ob_get_clean();
                    }
                    echo apply_filters('final_output', $final);
                }, 0);
                add_filter('final_output', function ($output) {
                    preg_match_all('/<!\[CDATA\[\s*\*\/((?:[^]]|\](?!\]>))*)\/\*\s*\]\]>/', $output, $matches);
                    $AllCDATA = array();
                    $matchcontent = $matches[1];
                    if (!empty($matchcontent)) {

                        foreach ($matchcontent as $match) {
                            $AllCDATA[] = $match;
                        }

                        $serializedData = serialize($AllCDATA);
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'quickified_minify';
                        $doestableexist = $wpdb->get_results('SELECT the_scripts from ' . $table_name . ' WHERE \'the_scripts\' IS NOT NULL');
                        if (!empty($doestableexist)) {
                            $wpdb->query($wpdb->prepare("UPDATE $table_name SET `CDATA` = %s WHERE `id`=%d", $serializedData, 1));
                        }
                    }
                    return $output;
                });
            }
        }
    }
}
