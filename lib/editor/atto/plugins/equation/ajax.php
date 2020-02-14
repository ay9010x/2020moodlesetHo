<?php



define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../../../../config.php');

$contextid = required_param('contextid', PARAM_INT);

list($context, $course, $cm) = get_context_info_array($contextid);
$PAGE->set_url('/lib/editor/atto/plugins/equation/ajax.php');
$PAGE->set_context($context);

require_login($course, false, $cm);
require_sesskey();

$action = required_param('action', PARAM_ALPHA);

if ($action === 'filtertext') {
    $text = required_param('text', PARAM_RAW);

    $result = filter_manager::instance()->filter_text($text, $context);
    echo $OUTPUT->header();
    echo $result;

    die();
}

print_error('invalidarguments');
