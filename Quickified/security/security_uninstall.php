<?php
defined('ABSPATH') or die('You are not allowed here.');

$htaccess = ABSPATH.".htaccess";
        $htaccess_admin = ABSPATH."wp-admin/.htaccess";
        if (is_writable($htaccess)) {
            $array_regex = array(
                '/(# BEGIN WebbX Security Firewall)\n((.*\n)+)(# END WebbX Security Firewall)/im',
                '/(# BEGIN WebbX Security Main)\n((.*\n)+)(# END WebbX Security Main)/im',
                '/(# BEGIN WebbX Performance)\n((.*\n)+)(# END WebbX Performance)/im'
            );
            foreach($array_regex as $regex_string){
                $contents = file_get_contents($htaccess);
                $contents = preg_replace($regex_string, '', $contents);
                file_put_contents($htaccess, $contents);
            }
        }
        if (is_writable($htaccess_admin)) {
        $regex = '/(# BEGIN WebbX Security Admin Area)\n((.*\n)+)(# END WebbX Security Admin Area)/im';
        $contents = file_get_contents($htaccess_admin);
        $contents = preg_replace($regex, '', $contents);
        file_put_contents($htaccess_admin, $contents);
        }