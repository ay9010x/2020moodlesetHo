<?php



defined('MOODLE_INTERNAL') || die();


class restore_assign_activity_structure_step extends restore_activity_structure_step {

    
    protected $includesubmission = true;

    
    protected function define_structure() {

        $paths = array();
                $userinfo = $this->get_setting_value('userinfo');

                $paths[] = new restore_path_element('assign', '/activity/assign');
        if ($userinfo) {
            $submission = new restore_path_element('assign_submission',
                                                   '/activity/assign/submissions/submission');
            $paths[] = $submission;
            $this->add_subplugin_structure('assignsubmission', $submission);
            $grade = new restore_path_element('assign_grade', '/activity/assign/grades/grade');
            $paths[] = $grade;
            $this->add_subplugin_structure('assignfeedback', $grade);
            $userflag = new restore_path_element('assign_userflag',
                                                   '/activity/assign/userflags/userflag');
            $paths[] = $userflag;
        }
        $paths[] = new restore_path_element('assign_plugin_config',
                                            '/activity/assign/plugin_configs/plugin_config');

        return $this->prepare_activity_structure($paths);
    }

    
    protected function process_assign($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->allowsubmissionsfromdate = $this->apply_date_offset($data->allowsubmissionsfromdate);
        $data->duedate = $this->apply_date_offset($data->duedate);

                        $groupinfo = $this->task->get_setting_value('groups');
        if ($data->teamsubmission && !$groupinfo) {
            $this->includesubmission = false;
        }

                $userinfo = $this->get_setting_value('userinfo');
        if (!$userinfo && $data->blindmarking) {
            $data->revealidentities = 0;
        }

        if (!empty($data->teamsubmissiongroupingid)) {
            $data->teamsubmissiongroupingid = $this->get_mappingid('grouping',
                                                                   $data->teamsubmissiongroupingid);
        } else {
            $data->teamsubmissiongroupingid = 0;
        }

        if (!isset($data->cutoffdate)) {
            $data->cutoffdate = 0;
        }
        if (!isset($data->markingworkflow)) {
            $data->markingworkflow = 0;
        }
        if (!isset($data->markingallocation)) {
            $data->markingallocation = 0;
        }
        if (!isset($data->preventsubmissionnotingroup)) {
            $data->preventsubmissionnotingroup = 0;
        }

        if (!empty($data->preventlatesubmissions)) {
            $data->cutoffdate = $data->duedate;
        } else {
            $data->cutoffdate = $this->apply_date_offset($data->cutoffdate);
        }

        if ($data->grade < 0) {             $data->grade = -($this->get_mappingid('scale', abs($data->grade)));
        }

        $newitemid = $DB->insert_record('assign', $data);

        $this->apply_activity_instance($newitemid);
    }

    
    protected function process_assign_submission($data) {
        global $DB;

        if (!$this->includesubmission) {
            return;
        }

        $data = (object)$data;
        $oldid = $data->id;

        $data->assignment = $this->get_new_parentid('assign');

        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        if ($data->userid > 0) {
            $data->userid = $this->get_mappingid('user', $data->userid);
        }
        if (!empty($data->groupid)) {
            $data->groupid = $this->get_mappingid('group', $data->groupid);
        } else {
            $data->groupid = 0;
        }

                $data->latest = 0;

        $newitemid = $DB->insert_record('assign_submission', $data);

                        $this->set_mapping('submission', $oldid, $newitemid, false, null, $this->task->get_old_contextid());
    }

    
    protected function process_assign_userflag($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->assignment = $this->get_new_parentid('assign');

        $data->userid = $this->get_mappingid('user', $data->userid);
        if (!empty($data->allocatedmarker)) {
            $data->allocatedmarker = $this->get_mappingid('user', $data->allocatedmarker);
        }
        if (!empty($data->extensionduedate)) {
            $data->extensionduedate = $this->apply_date_offset($data->extensionduedate);
        } else {
            $data->extensionduedate = 0;
        }
        
        $newitemid = $DB->insert_record('assign_user_flags', $data);
    }

    
    protected function process_assign_grade($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->assignment = $this->get_new_parentid('assign');

        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->grader = $this->get_mappingid('user', $data->grader);

                if (!empty($data->extensionduedate) ||
                !empty($data->mailed) ||
                !empty($data->locked)) {
            $flags = new stdClass();
            $flags->assignment = $this->get_new_parentid('assign');
            if (!empty($data->extensionduedate)) {
                $flags->extensionduedate = $this->apply_date_offset($data->extensionduedate);
            }
            if (!empty($data->mailed)) {
                $flags->mailed = $data->mailed;
            }
            if (!empty($data->locked)) {
                $flags->locked = $data->locked;
            }
            $flags->userid = $this->get_mappingid('user', $data->userid);
            $DB->insert_record('assign_user_flags', $flags);
        }

        $newitemid = $DB->insert_record('assign_grades', $data);

                        $this->set_mapping('grade', $oldid, $newitemid, false, null, $this->task->get_old_contextid());
        $this->set_mapping(restore_gradingform_plugin::itemid_mapping('submissions'), $oldid, $newitemid);
    }

    
    protected function process_assign_plugin_config($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->assignment = $this->get_new_parentid('assign');

        $newitemid = $DB->insert_record('assign_plugin_config', $data);
    }

    
    protected function set_latest_submission_field() {
        global $DB, $CFG;

                require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $assignmentid = $this->get_new_parentid('assign');
                
                $sql = 'SELECT DISTINCT userid FROM {assign_submission} WHERE assignment = ? AND groupid = ?';
        $params = array($assignmentid, 0);
        $users = $DB->get_records_sql($sql, $params);

        foreach ($users as $userid => $unused) {
            $params = array('assignment'=>$assignmentid, 'groupid'=>0, 'userid'=>$userid);

                        $submission = null;
            $submissions = $DB->get_records('assign_submission', $params, 'attemptnumber DESC', '*', 0, 1);
            if ($submissions) {
                $submission = reset($submissions);
                $submission->latest = 1;
                $DB->update_record('assign_submission', $submission);
            }
        }
                $sql = 'SELECT DISTINCT groupid FROM {assign_submission} WHERE assignment = ? AND userid = ?';
        $params = array($assignmentid, 0);
        $groups = $DB->get_records_sql($sql, $params);

        foreach ($groups as $groupid => $unused) {
            $params = array('assignment'=>$assignmentid, 'userid'=>0, 'groupid'=>$groupid);

                        $submission = null;
            $submissions = $DB->get_records('assign_submission', $params, 'attemptnumber DESC', '*', 0, 1);
            if ($submissions) {
                $submission = reset($submissions);
                $submission->latest = 1;
                $DB->update_record('assign_submission', $submission);
            }
        }

                        $records = $DB->get_recordset_sql('SELECT g.id, g.userid
                                           FROM {assign_grades} g
                                      LEFT JOIN {assign_submission} s
                                             ON s.assignment = g.assignment
                                            AND s.userid = g.userid
                                          WHERE s.id IS NULL AND g.assignment = ?', array($assignmentid));

        $submissions = array();
        foreach ($records as $record) {
            $submission = new stdClass();
            $submission->assignment = $assignmentid;
            $submission->userid = $record->userid;
            $submission->status = ASSIGN_SUBMISSION_STATUS_NEW;
            $submission->groupid = 0;
            $submission->latest = 1;
            $submission->timecreated = time();
            $submission->timemodified = time();
            array_push($submissions, $submission);
        }

        $records->close();

        $DB->insert_records('assign_submission', $submissions);
    }

    
    protected function after_execute() {
        $this->add_related_files('mod_assign', 'intro', null);
        $this->add_related_files('mod_assign', 'introattachment', null);

        $this->set_latest_submission_field();
    }
}
