<?php



defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    
    'enrol/manual:config' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        )
    ),

    
    'enrol/manual:enrol' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'moodleset' => CAP_ALLOW,
        )
    ),

    
    'enrol/manual:manage' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
        )
    ),

    
    'enrol/manual:unenrol' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'editingteacher' => CAP_PROHIBIT,
            'teacherassistant' => CAP_PROHIBIT,
            'moodleset' => CAP_ALLOW,
        )
    ),

    
    'enrol/manual:unenrolself' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'student' => CAP_PROHIBIT,
            'auditor' => CAP_ALLOW,
        )
    ),

);
