<?php



function block_course_menu_get_course_attendance($course) {
    global $DB;   
    $attid=1;  
    $sql = "SELECT cm.id FROM {attendance} a
            JOIN {modules} m  ON m.name= 'attendance'
            JOIN {course_modules} cm ON cm.course = a.course AND cm.module = m.id  AND a.id = cm.instance
            WHERE a.course= :courseid
            ORDER BY id 
            LIMIT 1";   
    $params['courseid']  = $course->id;
    if($attendance = $DB->get_record_sql($sql, $params, 0, '')){
        $attid = $attendance->id;
    }
    return $attid;
}
?>