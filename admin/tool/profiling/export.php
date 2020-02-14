<?php



require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir . '/xhprof/xhprof_moodle.php');

$runid = required_param('runid', PARAM_ALPHANUM);
$listurl = required_param('listurl', PARAM_PATH);

admin_externalpage_setup('toolprofiling');

$PAGE->navbar->add(get_string('export', 'tool_profiling'));

$tempdir = 'profiling';
make_temp_directory($tempdir);
$runids = array($runid);
$filename = $runid . '.mpr';
$filepath = $CFG->tempdir . '/' . $tempdir . '/' . $filename;

if (profiling_export_runs($runids, $filepath)) {
    send_file($filepath, $filename, 0, 0, false, false, '', true);
    unlink($filepath);     die;
}

$urlparams = array(
        'runid' => $runid,
        'listurl' => $listurl);
$url = new moodle_url('/admin/tool/profiling/index.php', $urlparams);
notice(get_string('exportproblem', 'tool_profiling', $urlparams), $url);
