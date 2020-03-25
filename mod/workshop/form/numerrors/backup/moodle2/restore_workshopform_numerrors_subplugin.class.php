<?php




defined('MOODLE_INTERNAL') || die();


class restore_workshopform_numerrors_subplugin extends restore_subplugin {

            
    
    protected function define_workshop_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('map');
        $elepath = $this->get_pathfor('/workshopform_numerrors_map');         $paths[] = new restore_path_element($elename, $elepath);

        $elename = $this->get_namefor('dimension');
        $elepath = $this->get_pathfor('/workshopform_numerrors_dimension');         $paths[] = new restore_path_element($elename, $elepath);

        return $paths;     }

    
    protected function define_referenceassessment_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('referencegrade');
        $elepath = $this->get_pathfor('/workshopform_numerrors_referencegrade');         $paths[] = new restore_path_element($elename, $elepath);

        return $paths;     }

    
    protected function define_exampleassessment_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('examplegrade');
        $elepath = $this->get_pathfor('/workshopform_numerrors_examplegrade');         $paths[] = new restore_path_element($elename, $elepath);

        return $paths;     }

    
    protected function define_assessment_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('grade');
        $elepath = $this->get_pathfor('/workshopform_numerrors_grade');         $paths[] = new restore_path_element($elename, $elepath);

        return $paths;     }

            
    
    public function process_workshopform_numerrors_map($data) {
        global $DB;

        $data = (object)$data;
        $data->workshopid = $this->get_new_parentid('workshop');
        $DB->insert_record('workshopform_numerrors_map', $data);
    }

    
    public function process_workshopform_numerrors_dimension($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->workshopid = $this->get_new_parentid('workshop');

        $newitemid = $DB->insert_record('workshopform_numerrors', $data);
        $this->set_mapping($this->get_namefor('dimension'), $oldid, $newitemid, true);

                $this->add_related_files('workshopform_numerrors', 'description', $this->get_namefor('dimension'), null, $oldid);
    }

    
    public function process_workshopform_numerrors_referencegrade($data) {
        $this->process_dimension_grades_structure('workshop_referenceassessment', $data);
    }

    
    public function process_workshopform_numerrors_examplegrade($data) {
        $this->process_dimension_grades_structure('workshop_exampleassessment', $data);
    }

    
    public function process_workshopform_numerrors_grade($data) {
        $this->process_dimension_grades_structure('workshop_assessment', $data);
    }

            
    
    private function process_dimension_grades_structure($elementname, $data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->assessmentid = $this->get_new_parentid($elementname);
        $data->strategy = 'numerrors';
        $data->dimensionid = $this->get_mappingid($this->get_namefor('dimension'), $data->dimensionid);

        $DB->insert_record('workshop_grades', $data);
    }
}
