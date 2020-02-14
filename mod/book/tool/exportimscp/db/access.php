<?php



defined('MOODLE_INTERNAL') || die;

$capabilities = array(
    'booktool/exportimscp:export' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE
    ),
);
