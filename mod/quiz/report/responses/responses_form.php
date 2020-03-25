<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport_form.php');



class quiz_responses_settings_form extends mod_quiz_attempts_report_form {

    protected function other_preference_fields(MoodleQuickForm $mform) {
        $mform->addGroup(array(
            $mform->createElement('advcheckbox', 'qtext', '',
                get_string('questiontext', 'quiz_responses')),
            $mform->createElement('advcheckbox', 'resp', '',
                get_string('response', 'quiz_responses')),
            $mform->createElement('advcheckbox', 'right', '',
                get_string('rightanswer', 'quiz_responses')),
        ), 'coloptions', get_string('showthe', 'quiz_responses'), array(' '), false);
        $mform->disabledIf('qtext', 'attempts', 'eq', quiz_attempts_report::ENROLLED_WITHOUT);
        $mform->disabledIf('resp',  'attempts', 'eq', quiz_attempts_report::ENROLLED_WITHOUT);
        $mform->disabledIf('right', 'attempts', 'eq', quiz_attempts_report::ENROLLED_WITHOUT);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['attempts'] != quiz_attempts_report::ENROLLED_WITHOUT && !(
                $data['qtext'] || $data['resp'] || $data['right'])) {
            $errors['coloptions'] = get_string('reportmustselectstate', 'quiz');
        }

        return $errors;
    }

    protected function other_attempt_fields(MoodleQuickForm $mform) {
        parent::other_attempt_fields($mform);
        if (quiz_allows_multiple_tries($this->_customdata['quiz'])) {
            $mform->addElement('select', 'whichtries', get_string('whichtries', 'question'), array(
                                           question_attempt::FIRST_TRY    => get_string('firsttry', 'question'),
                                           question_attempt::LAST_TRY     => get_string('lasttry', 'question'),
                                           question_attempt::ALL_TRIES    => get_string('alltries', 'question'))
            );
            $mform->setDefault('whichtries', question_attempt::LAST_TRY);
        }
    }
}
