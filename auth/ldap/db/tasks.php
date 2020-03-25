<?php



defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'auth_ldap\task\sync_task',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '0',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
        'disabled' => 1
    )
);
