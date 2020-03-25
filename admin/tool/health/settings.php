<?php



defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('unsupported', new admin_externalpage('toolhealth', get_string('pluginname', 'tool_health'), $CFG->wwwroot.'/'.$CFG->admin.'/tool/health/index.php', 'moodle/site:config', true));
}
