<?php





require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/mnet/services_form.php');
$mnet = get_mnet_environment();

require_login();
admin_externalpage_setup('mnetpeers');

$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

$hostid = required_param('hostid', PARAM_INT);

$mnet_peer = new mnet_peer();
$mnet_peer->set_id($hostid);

$mform = new mnet_services_form(null, array('peer' => $mnet_peer));
if ($formdata = $mform->get_data()) {
    if (!isset($formdata->publish)) {
        $formdata->publish = array();
    }
    if (!isset($formdata->subscribe)) {
        $formdata->subscribe = array();
    }
    foreach($formdata->exists as $key => $value) {
        $host2service   = $DB->get_record('mnet_host2service', array('hostid'=>$hostid, 'serviceid'=>$key));
        $publish        = (array_key_exists($key, $formdata->publish)) ? $formdata->publish[$key] : 0;
        $subscribe      = (array_key_exists($key, $formdata->subscribe)) ? $formdata->subscribe[$key] : 0;

        if ($publish != 1 && $subscribe != 1) {
            if (false == $host2service) {
                            } else {
                                $DB->delete_records('mnet_host2service', array('hostid' => $hostid, 'serviceid'=>$key));
            }
        } elseif (false == $host2service && ($publish == 1 || $subscribe == 1)) {
            $host2service = new stdClass();
            $host2service->hostid = $hostid;
            $host2service->serviceid = $key;

            $host2service->publish = $publish;
            $host2service->subscribe = $subscribe;

            $host2service->id = $DB->insert_record('mnet_host2service', $host2service);
        } elseif ($host2service->publish != $publish || $host2service->subscribe != $subscribe) {
            $host2service->publish   = $publish;
            $host2service->subscribe = $subscribe;
            $DB->update_record('mnet_host2service', $host2service);
        }
    }
    $redirecturl = new moodle_url('/admin/mnet/services.php?hostid=' . $hostid);
    redirect($redirecturl, get_string('changessaved'));
}

echo $OUTPUT->header();
$currenttab = 'mnetservices';
require_once($CFG->dirroot . '/' . $CFG->admin . '/mnet/tabs.php');
echo $OUTPUT->box_start();
$s = mnet_get_service_info($mnet_peer, false); $mform->set_data($s);
$mform->display();
echo $OUTPUT->box_end();

echo $OUTPUT->footer();
