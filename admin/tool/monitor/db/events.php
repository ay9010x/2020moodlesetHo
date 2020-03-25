<?php



$observers = array(
    array(
        'eventname'   => '\core\event\course_deleted',
        'priority'    => 1,
        'callback'    => '\tool_monitor\eventobservers::course_deleted',
    ),
    array(
        'eventname'   => '*',
        'callback'    => '\tool_monitor\eventobservers::process_event',
    ),
    array(
        'eventname'   => '\core\event\user_deleted',
        'callback'    => '\tool_monitor\eventobservers::user_deleted',
    ),
    array(
        'eventname'   => '\core\event\course_module_deleted',
        'callback'    => '\tool_monitor\eventobservers::course_module_deleted',
    )
);
