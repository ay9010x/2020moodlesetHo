<?php




require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

$PAGE->set_url('/' . $CFG->admin . '/webservice/protocols.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$returnurl = $CFG->wwwroot . "/" . $CFG->admin . "/settings.php?section=webserviceprotocols";

$action     = optional_param('action', '', PARAM_ALPHANUMEXT);
$webservice = optional_param('webservice', '', PARAM_SAFEDIR);
$confirm    = optional_param('confirm', 0, PARAM_BOOL);

$available_webservices = core_component::get_plugin_list('webservice');
if (!empty($webservice) and empty($available_webservices[$webservice])) {
    redirect($returnurl);
}

$active_webservices = empty($CFG->webserviceprotocols) ? array() : explode(',', $CFG->webserviceprotocols);
foreach ($active_webservices as $key=>$active) {
    if (empty($available_webservices[$active])) {
        unset($active_webservices[$key]);
    }
}


if (!confirm_sesskey()) {
    redirect($returnurl);
}

switch ($action) {

    case 'disable':
                $key = array_search($webservice, $active_webservices);
        unset($active_webservices[$key]);
        break;

    case 'enable':
                if (!in_array($webservice, $active_webservices)) {
            $active_webservices[] = $webservice;
            $active_webservices = array_unique($active_webservices);
        }
        break;

    default:
        break;
}

set_config('webserviceprotocols', implode(',', $active_webservices));

redirect($returnurl);
