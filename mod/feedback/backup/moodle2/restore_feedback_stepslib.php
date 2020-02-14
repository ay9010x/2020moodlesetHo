<?php






class restore_feedback_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('feedback', '/activity/feedback');
        $paths[] = new restore_path_element('feedback_item', '/activity/feedback/items/item');
        if ($userinfo) {
            $paths[] = new restore_path_element('feedback_completed', '/activity/feedback/completeds/completed');
            $paths[] = new restore_path_element('feedback_value', '/activity/feedback/completeds/completed/values/value');
        }

                return $this->prepare_activity_structure($paths);
    }

    protected function process_feedback($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

                $newitemid = $DB->insert_record('feedback', $data);
                $this->apply_activity_instance($newitemid);
    }

    protected function process_feedback_item($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->feedback = $this->get_new_parentid('feedback');

                $data->dependitem = $this->get_mappingid('feedback_item', $data->dependitem);

        $newitemid = $DB->insert_record('feedback_item', $data);
        $this->set_mapping('feedback_item', $oldid, $newitemid, true);     }

    protected function process_feedback_completed($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->feedback = $this->get_new_parentid('feedback');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        if ($this->task->is_samesite() && !empty($data->courseid)) {
            $data->courseid = $data->courseid;
        } else if ($this->get_courseid() == SITEID) {
            $data->courseid = SITEID;
        } else {
            $data->courseid = 0;
        }

        $newitemid = $DB->insert_record('feedback_completed', $data);
        $this->set_mapping('feedback_completed', $oldid, $newitemid);
    }

    protected function process_feedback_value($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->completed = $this->get_new_parentid('feedback_completed');
        $data->item = $this->get_mappingid('feedback_item', $data->item);
        if ($this->task->is_samesite() && !empty($data->course_id)) {
            $data->course_id = $data->course_id;
        } else if ($this->get_courseid() == SITEID) {
            $data->course_id = SITEID;
        } else {
            $data->course_id = 0;
        }

        $newitemid = $DB->insert_record('feedback_value', $data);
        $this->set_mapping('feedback_value', $oldid, $newitemid);
    }

    protected function after_execute() {
                $this->add_related_files('mod_feedback', 'intro', null);
        $this->add_related_files('mod_feedback', 'page_after_submit', null);
        $this->add_related_files('mod_feedback', 'item', 'feedback_item');
    }
}
