<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport_form.php');



class quiz_overview_settings_form extends mod_quiz_attempts_report_form {

    protected function other_attempt_fields(MoodleQuickForm $mform) {
        if (has_capability('mod/quiz:regrade', $this->_customdata['context'])) {
            $mform->addElement('advcheckbox', 'onlyregraded', get_string('reportshowonly', 'quiz'),
                    get_string('optonlyregradedattempts', 'quiz_overview'));
            $mform->disabledIf('onlyregraded', 'attempts', 'eq', quiz_attempts_report::ENROLLED_WITHOUT);
        }
    }

    protected function other_preference_fields(MoodleQuickForm $mform) {
        if (quiz_has_grades($this->_customdata['quiz'])) {
            $mform->addElement('selectyesno', 'slotmarks',
                    get_string('showdetailedmarks', 'quiz_overview'));
        } else {
            $mform->addElement('hidden', 'slotmarks', 0);
            $mform->setType('slotmarks', PARAM_INT);
        }
    }
}
