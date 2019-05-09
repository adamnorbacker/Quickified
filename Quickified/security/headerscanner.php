<?php
defined('ABSPATH') or die('You are not allowed here.');
function get_status_code() 
{
    
    if (is_404()) {
        $currenturl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        $currentIP = get_the_user_ip();
        if (strpos($currenturl, ".map") === FALSE) {
            LogError("404 error: $currenturl \nUseragent: $useragent\nThe IP: $currentIP");
        }
        
    }
    
}

add_action('template_redirect', 'get_status_code');