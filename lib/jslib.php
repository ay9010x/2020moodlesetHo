<?php



defined('MOODLE_INTERNAL') || die();


function js_send_cached($jspath, $etag, $filename = 'javascript.php') {
    require(__DIR__ . '/xsendfilelib.php');

    $lifetime = 60*60*24*60; 
    header('Etag: "'.$etag.'"');
    header('Content-Disposition: inline; filename="'.$filename.'"');
    header('Last-Modified: '. gmdate('D, d M Y H:i:s', filemtime($jspath)) .' GMT');
    header('Expires: '. gmdate('D, d M Y H:i:s', time() + $lifetime) .' GMT');
    header('Pragma: ');
    header('Cache-Control: public, max-age='.$lifetime);
    header('Accept-Ranges: none');
    header('Content-Type: application/javascript; charset=utf-8');

    if (xsendfile($jspath)) {
        die;
    }

    if (!min_enable_zlib_compression()) {
        header('Content-Length: '.filesize($jspath));
    }

    readfile($jspath);
    die;
}


function js_send_uncached($js, $filename = 'javascript.php') {
    header('Content-Disposition: inline; filename="'.$filename.'"');
    header('Last-Modified: '. gmdate('D, d M Y H:i:s', time()) .' GMT');
    header('Expires: '. gmdate('D, d M Y H:i:s', time() + 2) .' GMT');
    header('Pragma: ');
    header('Accept-Ranges: none');
    header('Content-Type: application/javascript; charset=utf-8');
    header('Content-Length: '.strlen($js));

    echo $js;
    die;
}


function js_send_unmodified($lastmodified, $etag) {
    $lifetime = 60*60*24*60;     header('HTTP/1.1 304 Not Modified');
    header('Expires: '. gmdate('D, d M Y H:i:s', time() + $lifetime) .' GMT');
    header('Cache-Control: public, max-age='.$lifetime);
    header('Content-Type: application/javascript; charset=utf-8');
    header('Etag: "'.$etag.'"');
    if ($lastmodified) {
        header('Last-Modified: '. gmdate('D, d M Y H:i:s', $lastmodified) .' GMT');
    }
    die;
}


function js_write_cache_file_content($file, $content) {
    global $CFG;

    clearstatcache();
    if (!file_exists(dirname($file))) {
        @mkdir(dirname($file), $CFG->directorypermissions, true);
    }

            ignore_user_abort(true);
    if ($fp = fopen($file.'.tmp', 'xb')) {
        fwrite($fp, $content);
        fclose($fp);
        rename($file.'.tmp', $file);
        @chmod($file, $CFG->filepermissions);
        @unlink($file.'.tmp');     }
    ignore_user_abort(false);
    if (connection_aborted()) {
        die;
    }
}


function js_send_css_not_found() {
    header('HTTP/1.0 404 not found');
    die('JS was not found, sorry.');
}
