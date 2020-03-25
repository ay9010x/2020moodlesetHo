<?php



require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/lti/edit_form.php');
require_once($CFG->dirroot.'/mod/lti/locallib.php');

$action       = optional_param('action', null, PARAM_ALPHANUMEXT);
$id           = optional_param('id', null, PARAM_INT);
$tab          = optional_param('tab', '', PARAM_ALPHAEXT);
$returnto     = optional_param('returnto', '', PARAM_ALPHA);

if ($returnto == 'toolconfigure') {
    $returnurl = new moodle_url($CFG->wwwroot . '/mod/lti/toolconfigure.php');
}

require_login(0, false);

require_sesskey();

if (!empty($id)) {
    $type = lti_get_type_type_config($id);
    if (!empty($type->toolproxyid)) {
        $sesskey = required_param('sesskey', PARAM_RAW);
        $params = array('action' => $action, 'id' => $id, 'sesskey' => $sesskey, 'tab' => $tab);
        if (!empty($returnto)) {
            $params['returnto'] = $returnto;
        }
        $redirect = new moodle_url('/mod/lti/toolssettings.php', $params);
        redirect($redirect);
    }
} else {
    $type = new stdClass();
        $type->lti_icon = '';
    $type->lti_secureicon = '';
}

$pageurl = new moodle_url('/mod/lti/typessettings.php');
if (!empty($id)) {
    $pageurl->param('id', $id);
}
if (!empty($returnto)) {
    $pageurl->param('returnto', $returnto);
}
$PAGE->set_url($pageurl);

admin_externalpage_setup('managemodules'); 
$redirect = "$CFG->wwwroot/$CFG->admin/settings.php?section=modsettinglti&tab={$tab}";
if (!empty($returnurl)) {
    $redirect = $returnurl;
}

if ($action == 'accept') {
    lti_set_state_for_type($id, LTI_TOOL_STATE_CONFIGURED);
    redirect($redirect);
} else if ($action == 'reject') {
    lti_set_state_for_type($id, LTI_TOOL_STATE_REJECTED);
    redirect($redirect);
} else if ($action == 'delete') {
    lti_delete_type($id);
    redirect($redirect);
}

if (lti_request_is_using_ssl() && !empty($type->lti_secureicon)) {
    $type->oldicon = $type->lti_secureicon;
} else {
    $type->oldicon = $type->lti_icon;
}

$form = new mod_lti_edit_types_form($pageurl, (object)array('isadmin' => true, 'istool' => false));

if ($data = $form->get_data()) {
    $type = new stdClass();
    if (!empty($id)) {
        $type->id = $id;
        lti_update_type($type, $data);

        redirect($redirect);
    } else {
        $type->state = LTI_TOOL_STATE_CONFIGURED;
        lti_add_type($type, $data);

        redirect($redirect);
    }
} else if ($form->is_cancelled()) {
    redirect($redirect);
}

$PAGE->set_title("$SITE->shortname: " . get_string('toolsetup', 'lti'));
$PAGE->navbar->add(get_string('lti_administration', 'lti'), $CFG->wwwroot.'/'.$CFG->admin.'/settings.php?section=modsettinglti');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('toolsetup', 'lti'));
echo $OUTPUT->box_start('generalbox');

if ($action == 'update') {
    $form->set_data($type);
}

$form->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
