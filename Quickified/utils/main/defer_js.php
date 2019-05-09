<?php
defined('ABSPATH') or die('You are not allowed here.');
// Add defer to js images
function Js_Defer_attr($tag)
{
    if (!is_admin()) {
        return str_replace(' src', ' defer src', $tag);
    }
}
add_filter('script_loader_tag', 'Js_Defer_attr', 9926);
