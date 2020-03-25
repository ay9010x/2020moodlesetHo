<?php



defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'tool_cohortroles\task\cohort_role_sync',
        'blocking' => 0,
        'minute' => 'R',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
);
