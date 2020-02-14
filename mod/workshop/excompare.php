<?php



require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

$cmid   = required_param('cmid', PARAM_INT);    $sid    = required_param('sid', PARAM_INT);     $aid    = required_param('aid', PARAM_INT);     
$cm     = get_coursemodule_from_id('workshop', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, false, $cm);
if (isguestuser()) {
    print_error('guestsarenotallowed');
}

$workshop = $DB->get_record('workshop', array('id' => $cm->instance), '*', MUST_EXIST);
$workshop = new workshop($workshop, $cm, $course);
$strategy = $workshop->grading_strategy_instance();

$PAGE->set_url($workshop->excompare_url($sid, $aid));

$example    = $workshop->get_example_by_id($sid);
$assessment = $workshop->get_assessment_by_id($aid);
if ($assessment->submissionid != $example->id) {
   print_error('invalidarguments');
}
$mformassessment = $strategy->get_assessment_form($PAGE->url, 'assessment', $assessment, false);
if ($refasid = $DB->get_field('workshop_assessments', 'id', array('submissionid' => $example->id, 'weight' => 1))) {
    $reference = $workshop->get_assessment_by_id($refasid);
    $mformreference = $strategy->get_assessment_form($PAGE->url, 'assessment', $reference, false);
}

$canmanage  = has_capability('mod/workshop:manageexamples', $workshop->context);
$isreviewer = ($USER->id == $assessment->reviewerid);

if ($canmanage) {
    } elseif ($isreviewer and $workshop->assessing_examples_allowed()) {
    } else {
    print_error('nopermissions', 'error', $workshop->view_url(), 'compare example assessment');
}

$PAGE->set_title($workshop->name);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('examplecomparing', 'workshop'));

$output = $PAGE->get_renderer('mod_workshop');
echo $output->header();
echo $output->heading(format_string($workshop->name));
echo $output->heading(get_string('assessedexample', 'workshop'), 3);

echo $output->render($workshop->prepare_example_submission($example));

if (!empty($mformreference)) {
    $options = array(
        'showreviewer'  => false,
        'showauthor'    => false,
        'showform'      => true,
    );
    $reference = $workshop->prepare_example_reference_assessment($reference, $mformreference, $options);
    $reference->title = get_string('assessmentreference', 'workshop');
    if ($canmanage) {
        $reference->url = $workshop->exassess_url($reference->id);
    }
    echo $output->render($reference);
}

if ($isreviewer) {
    $options = array(
        'showreviewer'  => true,
        'showauthor'    => false,
        'showform'      => true,
    );
    $assessment = $workshop->prepare_example_assessment($assessment, $mformassessment, $options);
    $assessment->title = get_string('assessmentbyyourself', 'workshop');
    if ($workshop->assessing_examples_allowed()) {
        $assessment->add_action(
            new moodle_url($workshop->exsubmission_url($example->id), array('assess' => 'on', 'sesskey' => sesskey())),
            get_string('reassess', 'workshop')
        );
    }
    echo $output->render($assessment);

} elseif ($canmanage) {
    $options = array(
        'showreviewer'  => true,
        'showauthor'    => false,
        'showform'      => true,
    );
    $assessment = $workshop->prepare_example_assessment($assessment, $mformassessment, $options);
    echo $output->render($assessment);
}

echo $output->footer();
