<?php







class backup_label_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                $label = new backup_nested_element('label', array('id'), array(
            'name', 'intro', 'introformat', 'timemodified'));

                
                $label->set_source_table('label', array('id' => backup::VAR_ACTIVITYID));

                
                $label->annotate_files('mod_label', 'intro', null); 
                return $this->prepare_activity_structure($label);
    }
}
