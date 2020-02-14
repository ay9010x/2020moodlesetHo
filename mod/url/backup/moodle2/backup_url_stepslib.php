<?php




defined('MOODLE_INTERNAL') || die;

 
class backup_url_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        
                $url = new backup_nested_element('url', array('id'), array(
            'name', 'intro', 'introformat', 'externalurl',
            'display', 'displayoptions', 'parameters', 'timemodified'));


                
                $url->set_source_table('url', array('id' => backup::VAR_ACTIVITYID));

                
                $url->annotate_files('mod_url', 'intro', null); 
                return $this->prepare_activity_structure($url);

    }
}
