<?php




require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

$cmid       = required_param('cmid', PARAM_INT);

$cm         = get_coursemodule_from_id('workshop', $cmid, 0, false, MUST_EXIST);
$course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, false, $cm);
require_capability('mod/workshop:editdimensions', $PAGE->context);

$workshop   = $DB->get_record('workshop', array('id' => $cm->instance), '*', MUST_EXIST);
$workshop   = new workshop($workshop, $cm, $course);


$PAGE->set_url($workshop->editform_url());
$PAGE->set_title($workshop->name);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('editingassessmentform', 'workshop'));

$strategy = $workshop->grading_strategy_instance();

$mform = $strategy->get_edit_strategy_form($PAGE->url);

if ($mform->is_cancelled()) {
    redirect($workshop->view_url());
} elseif ($data = $mform->get_data()) {
    if (($data->workshopid != $workshop->id) or ($data->strategy != $workshop->strategy)) {
                        throw new invalid_parameter_exception('Invalid workshop ID or the grading strategy has changed.');
    }
    $strategy->save_edit_strategy_form($data);
    if (isset($data->saveandclose)) {
        redirect($workshop->view_url());
    } elseif (isset($data->saveandpreview)) {
        redirect($workshop->previewform_url());
    } else {
                redirect($PAGE->url);
    }
}


echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($workshop->name));
echo $OUTPUT->heading(get_string('pluginname', 'workshopform_' . $workshop->strategy), 3);

$mform->display();

echo $OUTPUT->footer();
