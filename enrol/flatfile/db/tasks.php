<?php



defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => '\enrol_flatfile\task\flatfile_sync_task',
        'blocking' => 0,
        'minute' => '15',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    )
);
