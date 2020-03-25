<?php


require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/export/lib.php';
require_once 'grade_export_xml.php';

$id                = required_param('id', PARAM_INT); $PAGE->set_url('/grade/export/xml/export.php', array('id'=>$id));

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('nocourseid');
}

require_login($course);
$context = context_course::instance($id);
$groupid = groups_get_course_group($course, true);

require_capability('moodle/grade:export', $context);
require_capability('gradeexport/xml:view', $context);

$key = optional_param('key', '', PARAM_RAW);
if (!empty($CFG->gradepublishing) && !empty($key)) {
    print_grade_page_head($COURSE->id, 'export', 'xml', get_string('exportto', 'grades') . ' ' . get_string('pluginname', 'gradeexport_xml'));
}

if (groups_get_course_groupmode($COURSE) == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
    if (!groups_is_member($groupid, $USER->id)) {
        print_error('cannotaccessgroup', 'grades');
    }
}
$mform = new grade_export_form(null, array('publishing' => true, 'simpleui' => true, 'multipledisplaytypes' => false,
        'idnumberrequired' => true, 'updategradesonly' => true));
$formdata = $mform->get_data();
$export = new grade_export_xml($course, $groupid, $formdata);

if (!empty($CFG->gradepublishing) && !empty($key)) {
    groups_print_course_menu($course, 'index.php?id='.$id);
    echo $export->get_grade_publishing_url();
    echo $OUTPUT->footer();
} else {
    $export->print_grades();
}


