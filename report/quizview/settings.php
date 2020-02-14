<?php

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('report_quizview', get_string('quizview', 'report_quizview'),
        $CFG->wwwroot.'/report/quizview/index.php', 'report/quizview:view'));