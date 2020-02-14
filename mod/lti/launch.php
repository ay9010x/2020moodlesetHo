<?php



require_once("../../config.php");
require_once($CFG->dirroot.'/mod/lti/lib.php');
require_once($CFG->dirroot.'/mod/lti/locallib.php');

$id = required_param('id', PARAM_INT); 
$cm = get_coursemodule_from_id('lti', $id, 0, false, MUST_EXIST);
$lti = $DB->get_record('lti', array('id' => $cm->instance), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/lti:view', $context);

lti_view($lti, $course, $cm, $context);

$lti->cmid = $cm->id;
lti_launch_tool($lti);

