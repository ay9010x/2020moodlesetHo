<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');



class mod_quiz_mod_form extends moodleform_mod {
    
    public static $datefieldoptions = array('optional' => true, 'step' => 1);

    protected $_feedbacks;
    protected static $reviewfields = array(); 
    
    protected $maxattemptsanyoverride = null;

    public function __construct($current, $section, $cm, $course) {
        self::$reviewfields = array(
            'attempt'          => array('theattempt', 'quiz'),
            'correctness'      => array('whethercorrect', 'question'),
            'marks'            => array('marks', 'quiz'),
            'specificfeedback' => array('specificfeedback', 'question'),
            'generalfeedback'  => array('generalfeedback', 'question'),
            'rightanswer'      => array('rightanswer', 'question'),
            'overallfeedback'  => array('reviewoverallfeedback', 'quiz'),
        );
        parent::__construct($current, $section, $cm, $course);
    }

    protected function definition() {
        global $COURSE, $CFG, $DB, $PAGE;
        $quizconfig = get_config('quiz');
        $mform = $this->_form;

                $mform->addElement('header', 'general', get_string('general', 'form'));

                $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

                $this->standard_intro_elements(get_string('introduction', 'quiz'));

                $mform->addElement('header', 'timing', get_string('timing', 'quiz'));

                $mform->addElement('date_time_selector', 'timeopen', get_string('quizopen', 'quiz'),
                self::$datefieldoptions);
        $mform->addHelpButton('timeopen', 'quizopenclose', 'quiz');

        $mform->addElement('date_time_selector', 'timeclose', get_string('quizclose', 'quiz'),
                self::$datefieldoptions);

                $mform->addElement('duration', 'timelimit', get_string('timelimit', 'quiz'),
                array('optional' => true));
        $mform->addHelpButton('timelimit', 'timelimit', 'quiz');
        $mform->setAdvanced('timelimit', $quizconfig->timelimit_adv);
        $mform->setDefault('timelimit', $quizconfig->timelimit);

                $mform->addElement('select', 'overduehandling', get_string('overduehandling', 'quiz'),
                quiz_get_overdue_handling_options());
        $mform->addHelpButton('overduehandling', 'overduehandling', 'quiz');
        $mform->setAdvanced('overduehandling', $quizconfig->overduehandling_adv);
        $mform->setDefault('overduehandling', $quizconfig->overduehandling);
                        
                $mform->addElement('duration', 'graceperiod', get_string('graceperiod', 'quiz'),
                array('optional' => true));
        $mform->addHelpButton('graceperiod', 'graceperiod', 'quiz');
        $mform->setAdvanced('graceperiod', $quizconfig->graceperiod_adv);
        $mform->setDefault('graceperiod', $quizconfig->graceperiod);
        $mform->disabledIf('graceperiod', 'overduehandling', 'neq', 'graceperiod');

                        $this->standard_grading_coursemodule_elements();

        $mform->removeElement('grade');
        if (property_exists($this->current, 'grade')) {
            $currentgrade = $this->current->grade;
        } else {
            $currentgrade = $quizconfig->maximumgrade;
        }
        $mform->addElement('hidden', 'grade', $currentgrade);
        $mform->setType('grade', PARAM_FLOAT);

                $attemptoptions = array('0' => get_string('unlimited'));
        for ($i = 1; $i <= QUIZ_MAX_ATTEMPT_OPTION; $i++) {
            $attemptoptions[$i] = $i;
        }
        $mform->addElement('select', 'attempts', get_string('attemptsallowed', 'quiz'),
                $attemptoptions);
        $mform->setAdvanced('attempts', $quizconfig->attempts_adv);
        $mform->setDefault('attempts', $quizconfig->attempts);

                $mform->addElement('select', 'grademethod', get_string('grademethod', 'quiz'),
                quiz_get_grading_options());
        $mform->addHelpButton('grademethod', 'grademethod', 'quiz');
        $mform->setAdvanced('grademethod', $quizconfig->grademethod_adv);
        $mform->setDefault('grademethod', $quizconfig->grademethod);
        if ($this->get_max_attempts_for_any_override() < 2) {
            $mform->disabledIf('grademethod', 'attempts', 'eq', 1);
        }

                $mform->addElement('header', 'layouthdr', get_string('layout', 'quiz'));

        $pagegroup = array();
        $pagegroup[] = $mform->createElement('select', 'questionsperpage',
                get_string('newpage', 'quiz'), quiz_questions_per_page_options(), array('id' => 'id_questionsperpage'));
        $mform->setDefault('questionsperpage', $quizconfig->questionsperpage);

        if (!empty($this->_cm)) {
            $pagegroup[] = $mform->createElement('checkbox', 'repaginatenow', '',
                    get_string('repaginatenow', 'quiz'), array('id' => 'id_repaginatenow'));
        }

        $mform->addGroup($pagegroup, 'questionsperpagegrp',
                get_string('newpage', 'quiz'), null, false);
        $mform->addHelpButton('questionsperpagegrp', 'newpage', 'quiz');
        $mform->setAdvanced('questionsperpagegrp', $quizconfig->questionsperpage_adv);

                $mform->addElement('select', 'navmethod', get_string('navmethod', 'quiz'),
                quiz_get_navigation_options());
        $mform->addHelpButton('navmethod', 'navmethod', 'quiz');
        $mform->setAdvanced('navmethod', $quizconfig->navmethod_adv);
        $mform->setDefault('navmethod', $quizconfig->navmethod);

                $mform->addElement('header', 'interactionhdr', get_string('questionbehaviour', 'quiz'));

                $mform->addElement('selectyesno', 'shuffleanswers', get_string('shufflewithin', 'quiz'));
        $mform->addHelpButton('shuffleanswers', 'shufflewithin', 'quiz');
        $mform->setAdvanced('shuffleanswers', $quizconfig->shuffleanswers_adv);
        $mform->setDefault('shuffleanswers', $quizconfig->shuffleanswers);

                if (!empty($this->current->preferredbehaviour)) {
            $currentbehaviour = $this->current->preferredbehaviour;
        } else {
            $currentbehaviour = '';
        }
        $behaviours = question_engine::get_behaviour_options($currentbehaviour);
        $mform->addElement('select', 'preferredbehaviour',
                get_string('howquestionsbehave', 'question'), $behaviours);
        $mform->addHelpButton('preferredbehaviour', 'howquestionsbehave', 'question');
        $mform->setDefault('preferredbehaviour', $quizconfig->preferredbehaviour);

                $redochoices = array(0 => get_string('no'), 1 => get_string('canredoquestionsyes', 'quiz'));
        $mform->addElement('select', 'canredoquestions', get_string('canredoquestions', 'quiz'), $redochoices);
        $mform->addHelpButton('canredoquestions', 'canredoquestions', 'quiz');
        $mform->setAdvanced('canredoquestions', $quizconfig->canredoquestions_adv);
        $mform->setDefault('canredoquestions', $quizconfig->canredoquestions);
        foreach ($behaviours as $behaviour => $notused) {
            if (!question_engine::can_questions_finish_during_the_attempt($behaviour)) {
                $mform->disabledIf('canredoquestions', 'preferredbehaviour', 'eq', $behaviour);
            }
        }

                $mform->addElement('selectyesno', 'attemptonlast',
                get_string('eachattemptbuildsonthelast', 'quiz'));
        $mform->addHelpButton('attemptonlast', 'eachattemptbuildsonthelast', 'quiz');
        $mform->setAdvanced('attemptonlast', $quizconfig->attemptonlast_adv);
        $mform->setDefault('attemptonlast', $quizconfig->attemptonlast);
        if ($this->get_max_attempts_for_any_override() < 2) {
            $mform->disabledIf('attemptonlast', 'attempts', 'eq', 1);
        }

                $mform->addElement('header', 'reviewoptionshdr',
                get_string('reviewoptionsheading', 'quiz'));
        $mform->addHelpButton('reviewoptionshdr', 'reviewoptionsheading', 'quiz');

                $this->add_review_options_group($mform, $quizconfig, 'during',
                mod_quiz_display_options::DURING, true);
        $this->add_review_options_group($mform, $quizconfig, 'immediately',
                mod_quiz_display_options::IMMEDIATELY_AFTER);
        $this->add_review_options_group($mform, $quizconfig, 'open',
                mod_quiz_display_options::LATER_WHILE_OPEN);
        $this->add_review_options_group($mform, $quizconfig, 'closed',
                mod_quiz_display_options::AFTER_CLOSE);

        foreach ($behaviours as $behaviour => $notused) {
            $unusedoptions = question_engine::get_behaviour_unused_display_options($behaviour);
            foreach ($unusedoptions as $unusedoption) {
                $mform->disabledIf($unusedoption . 'during', 'preferredbehaviour',
                        'eq', $behaviour);
            }
        }
        $mform->disabledIf('attemptduring', 'preferredbehaviour',
                'neq', 'wontmatch');
        $mform->disabledIf('overallfeedbackduring', 'preferredbehaviour',
                'neq', 'wontmatch');

                $mform->addElement('header', 'display', get_string('appearance'));

                $mform->addElement('select', 'showuserpicture', get_string('showuserpicture', 'quiz'),
                quiz_get_user_image_options());
        $mform->addHelpButton('showuserpicture', 'showuserpicture', 'quiz');
        $mform->setAdvanced('showuserpicture', $quizconfig->showuserpicture_adv);
        $mform->setDefault('showuserpicture', $quizconfig->showuserpicture);

                $options = array();
        for ($i = 0; $i <= QUIZ_MAX_DECIMAL_OPTION; $i++) {
            $options[$i] = $i;
        }
        $mform->addElement('select', 'decimalpoints', get_string('decimalplaces', 'quiz'),
                $options);
        $mform->addHelpButton('decimalpoints', 'decimalplaces', 'quiz');
        $mform->setAdvanced('decimalpoints', $quizconfig->decimalpoints_adv);
        $mform->setDefault('decimalpoints', $quizconfig->decimalpoints);

                $options = array(-1 => get_string('sameasoverall', 'quiz'));
        for ($i = 0; $i <= QUIZ_MAX_Q_DECIMAL_OPTION; $i++) {
            $options[$i] = $i;
        }
        $mform->addElement('select', 'questiondecimalpoints',
                get_string('decimalplacesquestion', 'quiz'), $options);
        $mform->addHelpButton('questiondecimalpoints', 'decimalplacesquestion', 'quiz');
        $mform->setAdvanced('questiondecimalpoints', $quizconfig->questiondecimalpoints_adv);
        $mform->setDefault('questiondecimalpoints', $quizconfig->questiondecimalpoints);

                $mform->addElement('selectyesno', 'showblocks', get_string('showblocks', 'quiz'));
        $mform->addHelpButton('showblocks', 'showblocks', 'quiz');
        $mform->setAdvanced('showblocks', $quizconfig->showblocks_adv);
        $mform->setDefault('showblocks', $quizconfig->showblocks);

                $mform->addElement('header', 'security', get_string('extraattemptrestrictions', 'quiz'));

                $mform->addElement('passwordunmask', 'quizpassword', get_string('requirepassword', 'quiz'));
        $mform->setType('quizpassword', PARAM_TEXT);
        $mform->addHelpButton('quizpassword', 'requirepassword', 'quiz');
        $mform->setAdvanced('quizpassword', $quizconfig->password_adv);
        $mform->setDefault('quizpassword', $quizconfig->password);

                $mform->addElement('text', 'subnet', get_string('requiresubnet', 'quiz'));
        $mform->setType('subnet', PARAM_TEXT);
        $mform->addHelpButton('subnet', 'requiresubnet', 'quiz');
        $mform->setAdvanced('subnet', $quizconfig->subnet_adv);
        $mform->setDefault('subnet', $quizconfig->subnet);

                $mform->addElement('duration', 'delay1', get_string('delay1st2nd', 'quiz'),
                array('optional' => true));
        $mform->addHelpButton('delay1', 'delay1st2nd', 'quiz');
        $mform->setAdvanced('delay1', $quizconfig->delay1_adv);
        $mform->setDefault('delay1', $quizconfig->delay1);
        if ($this->get_max_attempts_for_any_override() < 2) {
            $mform->disabledIf('delay1', 'attempts', 'eq', 1);
        }

        $mform->addElement('duration', 'delay2', get_string('delaylater', 'quiz'),
                array('optional' => true));
        $mform->addHelpButton('delay2', 'delaylater', 'quiz');
        $mform->setAdvanced('delay2', $quizconfig->delay2_adv);
        $mform->setDefault('delay2', $quizconfig->delay2);
        if ($this->get_max_attempts_for_any_override() < 3) {
            $mform->disabledIf('delay2', 'attempts', 'eq', 1);
            $mform->disabledIf('delay2', 'attempts', 'eq', 2);
        }

                $mform->addElement('select', 'browsersecurity', get_string('browsersecurity', 'quiz'),
                quiz_access_manager::get_browser_security_choices());
        $mform->addHelpButton('browsersecurity', 'browsersecurity', 'quiz');
        $mform->setAdvanced('browsersecurity', $quizconfig->browsersecurity_adv);
        $mform->setDefault('browsersecurity', $quizconfig->browsersecurity);

                quiz_access_manager::add_settings_form_fields($this, $mform);

                $mform->addElement('header', 'overallfeedbackhdr', get_string('overallfeedback', 'quiz'));
        $mform->addHelpButton('overallfeedbackhdr', 'overallfeedback', 'quiz');

        if (isset($this->current->grade)) {
            $needwarning = $this->current->grade === 0;
        } else {
            $needwarning = $quizconfig->maximumgrade == 0;
        }
        if ($needwarning) {
            $mform->addElement('static', 'nogradewarning', '',
                    get_string('nogradewarning', 'quiz'));
        }

        $mform->addElement('static', 'gradeboundarystatic1',
                get_string('gradeboundary', 'quiz'), '100%');

        $repeatarray = array();
        $repeatedoptions = array();
        $repeatarray[] = $mform->createElement('editor', 'feedbacktext',
                get_string('feedback', 'quiz'), array('rows' => 3), array('maxfiles' => EDITOR_UNLIMITED_FILES,
                        'noclean' => true, 'context' => $this->context));
        $repeatarray[] = $mform->createElement('text', 'feedbackboundaries',
                get_string('gradeboundary', 'quiz'), array('size' => 10));
        $repeatedoptions['feedbacktext']['type'] = PARAM_RAW;
        $repeatedoptions['feedbackboundaries']['type'] = PARAM_RAW;

        if (!empty($this->_instance)) {
            $this->_feedbacks = $DB->get_records('quiz_feedback',
                    array('quizid' => $this->_instance), 'mingrade DESC');
            $numfeedbacks = count($this->_feedbacks);
        } else {
            $this->_feedbacks = array();
            $numfeedbacks = $quizconfig->initialnumfeedbacks;
        }
        $numfeedbacks = max($numfeedbacks, 1);

        $nextel = $this->repeat_elements($repeatarray, $numfeedbacks - 1,
                $repeatedoptions, 'boundary_repeats', 'boundary_add_fields', 3,
                get_string('addmoreoverallfeedbacks', 'quiz'), true);

                $mform->insertElementBefore($mform->createElement('editor',
                "feedbacktext[$nextel]", get_string('feedback', 'quiz'), array('rows' => 3),
                array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true,
                      'context' => $this->context)),
                'boundary_add_fields');
        $mform->insertElementBefore($mform->createElement('static',
                'gradeboundarystatic2', get_string('gradeboundary', 'quiz'), '0%'),
                'boundary_add_fields');

