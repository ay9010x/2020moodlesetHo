<?php



defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'gradereport/user:view' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'auditor' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'moodleset' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),
);


