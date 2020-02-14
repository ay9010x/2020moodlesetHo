<?php







class restore_assignment_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $assignment = new restore_path_element('assignment', '/activity/assignment');
        $paths[] = $assignment;

                $this->add_subplugin_structure('assignment', $assignment);

        if ($userinfo) {
            $submission = new restore_path_element('assignment_submission', '/activity/assignment/submissions/submission');
            $paths[] = $submission;
                        $this->add_subplugin_structure('assignment', $submission);
        }

                return $this->prepare_activity_structure($paths);
    }

    protected function process_assignment($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timedue = $this->apply_date_offset($data->timedue);
        $data->timeavailable = $this->apply_date_offset($data->timeavailable);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        if ($data->grade < 0) {             $data->grade = -($this->get_mappingid('scale', abs($data->grade)));
        }

                $newitemid = $DB->insert_record('assignment', $data);
                $this->apply_activity_instance($newitemid);

                if (!$this->is_valid_assignment_subplugin($data->assignmenttype)) {
            $DB->set_field('course_modules', 'visible', 0, array('id' => $this->get_task()->get_moduleid()));
        }
    }

    protected function process_assignment_submission($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->assignment = $this->get_new_parentid('assignment');
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timemarked = $this->apply_date_offset($data->timemarked);

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->teacher = $this->get_mappingid('user', $data->teacher);

        $newitemid = $DB->insert_record('assignment_submissions', $data);
        $this->set_mapping('assignment_submission', $oldid, $newitemid, true);         $this->set_mapping(restore_gradingform_plugin::itemid_mapping('submission'), $oldid, $newitemid);
    }

    
    private function upgrade_mod_assign() {
        global $DB, $CFG;

                $pluginmanager = core_plugin_manager::instance();

        $plugininfo = $pluginmanager->get_plugin_info('mod_assign');

                if ($plugininfo && $plugininfo->is_installed_and_upgraded()) {
                        require_once($CFG->dirroot . '/mod/assign/upgradelib.php');
            require_once($CFG->dirroot . '/mod/assign/locallib.php');

                        $newinstance = $this->task->get_activityid();

            $record = $DB->get_record('assignment', array('id'=>$newinstance), 'assignmenttype', MUST_EXIST);
            $type = $record->assignmenttype;

            $subplugininfo = $pluginmanager->get_plugin_info('assignment_' . $type);

                        if (assign::can_upgrade_assignment($type, $subplugininfo->versiondb)) {
                $assignment_upgrader = new assign_upgrade_manager();
                $log = '';
                $success = $assignment_upgrader->upgrade_assignment($newinstance, $log);
                if (!$success) {
                    throw new restore_step_exception('mod_assign_upgrade_failed', $log);
                }
            }
        }
    }

    protected function after_execute() {
                $this->add_related_files('mod_assignment', 'intro', null);
                $this->add_related_files('mod_assignment', 'submission', 'assignment_submission');
        $this->add_related_files('mod_assignment', 'response', 'assignment_submission');
    }

    
    protected function after_restore() {

        if ($this->get_task()->get_mode() != backup::MODE_IMPORT) {
                        $this->upgrade_mod_assign();
        }
    }

    
    protected function is_valid_assignment_subplugin($type) {
        static $subplugins = null;

        if (is_null($subplugins)) {
            $subplugins = get_plugin_list('assignment');
        }
        return array_key_exists($type, $subplugins);
    }
}
