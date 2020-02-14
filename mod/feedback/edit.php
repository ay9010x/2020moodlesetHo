<?php



require_once('../../config.php');
require_once('lib.php');
require_once('edit_form.php');

feedback_init_feedback_session();

$id = required_param('id', PARAM_INT);

if (($formdata = data_submitted()) AND !confirm_sesskey()) {
    print_error('invalidsesskey');
}

$do_show = optional_param('do_show', 'edit', PARAM_ALPHA);
$switchitemrequired = optional_param('switchitemrequired', false, PARAM_INT);
$deleteitem = optional_param('deleteitem', false, PARAM_INT);

$current_tab = $do_show;

$url = new moodle_url('/mod/feedback/edit.php', array('id'=>$id, 'do_show'=>$do_show));

list($course, $cm) = get_course_and_cm_from_cmid($id, 'feedback');

$context = context_module::instance($cm->id);
require_login($course, false, $cm);
require_capability('mod/feedback:edititems', $context);
$feedback = $PAGE->activityrecord;
$feedbackstructure = new mod_feedback_structure($feedback, $cm);

if ($switchitemrequired) {
    require_sesskey();
    $items = $feedbackstructure->get_items();
    if (isset($items[$switchitemrequired])) {
        feedback_switch_item_required($items[$switchitemrequired]);
    }
    redirect($url);
}

if ($deleteitem) {
    require_sesskey();
    $items = $feedbackstructure->get_items();
    if (isset($items[$deleteitem])) {
        feedback_delete_item($deleteitem);
    }
    redirect($url);
}

$cancreatetemplates = has_capability('mod/feedback:createprivatetemplate', $context) ||
            has_capability('mod/feedback:createpublictemplate', $context);
$create_template_form = new feedback_edit_create_template_form(null, array('id' => $id));
if ($data = $create_template_form->get_data()) {
        if (!$cancreatetemplates) {
        print_error('cannotsavetempl', 'feedback', $url);
    }
    $ispublic = !empty($data->ispublic) ? 1 : 0;
    if (!feedback_save_as_template($feedback, $data->templatename, $ispublic)) {
        redirect($url, get_string('saving_failed', 'feedback'), null, \core\output\notification::NOTIFY_ERROR);
    } else {
        redirect($url, get_string('template_saved', 'feedback'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

$lastposition = 0;
$feedbackitems = $DB->get_records('feedback_item', array('feedback'=>$feedback->id), 'position');
if (is_array($feedbackitems)) {
    $feedbackitems = array_values($feedbackitems);
    if (count($feedbackitems) > 0) {
        $lastitem = $feedbackitems[count($feedbackitems)-1];
        $lastposition = $lastitem->position;
    } else {
        $lastposition = 0;
    }
}
$lastposition++;


$use_template_form = new feedback_edit_use_template_form('use_templ.php', array('course' => $course, 'id' => $id));

$strfeedbacks = get_string('modulenameplural', 'feedback');
$strfeedback  = get_string('modulename', 'feedback');

$PAGE->set_url('/mod/feedback/edit.php', array('id'=>$cm->id, 'do_show'=>$do_show));
$PAGE->set_heading($course->fullname);
$PAGE->set_title($feedback->name);

if (count($feedbackitems) > 1) {
    if ($do_show == 'edit') {
        $PAGE->requires->strings_for_js(array(
               'pluginname',
               'move_item',
               'position',
            ), 'feedback');
        $PAGE->requires->yui_module('moodle-mod_feedback-dragdrop', 'M.mod_feedback.init_dragdrop',
                array(array('cmid' => $cm->id)));
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($feedback->name));

require('tabs.php');


if ($do_show == 'templates') {
        $use_template_form->display();

    if ($cancreatetemplates) {
        $deleteurl = new moodle_url('/mod/feedback/delete_template.php', array('id' => $id));
        $create_template_form->display();
        echo '<p><a href="'.$deleteurl->out().'">'.
             get_string('delete_templates', 'feedback').
             '</a></p>';
    } else {
        echo '&nbsp;';
    }

    if (has_capability('mod/feedback:edititems', $context)) {
        $urlparams = array('action'=>'exportfile', 'id'=>$id);
        $exporturl = new moodle_url('/mod/feedback/export.php', $urlparams);
        $importurl = new moodle_url('/mod/feedback/import.php', array('id'=>$id));
        echo '<p>
            <a href="'.$exporturl->out().'">'.get_string('export_questions', 'feedback').'</a>/
            <a href="'.$importurl->out().'">'.get_string('import_questions', 'feedback').'</a>
        </p>';
    }
}

if ($do_show == 'edit') {
    
    $select = new single_select(new moodle_url('/mod/feedback/edit_item.php',
            array('cmid' => $id, 'position' => $lastposition, 'sesskey' => sesskey())),
        'typ', feedback_load_feedback_items_options());
    $select->label = get_string('add_item', 'mod_feedback');
    echo $OUTPUT->render($select);


    $form = new mod_feedback_complete_form(mod_feedback_complete_form::MODE_EDIT,
            $feedbackstructure, 'feedback_edit_form');
    echo '<div id="feedback_dragarea">';     $form->display();
    echo '</div>';
}

echo $OUTPUT->footer();
