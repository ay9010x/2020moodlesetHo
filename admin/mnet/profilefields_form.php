<?php




if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once($CFG->libdir . '/formslib.php');


class mnet_profile_form extends moodleform {

    function definition() {
        global $CFG;
        $mform =& $this->_form;

        $mnetprofileimportfields = '';
        if (isset($CFG->mnetprofileimportfields)) {
            $mnetprofileimportfields = str_replace(',', ', ', $CFG->mnetprofileimportfields);
        }

        $mnetprofileexportfields = '';
        if (isset($CFG->mnetprofileexportfields)) {
            $mnetprofileexportfields = str_replace(',', ', ', $CFG->mnetprofileexportfields);
        }

        $mform->addElement('hidden', 'hostid', $this->_customdata['hostid']);
        $mform->setType('hostid', PARAM_INT);

        $fields = mnet_profile_field_options();

                $mform->addElement('header', 'import', get_string('importfields', 'mnet'));

        $select = $mform->addElement('select', 'importfields', get_string('importfields', 'mnet'), $fields['optional']);
        $select->setMultiple(true);

        $mform->addElement('checkbox', 'importdefault', get_string('leavedefault', 'mnet'), $mnetprofileimportfields);

                $mform->addElement('header', 'export', get_string('exportfields', 'mnet'));

        $select = $mform->addElement('select', 'exportfields', get_string('exportfields', 'mnet'), $fields['optional']);
        $select->setMultiple(true);

        $mform->addElement('checkbox', 'exportdefault', get_string('leavedefault', 'mnet'), $mnetprofileexportfields);

        $this->add_action_buttons();
    }
}
