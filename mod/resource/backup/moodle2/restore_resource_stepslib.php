<?php







class restore_resource_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('resource', '/activity/resource');

                return $this->prepare_activity_structure($paths);
    }

    protected function process_resource($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);

                $newitemid = $DB->insert_record('resource', $data);
                $this->apply_activity_instance($newitemid);
    }

    protected function after_execute() {
                $this->add_related_files('mod_resource', 'intro', null);
        $this->add_related_files('mod_resource', 'content', null);
    }
}
