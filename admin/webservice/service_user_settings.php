<?php



require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/webservice/lib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/webservice/forms.php');

$serviceid = required_param('serviceid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

admin_externalpage_setup('externalserviceusersettings');

$PAGE->set_url('/' . $CFG->admin . '/webservice/service_user_settings.php',
        array('id' => $serviceid, 'userid'  => $userid));
$node = $PAGE->settingsnav->find('externalservices', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}
$PAGE->navbar->add(get_string('serviceusers', 'webservice'),
        new moodle_url('/' . $CFG->admin . '/webservice/service_users.php', array('id' => $serviceid)));
$PAGE->navbar->add(get_string('serviceusersettings', 'webservice'));

$formaction = new moodle_url('', array('id' => $serviceid, 'userid' => $userid));
$returnurl = new moodle_url('/' . $CFG->admin . '/webservice/service_users.php', array('id' => $serviceid));

$webservicemanager = new webservice();
$serviceuser = $webservicemanager->get_ws_authorised_user($serviceid, $userid);
$usersettingsform = new external_service_authorised_user_settings_form($formaction, $serviceuser);
$settingsformdata = $usersettingsform->get_data();

if ($usersettingsform->is_cancelled()) {
    redirect($returnurl);

} else if (!empty($settingsformdata) and confirm_sesskey()) {
        $settingsformdata = (object)$settingsformdata;

    $serviceuserinfo = new stdClass();
    $serviceuserinfo->id = $serviceuser->serviceuserid;
    $serviceuserinfo->iprestriction = $settingsformdata->iprestriction;
    $serviceuserinfo->validuntil = $settingsformdata->validuntil;

    $webservicemanager->update_ws_authorised_user($serviceuserinfo);

    
        $notification = $OUTPUT->notification(get_string('usersettingssaved', 'webservice'), 'notifysuccess');
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('serviceusersettings', 'webservice'), 3, 'main');
if (!empty($notification)) {
    echo $notification;
}
$usersettingsform->display();

echo $OUTPUT->footer();
