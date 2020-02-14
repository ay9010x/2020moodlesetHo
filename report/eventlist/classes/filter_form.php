<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');


class report_eventlist_filter_form extends moodleform {

    
    public function definition() {

        $mform = $this->_form;
        $mform->disable_form_change_checker();
        $componentarray = $this->_customdata['components'];
        $edulevelarray = $this->_customdata['edulevel'];
        $crudarray = $this->_customdata['crud'];

        $mform->addElement('header', 'displayinfo', get_string('filter', 'report_eventlist'));

        $mform->addElement('text', 'eventname', get_string('name', 'report_eventlist'));
        $mform->setType('eventname', PARAM_RAW);

        $mform->addElement('select', 'eventcomponent', get_string('component', 'report_eventlist'), $componentarray);
        $mform->addElement('select', 'eventedulevel', get_string('edulevel', 'report_eventlist'), $edulevelarray);
        $mform->addElement('select', 'eventcrud', get_string('crud', 'report_eventlist'), $crudarray);

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('button', 'filterbutton', get_string('filter', 'report_eventlist'));
        $buttonarray[] = $mform->createElement('button', 'clearbutton', get_string('clear', 'report_eventlist'));
        $mform->addGroup($buttonarray, 'filterbuttons', '', array(' '), false);
    }
}
