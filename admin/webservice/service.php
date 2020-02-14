<?php



require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('forms.php');
require_once($CFG->dirroot . '/webservice/lib.php');

admin_externalpage_setup('externalservice');

$node = $PAGE->settingsnav->find('externalservice', navigation_node::TYPE_SETTING);
$newnode = $PAGE->settingsnav->find('externalservices', navigation_node::TYPE_SETTING);
if ($node && $newnode) {
    $node->display = false;
    $newnode->make_active();
}
$PAGE->navbar->add(get_string('externalservice', 'webservice'));

$id = required_param('id', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$webservicemanager = new webservice;
$renderer = $PAGE->get_renderer('core', 'webservice');
$returnurl = $CFG->wwwroot . "/" . $CFG->admin . "/settings.php?section=externalservices";
$service = $id ? $webservicemanager->get_external_service_by_id($id, MUST_EXIST) : null;

if ($action == 'delete' and confirm_sesskey() and $service and empty($service->component)) {
        if (!$confirm) {
        echo $OUTPUT->header();
        echo $renderer->admin_remove_service_confirmation($service);
        echo $OUTPUT->footer();
        die;
    }
        $webservicemanager->delete_service($service->id);
    $params = array(
        'objectid' => $service->id
    );
    $event = \core\event\webservice_service_deleted::create($params);
    $event->add_record_snapshot('external_services', $service);
    $event->trigger();
    redirect($returnurl);
}

$mform = new external_service_form(null, $service);
if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($servicedata = $mform->get_data()) {
    $servicedata = (object) $servicedata;
    if (!empty($servicedata->requiredcapability) && $servicedata->requiredcapability == "norequiredcapability") {
        $servicedata->requiredcapability = "";
    }

        if (empty($servicedata->id)) {
        $servicedata->id = $webservicemanager->add_external_service($servicedata);
        $params = array(
            'objectid' => $servicedata->id
        );
        $event = \core\event\webservice_service_created::create($params);
        $event->trigger();

                $addfunctionpage = new moodle_url(
                        $CFG->wwwroot . '/' . $CFG->admin . '/webservice/service_functions.php',
                        array('id' => $servicedata->id));
        $returnurl = $addfunctionpage->out(false);
    } else {
                $webservicemanager->update_external_service($servicedata);
        $params = array(
            'objectid' => $servicedata->id
        );
        $event = \core\event\webservice_service_updated::create($params);
        $event->trigger();
    }

    redirect($returnurl);
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();

