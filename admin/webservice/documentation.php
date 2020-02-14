<?php



require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require($CFG->dirroot . '/webservice/lib.php');

admin_externalpage_setup('webservicedocumentation');

$functions = $DB->get_records('external_functions', array(), 'name');
$functiondescs = array();
foreach ($functions as $function) {
    $functiondescs[$function->name] = external_api::external_function_info($function);
}

$protocols = array();
$protocols['rest'] = true;
$protocols['xmlrpc'] = true;

$printableformat = optional_param('print', false, PARAM_BOOL);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('core', 'webservice');
echo $renderer->documentation_html($functiondescs,
        $printableformat, $protocols, array(), $PAGE->url);

if (!empty($printableformat)) {
    $PAGE->requires->js_function_call('window.print', array());
}

echo $OUTPUT->footer();

