<?php



namespace report_search\output;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");


class form extends \moodleform {

    
    public function definition() {

        $mform = $this->_form;

        $checkboxarray = array();
        $checkboxarray[] =& $mform->createElement('checkbox', 'reindex', '', get_string('indexsite', 'report_search'));
        $mform->addGroup($checkboxarray, 'reindexcheckbox', '', array(''), false);
        $mform->closeHeaderBefore('reindexcheckbox');

        $checkboxarray = array();
        $checkboxarray[] =& $mform->createElement('checkbox', 'delete', '', get_string('delete', 'report_search'));
        $mform->addGroup($checkboxarray, 'deletecheckbox', '', array(''), false);
        $mform->closeHeaderBefore('deletecheckbox');

                $areacheckboxarray = array();
        $areacheckboxarray[] =& $mform->createElement('advcheckbox', 'all', '', get_string('entireindex', 'report_search'),
            array('group' => 1));
        $mform->setDefault('all', true);

        foreach ($this->_customdata['searchareas'] as $key => $searcharea) {
            $areacheckboxarray[] =& $mform->createElement('advcheckbox', $key, '',
                $searcharea->get_visible_name(), array('group' => 2));
        }
        $mform->addGroup($areacheckboxarray, 'areasadvcheckbox', '', array(' '), false);
        $mform->closeHeaderBefore('areasadvcheckbox');
        $mform->disabledIf('areasadvcheckbox', 'delete', 'notchecked');

        $this->add_action_buttons(false, get_string('execute', 'report_search'));
    }
}
