<?php



namespace tool_lpmigrate\form;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
\MoodleQuickForm::registerElementType('framework_autocomplete',
    $CFG->dirroot . '/admin/tool/lp/classes/form/framework_autocomplete.php',
    '\\tool_lp\\form\\framework_autocomplete');


class migrate_framework extends \moodleform {

    
    protected $pagecontext;

    
    public function __construct(\context $context) {
        $this->pagecontext = $context;
        parent::__construct();
    }

    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'hdrcourses', get_string('frameworks', 'tool_lpmigrate'));

        $mform->addElement('framework_autocomplete', 'from', get_string('migratefrom', 'tool_lpmigrate'), array(
            'contextid' => $this->pagecontext->id,
            'onlyvisible' => '0',
        ), 1, 2, 3);
        $mform->addRule('from', get_string('required'), 'required', null);
        $mform->addHelpButton('from', 'migratefrom', 'tool_lpmigrate');

        $mform->addElement('framework_autocomplete', 'to', get_string('migrateto', 'tool_lpmigrate'), array(
            'contextid' => $this->pagecontext->id,
            'onlyvisible' => '1',              ), 1, 2, 3);
        $mform->addRule('to', get_string('required'), 'required', null);
        $mform->addHelpButton('to', 'migrateto', 'tool_lpmigrate');

        $mform->addElement('header', 'hdrcourses', get_string('courses'));
        $mform->addElement('course', 'allowedcourses', get_string('limittothese', 'tool_lpmigrate'),
            array('showhidden' => true, 'multiple' => true));
        $mform->addHelpButton('allowedcourses', 'allowedcourses', 'tool_lpmigrate');
        $mform->addElement('course', 'disallowedcourses', get_string('excludethese', 'tool_lpmigrate'),
            array('showhidden' => true, 'multiple' => true));
        $mform->addHelpButton('disallowedcourses', 'disallowedcourses', 'tool_lpmigrate');
        $mform->addElement('date_time_selector', 'coursestartdate', get_string('startdatefrom', 'tool_lpmigrate'),
            array('optional' => true));
        $mform->addHelpButton('coursestartdate', 'coursestartdate', 'tool_lpmigrate');

        $this->add_action_buttons(true, get_string('performmigration', 'tool_lpmigrate'));
    }

    public function validation($data, $files) {
        $errors = array();

        if ($data['from'] == $data['to']) {
            $errors['to'] = get_string('errorcannotmigratetosameframework', 'tool_lpmigrate');

        } else if (!empty($data['from']) && !empty($data['to'])) {
            $mapper = new \tool_lpmigrate\framework_mapper($data['from'], $data['to']);
            $mapper->automap();
            if (!$mapper->has_mappings()) {
                $errors['to'] = 'Could not map to any competency in this framework.';
            }
        }

        return $errors;
    }

}
