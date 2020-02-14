<?php




require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

$asid       = required_param('asid', PARAM_INT);  $assessment = $DB->get_record('workshop_assessments', array('id' => $asid), '*', MUST_EXIST);
$example    = $DB->get_record('workshop_submissions', array('id' => $assessment->submissionid, 'example' => 1), '*', MUST_EXIST);
$workshop   = $DB->get_record('workshop', array('id' => $example->workshopid), '*', MUST_EXIST);
$course     = $DB->get_record('course', array('id' => $workshop->course), '*', MUST_EXIST);
$cm         = get_coursemodule_from_instance('workshop', $workshop->id, $course->id, false, MUST_EXIST);

require_login($course, false, $cm);
if (isguestuser()) {
    print_error('guestsarenotallowed');
}
$workshop = new workshop($workshop, $cm, $course);

$PAGE->set_url($workshop->exassess_url($assessment->id));
$PAGE->set_title($workshop->name);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('assessingexample', 'workshop'));
$currenttab = 'assessment';

$canmanage  = has_capability('mod/workshop:manageexamples', $workshop->context);
$isreviewer = ($USER->id == $assessment->reviewerid);

if ($isreviewer or $canmanage) {
    } else {
    print_error('nopermissions', 'error', $workshop->view_url(), 'assess example submission');
}

if (($canmanage and $assessment->weight == 1) or ($isreviewer and $workshop->assessing_examples_allowed())) {
    $assessmenteditable = true;
} else {
    $assessmenteditable = false;
}

$strategy = $workshop->grading_strategy_instance();

$mform = $strategy->get_assessment_form($PAGE->url, 'assessment', $assessment, $assessmenteditable);

$currentdata = (object)array(
    'feedbackauthor' => $assessment->feedbackauthor,
    'feedbackauthorformat' => $assessment->feedbackauthorformat,
);
if ($assessmenteditable and $workshop->overallfeedbackmode) {
    $currentdata = file_prepare_standard_editor($currentdata, 'feedbackauthor', $workshop->overall_feedback_content_options(),
        $workshop->context, 'mod_workshop', 'overallfeedback_content', $assessment->id);
    if ($workshop->overallfeedbackfiles) {
        $currentdata = file_prepare_standard_filemanager($currentdata, 'feedbackauthorattachment',
            $workshop->overall_feedback_attachment_options(), $workshop->context, 'mod_workshop', 'overallfeedback_attachment',
            $assessment->id);
    }
}
$mform->set_data($currentdata);

if ($mform->is_cancelled()) {
    redirect($workshop->view_url());
} elseif ($assessmenteditable and ($data = $mform->get_data())) {

        $rawgrade = $strategy->save_assessment($assessment, $data);

        $coredata = (object)array('id' => $assessment->id);
    if (isset($data->feedbackauthor_editor)) {
        $coredata->feedbackauthor_editor = $data->feedbackauthor_editor;
        $coredata = file_postupdate_standard_editor($coredata, 'feedbackauthor', $workshop->overall_feedback_content_options(),
            $workshop->context, 'mod_workshop', 'overallfeedback_content', $assessment->id);
        unset($coredata->feedbackauthor_editor);
    }
    if (isset($data->feedbackauthorattachment_filemanager)) {
        $coredata->feedbackauthorattachment_filemanager = $data->feedbackauthorattachment_filemanager;
        $coredata = file_postupdate_standard_filemanager($coredata, 'feedbackauthorattachment',
            $workshop->overall_feedback_attachment_options(), $workshop->context, 'mod_workshop', 'overallfeedback_attachment',
            $assessment->id);
        unset($coredata->feedbackauthorattachment_filemanager);
        if (empty($coredata->feedbackauthorattachment)) {
            $coredata->feedbackauthorattachment = 0;
        }
    }
    if ($canmanage) {
                $coredata->reviewerid = $USER->id;
    }
        if (count((array)$coredata) > 1 ) {
        $DB->update_record('workshop_assessments', $coredata);
    }

    if (!is_null($rawgrade) and isset($data->saveandclose)) {
        if ($canmanage) {
            redirect($workshop->view_url());
        } else {
            redirect($workshop->excompare_url($example->id, $assessment->id));
        }
    } else {
                        redirect($PAGE->url);
    }
}

$output = $PAGE->get_renderer('mod_workshop');      echo $output->header();
echo $output->heading(format_string($workshop->name));
echo $output->heading(get_string('assessedexample', 'workshop'), 3);

$example = $workshop->get_example_by_id($example->id);     echo $output->render($workshop->prepare_example_submission(($example)));

if (trim($workshop->instructreviewers)) {
    $instructions = file_rewrite_pluginfile_urls($workshop->instructreviewers, 'pluginfile.php', $PAGE->context->id,
        'mod_workshop', 'instructreviewers', null, workshop::instruction_editors_options($PAGE->context));
    print_collapsible_region_start('', 'workshop-viewlet-instructreviewers', get_string('instructreviewers', 'workshop'));
    echo $output->box(format_text($instructions, $workshop->instructreviewersformat, array('overflowdiv'=>true)), array('generalbox', 'instructions'));
    print_collapsible_region_end();
}

$assessment = $workshop->get_assessment_by_id($assessment->id);

if ($canmanage and $assessment->weight == 1) {
    $options = array(
        'showreviewer'  => false,
        'showauthor'    => false,
        'showform'      => true,
    );
    $assessment = $workshop->prepare_example_reference_assessment($assessment, $mform, $options);
    $assessment->title = get_string('assessmentreference', 'workshop');
    echo $output->render($assessment);

} else if ($isreviewer) {
    $options = array(
        'showreviewer'  => true,
        'showauthor'    => false,
        'showform'      => true,
    );
    $assessment = $workshop->prepare_example_assessment($assessment, $mform, $options);
    $assessment->title = get_string('assessmentbyyourself', 'workshop');
    echo $output->render($assessment);

} else if ($canmanage) {
    $options = array(
        'showreviewer'  => true,
        'showauthor'    => false,
        'showform'      => true,
        'showweight'    => false,
    );
    $assessment = $workshop->prepare_example_assessment($assessment, $mform, $options);
    echo $output->render($assessment);
}

echo $output->footer();
