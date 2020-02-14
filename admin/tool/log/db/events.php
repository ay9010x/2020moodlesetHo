<?php



defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname' => '*',
        'callback'  => '\tool_log\log\observer::store',
        'internal'  => false,         'priority'  => 1000,
    ),
);
