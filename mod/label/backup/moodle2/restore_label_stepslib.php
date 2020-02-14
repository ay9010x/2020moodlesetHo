<?php







class restore_label_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('label', '/activity/label');

                return $this->prepare_activity_structure($paths);
    }

    protected function process_label($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

                $newitemid = $DB->insert_record('label', $data);
                $this->apply_activity_instance($newitemid);
    }

    protected function after_execute() {
                $this->add_related_files('mod_label', 'intro', null);
    }

}
