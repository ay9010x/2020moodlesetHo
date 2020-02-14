<?php



namespace tool_lp\form;
defined('MOODLE_INTERNAL') || die();

use core_competency\plan as planpersistent;
use required_capability_exception;


class plan extends persistent {

    protected static $persistentclass = 'core_competency\\plan';

    
    public function definition() {
        $mform = $this->_form;
        $context = $this->_customdata['context'];

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        $mform->setConstant('userid', $this->_customdata['userid']);

        $mform->addElement('header', 'generalhdr', get_string('general'));

                $mform->addElement('text', 'name', get_string('planname', 'tool_lp'), 'maxlength="100"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 100), 'maxlength', 100, 'client');
                $mform->addElement('editor', 'description', get_string('plandescription', 'tool_lp'), array('rows' => 4));
        $mform->setType('description', PARAM_RAW);

        $mform->addElement('date_time_selector', 'duedate', get_string('duedate', 'tool_lp'), array('optional' => true));
        $mform->addHelpButton('duedate', 'duedate', 'tool_lp');

                        $status = planpersistent::get_status_list($this->_customdata['userid']);
        $plan = $this->get_persistent();
        if ($plan->get_id()) {
                        $mform->addElement('static', 'staticstatus', get_string('status', 'tool_lp'), $plan->get_statusname());
        } else if (!empty($status) && count($status) > 1) {
                        $mform->addElement('select', 'status', get_string('status', 'tool_lp'), $status);
        } else if (count($status) === 1) {
                        $mform->addElement('static', 'staticstatus', get_string('status', 'tool_lp'), current($status));
        } else {
            throw new required_capability_exception($context, 'moodle/competency:planmanage', 'nopermissions', '');
        }

                $mform->setDisableShortforms();
        $this->add_action_buttons(true, get_string('savechanges', 'tool_lp'));
    }

}
