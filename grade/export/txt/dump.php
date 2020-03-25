<?php


define('NO_MOODLE_COOKIES', true); require_once '../../../config.php';
require_once($CFG->dirroot.'/grade/export/txt/grade_export_txt.php');

$id                 = required_param('id', PARAM_INT);
$groupid            = optional_param('groupid', 0, PARAM_INT);
$itemids            = required_param('itemids', PARAM_RAW);
$exportfeedback     = optional_param('export_feedback', 0, PARAM_BOOL);
$separator          = optional_param('separator', 'comma', PARAM_ALPHA);
$displaytype        = optional_param('displaytype', $CFG->grade_export_displaytype, PARAM_RAW);
$decimalpoints      = optional_param('decimalpoints', $CFG->grade_export_decimalpoints, PARAM_INT);
$onlyactive         = optional_param('export_onlyactive', 0, PARAM_BOOL);

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('nocourseid');
}

require_user_key_login('grade/export', $id); 
if (empty($CFG->gradepublishing)) {
    print_error('gradepubdisable');
}

$context = context_course::instance($id);
require_capability('moodle/grade:export', $context);
require_capability('gradeexport/txt:publish', $context);
require_capability('gradeexport/txt:view', $context);

if (!groups_group_visible($groupid, $COURSE)) {
    print_error('cannotaccessgroup', 'grades');
}

$formdata = grade_export::export_bulk_export_data($id, $itemids, $exportfeedback, $onlyactive, $displaytype,
        $decimalpoints, null, $separator);

$export = new grade_export_txt($course, $groupid, $formdata);
$export->print_grades();


