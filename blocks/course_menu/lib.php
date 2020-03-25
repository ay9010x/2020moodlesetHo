<?php

function block_course_menu_information_standard_log_view($course) {
    $eventdata = array();
    $eventdata['objectid'] = $course->id;
    $eventdata['context'] = context_course::instance($course->id);
        $event = \block_course_menu\event\information_viewed::create($eventdata);
    $event->trigger();
}
?>