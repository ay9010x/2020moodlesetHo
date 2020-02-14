<?php



require_once("../../config.php");

$id = required_param('id', PARAM_INT); 
if (! $cm = get_coursemodule_from_id('scorm', $id)) {
    print_error('invalidcoursemodule');
}

if (! $scorm = $DB->get_record('scorm', array('id' => $cm->instance))) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record('course', array('id' => $scorm->course))) {
    print_error('coursemisconf');
}

require_login($course, false, $cm);

if (has_capability('mod/scorm:viewreport', context_module::instance($cm->id))) {
    redirect('report.php?id='.$cm->id);
} else {
    redirect('view.php?id='.$cm->id);
}
