<?php



defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('development', new admin_externalpage('toolxmld', get_string('pluginname', 'tool_xmldb'), "$CFG->wwwroot/$CFG->admin/tool/xmldb/"));
}
