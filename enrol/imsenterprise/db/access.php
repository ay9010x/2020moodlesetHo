<?php



defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'enrol/imsenterprise:config' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
        )
    ),
);

