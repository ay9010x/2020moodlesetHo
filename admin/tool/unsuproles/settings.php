<?php



defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('roles', new admin_externalpage('toolunsuproles', get_string('pluginname', 'tool_unsuproles'), "$CFG->wwwroot/$CFG->admin/tool/unsuproles/index.php", array('moodle/site:config', 'moodle/role:assign')));
}
