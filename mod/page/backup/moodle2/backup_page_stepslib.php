<?php




defined('MOODLE_INTERNAL') || die;




class backup_page_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                $page = new backup_nested_element('page', array('id'), array(
            'name', 'intro', 'introformat', 'content', 'contentformat',
            'legacyfiles', 'legacyfileslast', 'display', 'displayoptions',
            'revision', 'timemodified'));

                
                $page->set_source_table('page', array('id' => backup::VAR_ACTIVITYID));

                
                $page->annotate_files('mod_page', 'intro', null);         $page->annotate_files('mod_page', 'content', null); 
                return $this->prepare_activity_structure($page);
    }
}
