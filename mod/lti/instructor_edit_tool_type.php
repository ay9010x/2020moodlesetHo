<?php



require_once('../../config.php');
require_once($CFG->dirroot.'/mod/lti/edit_form.php');
require_once($CFG->dirroot.'/mod/lti/lib.php');

$courseid = required_param('course', PARAM_INT);

require_login($courseid, false);
$url = new moodle_url('/mod/lti/instructor_edit_tool_type.php');
$PAGE->set_url($url);
$PAGE->set_pagelayout('popup');
$PAGE->set_title(get_string('edittype', 'mod_lti'));

$action = optional_param('action', null, PARAM_TEXT);
$typeid = optional_param('typeid', null, PARAM_INT);

require_sesskey();

require_capability('mod/lti:addcoursetool', context_course::instance($courseid));

if (!empty($typeid)) {
    $type = lti_get_type($typeid);
    if ($type->course != $courseid) {
        throw new Exception('You do not have permissions to edit this tool type.');
        die;
    }
}

if ($action == 'delete') {
    lti_delete_type($typeid);
    die;
}

$timeout = 0;
if (defined('BEHAT_SITE_RUNNING') && BEHAT_SITE_RUNNING) {
    $timeout = 2000;
}

echo $OUTPUT->header();

$form = new mod_lti_edit_types_form();

if ($action == 'add' || $action == 'edit') {
    if ($action == 'edit') {
        $type = lti_get_type_type_config($typeid);
        $form->set_data($type);
    }
    echo $OUTPUT->heading(get_string('toolsetup', 'lti'));
    $form->display();
} else {
    $script = '';
    $closewindow = <<<EOF
        setTimeout(function() {
            window.close();
        }, $timeout);
EOF;

    if ($data = $form->get_data()) {
        $type = new stdClass();

        if (!empty($typeid)) {
            $type->id = $typeid;

            lti_load_type_if_cartridge($data);

            lti_update_type($type, $data);

            $fromdb = lti_get_type($typeid);
            $json = json_encode($fromdb);

                        $script = <<<EOF
                window.opener.M.mod_lti.editor.updateToolType({$json});
EOF;
        } else {
            $type->state = LTI_TOOL_STATE_CONFIGURED;
            $type->course = $COURSE->id;

            lti_load_type_if_cartridge($data);

            $id = lti_add_type($type, $data);

            $fromdb = lti_get_type($id);
            $json = json_encode($fromdb);

                        $script = <<<EOF
                window.opener.M.mod_lti.editor.addToolType({$json});
EOF;
        }
    }
    echo html_writer::script($script . $closewindow);
}

echo $OUTPUT->footer();
