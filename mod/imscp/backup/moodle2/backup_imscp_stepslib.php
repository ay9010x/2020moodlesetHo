<?php



defined('MOODLE_INTERNAL') || die();


class backup_imscp_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                $imscp = new backup_nested_element('imscp', array('id'), array(
            'name', 'intro', 'introformat', 'revision',
            'keepold', 'structure', 'timemodified'));

        
                $imscp->set_source_table('imscp', array('id' => backup::VAR_ACTIVITYID));

        
                $imscp->annotate_files('mod_imscp', 'intro', null);         $imscp->annotate_files('mod_imscp', 'backup', null);                 $imscp->annotate_files('mod_imscp', 'content', null); 
                return $this->prepare_activity_structure($imscp);
    }
}
