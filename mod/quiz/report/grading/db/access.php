<?php



defined('MOODLE_INTERNAL') || die();

$capabilities = array(
        'quiz/grading:viewstudentnames' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
        ),
        'clonepermissionsfrom' =>  'mod/quiz:viewreports'
    ),

        'quiz/grading:viewidnumber' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
        ),
        'clonepermissionsfrom' =>  'mod/quiz:viewreports'
    )
);