                        for ($i = 0; $i < $nextel; $i++) {
            $mform->disabledIf('feedbackboundaries[' . $i . ']', 'grade', 'eq', 0);
            $mform->disabledIf('feedbacktext[' . ($i + 1) . ']', 'grade', 'eq', 0);
        }

                $this->standard_coursemodule_elements();

                $mform->setAdvanced('modoutcomes', !empty($quizconfig->outcomes_adv));

                        $mform->setDefault('grade', $quizconfig->maximumgrade);

                $this->add_action_buttons();

        $PAGE->requires->yui_module('moodle-mod_quiz-modform', 'M.mod_quiz.modform.init');
    }

    protected function add_review_options_group($mform, $quizconfig, $whenname,
            $when, $withhelp = false) {
        global $OUTPUT;

        $group = array();
        foreach (self::$reviewfields as $field => $string) {
            list($identifier, $component) = $string;

            $label = get_string($identifier, $component);
            if ($withhelp) {
                $label .= ' ' . $OUTPUT->help_icon($identifier, $component);
            }

            $group[] = $mform->createElement('checkbox', $field . $whenname, '', $label);
        }
        $mform->addGroup($group, $whenname . 'optionsgrp',
                get_string('review' . $whenname, 'quiz'), null, false);

        foreach (self::$reviewfields as $field => $notused) {
            $cfgfield = 'review' . $field;
            if ($quizconfig->$cfgfield & $when) {
                $mform->setDefault($field . $whenname, 1);
            } else {
                $mform->setDefault($field . $whenname, 0);
            }
        }

        if ($whenname != 'during') {
            $mform->disabledIf('correctness' . $whenname, 'attempt' . $whenname);
            $mform->disabledIf('specificfeedback' . $whenname, 'attempt' . $whenname);
            $mform->disabledIf('generalfeedback' . $whenname, 'attempt' . $whenname);
            $mform->disabledIf('rightanswer' . $whenname, 'attempt' . $whenname);
        }
    }

    protected function preprocessing_review_settings(&$toform, $whenname, $when) {
        foreach (self::$reviewfields as $field => $notused) {
            $fieldname = 'review' . $field;
            if (array_key_exists($fieldname, $toform)) {
                $toform[$field . $whenname] = $toform[$fieldname] & $when;
            }
        }
    }

    public function data_preprocessing(&$toform) {
        if (isset($toform['grade'])) {
                        $toform['grade'] = $toform['grade'] + 0;
        }

        if (count($this->_feedbacks)) {
            $key = 0;
            foreach ($this->_feedbacks as $feedback) {
                $draftid = file_get_submitted_draft_itemid('feedbacktext['.$key.']');
                $toform['feedbacktext['.$key.']']['text'] = file_prepare_draft_area(
                    $draftid,                                   $this->context->id,                         'mod_quiz',                                 'feedback',                                 !empty($feedback->id) ? (int) $feedback->id : null,                     null,
                    $feedback->feedbacktext                 );
                $toform['feedbacktext['.$key.']']['format'] = $feedback->feedbacktextformat;
                $toform['feedbacktext['.$key.']']['itemid'] = $draftid;

                if ($toform['grade'] == 0) {
                                                                                                                        break;
                }

                if ($feedback->mingrade > 0) {
                    $toform['feedbackboundaries['.$key.']'] =
                            round(100.0 * $feedback->mingrade / $toform['grade'], 6) . '%';
                }
                $key++;
            }
        }

        if (isset($toform['timelimit'])) {
            $toform['timelimitenable'] = $toform['timelimit'] > 0;
        }

        $this->preprocessing_review_settings($toform, 'during',
                mod_quiz_display_options::DURING);
        $this->preprocessing_review_settings($toform, 'immediately',
                mod_quiz_display_options::IMMEDIATELY_AFTER);
        $this->preprocessing_review_settings($toform, 'open',
                mod_quiz_display_options::LATER_WHILE_OPEN);
        $this->preprocessing_review_settings($toform, 'closed',
                mod_quiz_display_options::AFTER_CLOSE);
        $toform['attemptduring'] = true;
        $toform['overallfeedbackduring'] = false;

                        if (isset($toform['password'])) {
            $toform['quizpassword'] = $toform['password'];
            unset($toform['password']);
        }

                if (!empty($toform['instance'])) {
            $accesssettings = quiz_access_manager::load_settings($toform['instance']);
            foreach ($accesssettings as $name => $value) {
                $toform[$name] = $value;
            }
        }
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

                if ($data['timeopen'] != 0 && $data['timeclose'] != 0 &&
                $data['timeclose'] < $data['timeopen']) {
            $errors['timeclose'] = get_string('closebeforeopen', 'quiz');
        }

                if ($data['overduehandling'] == 'graceperiod') {
            $graceperiodmin = get_config('quiz', 'graceperiodmin');
            if ($data['graceperiod'] <= $graceperiodmin) {
                $errors['graceperiod'] = get_string('graceperiodtoosmall', 'quiz', format_time($graceperiodmin));
            }
        }

        if (array_key_exists('completion', $data) && $data['completion'] == COMPLETION_TRACKING_AUTOMATIC) {
            $completionpass = isset($data['completionpass']) ? $data['completionpass'] : $this->current->completionpass;

                        if ($completionpass && (empty($data['gradepass']) || grade_floatval($data['gradepass']) == 0)) {
                if (isset($data['completionpass'])) {
                    $errors['completionpassgroup'] = get_string('gradetopassnotset', 'quiz');
                } else {
                    $errors['gradepass'] = get_string('gradetopassmustbeset', 'quiz');
                }
            }
        }

                $i = 0;
        while (!empty($data['feedbackboundaries'][$i] )) {
            $boundary = trim($data['feedbackboundaries'][$i]);
            if (strlen($boundary) > 0) {
                if ($boundary[strlen($boundary) - 1] == '%') {
                    $boundary = trim(substr($boundary, 0, -1));
                    if (is_numeric($boundary)) {
                        $boundary = $boundary * $data['grade'] / 100.0;
                    } else {
                        $errors["feedbackboundaries[$i]"] =
                                get_string('feedbackerrorboundaryformat', 'quiz', $i + 1);
                    }
                } else if (!is_numeric($boundary)) {
                    $errors["feedbackboundaries[$i]"] =
                            get_string('feedbackerrorboundaryformat', 'quiz', $i + 1);
                }
            }
            if (is_numeric($boundary) && $boundary <= 0 || $boundary >= $data['grade'] ) {
                $errors["feedbackboundaries[$i]"] =
                        get_string('feedbackerrorboundaryoutofrange', 'quiz', $i + 1);
            }
            if (is_numeric($boundary) && $i > 0 &&
                    $boundary >= $data['feedbackboundaries'][$i - 1]) {
                $errors["feedbackboundaries[$i]"] =
                        get_string('feedbackerrororder', 'quiz', $i + 1);
            }
            $data['feedbackboundaries'][$i] = $boundary;
            $i += 1;
        }
        $numboundaries = $i;

                if (!empty($data['feedbackboundaries'])) {
            for ($i = $numboundaries; $i < count($data['feedbackboundaries']); $i += 1) {
                if (!empty($data['feedbackboundaries'][$i] ) &&
                        trim($data['feedbackboundaries'][$i] ) != '') {
                    $errors["feedbackboundaries[$i]"] =
                            get_string('feedbackerrorjunkinboundary', 'quiz', $i + 1);
                }
            }
        }
        for ($i = $numboundaries + 1; $i < count($data['feedbacktext']); $i += 1) {
            if (!empty($data['feedbacktext'][$i]['text']) &&
                    trim($data['feedbacktext'][$i]['text'] ) != '') {
                $errors["feedbacktext[$i]"] =
                        get_string('feedbackerrorjunkinfeedback', 'quiz', $i + 1);
            }
        }

                $errors = quiz_access_manager::validate_settings_form_fields($errors, $data, $files, $this);

        return $errors;
    }

    
    public function add_completion_rules() {
        $mform = $this->_form;
        $items = array();

        $group = array();
        $group[] = $mform->createElement('advcheckbox', 'completionpass', null, get_string('completionpass', 'quiz'),
                array('group' => 'cpass'));

        $group[] = $mform->createElement('advcheckbox', 'completionattemptsexhausted', null,
                get_string('completionattemptsexhausted', 'quiz'),
                array('group' => 'cattempts'));
        $mform->disabledIf('completionattemptsexhausted', 'completionpass', 'notchecked');
        $mform->addGroup($group, 'completionpassgroup', get_string('completionpass', 'quiz'), ' &nbsp; ', false);
        $mform->addHelpButton('completionpassgroup', 'completionpass', 'quiz');
        $items[] = 'completionpassgroup';
        return $items;
    }

    
    public function completion_rule_enabled($data) {
        return !empty($data['completionattemptsexhausted']) || !empty($data['completionpass']);
    }

    
    public function get_max_attempts_for_any_override() {
        global $DB;

        if (empty($this->_instance)) {
                        return 1;
        }

        if ($this->maxattemptsanyoverride === null) {
            $this->maxattemptsanyoverride = $DB->get_field_sql("
                    SELECT MAX(CASE WHEN attempts = 0 THEN 1000 ELSE attempts END)
                      FROM {quiz_overrides}
                     WHERE quiz = ?",
                    array($this->_instance));
            if ($this->maxattemptsanyoverride < 1) {
                                $this->maxattemptsanyoverride = 1;
            }
        }

        return $this->maxattemptsanyoverride;
    }
}
