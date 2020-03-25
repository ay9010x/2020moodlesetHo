<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once(dirname(dirname(__FILE__)) . '/random/settings_form.php'); 

class workshop_scheduled_allocator_form extends workshop_random_allocator_form {

    
    public function definition() {
        global $OUTPUT;

        $mform = $this->_form;
        $workshop = $this->_customdata['workshop'];
        $current = $this->_customdata['current'];

        if (!empty($workshop->submissionend)) {
            $strtimeexpected = workshop::timestamp_formats($workshop->submissionend);
        }

        if (!empty($current->timeallocated)) {
            $strtimeexecuted = workshop::timestamp_formats($current->timeallocated);
        }

        $mform->addElement('header', 'scheduledallocationsettings', get_string('scheduledallocationsettings', 'workshopallocation_scheduled'));
        $mform->addHelpButton('scheduledallocationsettings', 'scheduledallocationsettings', 'workshopallocation_scheduled');

        $mform->addElement('checkbox', 'enablescheduled', get_string('enablescheduled', 'workshopallocation_scheduled'), get_string('enablescheduledinfo', 'workshopallocation_scheduled'), 1);

        $mform->addElement('header', 'scheduledallocationinfo', get_string('currentstatus', 'workshopallocation_scheduled'));

        if ($current === false) {
            $mform->addElement('static', 'infostatus', get_string('currentstatusexecution', 'workshopallocation_scheduled'),
                get_string('resultdisabled', 'workshopallocation_scheduled').' '.
                html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/invalid'))));

        } else {
            if (!empty($current->timeallocated)) {
                $mform->addElement('static', 'infostatus', get_string('currentstatusexecution', 'workshopallocation_scheduled'),
                    get_string('currentstatusexecution1', 'workshopallocation_scheduled', $strtimeexecuted).' '.
                    html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/valid'))));

                if ($current->resultstatus == workshop_allocation_result::STATUS_EXECUTED) {
                    $strstatus = get_string('resultexecuted', 'workshopallocation_scheduled').' '.
                        html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/valid')));
                } else if ($current->resultstatus == workshop_allocation_result::STATUS_FAILED) {
                    $strstatus = get_string('resultfailed', 'workshopallocation_scheduled').' '.
                        html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/invalid')));
                } else {
                    $strstatus = get_string('resultvoid', 'workshopallocation_scheduled').' '.
                        html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/invalid')));
                }

                if (!empty($current->resultmessage)) {
                    $strstatus .= html_writer::empty_tag('br').$current->resultmessage;                 }
                $mform->addElement('static', 'inforesult', get_string('currentstatusresult', 'workshopallocation_scheduled'), $strstatus);

                if ($current->timeallocated < $workshop->submissionend) {
                    $mform->addElement('static', 'infoexpected', get_string('currentstatusnext', 'workshopallocation_scheduled'),
                        get_string('currentstatusexecution2', 'workshopallocation_scheduled', $strtimeexpected).' '.
                        html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/caution'))));
                    $mform->addHelpButton('infoexpected', 'currentstatusnext', 'workshopallocation_scheduled');
                } else {
                    $mform->addElement('checkbox', 'reenablescheduled', get_string('currentstatusreset', 'workshopallocation_scheduled'),
                       get_string('currentstatusresetinfo', 'workshopallocation_scheduled'));
                    $mform->addHelpButton('reenablescheduled', 'currentstatusreset', 'workshopallocation_scheduled');
                }

            } else if (empty($current->enabled)) {
                $mform->addElement('static', 'infostatus', get_string('currentstatusexecution', 'workshopallocation_scheduled'),
                    get_string('resultdisabled', 'workshopallocation_scheduled').' '.
                    html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/invalid'))));

            } else if ($workshop->phase != workshop::PHASE_SUBMISSION) {
                $mform->addElement('static', 'infostatus', get_string('currentstatusexecution', 'workshopallocation_scheduled'),
                    get_string('resultfailed', 'workshopallocation_scheduled').' '.
                    html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/invalid'))).
                    html_writer::empty_tag('br').
                    get_string('resultfailedphase', 'workshopallocation_scheduled'));

            } else if (empty($workshop->submissionend)) {
                $mform->addElement('static', 'infostatus', get_string('currentstatusexecution', 'workshopallocation_scheduled'),
                    get_string('resultfailed', 'workshopallocation_scheduled').' '.
                    html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/invalid'))).
                    html_writer::empty_tag('br').
                    get_string('resultfaileddeadline', 'workshopallocation_scheduled'));

            } else if ($workshop->submissionend < time()) {
                                $mform->addElement('static', 'infostatus', get_string('currentstatusexecution', 'workshopallocation_scheduled'),
                    get_string('currentstatusexecution4', 'workshopallocation_scheduled').' '.
                    html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/caution'))));

            } else {
                $mform->addElement('static', 'infostatus', get_string('currentstatusexecution', 'workshopallocation_scheduled'),
                    get_string('currentstatusexecution3', 'workshopallocation_scheduled', $strtimeexpected).' '.
                    html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/caution'))));
            }
        }

        parent::definition();

        $mform->addHelpButton('randomallocationsettings', 'randomallocationsettings', 'workshopallocation_scheduled');
    }
}
