<?php



require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/formslib.php');

defined('BEHAT_SITE_RUNNING') || die('Only available on Behat test server');


class test_form extends moodleform {

    
    public function definition() {

        $mform = $this->_form;

        $labels = array('North', 'Est', 'South', 'West');
        $select = $mform->addElement('select', 'mselect_name', 'Choose one or more directions', $labels);
        $select->setMultiple(true);

        $mform->addElement('text', 'text_name', 'Enter your name');
        $mform->setType('text_name', PARAM_RAW);

        $mform->disabledIf('text_name', 'mselect_name[]', 'neq', array(2, 3));

        $this->add_action_buttons($cancel = true, $submitlabel = null);
    }
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/lib/form/tests/fixtures/multi_select_dependencies.php');
$PAGE->set_title('multi_select_dependencies');

$mform = new test_form(new moodle_url('/lib/form/tests/fixtures/multi_select_dependencies.php'));

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();