<?php



defined('MOODLE_INTERNAL') || die();


class backup_workshopeval_best_subplugin extends backup_subplugin {

    
    protected function define_workshop_subplugin_structure() {

                $subplugin = $this->get_subplugin_element();         $subplugin_wrapper = new backup_nested_element($this->get_recommended_name());
        $subplugin_table_settings = new backup_nested_element('workshopeval_best_settings', null, array('comparison'));

                $subplugin->add_child($subplugin_wrapper);
        $subplugin_wrapper->add_child($subplugin_table_settings);

                $subplugin_table_settings->set_source_table('workshopeval_best_settings', array('workshopid' => backup::VAR_ACTIVITYID));

        return $subplugin;
    }
}
