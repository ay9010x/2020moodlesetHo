<?php


defined('MOODLE_INTERNAL') || die;

$temp = new admin_externalpage(
    'toolmonitorrules',
    get_string('managerules', 'tool_monitor'),
    new moodle_url('/admin/tool/monitor/managerules.php', array('courseid' => 0)),
    'tool/monitor:managerules'
);
$ADMIN->add('reports', $temp);
