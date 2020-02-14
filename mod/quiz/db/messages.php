<?php



defined('MOODLE_INTERNAL') || die();

$messageproviders = array(
        'submission' => array(
        'capability' => 'mod/quiz:emailnotifysubmission'
    ),

        'confirmation' => array(
        'capability' => 'mod/quiz:emailconfirmsubmission'
    ),

            'attempt_overdue' => array(
        'capability' => 'mod/quiz:emailwarnoverdue'
    ),
);
