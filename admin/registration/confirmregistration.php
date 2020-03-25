<?php




require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/registration/lib.php');

$newtoken = optional_param('newtoken', '', PARAM_ALPHANUM);
$url = optional_param('url', '', PARAM_URL);
$hubname = optional_param('hubname', '', PARAM_TEXT);
$token = optional_param('token', '', PARAM_TEXT);
$error = optional_param('error', '', PARAM_ALPHANUM);

admin_externalpage_setup('registrationhubs');

if (!empty($error) and $error == 'urlalreadyexist') {
    throw new moodle_exception('urlalreadyregistered', 'hub',
            $CFG->wwwroot . '/' . $CFG->admin . '/registration/index.php');
}

$registrationmanager = new registration_manager();
$registeredhub = $registrationmanager->get_unconfirmedhub($url);
if (!empty($registeredhub) and $registeredhub->token == $token) {

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('registrationconfirmed', 'hub'), 3, 'main');

    $registeredhub->token = $newtoken;
    $registeredhub->confirmed = 1;
    $registeredhub->hubname = $hubname;
    $registrationmanager->update_registeredhub($registeredhub);

        echo $OUTPUT->notification(get_string('registrationconfirmedon', 'hub'), 'notifysuccess');

        $registrationpage = new moodle_url('/admin/registration/index.php');
    $continuebutton = $OUTPUT->render(new single_button($registrationpage, get_string('continue', 'hub')));
    $continuebutton = html_writer::tag('div', $continuebutton, array('class' => 'mdl-align'));
    echo $continuebutton;

    if (!extension_loaded('xmlrpc')) {
                $xmlrpcnotification = $OUTPUT->doc_link('admin/environment/php_extension/xmlrpc', '');
        $xmlrpcnotification .= get_string('xmlrpcdisabledregistration', 'hub');
        echo $OUTPUT->notification($xmlrpcnotification);
    }

    echo $OUTPUT->footer();
} else {
    throw new moodle_exception('wrongtoken', 'hub',
            $CFG->wwwroot . '/' . $CFG->admin . '/registration/index.php');
}


