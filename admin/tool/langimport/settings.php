<?php



defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('language', new admin_externalpage('toollangimport', get_string('pluginname', 'tool_langimport'), "$CFG->wwwroot/$CFG->admin/tool/langimport/index.php"));
}

