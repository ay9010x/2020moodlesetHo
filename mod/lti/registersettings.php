<?php



require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/lti/register_form.php');
require_once($CFG->dirroot.'/mod/lti/locallib.php');

$action       = optional_param('action', null, PARAM_ALPHANUMEXT);
$id           = optional_param('id', null, PARAM_INT);
$tab          = optional_param('tab', '', PARAM_ALPHAEXT);
$returnto     = optional_param('returnto', '', PARAM_ALPHA);

if ($returnto == 'toolconfigure') {
    $returnurl = new moodle_url($CFG->wwwroot . '/mod/lti/toolconfigure.php');
}

require_login(0, false);

$isupdate = !empty($id);
$pageurl = new moodle_url('/mod/lti/registersettings.php');
if ($isupdate) {
    $pageurl->param('id', $id);
}
if (!empty($returnto)) {
    $pageurl->param('returnto', $returnto);
}
$PAGE->set_url($pageurl);

admin_externalpage_setup('ltitoolproxies');

$redirect = new moodle_url('/mod/lti/toolproxies.php', array('tab' => $tab));
$redirect = $redirect->out();
if (!empty($returnurl)) {
    $redirect = $returnurl;
}

require_sesskey();

if ($action == 'delete') {
    lti_delete_tool_proxy($id);
    redirect($redirect);
}

$data = array();
if ($isupdate) {
    $data['isupdate'] = true;
}

$form = new mod_lti_register_types_form($pageurl, (object)$data);

if ($form->is_cancelled()) {
    redirect($redirect);
} else if ($data = $form->get_data()) {
    $id = lti_add_tool_proxy($data);
    redirect($redirect);
} else {
    $PAGE->set_title("{$SITE->shortname}: " . get_string('toolregistration', 'lti'));
    $PAGE->navbar->add(get_string('lti_administration', 'lti'), $redirect);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('toolregistration', 'lti'));
    echo $OUTPUT->box_start('generalbox');
    if ($action == 'update') {
        $toolproxy = lti_get_tool_proxy_config($id);
        $form->set_data($toolproxy);
        if ($toolproxy->state == LTI_TOOL_PROXY_STATE_ACCEPTED) {
            $form->disable_fields();
        }
    }
    $form->display();

    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
}
