<?php



defined('MOODLE_INTERNAL') || die(); 

$min_enableBuilder = false;
$min_errorLogger = false;
$min_allowDebugFlag = $CFG->debugdeveloper;
$min_cachePath = $CFG->tempdir;
$min_documentRoot = $CFG->dirroot.'/lib/minify';
$min_cacheFileLocking = empty($CFG->preventfilelocking);
$min_serveOptions['bubbleCssImports'] = false;
$min_serveOptions['maxAge'] = 1800;
$min_serveOptions['minApp']['groupsOnly'] = true;
$min_symlinks = array();
$min_uploaderHoursBehind = 0;
$min_libPath = dirname(__FILE__) . '/lib';

return; 


$min_enableBuilder = false;


$min_builderPassword = 'admin';



$min_errorLogger = false;



$min_allowDebugFlag = false;







$min_documentRoot = '';



$min_cacheFileLocking = true;



$min_serveOptions['bubbleCssImports'] = false;



$min_serveOptions['maxAge'] = 1800;








$min_serveOptions['minApp']['groupsOnly'] = false;






$min_symlinks = array();



$min_uploaderHoursBehind = 0;



$min_libPath = dirname(__FILE__) . '/lib';


ini_set('zlib.output_compression', '0');
