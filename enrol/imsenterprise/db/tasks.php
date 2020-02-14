<?php



defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'enrol_imsenterprise\task\cron_task',
        'blocking' => 0,
        'minute' => '10',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    )
);
