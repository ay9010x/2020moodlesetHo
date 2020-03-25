<?php



defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
        $ADMIN->add('root', new admin_externalpage('assignmentupgrade',
            get_string('pluginname', 'tool_assignmentupgrade'),
            new moodle_url('/admin/tool/assignmentupgrade/index.php')));
}
