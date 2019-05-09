<?php
defined('ABSPATH') or die('You are not allowed here.');
error_reporting(-1);
ini_set("display_errors", 1);
//add 1440px image size
add_image_size('image_1440', 1440, 9999, false);

remove_filter('the_content', 'wp_make_content_images_responsive', 10);
add_filter('the_content', 'wp_make_content_images_responsive', 1600, 1);
add_action('wp_loaded', 'hb_add_id_to_images', 99999999999999999999);
add_action('wp_loaded', 'responsive_bg', 999999999999999999999);

function hb_add_id_to_images($content)
{

    if (is_feed() || is_preview() || is_admin()) {return $content;}
    ob_start(function ($content) {
        $dom = new DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        global $wpdb;
        $imgs = $dom->getElementsByTagName('img');
        foreach ($imgs as $img) {
            $wp_upload_dir = wp_upload_dir();
            if ($img->hasAttribute('src')) {
                $imgsrc = $img->getAttribute('src');
                if (stripos($imgsrc, $_SERVER['REQUEST_URI'])) {
                    $image_path = str_ireplace(trailingslashit(preg_replace("(^https?://)", "", $wp_upload_dir['baseurl'])), '', preg_replace("(^https?://)", "", $imgsrc));

                    $attachment = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_wp_attached_file' AND BINARY meta_value='%s';", $image_path));

                    if ($attachment) {
                        if ($img->hasAttribute('class')) {
                            $img->setAttribute('class', trim($img->getAttribute('class') . ' wp-image-' . $attachment[0]));
                        } else {
                            $img->setAttribute("class", "wp-image-" . $attachment[0]);
                        }
                        $img_srcset = wp_get_attachment_image_srcset($attachment[0]);
                        $img_sizes = wp_get_attachment_image_sizes($attachment[0]);
                        $alt_text = get_post_meta($attachment[0], '_wp_attachment_image_alt', true);
                        $img->setAttribute("alt", $alt_text);
                        $img->setAttribute("srcset", $img_srcset);
                        $img->setAttribute("sizes", $img_sizes);
                    }
                }
            }
        }
        $content = $dom->saveHTML();
        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
        return $content;
    });
}

//max-width till 1080px (retina kompatibel)
function hb_content_image_sizes_attr($sizes, $size)
{
    $width = $size[0];

    if ($width <= 1080) {
        $sizes = '(max-width: 1080px) 100vw, 1080px';
    }

    return $sizes;
}
add_filter('wp_calculate_image_sizes', 'hb_content_image_sizes_attr', 10, 2);

function responsive_bg($html)
{

    if (is_feed() || is_preview() || is_admin()) {return $html;}
    ob_start(function ($html) {
        if (!preg_match_all('/\<\bstyle\b.*?>(.*?)<\/\bstyle\b>/mi', $html, $matches)) {
            return $html;
        }
        $gutt = '/^background+.*url\s*\(\s*[\'"]?(?!(?:data\:?:))([^\'"\)]+)[\'"]?\s*\)/mi';

        $selektorer = array();
        $urls = array();

        $findSelector = '/\s*([^{]+)\s*\{\s*([^}]*?)\s*}/';
        preg_match_all($findSelector, $html, $classmatches);
        $cssContainer = '';
        $theClassMatches = count($classmatches[0]);
        for ($i = 0; $i < $theClassMatches; $i++) {
            $stuff = $classmatches[2][$i];

            preg_match_all($gutt, $stuff, $res);
            if (!array_empty($res)) {
                $prop = $res[0][0]; //background-image värde
                $url = $res[1][0]; //url från prop

                $the_id = get_attachment_ID($url);
                $img_srcset = wp_get_attachment_image_srcset($the_id, 'full');
                $sizes = explode(", ", $img_srcset);
                $css = '';
                $prev_size = '';
                foreach ($sizes as $size) {
                    $split = explode(" ", $size);
                    if (!isset($split[0], $split[1])) {
                        continue;
                    }

                    $prop1 = str_replace($url, esc_url($split[0]), $prop); //byt ut url:en mot en som har en annan storlek
                    if (!empty($prev_size)) {
                        $prev_size = str_replace("w", "px", $split[1]); //ta ut storleken
                        $prop1 .= '!important;'; //lägg till important på prop

                        $selektor = str_replace($stuff, $prop1, $classmatches[0][$i]); //ersätt innehållet i selektorns block
                        $css .= "@media all and (min-width: $prev_size) " . '{' . $selektor . '}';
                    } else {
                        $prev_size = '1'; //första bilden, som inte ska behandlas
                    }
                }
                if (!empty($css)) {
                    $cssContainer .= $css;
                }
            }
        }
        $html = str_replace('</body>', '<style>' . $cssContainer . '</style></body>', $html);
        return $html;
    });
}

function array_empty($mixed)
{
    if (is_array($mixed)) {
        foreach ($mixed as $value) {
            if (!array_empty($value)) {
                return false;
            }
        }
    } elseif (!empty($mixed)) {
        return false;
    }
    return true;
}

function get_attachment_ID($image_url)
{
    global $wpdb;
    if (!empty($image_url)) {
        $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url));
        return !empty($attachment) ? $attachment[0] : false;
    } else {
        return false;
    }
}
