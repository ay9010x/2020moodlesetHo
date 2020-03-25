<?php



defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => '\tool_messageinbound\task\pickup_task',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),

    array(
        'classname' => '\tool_messageinbound\task\cleanup_task',
        'blocking' => 0,
        'minute' => '55',
        'hour' => '1',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
);
