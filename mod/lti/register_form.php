<?php



defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/lti/locallib.php');


class mod_lti_register_types_form extends moodleform {

    
    public function definition() {
        global $CFG;

        $mform    =& $this->_form;

        $mform->addElement('header', 'setup', get_string('registration_options', 'lti'));

        
        $strrequired = get_string('required');
        $mform->addElement('text', 'lti_registrationname', get_string('registrationname', 'lti'));
        $mform->setType('lti_registrationname', PARAM_TEXT);
        $mform->addHelpButton('lti_registrationname', 'registrationname', 'lti');
        $mform->addRule('lti_registrationname', $strrequired, 'required', null, 'client');

        
        $mform->addElement('text', 'lti_registrationurl', get_string('registrationurl', 'lti'), array('size' => '64'));
        $mform->setType('lti_registrationurl', PARAM_URL);
        $mform->addHelpButton('lti_registrationurl', 'registrationurl', 'lti');
        $mform->addRule('lti_registrationurl', $strrequired, 'required', null, 'client');

        
        $options = array_keys(lti_get_capabilities());
        natcasesort($options);
        $attributes = array( 'multiple' => 1, 'size' => min(count($options), 10) );
        $mform->addElement('select', 'lti_capabilities', get_string('capabilities', 'lti'),
            array_combine($options, $options), $attributes);
        $mform->setType('lti_capabilities', PARAM_TEXT);
        $mform->addHelpButton('lti_capabilities', 'capabilities', 'lti');
        $mform->addRule('lti_capabilities', $strrequired, 'required', null, 'client');

        
        $services = lti_get_services();
        $options = array();
        foreach ($services as $service) {
            $options[$service->get_id()] = $service->get_name();
        }
        $attributes = array( 'multiple' => 1, 'size' => min(count($options), 10) );
        $mform->addElement('select', 'lti_services', get_string('services', 'lti'), $options, $attributes);
        $mform->setType('lti_services', PARAM_TEXT);
        $mform->addHelpButton('lti_services', 'services', 'lti');
        $mform->addRule('lti_services', $strrequired, 'required', null, 'client');

        $mform->addElement('hidden', 'toolproxyid');
        $mform->setType('toolproxyid', PARAM_INT);

        $tab = optional_param('tab', '', PARAM_ALPHAEXT);
        $mform->addElement('hidden', 'tab', $tab);
        $mform->setType('tab', PARAM_ALPHAEXT);

        $courseid = optional_param('course', 1, PARAM_INT);
        $mform->addElement('hidden', 'course', $courseid);
        $mform->setType('course', PARAM_INT);

        
        $this->add_action_buttons();
    }

    
    public function disable_fields() {

        $mform    =& $this->_form;

        $mform->disabledIf('lti_registrationurl', null);
        $mform->disabledIf('lti_capabilities', null);
        $mform->disabledIf('lti_services', null);

    }
}
