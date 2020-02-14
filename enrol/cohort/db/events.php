<?php



defined('MOODLE_INTERNAL') || die();

$observers = array(

    array(
        'eventname' => '\core\event\cohort_member_added',
        'callback' => 'enrol_cohort_handler::member_added',
        'includefile' => '/enrol/cohort/locallib.php'
    ),

    array(
        'eventname' => '\core\event\cohort_member_removed',
        'callback' => 'enrol_cohort_handler::member_removed',
        'includefile' => '/enrol/cohort/locallib.php'
    ),

    array(
        'eventname' => '\core\event\cohort_deleted',
        'callback' => 'enrol_cohort_handler::deleted',
        'includefile' => '/enrol/cohort/locallib.php'
    ),
);
