<?php



defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'tool_langimport\task\update_langpacks_task',
        'blocking' => 0,
        'minute' => 'R',
        'hour' => '4',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    )
);
