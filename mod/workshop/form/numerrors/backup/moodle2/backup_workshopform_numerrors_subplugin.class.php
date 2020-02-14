<?php




defined('MOODLE_INTERNAL') || die();


class backup_workshopform_numerrors_subplugin extends backup_subplugin {

    
    protected function define_workshop_subplugin_structure() {

                $subplugin = $this->get_subplugin_element();         $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginmap = new backup_nested_element('workshopform_numerrors_map', array('id'), array(
            'nonegative', 'grade'));
        $subplugindimension = new backup_nested_element('workshopform_numerrors_dimension', array('id'), array(
            'sort', 'description', 'descriptionformat', 'grade0', 'grade1', 'weight'));

                $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginmap);
        $subpluginwrapper->add_child($subplugindimension);

                $subpluginmap->set_source_table('workshopform_numerrors_map', array('workshopid' => backup::VAR_ACTIVITYID));
        $subplugindimension->set_source_table('workshopform_numerrors', array('workshopid' => backup::VAR_ACTIVITYID));

                $subplugindimension->annotate_files('workshopform_numerrors', 'description', 'id');

        return $subplugin;
    }

    
    protected function define_referenceassessment_subplugin_structure() {
        return $this->dimension_grades_structure('workshopform_numerrors_referencegrade');
    }

    
    protected function define_exampleassessment_subplugin_structure() {
        return $this->dimension_grades_structure('workshopform_numerrors_examplegrade');
    }

    
    protected function define_assessment_subplugin_structure() {
        return $this->dimension_grades_structure('workshopform_numerrors_grade');
    }

            
    
    private function dimension_grades_structure($elementname) {

                $subplugin = $this->get_subplugin_element();         $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subplugingrade = new backup_nested_element($elementname, array('id'), array(
            'dimensionid', 'grade', 'peercomment', 'peercommentformat'));

                $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subplugingrade);

                $subplugingrade->set_source_sql(
            "SELECT id, dimensionid, grade, peercomment, peercommentformat
               FROM {workshop_grades}
              WHERE strategy = 'numerrors' AND assessmentid = ?",
              array(backup::VAR_PARENTID));

        return $subplugin;
    }
}
