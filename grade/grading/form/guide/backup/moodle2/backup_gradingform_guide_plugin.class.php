<?php



defined('MOODLE_INTERNAL') || die();


class backup_gradingform_guide_plugin extends backup_gradingform_plugin {

    
    protected function define_definition_plugin_structure() {

                $plugin = $this->get_plugin_element(null, '../../method', 'guide');

                $pluginwrapper = new backup_nested_element($this->get_recommended_name());

                $plugin->add_child($pluginwrapper);

        
        $criteria = new backup_nested_element('guidecriteria');

        $criterion = new backup_nested_element('guidecriterion', array('id'), array(
            'sortorder', 'shortname', 'description', 'descriptionformat',
            'descriptionmarkers', 'descriptionmarkersformat', 'maxscore'));

        $comments = new backup_nested_element('guidecomments');

        $comment = new backup_nested_element('guidecomment', array('id'), array(
            'sortorder', 'description', 'descriptionformat'));

        
        $pluginwrapper->add_child($criteria);
        $criteria->add_child($criterion);
        $pluginwrapper->add_child($comments);
        $comments->add_child($comment);

        
        $criterion->set_source_table('gradingform_guide_criteria',
                array('definitionid' => backup::VAR_PARENTID));

        $comment->set_source_table('gradingform_guide_comments',
                array('definitionid' => backup::VAR_PARENTID));

                
        return $plugin;
    }

    
    protected function define_instance_plugin_structure() {

                $plugin = $this->get_plugin_element(null, '../../../../method', 'guide');

                $pluginwrapper = new backup_nested_element($this->get_recommended_name());

                $plugin->add_child($pluginwrapper);

        
        $fillings = new backup_nested_element('fillings');

        $filling = new backup_nested_element('filling', array('id'), array(
            'criterionid', 'remark', 'remarkformat', 'score'));

        
        $pluginwrapper->add_child($fillings);
        $fillings->add_child($filling);

        
        $filling->set_source_table('gradingform_guide_fillings',
            array('instanceid' => backup::VAR_PARENTID));

                
        return $plugin;
    }
}
