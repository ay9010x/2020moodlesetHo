<?php



defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('unsupported', new admin_externalpage('toolreplace', get_string('pluginname', 'tool_replace'), $CFG->wwwroot.'/'.$CFG->admin.'/tool/replace/index.php', 'moodle/site:config', true));
}
