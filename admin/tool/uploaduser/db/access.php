<?php



defined('MOODLE_INTERNAL') || die();

$capabilities = array(

        'tool/uploaduser:uploaduserpictures' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' =>  'moodle/site:uploadusers',
    ),
);
