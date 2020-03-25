<?php



define('NO_DEBUG_DISPLAY', true);

define('ABORT_AFTER_CONFIG', true);
require('../config.php'); require_once("$CFG->dirroot/lib/jslib.php");
require_once("$CFG->dirroot/lib/classes/requirejs.php");

$slashargument = min_get_slash_argument();
if (!$slashargument) {
        die('Invalid request');
}

$slashargument = ltrim($slashargument, '/');
if (substr_count($slashargument, '/') < 1) {
    header('HTTP/1.0 404 not found');
    die('Slash argument must contain both a revision and a file path');
}
list($rev, $file) = explode('/', $slashargument, 2);
$rev  = min_clean_param($rev, 'INT');
$file = '/' . min_clean_param($file, 'SAFEPATH');

$jsfiles = array();
list($unused, $component, $module) = explode('/', $file, 3);

if (strpos('/', $module) !== false) {
    die('Invalid module');
}

$lazysuffix = "-lazy.js";
$lazyload = (strpos($module, $lazysuffix) !== false);

if ($lazyload) {
        $etag = sha1($rev . '/' . $component . '/' . $module);
} else {
        $etag = sha1($rev);
}


if ($rev > 0 and $rev < (time() + 60 * 60)) {
    $candidate = $CFG->localcachedir . '/requirejs/' . $etag;

    if (file_exists($candidate)) {
        if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) || !empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                                    js_send_unmodified(filemtime($candidate), $etag);
        }
        js_send_cached($candidate, $etag, 'requirejs.php');
        exit(0);

    } else {
        $jsfiles = array();
        if ($lazyload) {
            $jsfiles = core_requirejs::find_one_amd_module($component, $module);
        } else {
                        
            $jsfiles = core_requirejs::find_all_amd_modules();
        }

        $content = '';
        foreach ($jsfiles as $modulename => $jsfile) {
            $js = file_get_contents($jsfile) . "\n";
                        $replace = 'define(\'' . $modulename . '\', ';
            $search = 'define(';
                        $js = implode($replace, explode($search, $js, 2));
            $content .= $js;
        }

        js_write_cache_file_content($candidate, $content);
                clearstatcache();
        if (file_exists($candidate)) {
            js_send_cached($candidate, $etag, 'requirejs.php');
            exit(0);
        }
    }
}

if ($lazyload) {
    $jsfiles = core_requirejs::find_one_amd_module($component, $module, true);
} else {
    $jsfiles = core_requirejs::find_all_amd_modules(true);
}

$content = '';
foreach ($jsfiles as $modulename => $jsfile) {
    $shortfilename = str_replace($CFG->dirroot, '', $jsfile);
    $js = "// ---- $shortfilename ----\n";
    $js .= file_get_contents($jsfile) . "\n";
        $replace = 'define(\'' . $modulename . '\', ';
    $search = 'define(';

    if (strpos($js, $search) === false) {
                header('HTTP/1.0 500 error');
        die('JS file: ' . $shortfilename . ' does not contain a javascript module in AMD format. "define()" not found.');
    }

        $js = implode($replace, explode($search, $js, 2));
    $content .= $js;
}
js_send_uncached($content, 'requirejs.php');
