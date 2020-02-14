<?php



defined('MOODLE_INTERNAL') || die();


class backup_gradingform_rubric_plugin extends backup_gradingform_plugin {

    
    protected function define_definition_plugin_structure() {

                $plugin = $this->get_plugin_element(null, '../../method', 'rubric');

                $pluginwrapper = new backup_nested_element($this->get_recommended_name());

                $plugin->add_child($pluginwrapper);

        
        $criteria = new backup_nested_element('criteria');

        $criterion = new backup_nested_element('criterion', array('id'), array(
            'sortorder', 'description', 'descriptionformat'));

        $levels = new backup_nested_element('levels');

        $level = new backup_nested_element('level', array('id'), array(
            'score', 'definition', 'definitionformat'));

        
        $pluginwrapper->add_child($criteria);
        $criteria->add_child($criterion);
        $criterion->add_child($levels);
        $levels->add_child($level);

        
        $criterion->set_source_table('gradingform_rubric_criteria',
                array('definitionid' => backup::VAR_PARENTID));

        $level->set_source_table('gradingform_rubric_levels',
                array('criterionid' => backup::VAR_PARENTID));

                
        return $plugin;
    }

    
    protected function define_instance_plugin_structure() {

                $plugin = $this->get_plugin_element(null, '../../../../method', 'rubric');

                $pluginwrapper = new backup_nested_element($this->get_recommended_name());

                $plugin->add_child($pluginwrapper);

        
        $fillings = new backup_nested_element('fillings');

        $filling = new backup_nested_element('filling', array('id'), array(
            'criterionid', 'levelid', 'remark', 'remarkformat'));

        
        $pluginwrapper->add_child($fillings);
        $fillings->add_child($filling);

        
                $filling->set_source_sql('SELECT rf.*
                FROM {gradingform_rubric_fillings} rf
                JOIN {grading_instances} gi ON gi.id = rf.instanceid
                JOIN {gradingform_rubric_criteria} rc ON rc.id = rf.criterionid AND gi.definitionid = rc.definitionid
                WHERE rf.instanceid = :instanceid',
                array('instanceid' => backup::VAR_PARENTID));

                
        return $plugin;
    }
}
