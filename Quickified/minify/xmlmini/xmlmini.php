<?php
defined('ABSPATH') or die('You are not allowed here.');

add_action('shutdown', 'xmlminify', 0);
function xmlminify()
{

    $contentype = ifxml();
    if (strpos($contentype, "text/xml") !== false) {
        ob_start();
        $final = '';
        $levels = ob_get_level();
        for ($i = 0; $i < $levels; $i++) {
            $final .= ob_get_clean();
        }
        echo apply_filters('xml_filter', $final);
    }

}

add_filter('xml_filter', 'xml_output');
function xml_output($output)
{
    //Ta bort kommentar samt onödiga kommentarer eller onödiga xml deklarationer
    $replace = array(
        '/<!--(.*)-->/Uis' => "",
        '/(>)\s+?(<)/' => '$1$2',
        '/\n/' => ' ',
    );
    $search = array_keys($replace);
    $output = preg_replace($search, $replace, $output);
    return $output;
}

function ifxml()
{
    $headers = headers_list();
    foreach ($headers as $header) {
        if (stripos($header, 'Content-Type') !== false) {
            $headerParts = explode(':', $header);
            $headerValue = trim($headerParts[1]);
            return $headerValue;
        }
    }
}