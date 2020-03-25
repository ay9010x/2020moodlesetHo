<?php




defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('development', new admin_externalpage('toolphpunit', get_string('pluginname', 'tool_phpunit'), "$CFG->wwwroot/$CFG->admin/tool/phpunit/index.php"));
    $ADMIN->add('development', new admin_externalpage('toolphpunitwebrunner', get_string('pluginname', 'tool_phpunit'), "$CFG->wwwroot/$CFG->admin/tool/phpunit/webrunner.php",
        'moodle/site:config', true));
}
