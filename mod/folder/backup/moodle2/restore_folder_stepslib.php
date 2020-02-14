<?php







class restore_folder_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('folder', '/activity/folder');

                return $this->prepare_activity_structure($paths);
    }

    protected function process_folder($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);

                if (!isset($data->showexpanded)) {
            $data->showexpanded = get_config('folder', 'showexpanded');
        }

                $newitemid = $DB->insert_record('folder', $data);
                $this->apply_activity_instance($newitemid);
    }

    protected function after_execute() {
                $this->add_related_files('mod_folder', 'intro', null);
        $this->add_related_files('mod_folder', 'content', null);
    }
}
