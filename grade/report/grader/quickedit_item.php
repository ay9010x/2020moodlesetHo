<?php



require_once '../../../config.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/grader/lib.php';

$courseid      = required_param('id', PARAM_INT);        $itemid        = required_param('itemid', PARAM_INT);        $page          = optional_param('page', 0, PARAM_INT);   $perpageurl    = optional_param('perpage', 0, PARAM_INT);

$url = new moodle_url('/grade/report/grader/quickedit_item.php', array('id'=>$courseid, 'itemid'=>$itemid));
if ($page !== 0) {
    $url->param('page', $page);
}
if ($perpage !== 0) {
    $url->param('perpage', $perpage);
}
$PAGE->set_url($url);


if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}

if (!$item = $DB->get_record('grade_items', array('id' => $itemid))) {
    print_error('noitemid', 'grades');
}

require_login($course);
$context = context_course::instance($course->id);

require_capability('gradereport/grader:view', $context);
require_capability('moodle/grade:viewall', $context);
require_capability('moodle/grade:edit', $context);

$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'grader', 'courseid'=>$courseid, 'page'=>$page));

if (!isset($USER->grade_last_report)) {
    $USER->grade_last_report = array();
}
$USER->grade_last_report[$course->id] = 'grader';

$report = new grade_report_grader($courseid, $gpr, $context, $page);

if ($data = data_submitted() and confirm_sesskey() and has_capability('moodle/grade:edit', $context)) {
    $warnings = $report->process_data($data);
} else {
    $warnings = array();
}

if ($perpageurl) {
    $report->user_prefs['studentsperpage'] = $perpageurl;
}

$report->load_users();
$numusers = $report->get_numusers();
$report->load_final_grades();

$a->item = $item->itemname;
$reportname = get_string('quickedititem', 'gradereport_grader', $a);
print_grade_page_head($COURSE->id, 'report', 'grader', $reportname);

echo $report->group_selector;
echo '<div class="clearer"></div>';

foreach($warnings as $warning) {
    echo $OUTPUT->notification($warning);
}

$studentsperpage = $report->get_pref('studentsperpage');
if (!empty($studentsperpage)) {
    echo $OUTPUT->paging_bar($numusers, $report->page, $studentsperpage, $report->pbarurl);
}


echo '<div class="submit"><input type="submit" value="'.get_string('update').'" /></div>';
echo '</div></form>';

if (!empty($studentsperpage) && $studentsperpage >= 20) {
    echo $OUTPUT->paging_bar($numusers, $report->page, $studentsperpage, $report->pbarurl);
}

echo $OUTPUT->footer();

