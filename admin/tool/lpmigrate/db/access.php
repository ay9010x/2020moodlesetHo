<?php



defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'tool/lpmigrate:frameworksmigrate' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),
);
