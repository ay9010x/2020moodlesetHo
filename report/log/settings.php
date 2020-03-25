<?php



defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('reportlog', get_string('log', 'admin'),
        $CFG->wwwroot . "/report/log/index.php?id=0", 'report/log:view'));

$settings = null;
