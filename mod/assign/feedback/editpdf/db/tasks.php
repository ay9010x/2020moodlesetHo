<?php



defined('MOODLE_INTERNAL') || die();



$tasks = array(
    array(
        'classname' => 'assignfeedback_editpdf\task\convert_submissions',
        'blocking' => 0,
        'minute' => '*/15',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
);
