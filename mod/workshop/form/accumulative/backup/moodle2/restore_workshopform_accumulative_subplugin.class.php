<?php




defined('MOODLE_INTERNAL') || die();


class restore_workshopform_accumulative_subplugin extends restore_subplugin {

            
    
    protected function define_workshop_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('dimension');
        $elepath = $this->get_pathfor('/workshopform_accumulative_dimension');         $paths[] = new restore_path_element($elename, $elepath);

        return $paths;     }

    
    protected function define_referenceassessment_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('referencegrade');
        $elepath = $this->get_pathfor('/workshopform_accumulative_referencegrade');         $paths[] = new restore_path_element($elename, $elepath);

        return $paths;     }

    
    protected function define_exampleassessment_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('examplegrade');
        $elepath = $this->get_pathfor('/workshopform_accumulative_examplegrade');         $paths[] = new restore_path_element($elename, $elepath);

        return $paths;     }

    
    protected function define_assessment_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('grade');
        $elepath = $this->get_pathfor('/workshopform_accumulative_grade');         $paths[] = new restore_path_element($elename, $elepath);

        return $paths;     }

            
    
    public function process_workshopform_accumulative_dimension($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->workshopid = $this->get_new_parentid('workshop');
        if ($data->grade < 0) {             $data->grade = -($this->get_mappingid('scale', abs($data->grade)));
        }

        $newitemid = $DB->insert_record('workshopform_accumulative', $data);
        $this->set_mapping($this->get_namefor('dimension'), $oldid, $newitemid, true);

                $this->add_related_files('workshopform_accumulative', 'description', $this->get_namefor('dimension'), null, $oldid);
    }

    
    public function process_workshopform_accumulative_referencegrade($data) {
        $this->process_dimension_grades_structure('workshop_referenceassessment', $data);
    }

    
    public function process_workshopform_accumulative_examplegrade($data) {
        $this->process_dimension_grades_structure('workshop_exampleassessment', $data);
    }

    
    public function process_workshopform_accumulative_grade($data) {
        $this->process_dimension_grades_structure('workshop_assessment', $data);
    }

            
    
    private function process_dimension_grades_structure($elementname, $data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->assessmentid = $this->get_new_parentid($elementname);
        $data->strategy = 'accumulative';
        $data->dimensionid = $this->get_mappingid($this->get_namefor('dimension'), $data->dimensionid);

        $DB->insert_record('workshop_grades', $data);
    }
}
