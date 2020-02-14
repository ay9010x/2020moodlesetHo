<?php




defined('MOODLE_INTERNAL') || die();

$observers = array(

        array(
        'eventname' => '\core\event\course_reset_started',
        'callback' => '\mod_quiz\group_observers::course_reset_started',
    ),
    array(
        'eventname' => '\core\event\course_reset_ended',
        'callback' => '\mod_quiz\group_observers::course_reset_ended',
    ),
    array(
        'eventname' => '\core\event\group_deleted',
        'callback' => '\mod_quiz\group_observers::group_deleted'
    ),
    array(
        'eventname' => '\core\event\group_member_added',
        'callback' => '\mod_quiz\group_observers::group_member_added',
    ),
    array(
        'eventname' => '\core\event\group_member_removed',
        'callback' => '\mod_quiz\group_observers::group_member_removed',
    ),

            array(
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'includefile'     => '/mod/quiz/locallib.php',
        'callback' => 'quiz_attempt_submitted_handler',
        'internal' => false
    ),
);
