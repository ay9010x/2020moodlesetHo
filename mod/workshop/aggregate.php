<?php




require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

$cmid       = required_param('cmid', PARAM_INT);            $confirm    = optional_param('confirm', false, PARAM_BOOL); 
$page       = optional_param('page', 0, PARAM_INT);
$sortby     = optional_param('sortby', 'lastname', PARAM_ALPHA);
$sorthow    = optional_param('sorthow', 'ASC', PARAM_ALPHA);

$cm         = get_coursemodule_from_id('workshop', $cmid, 0, false, MUST_EXIST);
$course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$workshop   = $DB->get_record('workshop', array('id' => $cm->instance), '*', MUST_EXIST);
$workshop   = new workshop($workshop, $cm, $course);

$PAGE->set_url($workshop->aggregate_url(), compact('confirm', 'page', 'sortby', 'sorthow'));

require_login($course, false, $cm);
require_capability('mod/workshop:overridegrades', $PAGE->context);

$evaluator = $workshop->grading_evaluation_instance();
$settingsform = $evaluator->get_settings_form($PAGE->url);

if ($settingsdata = $settingsform->get_data()) {
    $workshop->aggregate_submission_grades();               $evaluator->update_grading_grades($settingsdata);       $workshop->aggregate_grading_grades();              }

redirect(new moodle_url($workshop->view_url(), compact('page', 'sortby', 'sorthow')));
