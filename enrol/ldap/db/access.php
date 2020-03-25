<?php



defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'enrol/ldap:manage' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        )
    ),

);


