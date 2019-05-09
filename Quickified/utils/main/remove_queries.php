<?php
defined('ABSPATH') or die('You are not allowed here.');
	function cleanqueries( $src ){
		$tet = array("google", "gstatic");
		if (stripos($src, 'google') || stripos($src, 'gstatic') !== false) {
			return $src;
			} else {
				$fixurls = preg_replace('/\?.*/', '', $src);
		return $fixurls;
				}
	}
	add_filter( 'script_loader_src', 'cleanqueries', 15, 1 );
	add_filter( 'style_loader_src', 'cleanqueries', 15, 1 );