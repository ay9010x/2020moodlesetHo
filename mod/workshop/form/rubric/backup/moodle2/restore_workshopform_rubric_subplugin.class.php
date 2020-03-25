<?php




defined('MOODLE_INTERNAL') || die();


class restore_workshopform_rubric_subplugin extends restore_subplugin {

            
    
    protected function define_workshop_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('config');
        $elepath = $this->get_pathfor('/workshopform_rubric_config');
        $paths[] = new restore_path_element($elename, $elepath);

        $elename = $this->get_namefor('dimension');
        $elepath = $this->get_pathfor('/workshopform_rubric_dimension');
        $paths[] = new restore_path_element($elename, $elepath);

        $elename = $this->get_namefor('level');
        $elepath = $this->get_pathfor('/workshopform_rubric_dimension/workshopform_rubric_level');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths;     }

    
    protected function define_referenceassessment_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('referencegrade');
        $elepath = $this->get_pathfor('/workshopform_rubric_referencegrade');         $paths[] = new restore_path_element($elename, $elepath);

        return $paths;     }

    
    protected function define_exampleassessment_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('examplegrade');
        $elepath = $this->get_pathfor('/workshopform_rubric_examplegrade');         $paths[] = new restore_path_element($elename, $elepath);

        return $paths;     }

    
    protected function define_assessment_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('grade');
        $elepath = $this->get_pathfor('/workshopform_rubric_grade');         $paths[] = new restore_path_element($elename, $elepath);

        return $paths;     }

            
    
    public function process_workshopform_rubric_config($data) {
        global $DB;

        $data = (object)$data;
        $data->workshopid = $this->get_new_parentid('workshop');
        $DB->insert_record('workshopform_rubric_config', $data);
    }

    
    public function process_workshopform_rubric_dimension($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->workshopid = $this->get_new_parentid('workshop');

        $newitemid = $DB->insert_record('workshopform_rubric', $data);
        $this->set_mapping($this->get_namefor('dimension'), $oldid, $newitemid, true);

                $this->add_related_files('workshopform_rubric', 'description', $this->get_namefor('dimension'), null, $oldid);
    }

    
    public function process_workshopform_rubric_level($data) {
        global $DB;

        $data = (object)$data;
        $data->dimensionid = $this->get_new_parentid($this->get_namefor('dimension'));
        $DB->insert_record('workshopform_rubric_levels', $data);
    }

    
    public function process_workshopform_rubric_referencegrade($data) {
        $this->process_dimension_grades_structure('workshop_referenceassessment', $data);
    }

    
    public function process_workshopform_rubric_examplegrade($data) {
        $this->process_dimension_grades_structure('workshop_exampleassessment', $data);
    }

    
    public function process_workshopform_rubric_grade($data) {
        $this->process_dimension_grades_structure('workshop_assessment', $data);
    }

            
    
    private function process_dimension_grades_structure($elementname, $data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->assessmentid = $this->get_new_parentid($elementname);
        $data->strategy = 'rubric';
        $data->dimensionid = $this->get_mappingid($this->get_namefor('dimension'), $data->dimensionid);

        $DB->insert_record('workshop_grades', $data);
    }
}
