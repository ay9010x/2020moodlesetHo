<?php

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('report_landview', get_string('courseview', 'report_landview'),
        $CFG->wwwroot.'/report/landview/index.php', 'report/landview:view'));