<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');


class quiz_statistics_settings_form extends moodleform {
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'preferencespage', get_string('reportsettings', 'quiz_statistics'));

        $options = array();
        foreach (array_keys(quiz_get_grading_options()) as $which) {
            $options[$which] = \quiz_statistics\calculator::using_attempts_lang_string($which);
        }

        $mform->addElement('select', 'whichattempts', get_string('calculatefrom', 'quiz_statistics'), $options);

        if (quiz_allows_multiple_tries($this->_customdata['quiz'])) {
            $mform->addElement('select', 'whichtries', get_string('whichtries', 'quiz_statistics'), array(
                                           question_attempt::FIRST_TRY    => get_string('firsttry', 'question'),
                                           question_attempt::LAST_TRY     => get_string('lasttry', 'question'),
                                           question_attempt::ALL_TRIES    => get_string('alltries', 'question'))
            );
            $mform->setDefault('whichtries', question_attempt::LAST_TRY);
        }
        $mform->addElement('submit', 'submitbutton', get_string('preferencessave', 'quiz_overview'));
    }

}
