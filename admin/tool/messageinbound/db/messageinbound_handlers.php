<?php



defined('MOODLE_INTERNAL') || die();

$handlers = array(
    array(
        'classname'         => '\tool_messageinbound\message\inbound\invalid_recipient_handler',
        'enabled'           => true,
        'validateaddress'   => false,
    ),
);
