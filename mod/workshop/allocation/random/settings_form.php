<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');


class workshop_random_allocator_form extends moodleform {

    
    public function definition() {
        $mform          = $this->_form;
        $workshop       = $this->_customdata['workshop'];
        $plugindefaults = get_config('workshopallocation_random');

        $mform->addElement('header', 'randomallocationsettings', get_string('allocationsettings', 'workshopallocation_random'));

        $gmode = groups_get_activity_groupmode($workshop->cm, $workshop->course);
        switch ($gmode) {
        case NOGROUPS:
            $grouplabel = get_string('groupsnone', 'group');
            break;
        case VISIBLEGROUPS:
            $grouplabel = get_string('groupsvisible', 'group');
            break;
        case SEPARATEGROUPS:
            $grouplabel = get_string('groupsseparate', 'group');
            break;
        }
        $mform->addElement('static', 'groupmode', get_string('groupmode', 'group'), $grouplabel);

        $options_numper = array(
            workshop_random_allocator_setting::NUMPER_SUBMISSION => get_string('numperauthor', 'workshopallocation_random'),
            workshop_random_allocator_setting::NUMPER_REVIEWER   => get_string('numperreviewer', 'workshopallocation_random')
        );
        $grpnumofreviews = array();
        $grpnumofreviews[] = $mform->createElement('select', 'numofreviews', '',
                workshop_random_allocator::available_numofreviews_list());
        $mform->setDefault('numofreviews', $plugindefaults->numofreviews);
        $grpnumofreviews[] = $mform->createElement('select', 'numper', '', $options_numper);
        $mform->setDefault('numper', workshop_random_allocator_setting::NUMPER_SUBMISSION);
        $mform->addGroup($grpnumofreviews, 'grpnumofreviews', get_string('numofreviews', 'workshopallocation_random'),
                array(' '), false);

        if (VISIBLEGROUPS == $gmode) {
            $mform->addElement('checkbox', 'excludesamegroup', get_string('excludesamegroup', 'workshopallocation_random'));
            $mform->setDefault('excludesamegroup', 0);
        } else {
            $mform->addElement('hidden', 'excludesamegroup', 0);
            $mform->setType('excludesamegroup', PARAM_BOOL);
        }

        $mform->addElement('checkbox', 'removecurrent', get_string('removecurrentallocations', 'workshopallocation_random'));
        $mform->setDefault('removecurrent', 0);

        $mform->addElement('checkbox', 'assesswosubmission', get_string('assesswosubmission', 'workshopallocation_random'));
        $mform->setDefault('assesswosubmission', 0);

        if (empty($workshop->useselfassessment)) {
            $mform->addElement('static', 'addselfassessment', get_string('addselfassessment', 'workshopallocation_random'),
                                                                 get_string('selfassessmentdisabled', 'workshop'));
        } else {
            $mform->addElement('checkbox', 'addselfassessment', get_string('addselfassessment', 'workshopallocation_random'));
        }

        $this->add_action_buttons();
    }
}
