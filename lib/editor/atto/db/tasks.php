<?php



defined('MOODLE_INTERNAL') || die();



$tasks = array(
    array(
        'classname' => 'editor_atto\task\autosave_cleanup_task',
        'blocking' => 0,
        'minute' => 'R',
        'hour' => 'R',
        'day' => '*',
        'dayofweek' => 'R',
        'month' => '*'
    )
);
