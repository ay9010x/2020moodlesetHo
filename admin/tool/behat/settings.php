<?php




defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $url = $CFG->wwwroot . '/' . $CFG->admin . '/tool/behat/index.php';
    $ADMIN->add('development', new admin_externalpage('toolbehat', get_string('pluginname', 'tool_behat'), $url));
}
