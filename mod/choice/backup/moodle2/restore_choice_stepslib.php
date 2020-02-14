<?php







class restore_choice_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('choice', '/activity/choice');
        $paths[] = new restore_path_element('choice_option', '/activity/choice/options/option');
        if ($userinfo) {
            $paths[] = new restore_path_element('choice_answer', '/activity/choice/answers/answer');
        }

                return $this->prepare_activity_structure($paths);
    }

    protected function process_choice($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

                $newitemid = $DB->insert_record('choice', $data);
                $this->apply_activity_instance($newitemid);
    }

    protected function process_choice_option($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->choiceid = $this->get_new_parentid('choice');
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('choice_options', $data);
        $this->set_mapping('choice_option', $oldid, $newitemid);
    }

    protected function process_choice_answer($data) {
        global $DB;

        $data = (object)$data;

        $data->choiceid = $this->get_new_parentid('choice');
        $data->optionid = $this->get_mappingid('choice_option', $data->optionid);
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('choice_answers', $data);
                    }

    protected function after_execute() {
                $this->add_related_files('mod_choice', 'intro', null);
    }
}
