<?php



defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('server', new admin_externalpage('tool_filetypes',
            new lang_string('pluginname', 'tool_filetypes'),
            $CFG->wwwroot . '/admin/tool/filetypes/index.php'));
}
