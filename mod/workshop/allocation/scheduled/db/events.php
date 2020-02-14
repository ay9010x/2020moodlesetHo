<?php




defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname' => '\mod_workshop\event\course_module_viewed',
        'callback'  => '\workshopallocation_scheduled\observer::workshop_viewed',
    )
);
