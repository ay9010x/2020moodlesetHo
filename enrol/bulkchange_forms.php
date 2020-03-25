<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");


abstract class enrol_bulk_enrolment_change_form extends moodleform {

    
    protected function definition() {
        $form = $this->_form;
        $users = $this->_customdata['users'];

        $statusoptions = $this->get_status_options();
        $form->addElement('html', $this->get_users_table($users, $statusoptions));
        $form->addElement('select', 'status', get_string('alterstatus', 'enrol_manual'), $statusoptions, array('optional' => true));
        $form->addElement('date_time_selector', 'timestart', get_string('altertimestart', 'enrol_manual'), array('optional' => true));
        $form->addElement('date_time_selector', 'timeend', get_string('altertimeend', 'enrol_manual'), array('optional' => true));

        $this->add_action_buttons();
    }

    
    protected function get_status_options() {
        return array(-1                   => get_string('nochange', 'enrol'),
                     ENROL_USER_ACTIVE    => get_string('participationactive', 'enrol'),
                     ENROL_USER_SUSPENDED => get_string('participationsuspended', 'enrol'));
    }

    
    protected function get_users_table(array $users, array $statusoptions) {
        $table = new html_table();
        $table->head = array(
            get_string('name'),
            get_string('participationstatus', 'enrol'),
            get_string('enroltimestart', 'enrol'),
            get_string('enroltimeend', 'enrol'),
        );
        $table->data = array();
        foreach ($users as $user) {
            foreach ($user->enrolments as $enrolment) {
                $input = html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'bulkuser[]', 'value' => $user->id));
                $table->data[] = array(
                    fullname($user).$input,
                    $statusoptions[$enrolment->status],
                    (!empty($enrolment->timestart))?userdate($enrolment->timestart):'',
                    (!empty($enrolment->timeend))?userdate($enrolment->timeend):'',
                );
            }
        }
        return html_writer::table($table);
    }
}


abstract class enrol_bulk_enrolment_confirm_form extends enrol_bulk_enrolment_change_form {

    
    protected function definition() {
        $form = $this->_form;
        $users = $this->_customdata['users'];
        $title = $this->_customdata['title'];
        $message = $this->_customdata['message'];
        $button = $this->_customdata['button'];

        $form->addElement('html', $this->get_users_table($users, $this->get_status_options()));
        $form->addElement('header', 'ebecf_header', $title);
        $form->addElement('html', html_writer::tag('p', $message));
        $this->add_action_buttons(true, $button);
    }
}