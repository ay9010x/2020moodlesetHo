<?php



defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('experimental', new admin_externalpage('tooldbtransfer', get_string('dbtransfer', 'tool_dbtransfer'),
        $CFG->wwwroot.'/'.$CFG->admin.'/tool/dbtransfer/index.php', 'moodle/site:config', false));
        $ADMIN->add('experimental', new admin_externalpage('tooldbexport', get_string('dbexport', 'tool_dbtransfer'),
        $CFG->wwwroot.'/'.$CFG->admin.'/tool/dbtransfer/dbexport.php', 'moodle/site:config', true));
}
