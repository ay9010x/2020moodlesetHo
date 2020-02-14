<?php



require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir . '/xhprof/xhprof_moodle.php');
require_once(dirname(__FILE__) . '/import_form.php');

admin_externalpage_setup('toolprofiling');

$PAGE->navbar->add(get_string('import', 'tool_profiling'));

$tempdir = 'profiling';
make_temp_directory($tempdir);

$url = new moodle_url('/admin/tool/profiling/index.php');

$mform = new profiling_import_form();

if ($data = $mform->get_data()) {
    $filename = $mform->get_new_filename('mprfile');
    $file = $CFG->tempdir . '/' . $tempdir . '/' . $filename;
    $status = $mform->save_file('mprfile', $file);
    if ($status) {
                $status = profiling_import_runs($file, $data->importprefix);
    }
        if (file_exists($file)) {
        unlink($file);
    }
    if ($status) {
                redirect($url, get_string('importok', 'tool_profiling', $filename));
    }
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('import', 'tool_profiling'));
    $mform->display();
    echo $OUTPUT->footer();
    die;
}

notice(get_string('importproblem', 'tool_profiling', $filename), $url);
