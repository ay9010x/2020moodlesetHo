<?php



require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/webservice/forms.php');
require_once($CFG->libdir . '/externallib.php');

$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$tokenid = optional_param('tokenid', '', PARAM_SAFEDIR);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

admin_externalpage_setup('addwebservicetoken');

$node = $PAGE->settingsnav->find('addwebservicetoken', navigation_node::TYPE_SETTING);
$newnode = $PAGE->settingsnav->find('webservicetokens', navigation_node::TYPE_SETTING);
if ($node && $newnode) {
    $node->display = false;
    $newnode->make_active();
}

require_capability('moodle/site:config', context_system::instance());

$tokenlisturl = new moodle_url("/" . $CFG->admin . "/settings.php", array('section' => 'webservicetokens'));

require_once($CFG->dirroot . "/webservice/lib.php");
$webservicemanager = new webservice();

switch ($action) {

    case 'create':
        $mform = new web_service_token_form(null, array('action' => 'create'));
        $data = $mform->get_data();
        if ($mform->is_cancelled()) {
            redirect($tokenlisturl);
        } else if ($data and confirm_sesskey()) {
            ignore_user_abort(true);

                        $selectedservice = $webservicemanager->get_external_service_by_id($data->service);
            if ($selectedservice->restrictedusers) {
                $restricteduser = $webservicemanager->get_ws_authorised_user($data->service, $data->user);
                if (empty($restricteduser)) {
                    $allowuserurl = new moodle_url('/' . $CFG->admin . '/webservice/service_users.php',
                            array('id' => $selectedservice->id));
                    $allowuserlink = html_writer::tag('a', $selectedservice->name , array('href' => $allowuserurl));
                    $errormsg = $OUTPUT->notification(get_string('usernotallowed', 'webservice', $allowuserlink));
                }
            }

                        $user = $DB->get_record('user', array('id' => $data->user));
            if ($user->id == $CFG->siteguest or $user->deleted or !$user->confirmed or $user->suspended) {
                throw new moodle_exception('forbiddenwsuser', 'webservice');
            }

                        if (empty($errormsg)) {
                                                                external_generate_token(EXTERNAL_TOKEN_PERMANENT, $data->service,
                        $data->user, context_system::instance(),
                        $data->validuntil, $data->iprestriction);
                redirect($tokenlisturl);
            }
        }

                echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('createtoken', 'webservice'));
        if (!empty($errormsg)) {
            echo $errormsg;
        }
        $mform->display();
        echo $OUTPUT->footer();
        die;
        break;

    case 'delete':
        $token = $webservicemanager->get_created_by_user_ws_token($USER->id, $tokenid);

                if ($confirm and confirm_sesskey()) {
            $webservicemanager->delete_user_ws_token($token->id);
            redirect($tokenlisturl);
        }

                echo $OUTPUT->header();
        $renderer = $PAGE->get_renderer('core', 'webservice');
        echo $renderer->admin_delete_token_confirmation($token);
        echo $OUTPUT->footer();
        die;
        break;

    default:
                redirect($tokenlisturl);
        break;
}
