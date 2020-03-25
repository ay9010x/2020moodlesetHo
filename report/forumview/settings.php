<?php

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('report_forumview', get_string('forumview', 'report_forumview'),
        $CFG->wwwroot.'/report/forumview/index.php', 'report/forumview:view'));