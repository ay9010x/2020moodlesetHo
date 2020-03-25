<?php

function local_syllabus_timeline_standard_log_view($course) {
    $eventdata = array();
    $eventdata['objectid'] = $course->id;
    $eventdata['context'] = context_course::instance($course->id);
    $event = \local_syllabus_timeline\event\timeline_viewed::create($eventdata);
    $event->trigger();
}
function local_syllabus_timeline_standard_log_update($course) {
    $eventdata = array();
    $eventdata['objectid'] = $course->id;
    $eventdata['context'] = context_course::instance($course->id);
    $event = \local_syllabus_timeline\event\timeline_updated::create($eventdata);
    $event->trigger();
}