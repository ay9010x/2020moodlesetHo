<?php



defined('MOODLE_INTERNAL') || die();


class restore_attendance_activity_structure_step extends restore_activity_structure_step {

    
    protected function define_structure() {

        $paths = array();

        $userinfo = $this->get_setting_value('userinfo'); 
                $paths[] = new restore_path_element('attendance', '/activity/attendance');

        $paths[] = new restore_path_element('attendance_status',
                       '/activity/attendance/statuses/status');

        $paths[] = new restore_path_element('attendance_session',
                       '/activity/attendance/sessions/session');

                if (!$userinfo) {
            return $this->prepare_activity_structure($paths);
        }

                $paths[] = new restore_path_element('attendance_log',
                       '/activity/attendance/sessions/session/logs/log');

                return $this->prepare_activity_structure($paths);
    }

    
    protected function process_attendance($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();

                $newitemid = $DB->insert_record('attendance', $data);
                $this->apply_activity_instance($newitemid);
    }

    
    protected function process_attendance_status($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->attendanceid = $this->get_new_parentid('attendance');

        $newitemid = $DB->insert_record('attendance_statuses', $data);
        $this->set_mapping('attendance_status', $oldid, $newitemid);
    }

    
    protected function process_attendance_session($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->attendanceid = $this->get_new_parentid('attendance');
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        $data->sessdate = $this->apply_date_offset($data->sessdate);
        $data->lasttaken = $this->apply_date_offset($data->lasttaken);
        $data->lasttakenby = $this->get_mappingid('user', $data->lasttakenby);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('attendance_sessions', $data);
        $this->set_mapping('attendance_session', $oldid, $newitemid, true);
    }

    
    protected function process_attendance_log($data) {
        global $DB;

        $data = (object)$data;

        $data->sessionid = $this->get_mappingid('attendance_session', $data->sessionid);
        $data->studentid = $this->get_mappingid('user', $data->studentid);
        $data->statusid = $this->get_mappingid('attendance_status', $data->statusid);
        $statusset = explode(',', $data->statusset);
        foreach ($statusset as $st) {
            $st = $this->get_mappingid('attendance_status', $st);
        }
        $data->statusset = implode(',', $statusset);
        $data->timetaken = $this->apply_date_offset($data->timetaken);
        $data->takenby = $this->get_mappingid('user', $data->takenby);

        $DB->insert_record('attendance_log', $data);
    }

    
    protected function after_execute() {
        $this->add_related_files('mod_attendance', 'session', 'attendance_session');
    }
}
