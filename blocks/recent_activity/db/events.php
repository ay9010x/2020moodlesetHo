<?php



defined('MOODLE_INTERNAL') || die();

$observers = array (
    array (
        'eventname' => '\core\event\course_module_created',
        'callback'  => 'block_recent_activity_observer::store',
        'internal'  => false,         'priority'  => 1000,
    ),
    array (
        'eventname' => '\core\event\course_module_updated',
        'callback'  => 'block_recent_activity_observer::store',
        'internal'  => false,         'priority'  => 1000,
    ),
    array (
        'eventname' => '\core\event\course_module_deleted',
        'callback'  => 'block_recent_activity_observer::store',
        'internal'  => false,         'priority'  => 1000,
    ),
);
