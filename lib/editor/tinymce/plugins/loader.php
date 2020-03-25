<?php



define('NO_MOODLE_COOKIES', true);
require_once('../../../../config.php');
require_once($CFG->dirroot . '/lib/filelib.php');
require_once($CFG->dirroot . '/lib/jslib.php');

$path = get_file_argument();

$matches = array();
if (!preg_match('~^/([a-z0-9_]+)/((?:[0-9.]+)|-1)(/.*)$~', $path, $matches)) {
    print_error('filenotfound');
}
list($junk, $tinymceplugin, $version, $innerpath) = $matches;


$pluginfolder = $CFG->dirroot . '/lib/editor/tinymce/plugins/' . $tinymceplugin;
$file = $pluginfolder . '/tinymce' .$innerpath;
if (!file_exists($file)) {
    print_error('filenotfound');
}

$allowcache = ($version !== '-1');
if ($allowcache) {
            header('Expires: ' . date('r', time() + 365 * 24 * 3600));
    header('Cache-Control: max-age=' . 365 * 24 * 3600);
        header('Pragma:');
}

$mimetype = mimeinfo('type', $file);

if ($mimetype === 'application/x-javascript' && $allowcache) {
            
        $cache = $CFG->cachedir . '/editor_tinymce/pluginjs';
    $cachefile = $cache . '/' . $tinymceplugin .
            str_replace('/', '_', $innerpath);

        if (!file_exists($cachefile)) {
        $content = core_minify::js_files(array($file));
        js_write_cache_file_content($cachefile, $content);
    }

    $file = $cachefile;
} else if ($mimetype === 'text/html') {
    header('X-UA-Compatible: IE=edge');
}

header('Content-Length: ' . filesize($file));
header('Content-Type: ' . $mimetype);
readfile($file);
