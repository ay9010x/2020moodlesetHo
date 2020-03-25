<?php


defined('MOODLE_INTERNAL') OR die('not allowed');

$tabs = array();
$row  = array();
$inactive = array();
$activated = array();

if (isset($cmid) AND intval($cmid) AND $cmid > 0) {
    $usedid = $cmid;
} else {
    $usedid = $id;
}

$context = context_module::instance($usedid);

$courseid = optional_param('courseid', false, PARAM_INT);
if (!isset($current_tab)) {
    $current_tab = '';
}

$viewurl = new moodle_url('/mod/feedback/view.php', array('id' => $usedid));
$row[] = new tabobject('view', $viewurl->out(), get_string('overview', 'feedback'));
$urlparams = ['id' => $usedid];
if ($feedback->course == SITEID && $courseid) {
    $urlparams['courseid'] = $courseid;
}

if (has_capability('mod/feedback:edititems', $context)) {
    $editurl = new moodle_url('/mod/feedback/edit.php', $urlparams + ['do_show' => 'edit']);
    $row[] = new tabobject('edit', $editurl->out(), get_string('edit_items', 'feedback'));

    $templateurl = new moodle_url('/mod/feedback/edit.php', $urlparams + ['do_show' => 'templates']);
    $row[] = new tabobject('templates', $templateurl->out(), get_string('templates', 'feedback'));
}

if ($feedback->course == SITEID && has_capability('mod/feedback:mapcourse', $context)) {
    $mapurl = new moodle_url('/mod/feedback/mapcourse.php', $urlparams);
    $row[] = new tabobject('mapcourse', $mapurl->out(), get_string('mappedcourses', 'feedback'));
}

if (has_capability('mod/feedback:viewreports', $context)) {
    if ($feedback->course == SITEID) {
        $analysisurl = new moodle_url('/mod/feedback/analysis_course.php', $urlparams);
    } else {
        $analysisurl = new moodle_url('/mod/feedback/analysis.php', $urlparams);
    }
    $row[] = new tabobject('analysis', $analysisurl->out(), get_string('analysis', 'feedback'));

    $reporturl = new moodle_url('/mod/feedback/show_entries.php', $urlparams);
    $row[] = new tabobject('showentries',
                            $reporturl->out(),
                            get_string('show_entries', 'feedback'));

    if ($feedback->anonymous == FEEDBACK_ANONYMOUS_NO AND $feedback->course != SITEID) {
        $nonrespondenturl = new moodle_url('/mod/feedback/show_nonrespondents.php', $urlparams);
        $row[] = new tabobject('nonrespondents',
                                $nonrespondenturl->out(),
                                get_string('show_nonrespondents', 'feedback'));
    }
}

if (count($row) > 1) {
    $tabs[] = $row;

    print_tabs($tabs, $current_tab, $inactive, $activated);
}

