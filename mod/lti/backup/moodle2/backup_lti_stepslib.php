<?php



defined('MOODLE_INTERNAL') || die;


class backup_lti_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        
                $userinfo = $this->get_setting_value('userinfo');

                $lti = new backup_nested_element('lti', array('id'), array(
            'name',
            'intro',
            'introformat',
            'timecreated',
            'timemodified',
            'typeid',
            'toolurl',
            'securetoolurl',
            'preferheight',
            'launchcontainer',
            'instructorchoicesendname',
            'instructorchoicesendemailaddr',
            'instructorchoiceacceptgrades',
            'instructorchoiceallowroster',
            'instructorchoiceallowsetting',
            'grade',
            'instructorcustomparameters',
            'debuglaunch',
            'showtitlelaunch',
            'showdescriptionlaunch',
            'icon',
            'secureicon',
            )
        );

                
                $lti->set_source_table('lti', array('id' => backup::VAR_ACTIVITYID));

                
                $lti->annotate_files('mod_lti', 'intro', null); 
                $this->add_subplugin_structure('ltisource', $lti, true);
        $this->add_subplugin_structure('ltiservice', $lti, true);

                return $this->prepare_activity_structure($lti);
    }
}
