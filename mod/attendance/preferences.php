<?php



require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

$pageparams = new mod_attendance_preferences_page_params();

$id                         = required_param('id', PARAM_INT);
$pageparams->action         = optional_param('action', null, PARAM_INT);
$pageparams->statusid       = optional_param('statusid', null, PARAM_INT);
$pageparams->statusset      = optional_param('statusset', 0, PARAM_INT); 
$cm             = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
$course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$att            = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/attendance:changepreferences', $context);

$maxstatusset = attendance_get_max_statusset($att->id);
if ($pageparams->statusset > $maxstatusset + 1) {
    $pageparams->statusset = $maxstatusset + 1;
}

$att = new mod_attendance_structure($att, $cm, $course, $context, $pageparams);

$PAGE->set_url($att->url_preferences());
$PAGE->set_title($course->shortname. ": ".$att->name.' - '.get_string('settings', 'attendance'));
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'attendance'));
$PAGE->navbar->add(get_string('settings', 'attendance'));

$errors = array();

if (!empty($att->pageparams->action)) {
    require_sesskey();
}

switch ($att->pageparams->action) {
    case mod_attendance_preferences_page_params::ACTION_ADD:
        $newacronym         = optional_param('newacronym', null, PARAM_TEXT);
        $newdescription     = optional_param('newdescription', null, PARAM_TEXT);
        $newgrade           = optional_param('newgrade', 0, PARAM_RAW);
        $newgrade = unformat_float($newgrade);

        $att->add_status($newacronym, $newdescription, $newgrade);
        if ($pageparams->statusset > $maxstatusset) {
            $maxstatusset = $pageparams->statusset;         }
        break;
    case mod_attendance_preferences_page_params::ACTION_DELETE:
        if (attendance_has_logs_for_status($att->pageparams->statusid)) {
            print_error('cantdeletestatus', 'attendance', "attsettings.php?id=$id");
        }

        $confirm    = optional_param('confirm', null, PARAM_INT);
        $statuses = $att->get_statuses(false);
        $status = $statuses[$att->pageparams->statusid];

        if (isset($confirm)) {
            $att->remove_status($status);
            redirect($att->url_preferences(), get_string('statusdeleted', 'attendance'));
        }

        $message = get_string('deletecheckfull', '', get_string('variable', 'attendance'));
        $message .= str_repeat(html_writer::empty_tag('br'), 2);
        $message .= $status->acronym.': '.
                    ($status->description ? $status->description : get_string('nodescription', 'attendance'));
        $params = array_merge($att->pageparams->get_significant_params(), array('confirm' => 1));
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('attendanceforthecourse', 'attendance').' :: ' .format_string($course->fullname));
        echo $OUTPUT->confirm($message, $att->url_preferences($params), $att->url_preferences());
        echo $OUTPUT->footer();
        exit;
    case mod_attendance_preferences_page_params::ACTION_HIDE:
        $statuses = $att->get_statuses(false);
        $status = $statuses[$att->pageparams->statusid];
        $att->update_status($status, null, null, null, 0);
        break;
    case mod_attendance_preferences_page_params::ACTION_SHOW:
        $statuses = $att->get_statuses(false);
        $status = $statuses[$att->pageparams->statusid];
        $att->update_status($status, null, null, null, 1);
        break;
    case mod_attendance_preferences_page_params::ACTION_SAVE:
        $acronym        = required_param_array('acronym', PARAM_TEXT);
        $description    = required_param_array('description', PARAM_TEXT);
        $grade          = required_param_array('grade', PARAM_RAW);
        foreach ($grade as &$val) {
            $val = unformat_float($val);
        }
        $statuses = $att->get_statuses(false);

        foreach ($acronym as $id => $v) {
            $status = $statuses[$id];
            $errors[$id] = $att->update_status($status, $acronym[$id], $description[$id], $grade[$id], null);
        }
        attendance_update_users_grade($att);
        break;
}

$output = $PAGE->get_renderer('mod_attendance');
$tabs = new attendance_tabs($att, attendance_tabs::TAB_PREFERENCES);
$prefdata = new attendance_preferences_data($att, array_filter($errors));
$setselector = new attendance_set_selector($att, $maxstatusset);


echo $output->header();
echo $output->heading(get_string('attendanceforthecourse', 'attendance').' :: '. format_string($course->fullname));
echo $output->render($tabs);
echo $output->render($setselector);
echo $output->render($prefdata);

echo $output->footer();
