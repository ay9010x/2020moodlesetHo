<?php



defined('MOODLE_INTERNAL') || die();


class backup_workshopform_comments_subplugin extends backup_subplugin {

    
    protected function define_workshop_subplugin_structure() {

                $subplugin = $this->get_subplugin_element();         $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subplugindimension = new backup_nested_element('workshopform_comments_dimension', array('id'), array(
            'sort', 'description', 'descriptionformat'));

                $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subplugindimension);

                $subplugindimension->set_source_table('workshopform_comments', array('workshopid' => backup::VAR_ACTIVITYID));

                $subplugindimension->annotate_files('workshopform_comments', 'description', 'id');

        return $subplugin;
    }

    
    protected function define_referenceassessment_subplugin_structure() {
        return $this->dimension_grades_structure('workshopform_comments_referencegrade');
    }

    
    protected function define_exampleassessment_subplugin_structure() {
        return $this->dimension_grades_structure('workshopform_comments_examplegrade');
    }

    
    protected function define_assessment_subplugin_structure() {
        return $this->dimension_grades_structure('workshopform_comments_grade');
    }

            
    
    private function dimension_grades_structure($elementname) {

                $subplugin = $this->get_subplugin_element();         $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subplugingrade = new backup_nested_element($elementname, array('id'), array(
            'dimensionid', 'peercomment', 'peercommentformat'));

                $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subplugingrade);

                $subplugingrade->set_source_sql(
            "SELECT id, dimensionid, peercomment, peercommentformat
               FROM {workshop_grades}
              WHERE strategy = 'comments' AND assessmentid = ?",
              array(backup::VAR_PARENTID));

        return $subplugin;
    }
}
