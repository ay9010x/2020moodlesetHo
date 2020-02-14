<?php



defined('MOODLE_INTERNAL') || die();


class restore_gradingform_guide_plugin extends restore_gradingform_plugin {

    
    protected function define_definition_plugin_structure() {

        $paths = array();

        $paths[] = new restore_path_element('gradingform_guide_criterion',
            $this->get_pathfor('/guidecriteria/guidecriterion'));

        $paths[] = new restore_path_element('gradingform_guide_comment',
            $this->get_pathfor('/guidecomments/guidecomment'));

                        $paths[] = new restore_path_element('gradingform_guide_comment_legacy',
            $this->get_pathfor('/guidecriteria/guidecomments/guidecomment'));

        return $paths;
    }

    
    protected function define_instance_plugin_structure() {

        $paths = array();

        $paths[] = new restore_path_element('gradinform_guide_filling',
            $this->get_pathfor('/fillings/filling'));

        return $paths;
    }

    
    public function process_gradingform_guide_criterion($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->definitionid = $this->get_new_parentid('grading_definition');

        $newid = $DB->insert_record('gradingform_guide_criteria', $data);
        $this->set_mapping('gradingform_guide_criterion', $oldid, $newid);
    }

    
    public function process_gradingform_guide_comment($data) {
        global $DB;

        $data = (object)$data;
        $data->definitionid = $this->get_new_parentid('grading_definition');

        $DB->insert_record('gradingform_guide_comments', $data);
    }

    
    public function process_gradingform_guide_comment_legacy($data) {
        global $DB;

        $data = (object)$data;
        $data->definitionid = $this->get_new_parentid('grading_definition');

        $DB->insert_record('gradingform_guide_comments', $data);
    }

    
    public function process_gradinform_guide_filling($data) {
        global $DB;

        $data = (object)$data;
        $data->instanceid = $this->get_new_parentid('grading_instance');
        $data->criterionid = $this->get_mappingid('gradingform_guide_criterion', $data->criterionid);

        $DB->insert_record('gradingform_guide_fillings', $data);
    }
}
