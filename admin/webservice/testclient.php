<?php




require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . "/" . $CFG->admin . "/webservice/testclient_forms.php");

$function = optional_param('function', '', PARAM_PLUGIN);
$protocol = optional_param('protocol', '', PARAM_ALPHA);
$authmethod = optional_param('authmethod', '', PARAM_ALPHA);

$PAGE->set_url('/' . $CFG->admin . '/webservice/testclient.php');
$PAGE->navbar->ignore_active(true);
$PAGE->navbar->add(get_string('administrationsite'));
$PAGE->navbar->add(get_string('development', 'admin'));
$PAGE->navbar->add(get_string('testclient', 'webservice'),
        new moodle_url('/' . $CFG->admin . '/webservice/testclient.php'));
if (!empty($function)) {
    $PAGE->navbar->add($function);
}

admin_externalpage_setup('testclient');

$allfunctions = $DB->get_records('external_functions', array(), 'name ASC');
$functions = array();
foreach ($allfunctions as $f) {
    $finfo = external_api::external_function_info($f);
    if (!empty($finfo->testclientpath) and file_exists($CFG->dirroot.'/'.$finfo->testclientpath)) {
                include_once($CFG->dirroot.'/'.$finfo->testclientpath);
    }
    $class = $f->name.'_form';
    if (class_exists($class)) {
        $functions[$f->name] = $f->name;
        continue;
    }
}

if (!isset($functions[$function])) {
    $function = '';
}

$available_protocols = core_component::get_plugin_list('webservice');
$active_protocols = empty($CFG->webserviceprotocols) ? array() : explode(',', $CFG->webserviceprotocols);
$protocols = array();
foreach ($active_protocols as $p) {
    if (empty($available_protocols[$p])) {
        continue;
    }
    include_once($available_protocols[$p].'/locallib.php');
    if (!class_exists('webservice_'.$p.'_test_client')) {
                continue;
    }
    $protocols[$p] = get_string('pluginname', 'webservice_'.$p);
}
if (!isset($protocols[$protocol])) {     $protocol = '';
}

if (!$function or !$protocol) {
    $mform = new webservice_test_client_form(null, array($functions, $protocols));
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('testclient', 'webservice'));
    echo $OUTPUT->box_start();
    $url = new moodle_url('/' . $CFG->admin . '/settings.php?section=debugging');
    $atag =html_writer::start_tag('a', array('href' => $url)).get_string('debug', 'admin').html_writer::end_tag('a');
    $descparams = new stdClass();
    $descparams->atag = $atag;
    $descparams->mode = get_string('debugnormal', 'admin');
    echo get_string('testclientdescription', 'webservice', $descparams);
    echo $OUTPUT->box_end();

    $mform->display();
    echo $OUTPUT->footer();
    die;
}

$class = $function.'_form';

$mform = new $class(null, array('authmethod' => $authmethod));
$mform->set_data(array('function'=>$function, 'protocol'=>$protocol));

if ($mform->is_cancelled()) {
    redirect('testclient.php');

} else if ($data = $mform->get_data()) {

    $functioninfo = external_api::external_function_info($function);

        require_once("$CFG->dirroot/webservice/$protocol/locallib.php");

    $testclientclass = "webservice_{$protocol}_test_client";
    if (!class_exists($testclientclass)) {
        throw new coding_exception('Missing WS test class in protocol '.$protocol);
    }
    $testclient = new $testclientclass();

    $serverurl = "$CFG->wwwroot/webservice/$protocol/";
    if ($authmethod == 'simple') {
        $serverurl .= 'simpleserver.php';
        $serverurl .= '?wsusername='.urlencode($data->wsusername);
        $serverurl .= '&wspassword='.urlencode($data->wspassword);
    } else if ($authmethod == 'token') {
        $serverurl .= 'server.php';
        $serverurl .= '?wstoken='.urlencode($data->token);
    }

        $params = $mform->get_params();

        $params = external_api::validate_parameters($functioninfo->parameters_desc, $params);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'webservice_'.$protocol).': '.$function);

    echo 'URL: '.s($serverurl);
    echo $OUTPUT->box_start();

    try {
        $response = $testclient->simpletest($serverurl, $function, $params);
        echo str_replace("\n", '<br />', s(var_export($response, true)));
    } catch (Exception $ex) {
                echo str_replace("\n", '<br />', s($ex));
    }

    echo $OUTPUT->box_end();
    $mform->display();
    echo $OUTPUT->footer();
    die;

} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'webservice_'.$protocol).': '.$function);
    $mform->display();
    echo $OUTPUT->footer();
    die;
}
