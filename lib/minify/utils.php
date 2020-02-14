<?php


if (! class_exists('Minify_Loader', false)) {
    require dirname(__FILE__) . '/lib/Minify/Loader.php';
    Minify_Loader::register();
}


function Minify_getUri($keyOrFiles, $opts = array())
{
    return Minify_HTML_Helper::getUri($keyOrFiles, $opts);
}



function Minify_mtime($keysAndFiles, $groupsConfigFile = null)
{
    $gc = null;
    if (! $groupsConfigFile) {
        $groupsConfigFile = dirname(__FILE__) . '/groupsConfig.php';
    }
    $sources = array();
    foreach ($keysAndFiles as $keyOrFile) {
        if (is_object($keyOrFile)
            || 0 === strpos($keyOrFile, '/')
            || 1 === strpos($keyOrFile, ':\\')) {
                        $sources[] = $keyOrFile;
        } else {
            if (! $gc) {
                $gc = (require $groupsConfigFile);
            }
            foreach ($gc[$keyOrFile] as $source) {
                $sources[] = $source;
            }
        }
    }
    return Minify_HTML_Helper::getLastModified($sources);
}
