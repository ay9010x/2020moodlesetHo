<?php



defined('MOODLE_INTERNAL') || die();


function mod_feedback_upgrade_courseid($tmp = false) {
    global $DB;
    $suffix = $tmp ? 'tmp' : '';

        $sql = "SELECT c.id
        FROM {feedback_completed$suffix} c, {feedback_value$suffix} v
        WHERE c.id = v.completed
        GROUP by c.id
        having count(DISTINCT v.course_id) > 1";
    $problems = $DB->get_fieldset_sql($sql);
    foreach ($problems as $problem) {
        $courses = $DB->get_fieldset_sql("SELECT DISTINCT course_id "
                . "FROM {feedback_value$suffix} WHERE completed = ?", array($problem));
        $firstcourse = array_shift($courses);
        $record = $DB->get_record('feedback_completed'.$suffix, array('id' => $problem));
        unset($record->id);
        $DB->update_record('feedback_completed'.$suffix, ['id' => $problem, 'courseid' => $firstcourse]);
        foreach ($courses as $courseid) {
            $record->courseid = $courseid;
            $completedid = $DB->insert_record('feedback_completed'.$suffix, $record);
            $DB->execute("UPDATE {feedback_value$suffix} SET completed = ? WHERE completed = ? AND course_id = ?",
                    array($completedid, $problem, $courseid));
        }
    }

        if ($DB->get_dbfamily() !== 'mysql') {
        $sql = "UPDATE {feedback_completed$suffix} "
            . "SET courseid = (SELECT COALESCE(MIN(v.course_id), 0) "
            . "FROM {feedback_value$suffix} v "
            . "WHERE v.completed = {feedback_completed$suffix}.id)";
        $DB->execute($sql);
    } else {
        $sql = "UPDATE {feedback_completed$suffix} c, {feedback_value$suffix} v "
            . "SET c.courseid = v.course_id "
            . "WHERE v.completed = c.id AND v.course_id <> 0";
        $DB->execute($sql);
    }
}


function mod_feedback_upgrade_delete_duplicate_values($tmp = false) {
    global $DB;
    $suffix = $tmp ? 'tmp' : '';

    $sql = "SELECT MIN(id) AS id, completed, item, course_id " .
            "FROM {feedback_value$suffix} GROUP BY completed, item, course_id HAVING count(id)>1";
    $records = $DB->get_records_sql($sql);
    foreach ($records as $record) {
        $DB->delete_records_select("feedback_value$suffix",
            "completed = :completed AND item = :item AND course_id = :course_id AND id > :id", (array)$record);
    }
}
