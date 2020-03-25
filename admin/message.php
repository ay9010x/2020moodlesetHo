<?php


require_once(dirname(__FILE__) . '/../config.php');
require_once($CFG->dirroot . '/message/lib.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('managemessageoutputs');

require_capability('moodle/site:config', context_system::instance());

$disable    = optional_param('disable', 0, PARAM_INT);
$enable     = optional_param('enable', 0, PARAM_INT);

$headingtitle = get_string('managemessageoutputs', 'message');

if (!empty($disable) && confirm_sesskey()) {
    if (!$processor = $DB->get_record('message_processors', array('id'=>$disable))) {
        print_error('outputdoesnotexist', 'message');
    }
    $DB->set_field('message_processors', 'enabled', '0', array('id'=>$processor->id));          core_plugin_manager::reset_caches();
}

if (!empty($enable) && confirm_sesskey()) {
    if (!$processor = $DB->get_record('message_processors', array('id'=>$enable))) {
        print_error('outputdoesnotexist', 'message');
    }
    $DB->set_field('message_processors', 'enabled', '1', array('id'=>$processor->id));          core_plugin_manager::reset_caches();
}

if ($disable || $enable) {
    $url = new moodle_url('message.php');
    redirect($url);
}
$PAGE->set_context(context_system::instance());

$renderer = $PAGE->get_renderer('core', 'message');

$processors = get_message_processors();
$messageoutputs = $renderer->manage_messageoutputs($processors);

echo $OUTPUT->header();
echo $OUTPUT->heading($headingtitle);
echo $messageoutputs;
echo $OUTPUT->footer();
