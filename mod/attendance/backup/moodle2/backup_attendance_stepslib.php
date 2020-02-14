<?php



defined('MOODLE_INTERNAL') || die();


class backup_attendance_activity_structure_step extends backup_activity_structure_step {

    
    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                $attendance = new backup_nested_element('attendance', array('id'), array(
            'name', 'grade'));

        $statuses = new backup_nested_element('statuses');
        $status  = new backup_nested_element('status', array('id'), array(
            'acronym', 'description', 'grade', 'visible', 'deleted'));

        $sessions = new backup_nested_element('sessions');
        $session  = new backup_nested_element('session', array('id'), array(
            'groupid', 'sessdate', 'duration', 'lasttaken', 'lasttakenby',
            'timemodified', 'description', 'descriptionformat'));

                $logs = new backup_nested_element('logs');
        $log  = new backup_nested_element('log', array('id'), array(
            'sessionid', 'studentid', 'statusid', 'lasttaken', 'statusset',
            'timetaken', 'takenby', 'remarks'));

                $attendance->add_child($statuses);
        $statuses->add_child($status);

        $attendance->add_child($sessions);
        $sessions->add_child($session);

        $session->add_child($logs);
        $logs->add_child($log);

        
        $attendance->set_source_table('attendance', array('id' => backup::VAR_ACTIVITYID));

        $status->set_source_table('attendance_statuses', array('attendanceid' => backup::VAR_PARENTID));

        $session->set_source_table('attendance_sessions', array('attendanceid' => backup::VAR_PARENTID));

                if ($userinfo) {
            $log->set_source_table('attendance_log', array('sessionid' => backup::VAR_PARENTID));
        }

                $session->annotate_ids('user', 'lasttakenby');
        $session->annotate_ids('group', 'groupid');
        $log->annotate_ids('user', 'studentid');
        $log->annotate_ids('user', 'takenby');

                $session->annotate_files('mod_attendance', 'session', 'id');

                return $this->prepare_activity_structure($attendance);
    }
}
