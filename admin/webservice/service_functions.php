<?php



require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/webservice/lib.php');
require_once('forms.php');

$serviceid = required_param('id', PARAM_INT);
$functionid = optional_param('fid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

admin_externalpage_setup('externalservicefunctions');

$PAGE->set_url('/' . $CFG->admin . '/webservice/service_functions.php', array('id' => $serviceid));
$node = $PAGE->settingsnav->find('externalservices', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}
$PAGE->navbar->add(get_string('functions', 'webservice'),
        new moodle_url('/' . $CFG->admin . '/webservice/service_functions.php', array('id' => $serviceid)));

$service = $DB->get_record('external_services', array('id' => $serviceid), '*', MUST_EXIST);
$webservicemanager = new webservice();
$renderer = $PAGE->get_renderer('core', 'webservice');
$functionlisturl = new moodle_url('/' . $CFG->admin . '/webservice/service_functions.php',
        array('id' => $serviceid));

switch ($action) {
    case 'add':
        $PAGE->navbar->add(get_string('addfunctions', 'webservice'));
                if (confirm_sesskey() and $service and empty($service->component)) {
            $mform = new external_service_functions_form(null,
                    array('action' => 'add', 'id' => $service->id));

                        if ($mform->is_cancelled()) {
                redirect($functionlisturl);
            }

                        if ($data = $mform->get_data()) {
                ignore_user_abort(true);                 foreach ($data->fids as $fid) {
                    $function = $webservicemanager->get_external_function_by_id(
                            $fid, MUST_EXIST);
                                        if (!$webservicemanager->service_function_exists($function->name,
                            $service->id)) {
                        $webservicemanager->add_external_function_to_service(
                                $function->name, $service->id);
                    }
                }
                redirect($functionlisturl);
            }

                        echo $OUTPUT->header();
            echo $OUTPUT->heading($service->name);
            $mform->display();
            echo $OUTPUT->footer();
            die;
        }

        break;

    case 'delete':
        $PAGE->navbar->add(get_string('removefunction', 'webservice'));
                if (confirm_sesskey() and $service and empty($service->component)) {
                        $function = $webservicemanager->get_external_function_by_id(
                            $functionid, MUST_EXIST);

                        if (!$confirm) {
                echo $OUTPUT->header();
                echo $renderer->admin_remove_service_function_confirmation($function, $service);
                echo $OUTPUT->footer();
                die;
            }

                        $webservicemanager->remove_external_function_from_service($function->name,
                   $service->id);
            redirect($functionlisturl);
        }
        break;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('addservicefunction', 'webservice', $service->name));
$functions = $webservicemanager->get_external_functions(array($service->id));
echo $renderer->admin_service_function_list($functions, $service);
echo $OUTPUT->footer();

