<?php




defined('MOODLE_INTERNAL') || die;


class backup_resource_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                $resource = new backup_nested_element('resource', array('id'), array(
            'name', 'intro', 'introformat', 'tobemigrated',
            'legacyfiles', 'legacyfileslast', 'display',
            'displayoptions', 'filterfiles', 'revision', 'timemodified'));

                
                $resource->set_source_table('resource', array('id' => backup::VAR_ACTIVITYID));

                
                $resource->annotate_files('mod_resource', 'intro', null);         $resource->annotate_files('mod_resource', 'content', null); 
                return $this->prepare_activity_structure($resource);
    }
}
