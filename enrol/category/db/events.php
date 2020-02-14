<?php



defined('MOODLE_INTERNAL') || die();

$observers = array (

    array (
        'eventname' => '\core\event\role_assigned',
        'callback'  => 'enrol_category_observer::role_assigned',
    ),

    array (
        'eventname' => '\core\event\role_unassigned',
        'callback'  => 'enrol_category_observer::role_unassigned',
    ),

);
