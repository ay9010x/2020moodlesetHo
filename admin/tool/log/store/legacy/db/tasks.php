<?php



defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => '\logstore_legacy\task\cleanup_task',
        'blocking' => 0,
        'minute' => 'R',
        'hour' => '5',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
);