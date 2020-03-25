<?php



namespace tool_lp\form;
defined('MOODLE_INTERNAL') || die();

use moodleform;

require_once($CFG->libdir . '/formslib.php');


class template_cohorts extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $options = array(
            'ajax' => 'tool_lp/form-cohort-selector',
            'multiple' => true,
            'data-contextid' => $this->_customdata['pagecontextid'],
            'data-includes' => 'parents'
        );
        $mform->addElement('autocomplete', 'cohorts', get_string('selectcohortstosync', 'tool_lp'), array(), $options);
        $mform->addElement('submit', 'submit', get_string('addcohorts', 'tool_lp'));
    }

}
