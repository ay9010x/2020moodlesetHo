<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class enrol_guest_enrol_form extends moodleform {
    protected $instance;

    public function definition() {
        $mform = $this->_form;
        $instance = $this->_customdata;
        $this->instance = $instance;
        $plugin = enrol_get_plugin('guest');

        $heading = $plugin->get_instance_name($instance);
        $mform->addElement('header', 'guestheader', $heading);

        $mform->addElement('passwordunmask', 'guestpassword', get_string('password', 'enrol_guest'));

        $this->add_action_buttons(false, get_string('submit'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $instance->courseid);

        $mform->addElement('hidden', 'instance');
        $mform->setType('instance', PARAM_INT);
        $mform->setDefault('instance', $instance->id);
    }

    public function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
        $instance = $this->instance;

        if ($instance->password !== '') {
            if ($data['guestpassword'] !== $instance->password) {
                $plugin = enrol_get_plugin('guest');
                if ($plugin->get_config('showhint')) {
                    $hint = core_text::substr($instance->password, 0, 1);
                    $errors['guestpassword'] = get_string('passwordinvalidhint', 'enrol_guest', $hint);
                } else {
                    $errors['guestpassword'] = get_string('passwordinvalid', 'enrol_guest');
                }
            }
        }

        return $errors;
    }
}
