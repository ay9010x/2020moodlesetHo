<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
class mod_scorm_report_interactions_settings extends moodleform {

    public function definition() {
        global $COURSE;
        $mform    =& $this->_form;
                $mform->addElement('header', 'preferencespage', get_string('preferencespage', 'scorm'));

        $options = array();
        if ($COURSE->id != SITEID) {
            $options[SCORM_REPORT_ATTEMPTS_ALL_STUDENTS] = get_string('optallstudents', 'scorm');
            $options[SCORM_REPORT_ATTEMPTS_STUDENTS_WITH] = get_string('optattemptsonly', 'scorm');
            $options[SCORM_REPORT_ATTEMPTS_STUDENTS_WITH_NO] = get_string('optnoattemptsonly', 'scorm');
        }
        $mform->addElement('select', 'attemptsmode', get_string('show', 'scorm'), $options);
        $mform->addElement('advcheckbox', 'qtext', '', get_string('summaryofquestiontext', 'scormreport_interactions'));
        $mform->addElement('advcheckbox', 'resp', '', get_string('summaryofresponse', 'scormreport_interactions'));
        $mform->addElement('advcheckbox', 'right', '', get_string('summaryofrightanswer', 'scormreport_interactions'));
        $mform->addElement('advcheckbox', 'result', '', get_string('summaryofresult', 'scormreport_interactions'));

                $mform->addElement('header', 'preferencesuser', get_string('preferencesuser', 'scorm'));

        $mform->addElement('text', 'pagesize', get_string('pagesize', 'scorm'));
        $mform->setType('pagesize', PARAM_INT);

        $this->add_action_buttons(false, get_string('savepreferences'));
    }
}