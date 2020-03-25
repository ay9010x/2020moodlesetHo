<?php





class backup_assignment_offline_subplugin extends backup_subplugin {

    
    protected function define_assignment_subplugin_structure() {

        return false; 
        

        
        $subplugin = $this->get_subplugin_element(null, '/assignment/assignmenttype', 'offline');

        
        $assassoff = new backup_nested_element($this->get_recommended_name());
        $config = new backup_nested_element('config', null, array('name', 'value'));

        $subplugin->add_child($assassoff);
        $assassoff->add_child($config);

        $config->set_source_table('config', array('id' => '/assignment/id'));

        return $subplugin;     }

    
    protected function define_submission_subplugin_structure() {

        return false; 
                $subplugin = $this->get_subplugin_element(null, '/assignment/assignmenttype', 'offline');

                $asssuboff = new backup_nested_element($this->get_recommended_name());
                        $config = new backup_nested_element('submission_config', null, array('name', 'value'));

        $subplugin->add_child($asssuboff);
        $asssuboff->add_child($config);

        $config->set_source_table('config', array('id' => backup::VAR_PARENTID));

        return $subplugin;     }
}
