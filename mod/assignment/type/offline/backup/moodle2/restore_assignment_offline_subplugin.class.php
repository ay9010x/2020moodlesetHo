<?php





class restore_assignment_offline_subplugin extends restore_subplugin {

    
    protected function define_assignment_subplugin_structure() {

        return false; 
        $paths = array();

        $elename = $this->get_namefor('config');
        $elepath = $this->get_pathfor('/config');         $paths[] = new restore_path_element($elename, $elepath);

        return $paths;     }

    
    protected function define_submission_subplugin_structure() {

        return false; 
        $paths = array();

        $elename = $this->get_namefor('submission_config');
        $elepath = $this->get_pathfor('/submission_config');         $paths[] = new restore_path_element($elename, $elepath);

        return $paths;     }

    
    public function process_assignment_offline_config($data) {
        $data = (object)$data;
        print_object($data); 
                $this->set_mapping('assignment_offline_config', 1, 1, true);
        $this->add_related_files('mod_assignment', 'intro', 'assignment_offline_config');
        print_object($this->get_mappingid('assignment_offline_config', 1));
        print_object($this->get_old_parentid('assignment'));
        print_object($this->get_new_parentid('assignment'));
        print_object($this->get_mapping('assignment', $this->get_old_parentid('assignment')));
        print_object($this->apply_date_offset(1));
        print_object($this->task->get_courseid());
        print_object($this->task->get_contextid());
        print_object($this->get_restoreid());
    }

    
    public function process_assignment_offline_submission_config($data) {
        $data = (object)$data;
        print_object($data);     }
}
