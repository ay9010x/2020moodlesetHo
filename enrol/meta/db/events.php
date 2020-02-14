<?php



defined('MOODLE_INTERNAL') || die();

$observers = array(

    array(
        'eventname'   => '\core\event\user_enrolment_created',
        'callback'    => 'enrol_meta_observer::user_enrolment_created',
    ),
    array(
        'eventname'   => '\core\event\user_enrolment_deleted',
        'callback'    => 'enrol_meta_observer::user_enrolment_deleted',
    ),
    array(
        'eventname'   => '\core\event\user_enrolment_updated',
        'callback'    => 'enrol_meta_observer::user_enrolment_updated',
    ),
    array(
        'eventname'   => '\core\event\role_assigned',
        'callback'    => 'enrol_meta_observer::role_assigned',
    ),
    array(
        'eventname'   => '\core\event\role_unassigned',
        'callback'    => 'enrol_meta_observer::role_unassigned',
    ),
    array(
        'eventname'   => '\core\event\course_deleted',
        'callback'    => 'enrol_meta_observer::course_deleted',
    ),
    array(
        'eventname'   => '\core\event\enrol_instance_updated',
        'callback'    => 'enrol_meta_observer::enrol_instance_updated',
    ),
);
