<?php



defined('MOODLE_INTERNAL') || die();

$handlers = array(
    array(
        'classname' => '\core\message\inbound\private_files_handler',
        'defaultexpiration' => 0,
    ),
);
