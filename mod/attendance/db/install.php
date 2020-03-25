<?php




function xmldb_attendance_install() {
    global $DB;

    $result = true;
    $arr = array('P' => 2, 'A' => 0, 'L' => 1, 'E' => 1);
    foreach ($arr as $k => $v) {
        $rec = new stdClass;
        $rec->attendanceid = 0;
        $rec->acronym = get_string($k.'acronym', 'attendance');
        $rec->description = get_string($k.'full', 'attendance');
        $rec->grade = $v;
        $rec->visible = 1;
        $rec->deleted = 0;
        if (!$DB->record_exists('attendance_statuses', array('attendanceid' => 0, 'acronym' => $rec->acronym))) {
            $result = $result && $DB->insert_record('attendance_statuses', $rec);
        }
    }

    return $result;
}
