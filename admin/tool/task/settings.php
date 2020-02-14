<?php



defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('server', new admin_externalpage('scheduledtasks', new lang_string('scheduledtasks','tool_task'), "$CFG->wwwroot/$CFG->admin/tool/task/scheduledtasks.php"));
}
