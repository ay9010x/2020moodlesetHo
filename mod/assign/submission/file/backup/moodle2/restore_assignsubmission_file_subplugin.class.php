<?php




class restore_assignsubmission_file_subplugin extends restore_subplugin {

    
    protected function define_submission_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('submission');
        $elepath = $this->get_pathfor('/submission_file');
                $paths[] = new restore_path_element($elename, $elepath);

        return $paths;
    }

    
    public function process_assignsubmission_file_submission($data) {
        global $DB;

        $data = (object)$data;
        $data->assignment = $this->get_new_parentid('assign');
        $oldsubmissionid = $data->submission;
                        $data->submission = $this->get_mappingid('submission', $data->submission);

        $DB->insert_record('assignsubmission_file', $data);

        $this->add_related_files('assignsubmission_file',
                                 'submission_files',
                                 'submission',
                                 null,
                                 $oldsubmissionid);
    }

}
