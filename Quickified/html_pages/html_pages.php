<?php
defined('ABSPATH') or die('You are not allowed here.');

function insert_pages($url, $relativeurl, $jsurl, $jspagefile, $pagefile = "test")
{
    global $wpdb;
    $table_name = $wpdb->prefix . "quickified_pages";
    if (!empty($url)) {
        $time_zone = get_option('timezone_string');
        date_default_timezone_set($time_zone);
        //Date format: 2019-05-25 11:35:07
        $date = date('Y-m-d H:i:s');
        $charset_collate = $wpdb->get_charset_collate();

        $wpdb->query($wpdb->prepare("INSERT INTO $table_name(time, url, pagefile, relativeurl, jsurl, jspagefile, crawled) VALUES(%s, %s, %s, %s, %s, %s, %d)", $date, $url, $pagefile, $relativeurl, $jsurl, $jspagefile, 1));
    }
}

function start_crawler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "quickified_crawler";

    if($wpdb->get_var("SELECT COUNT(*) FROM $table_name") == 0){
        $wpdb->query($wpdb->prepare("INSERT INTO $table_name(num) VALUES(%d)", 1));
        return true;
    }
    return false;
}

add_action('shutdown', 'crawlPages');
function crawlPages()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "quickified_pages";
    if(start_crawler()){
        $pages = get_pages();
        $numPages = count($pages);

        if (!empty($numPages)) {
            for ($i = 0; $i < $numPages; $i++) {
                $page = $pages[$i];
                $pageurl = get_page_link($page->ID);
                $checklink = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE url = '$pageurl'");
                $crawled = $wpdb->get_var("SELECT crawled FROM $table_name WHERE url = '$pageurl'");

                //Börja med insert ifall inga pages är inlagda.
                if ($checklink == 0) {
                    wp_remote_get("$pageurl", array('user-agent' => 'Quickified Page Crawler 1.0', 'timeout' => 10+$i, 'sslverify' => false, 'cookies' => array(new WP_Http_Cookie(array('name' => 'html_crawl', 'value' => 'asdasd')))));
                }
            }
        }
    }
}

getPages();
function getPages()
{

    //Kolla först ifall det kommer från crawlern...
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    if ($userAgent == 'Quickified Page Crawler 1.0' && isset($_COOKIE['html_crawl'])) {
        function sc_callback($data)
        {
            if(!isset($GLOBALS['final_html']))
                $GLOBALS['final_html'] = '';
            $GLOBALS['final_html'] .= $data;
            return $data;
        }
        function sc_buffer_start()
        {
            ob_start('sc_callback');
        }
        function sc_buffer_end()
        {
            if (ob_get_length()) {
                ob_end_clean();
            }

            $output = $GLOBALS['final_html'];
            $time_zone = get_option('timezone_string');
            date_default_timezone_set($time_zone);
            //Date format: 201905061138
            $date = date('YmdHi');
            $currenturl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            if(empty($output)){
                logError("Empty, trying again: $currenturl");
                wp_remote_get("$currenturl", array('user-agent' => 'Quickified Page Crawler 1.0', 'timeout' => 10, 'sslverify' => false, 'cookies' => array(new WP_Http_Cookie(array('name' => 'html_crawl', 'value' => 'asdasd')))));
                return;
            }
            $pages = get_pages();
            global $wpdb;
            $table_name = $wpdb->prefix . "quickified_pages";
            include_once plugin_dir_path(__DIR__).'api/apirequestor.php';
            foreach ($pages as $page) {
                $pageurl = get_page_link($page->ID);
                $pagetitle = $page->post_title;

                if ($currenturl == $pageurl) {
                    $cacheFile = urlencode(strtolower($pagetitle)) . '-' . $date . '.html';
                    //$fileUrl = plugin_dir_path( __FILE__ ).$cacheFile;
                    $fileUrl = ABSPATH . "wp-content/cache/opt_html/$cacheFile";
                    if (!file_exists($fileUrl) && $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE url = '$pageurl'") == 0) {
                        logError("Crawl $pageurl");
                        $outputdata = processHtml($output);
                        logError("File size: before: ".strlen($output)." after: ".strlen($outputdata->html).(!empty($outputdata->inline) ? " inline: ".strlen($outputdata->inline) : " No inline"));
                        $jsfile = '';
                        $jsurl = '';
                        if(!empty($outputdata->inline)){
                            $jsfile = ABSPATH . "wp-content/cache/opt_minify/".urlencode(strtolower($pagetitle)) . '-' . $date . '-inline.js';
                            $jsurl = home_url() . "/wp-content/cache/opt_minify/".urlencode(strtolower($pagetitle)) . '-' . $date . '-inline.js';
                            file_put_contents($jsfile, $outputdata->inline);
                            $outputhtml = str_replace('@INLINE_SCRIPT_LOCATION@', $jsurl, $outputdata->html);
                            logError('Inserted inline script');
                        } else {
                            $outputhtml = $outputdata->html;
                        }
                        if(empty($outputhtml))
                            $outputhtml = $output;
                        $htaccessUrlPosition = $_SERVER['REQUEST_URI'];
                        $htaccessUrlPosition = ltrim($htaccessUrlPosition, '/');
                            insert_pages($pageurl, $htaccessUrlPosition, $jsurl, $jsfile, $fileUrl);
                            logError("Crawled and inserted new $pageurl");
                            $putfile = file_put_contents($fileUrl, $outputhtml);
                            if ($putfile) {
                                //logError("Lagt in fil: $fileUrl");
                                if ($page === end($pages)) {
                                    //logError("Last page: $pageurl");
                                    rewriteCachedPages();
                                }
                            } else {
                                logError("Couldn't insert $pageurl");
                                if ($page === end($pages)) {
                                    $table_name = $wpdb->prefix . 'quickified_minify';
                                    $wpdb->query($wpdb->prepare("UPDATE $table_name SET `is_running` = 0 WHERE `id`=%d", 1));
                                }
                            }


                    }
                    clearstatcache();
                }
            }
        }
        add_action('wp_loaded', 'sc_buffer_start');
        add_action('shutdown', 'sc_buffer_end');
    }
}

