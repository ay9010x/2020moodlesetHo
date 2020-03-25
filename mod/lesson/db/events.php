<?php




defined('MOODLE_INTERNAL') || die();

$observers = array(

    array(
        'eventname' => '\core\event\course_reset_started',
        'callback' => '\mod_lesson\group_observers::course_reset_started',
    ),
    array(
        'eventname' => '\core\event\course_reset_ended',
        'callback' => '\mod_lesson\group_observers::course_reset_ended',
    ),
    array(
        'eventname' => '\core\event\group_deleted',
        'callback' => '\mod_lesson\group_observers::group_deleted'
    ),
);
