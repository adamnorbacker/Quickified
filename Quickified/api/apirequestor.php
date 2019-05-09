<?php
defined('ABSPATH') or die('You are not allowed here.');

function processCss($files)
{

    $data = array(
        'content' => $files,
        'type' => 'css',
        'home_url' => home_url(),
    );

    return handleCurl($data);
}

function processJs($files)
{

    global $wpdb;
    $inlinejs = '';
    $table = $wpdb->prefix . 'quickified_minify';
    $doestableexist = $wpdb->get_results('SELECT inline_js FROM ' . $table . ' WHERE \'inline_js\' IS NOT NULL');
    if (!empty($doestableexist)) {
        $getthescripts = $wpdb->get_row('SELECT inline_js FROM ' . $table . '', ARRAY_A);
        $savedscriptstoarray = implode('', $getthescripts);
        $inline = unserialize($savedscriptstoarray);
        if (!empty($inline)) {
            $inline = array_unique($inline);
            foreach ($inline as $jdata) {
                $inlinejs .= $jdata;
            }
        }
        $wpdb->query($wpdb->prepare("UPDATE $table SET `inline_js` = '' WHERE `id`=%d", 1));
    }

    $data = array(
        'content' => $files,
        'type' => 'js',
        'inline' => $inlinejs,
    );

    return handleCurl($data);
}

function processImage($file)
{
    $data = array(
        'content' => new CurlFile($file),
        'type' => 'img',
        'settings' => json_encode(array('webp' => get_option('create_webp-checkbox'), 'jpg' => get_option('compress_jpg-checkbox'),
            'png' => get_option('compress_png-checkbox'), 'jpg_compression' => get_option('jpg_compression-textinput'),
            'webp_compression' => get_option('webp_compression-textinput'))),
    );

    $result = handleCurl($data, false);

    if ($result !== false) {
        if (!empty($result->image)) {
            $oldSize = number_format(filesize($file) / 1024, 1, '.', '');
            $newSize = number_format(strlen(base64_decode($result->image)) / 1024, 1, '.', '');
            if ($newSize < $oldSize) {
                file_put_contents($file, base64_decode($result->image));
            } else {
                $newSize = $oldSize;
            }

            $time_zone = get_option('timezone_string');
            date_default_timezone_set($time_zone);
            global $wpdb;
            $table_name = $wpdb->prefix . "quickified_optimized_images";
            $isOptimized = $wpdb->get_var("SELECT optimized FROM $table_name WHERE url = '$file'");
            if ($isOptimized != 1) {
                //Date format: 2019-05-25 11:35:07
                $date = date('Y-m-d H:i:s');
                $wpdb->query($wpdb->prepare("INSERT INTO $table_name(time, url, optimized, original_size, optimized_size) VALUES(%s, %s, %d, %d, %d)", $date, $file, 1, $oldSize, $newSize));
            }
        }

        if (!empty($result->webp)) {
            file_put_contents(substr($file, 0, strrpos($file, ".")) . '.webp', base64_decode($result->webp));
        }

        return true;
    } else {
        return false;
    }
}

function processHtaccess()
{
    $htaccessfile = ABSPATH . '.htaccess';
    $file = file_get_contents($htaccessfile);
    $data = array(
        'content' => $file,
        'type' => 'htaccess',
    );

    $result = handleCurl($data);

    if ($result !== false) {
        file_put_contents($htaccessfile, $result);
    }

}

function processFont($file, $font_type = '')
{
    $data = array(
        'content' => new CurlFile($file),
        'type' => 'font',
        'font-type' => $font_type,
    );

    $result = handleCurl($data, false);

    if ($result !== false) {
        if (empty($font_type)) {
            $font = substr($file, 0, strrpos($file, ".")) . '.woff';
            $font_2 = $font . '2';
            if ($result->font1 !== false) {
                file_put_contents($font, base64_decode($result->font1));
            }
            if ($result->font2 !== false) {
                file_put_contents($font_2, base64_decode($result->font2));
            }
        } elseif ($font_type == 'woff') {
            $font = substr($file, 0, strrpos($file, ".")) . '.woff';
            if ($result->font !== false) {
                file_put_contents($font, base64_decode($result->font));
            }
        } elseif ($font_type == 'woff2') {
            $font = substr($file, 0, strrpos($file, ".")) . '.woff2';
            if ($result->font !== false) {
                file_put_contents($font, base64_decode($result->font));
            }
        }

    }
}

function getFontFamily($file)
{
    $data = array(
        'content' => new CurlFile($file),
        'type' => 'font',
        'font-type' => 'family',
    );

    $result = handleCurl($data, false);

    if ($result !== false) {
        return $result;
    }
    return "";
}

function processHtml($content)
{
    $data = array(
        'content' => $content,
        'type' => 'html',
        'home_url' => home_url(),
    );

    return handleCurl($data);

}

function processInline($content)
{
    $data = array(
        'content' => $content,
        'type' => 'inline',
    );

    return handleCurl($data);
}

function replaceInline($content, $inlinescript)
{
    $data = array(
        'content' => $content,
        'type' => 'inline',
        'inlinescript' => $inlinescript,
    );

    return handleCurl($data);
}

function handleCurl($data, $doQuery = true)
{
    if ($doQuery) {
        $data_string = http_build_query($data);
    }

    $response = new stdClass;
    $response->data = '';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://142.93.109.25/kommunikation/kommunikation.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $doQuery ? $data_string : $data);
    curl_setopt($ch, CURLOPT_USERPWD, getAPIKey());
    curl_setopt($ch, CURLOPT_WRITEFUNCTION,
        function ($ch, $string) use ($response) {
            $response->data .= $string;
            return strlen($string);
        }
    );
    curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode == 200) {
        $result = json_decode($response->data);
        return $result->result;
    } else {
        echo "<script>console.log('$httpcode: " . $response->data . "');</script>";
        logError("$httpcode: " . $response->data);
        return false;
    }
}

function getAPIKey()
{
    return 'test:adwoij1291090du39';
}
