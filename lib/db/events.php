<?php



defined('MOODLE_INTERNAL') || die();


$handlers = array(
    );


$observers = array(

    array(
        'eventname'   => '\core\event\course_module_completion_updated',
        'callback'    => 'core_badges_observer::course_module_criteria_review',
    ),
    array(
        'eventname'   => '\core\event\course_completed',
        'callback'    => 'core_badges_observer::course_criteria_review',
    ),
    array(
        'eventname'   => '\core\event\user_updated',
        'callback'    => 'core_badges_observer::profile_criteria_review',
    ),

        array(
        'eventname'   => '\core\event\course_completed',
        'callback'    => 'core_competency\api::observe_course_completed',
    ),
    array(
        'eventname'   => '\core\event\course_module_completion_updated',
        'callback'    => 'core_competency\api::observe_course_module_completion_updated',
    ),
);

