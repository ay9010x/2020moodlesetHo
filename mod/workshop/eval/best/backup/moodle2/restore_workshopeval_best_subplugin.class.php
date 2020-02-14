<?php





class restore_workshopeval_best_subplugin extends restore_subplugin {

            
    
    protected function define_workshop_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('setting');
        $elepath = $this->get_pathfor('/workshopeval_best_settings');         $paths[] = new restore_path_element($elename, $elepath);

        return $paths;     }

            
    
    public function process_workshopeval_best_setting($data) {
        global $DB;

        $data = (object)$data;
        $data->workshopid = $this->get_new_parentid('workshop');
        $DB->insert_record('workshopeval_best_settings', $data);
    }
}
