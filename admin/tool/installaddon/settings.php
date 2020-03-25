<?php




defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig and empty($CFG->disableupdateautodeploy)) {

    $ADMIN->add('modules', new admin_externalpage('tool_installaddon_index',
        get_string('installaddons', 'tool_installaddon'),
        "$CFG->wwwroot/$CFG->admin/tool/installaddon/index.php"), 'modsettings');
}
