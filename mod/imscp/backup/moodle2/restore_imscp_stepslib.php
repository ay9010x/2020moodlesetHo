<?php




class restore_imscp_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('imscp', '/activity/imscp');

                return $this->prepare_activity_structure($paths);
    }

    protected function process_imscp($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);

                $newitemid = $DB->insert_record('imscp', $data);
                $this->apply_activity_instance($newitemid);
    }

    protected function after_execute() {
                        $this->add_related_files('mod_imscp', 'intro', null);
        $this->add_related_files('mod_imscp', 'backup', null);
        $this->add_related_files('mod_imscp', 'content', null);
    }
}
