<?php
defined('ABSPATH') or die('You are not allowed here.');
function logError($error_content){
$filename = plugin_dir_path(__FILE__) . 'OLL.log';
$wp_timezone = get_option('timezone_string');
date_default_timezone_set($wp_timezone);
//Date format: 2019-05-25 11:35:07
$currentDate = date('Y-m-d H:i');
$somecontent = "|**************|$currentDate|**************|\n$error_content\n";


		if (!$handle = fopen($filename, 'a')) {
			 echo 'Cannot open file ('.$filename.')';
			 exit;
		}

		if (fwrite($handle, $somecontent) === FALSE) {
			echo 'Cannot write to file ('.$filename.')';
			exit;
		}

		fclose($handle);
}
