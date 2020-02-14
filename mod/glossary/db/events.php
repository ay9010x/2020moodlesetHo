<?php



defined('MOODLE_INTERNAL') || die();

$observers = array(
    array (
        'eventname' => '\core\event\course_module_updated',
        'callback'  => '\mod_glossary\local\concept_cache::cm_updated',
    ),
);