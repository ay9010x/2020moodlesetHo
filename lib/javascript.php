<?php



define('NO_DEBUG_DISPLAY', true);

define('ABORT_AFTER_CONFIG', true);
require('../config.php'); require_once("$CFG->dirroot/lib/jslib.php");
require_once("$CFG->dirroot/lib/classes/minify.php");

if ($slashargument = min_get_slash_argument()) {
    $slashargument = ltrim($slashargument, '/');
    if (substr_count($slashargument, '/') < 1) {
        header('HTTP/1.0 404 not found');
        die('Slash argument must contain both a revision and a file path');
    }
        list($rev, $file) = explode('/', $slashargument, 2);
    $rev  = min_clean_param($rev, 'INT');
    $file = '/'.min_clean_param($file, 'SAFEPATH');

} else {
    $rev  = min_optional_param('rev', -1, 'INT');
    $file = min_optional_param('jsfile', '', 'RAW'); }

$jsfiles = array();
$files = explode(',', $file);
foreach ($files as $fsfile) {
    $jsfile = realpath($CFG->dirroot.$fsfile);
    if ($jsfile === false) {
                continue;
    }
    if ($CFG->dirroot === '/') {
                            } else if (strpos($jsfile, $CFG->dirroot . DIRECTORY_SEPARATOR) !== 0) {
                continue;
    }
    if (substr($jsfile, -3) !== '.js') {
                continue;
    }
    $jsfiles[] = $jsfile;
}

if (!$jsfiles) {
        header('HTTP/1.0 404 not found');
    die('No valid javascript files found');
}

$etag = sha1($rev.implode(',', $jsfiles));

if ($rev > 0 and $rev < (time() + 60*60)) {
    $candidate = $CFG->localcachedir.'/js/'.$etag;

    if (file_exists($candidate)) {
        if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) || !empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                                    js_send_unmodified(filemtime($candidate), $etag);
        }
        js_send_cached($candidate, $etag);

    } else {
        js_write_cache_file_content($candidate, core_minify::js_files($jsfiles));
                clearstatcache();
        if (file_exists($candidate)) {
            js_send_cached($candidate, $etag);
        }
    }
}

$content = '';
foreach ($jsfiles as $jsfile) {
    $content .= file_get_contents($jsfile)."\n";
}
js_send_uncached($content);
