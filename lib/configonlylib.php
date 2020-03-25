<?php





function min_optional_param($name, $default, $type) {
    if (isset($_GET[$name])) {
        $value = $_GET[$name];

    } else if (isset($_GET['amp;'.$name])) {
                $value = $_GET['amp;'.$name];

    } else if (isset($_POST[$name])) {
        $value = $_POST[$name];

    } else {
        return $default;
    }

    return min_clean_param($value, $type);
}


function min_clean_param($value, $type) {
    switch($type) {
        case 'RAW':      $value = min_fix_utf8((string)$value);
                         break;
        case 'INT':      $value = (int)$value;
                         break;
        case 'SAFEDIR':  $value = preg_replace('/[^a-zA-Z0-9_-]/', '', $value);
                         break;
        case 'SAFEPATH': $value = preg_replace('/[^a-zA-Z0-9\/\._-]/', '', $value);
                         $value = preg_replace('/\.+/', '.', $value);
                         $value = preg_replace('#/+#', '/', $value);
                         break;
        default:         die("Coding error: incorrect parameter type specified ($type).");
    }

    return $value;
}


function min_fix_utf8($value) {
        $value = str_replace("\0", '', $value);

    static $buggyiconv = null;
    if ($buggyiconv === null) {
        set_error_handler(function () {
            return true;
        });
        $buggyiconv = (!function_exists('iconv') or iconv('UTF-8', 'UTF-8//IGNORE', '100'.chr(130).'€') !== '100€');
        restore_error_handler();
    }

    if ($buggyiconv) {
        if (function_exists('mb_convert_encoding')) {
            $subst = mb_substitute_character();
            mb_substitute_character('');
            $result = mb_convert_encoding($value, 'utf-8', 'utf-8');
            mb_substitute_character($subst);

        } else {
                        $result = $value;
        }

    } else {
        $result = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
    }

    return $result;
}


function min_enable_zlib_compression() {

    if (headers_sent()) {
        return false;
    }

        if (!empty($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6') !== false) {
        ini_set('zlib.output_compression', 'Off');
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', 1);
        }
        return false;
    }

    ini_set('output_handler', '');

    
    ini_set('zlib.output_compression', 65536);

    return true;
}


function min_get_slash_argument($clean = true) {
            
    $relativepath = '';

    if (!empty($_GET['file']) and strpos($_GET['file'], '/') === 0) {
                        $relativepath = $_GET['file'];
        if ($clean) {
            $relativepath = min_clean_param($relativepath, 'SAFEPATH');
        }

        return $relativepath;

    } else if (stripos($_SERVER['SERVER_SOFTWARE'], 'iis') !== false) {
        if (isset($_SERVER['PATH_INFO']) and $_SERVER['PATH_INFO'] !== '') {
            $relativepath = urldecode($_SERVER['PATH_INFO']);
        }

    } else {
        if (isset($_SERVER['PATH_INFO'])) {
            $relativepath = $_SERVER['PATH_INFO'];
        }
    }

    $matches = null;
    if (preg_match('|^.+\.php(.*)$|i', $relativepath, $matches)) {
        $relativepath = $matches[1];
    }

        if ($clean) {
        $relativepath = min_clean_param($relativepath, 'SAFEPATH');
    }
    return $relativepath;
}
