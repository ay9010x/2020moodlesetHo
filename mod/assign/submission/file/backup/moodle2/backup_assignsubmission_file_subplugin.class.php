<?php



defined('MOODLE_INTERNAL') || die();


class backup_assignsubmission_file_subplugin extends backup_subplugin {

    
    protected function define_submission_subplugin_structure() {

                $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginelement = new backup_nested_element('submission_file',
                                                      null,
                                                      array('numfiles', 'submission'));

                $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);

                $subpluginelement->set_source_table('assignsubmission_file',
                                            array('submission' => backup::VAR_PARENTID));

                $subpluginelement->annotate_files('assignsubmission_file',
                                          'submission_files',
                                          'submission');
        return $subplugin;
    }

}
