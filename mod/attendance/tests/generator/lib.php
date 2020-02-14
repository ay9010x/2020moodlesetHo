<?php



defined('MOODLE_INTERNAL') || die();


class mod_attendance_generator extends testing_module_generator {

    
    public function create_instance($record = null, array $options = null) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/attendance/lib.php');

        $this->instancecount++;
        $i = $this->instancecount;

        $record = (object)(array)$record;
        $options = (array)$options;

        if (empty($record->course)) {
            throw new coding_exception('module generator requires $record->course');
        }
        if (!isset($record->name)) {
            $record->name = get_string('pluginname', 'attendance').' '.$i;
        }
        if (!isset($record->grade)) {
            $record->grade = 100;
        }

        $record->coursemodule = $this->precreate_course_module($record->course, $options);
        $id = attendance_add_instance($record, null);
        return $this->post_add_instance($id, $record->coursemodule);
    }
}