function rewriteCachedPages()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "quickified_pages";
    $getrows = $wpdb->get_results("SELECT * FROM $table_name");
    $htaccessUrlPosition = $_SERVER['REQUEST_URI'];
    $htaccessUrlPosition = ltrim($htaccessUrlPosition, '/');
    $hostpage = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
    //print_r($hostpage);
    $beginHtaccess = '# BEGIN QUICKIFIED REWRITES';
    $htaccess_content = $beginHtaccess;
    $htaccess_content .= "\n<IfModule mod_rewrite.c>
SetEnvIfNoCase User-Agent .*Quickified.* ignoreoptcrawler
RewriteEngine On
RewriteBase /";
    foreach ($getrows as $row) {
        $pageurl = $row->url;
        $pagefile = $row->pagefile;
        $relativeurl = $row->relativeurl;
        $htaccess_content .= "\nRewriteCond %{HTTP_COOKIE} !^.*wordpress_logged_in_.*$\nRewriteRule ^^$relativeurl$ $pagefile [QSA,L] env=ignoreoptcrawler";
    }

    $htaccess_content .= "\n</IfModule>";
    $htaccess_content .= "\n# END QUICKIFIED REWRITES\n";
    $htaccess = ABSPATH . ".htaccess";
    if (is_writable($htaccess)) {
        $file_data = file_get_contents($htaccess);
        if (stripos($file_data, $beginHtaccess) === false) {
            $htaccess_content .= $file_data;
            if(file_put_contents($htaccess, $htaccess_content)){
                $table_name = $wpdb->prefix . 'quickified_minify';
                $wpdb->query($wpdb->prepare("UPDATE $table_name SET `is_running` = 0 WHERE `id`=%d", 1));
            }
        } else {
            $findQuickified = '/(?=\# BEGIN QUICKIFIED REWRITES)(.*)(?<=\# END QUICKIFIED REWRITES)\s*/is';
            $file_data = preg_replace($findQuickified, '', $file_data);
            $htaccess_content .= $file_data;
            if(file_put_contents($htaccess, $htaccess_content)){
                $table_name = $wpdb->prefix . 'quickified_minify';
                $wpdb->query($wpdb->prepare("UPDATE $table_name SET `is_running` = 0 WHERE `id`=%d", 1));
            }
        }
    } else {
        echo "<script>alert('Htaccess is not writeable at the moment, please make sure that htaccess is editable and then enter the admin area again.');</script>";
        logError("Htaccess is not writeable at the moment, please make sure that htaccess is editable and then enter the admin area again.");
    }

}
