<?php

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('report_materialview', get_string('materialview', 'report_materialview'),
        $CFG->wwwroot.'/report/materialview/index.php', 'report/materialview:view'));