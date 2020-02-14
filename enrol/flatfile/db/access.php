<?php



defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    
    'enrol/flatfile:manage' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
        )
    ),

    
    'enrol/flatfile:unenrol' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
        )
    ),
);
