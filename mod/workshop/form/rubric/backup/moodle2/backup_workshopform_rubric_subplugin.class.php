<?php



defined('MOODLE_INTERNAL') || die();


class backup_workshopform_rubric_subplugin extends backup_subplugin {

    
    protected function define_workshop_subplugin_structure() {

                $subplugin = $this->get_subplugin_element();         $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginconfig = new backup_nested_element('workshopform_rubric_config', null, 'layout');
        $subplugindimension = new backup_nested_element('workshopform_rubric_dimension', array('id'), array(
            'sort', 'description', 'descriptionformat'));
        $subpluginlevel = new backup_nested_element('workshopform_rubric_level', array('id'), array(
            'grade', 'definition', 'definitionformat'));

                $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginconfig);
        $subpluginwrapper->add_child($subplugindimension);
        $subplugindimension->add_child($subpluginlevel);

                $subpluginconfig->set_source_table('workshopform_rubric_config', array('workshopid' => backup::VAR_ACTIVITYID));
        $subplugindimension->set_source_table('workshopform_rubric', array('workshopid' => backup::VAR_ACTIVITYID));
        $subpluginlevel->set_source_table('workshopform_rubric_levels', array('dimensionid' => backup::VAR_PARENTID));

                $subplugindimension->annotate_files('workshopform_rubric', 'description', 'id');

        return $subplugin;
    }

    
    protected function define_referenceassessment_subplugin_structure() {
        return $this->dimension_grades_structure('workshopform_rubric_referencegrade');
    }

    
    protected function define_exampleassessment_subplugin_structure() {
        return $this->dimension_grades_structure('workshopform_rubric_examplegrade');
    }

    
    protected function define_assessment_subplugin_structure() {
        return $this->dimension_grades_structure('workshopform_rubric_grade');
    }

            
    
    private function dimension_grades_structure($elementname) {

                $subplugin = $this->get_subplugin_element();         $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subplugingrade = new backup_nested_element($elementname, array('id'), array(
            'dimensionid', 'grade'));

                $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subplugingrade);

                $subplugingrade->set_source_sql(
            "SELECT id, dimensionid, grade
               FROM {workshop_grades}
              WHERE strategy = 'rubric' AND assessmentid = ?",
              array(backup::VAR_PARENTID));

        return $subplugin;
    }
}
