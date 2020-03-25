<?php



defined('MOODLE_INTERNAL') || die;

$ADMIN->add('roles', new admin_externalpage(
    'toolcapability',
    get_string('pluginname', 'tool_capability'),
    "$CFG->wwwroot/$CFG->admin/tool/capability/index.php",
    'moodle/role:manage'
));
