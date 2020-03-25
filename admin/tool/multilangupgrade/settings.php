<?php



defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
        $ADMIN->add('root', new admin_externalpage('toolmultilangupgrade', get_string('pluginname', 'tool_multilangupgrade'), $CFG->wwwroot.'/'.$CFG->admin.'/tool/multilangupgrade/index.php', 'moodle/site:config', !empty($CFG->filter_multilang_converted)));
}
