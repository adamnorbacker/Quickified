<?php
defined('ABSPATH') or die('You are not allowed here.');
// Remove google fonts
function Clean_The_fonts($html)
{
    if (preg_match('/(?<=)fonts\.g(?:[^"\'\s()])*(?=\.com)/i', $html, $removetag)) {
        $html = wp_dequeue_style($removetag);
    }
    return $html;
}
add_filter('style_loader_tag', 'Clean_The_fonts', 9725);
