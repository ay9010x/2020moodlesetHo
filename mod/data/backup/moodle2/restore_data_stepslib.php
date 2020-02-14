<?php







class restore_data_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('data', '/activity/data');
        $paths[] = new restore_path_element('data_field', '/activity/data/fields/field');
        if ($userinfo) {
            $paths[] = new restore_path_element('data_record', '/activity/data/records/record');
            $paths[] = new restore_path_element('data_content', '/activity/data/records/record/contents/content');
            $paths[] = new restore_path_element('data_rating', '/activity/data/records/record/ratings/rating');
        }

                return $this->prepare_activity_structure($paths);
    }

    protected function process_data($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timeavailablefrom = $this->apply_date_offset($data->timeavailablefrom);
        $data->timeavailableto = $this->apply_date_offset($data->timeavailableto);
        $data->timeviewfrom = $this->apply_date_offset($data->timeviewfrom);
        $data->timeviewto = $this->apply_date_offset($data->timeviewto);
        $data->assesstimestart = $this->apply_date_offset($data->assesstimestart);
        $data->assesstimefinish = $this->apply_date_offset($data->assesstimefinish);
                $data->timemodified = isset($data->timemodified) ? $this->apply_date_offset($data->timemodified) : time();

        if ($data->scale < 0) {             $data->scale = -($this->get_mappingid('scale', abs($data->scale)));
        }

                        if (is_null($data->notification)) {
            $data->notification = 0;
        }

                $newitemid = $DB->insert_record('data', $data);
        $this->apply_activity_instance($newitemid);
    }

    protected function process_data_field($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->dataid = $this->get_new_parentid('data');

                $newitemid = $DB->insert_record('data_fields', $data);
        $this->set_mapping('data_field', $oldid, $newitemid, false);     }

    protected function process_data_record($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        $data->dataid = $this->get_new_parentid('data');

                $newitemid = $DB->insert_record('data_records', $data);
        $this->set_mapping('data_record', $oldid, $newitemid, false);     }

    protected function process_data_content($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->fieldid = $this->get_mappingid('data_field', $data->fieldid);
        $data->recordid = $this->get_new_parentid('data_record');

                $newitemid = $DB->insert_record('data_content', $data);
        $this->set_mapping('data_content', $oldid, $newitemid, true);     }

    protected function process_data_rating($data) {
        global $DB;

        $data = (object)$data;

                $data->contextid = $this->task->get_contextid();
        $data->itemid    = $this->get_new_parentid('data_record');
        if ($data->scaleid < 0) {             $data->scaleid = -($this->get_mappingid('scale', abs($data->scaleid)));
        }
        $data->rating = $data->value;
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

                if (empty($data->component)) {
            $data->component = 'mod_data';
        }
        if (empty($data->ratingarea)) {
            $data->ratingarea = 'entry';
        }

        $newitemid = $DB->insert_record('rating', $data);
    }

    protected function after_execute() {
        global $DB;
                $this->add_related_files('mod_data', 'intro', null);
                $this->add_related_files('mod_data', 'content', 'data_content');
                if ($defaultsort = $DB->get_field('data', 'defaultsort', array('id' => $this->get_new_parentid('data')))) {
            if ($defaultsort = $this->get_mappingid('data_field', $defaultsort)) {
                $DB->set_field('data', 'defaultsort', $defaultsort, array('id' => $this->get_new_parentid('data')));
            }
        }
    }
}
