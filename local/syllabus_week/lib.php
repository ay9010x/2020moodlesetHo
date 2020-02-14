<?php

function local_syllabus_week_standard_log_view($course) {
    $eventdata = array();
    $eventdata['objectid'] = $course->id;
    $eventdata['context'] = context_course::instance($course->id);
    $event = \local_syllabus_week\event\week_viewed::create($eventdata);
    $event->trigger();
}
function local_syllabus_week_standard_log_update($course) {
    $eventdata = array();
    $eventdata['objectid'] = $course->id;
    $eventdata['context'] = context_course::instance($course->id);
    $event = \local_syllabus_week\event\week_updated::create($eventdata);
    $event->trigger();
}
function local_syllabus_week_setup_standard_log_update($course) {
    $eventdata = array();
    $eventdata['objectid'] = $course->id;
    $eventdata['context'] = context_course::instance($course->id);
    $event = \local_syllabus_week\event\setup_updated::create($eventdata);
    $event->trigger();
}