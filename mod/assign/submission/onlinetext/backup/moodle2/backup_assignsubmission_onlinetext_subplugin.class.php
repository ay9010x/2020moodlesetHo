<?php



defined('MOODLE_INTERNAL') || die();


class backup_assignsubmission_onlinetext_subplugin extends backup_subplugin {

    
    protected function define_submission_subplugin_structure() {

                $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginelement = new backup_nested_element('submission_onlinetext',
                                                      null,
                                                      array('onlinetext', 'onlineformat', 'submission'));

                $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);

                $subpluginelement->set_source_table('assignsubmission_onlinetext',
                                          array('submission' => backup::VAR_PARENTID));

        $subpluginelement->annotate_files('assignsubmission_onlinetext',
                                          'submissions_onlinetext',
                                          'submission');
        return $subplugin;
    }

}
