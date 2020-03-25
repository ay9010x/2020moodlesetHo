<?php



require_once '../../../config.php';
require_once $CFG->libdir . '/gradelib.php';
require_once '../../lib.php';
core_php_time_limit::raise();

$courseid      = required_param('id', PARAM_INT);

$PAGE->set_url(new moodle_url('/grade/report/grader/preferences.php', array('id'=>$courseid)));
$PAGE->set_pagelayout('admin');


if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}

require_login($course);

$context = context_course::instance($course->id);
$systemcontext = context_system::instance();
require_capability('gradereport/grader:view', $context);

require('preferences_form.php');
$mform = new grader_report_preferences_form('preferences.php', compact('course'));

if (!$mform->is_cancelled() && $data = $mform->get_data()) {
    foreach ($data as $preference => $value) {
        if (substr($preference, 0, 6) !== 'grade_') {
            continue;
        }

        if ($value == GRADE_REPORT_PREFERENCE_DEFAULT || strlen($value) == 0) {
            unset_user_preference($preference);
        } else {
            set_user_preference($preference, $value);
        }
    }

    redirect($CFG->wwwroot . '/grade/report/grader/index.php?id='.$courseid);     exit;
}

if ($mform->is_cancelled()){
    redirect($CFG->wwwroot . '/grade/report/grader/index.php?id='.$courseid);
}

print_grade_page_head($courseid, 'settings', 'grader', get_string('preferences', 'gradereport_grader'));

if (has_capability('moodle/site:config', $systemcontext)) {
    echo '<div id="siteconfiglink"><a href="'.$CFG->wwwroot.'/'.$CFG->admin.'/settings.php?section=gradereportgrader">';
    echo get_string('changereportdefaults', 'grades');
    echo "</a></div>\n";
}

echo $OUTPUT->box_start();

$mform->display();
echo $OUTPUT->box_end();

echo $OUTPUT->footer();

