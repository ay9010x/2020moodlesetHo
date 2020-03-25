<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'report/quizview:view' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'coursereport/log:view',
    )
);
