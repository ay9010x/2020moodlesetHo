<?php




defined('MOODLE_INTERNAL') || die();



class restore_quiz_activity_structure_step extends restore_questions_activity_structure_step {

    
    protected $sectioncreated = false;

    
    protected $legacyshufflequestionsoption = false;

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $quiz = new restore_path_element('quiz', '/activity/quiz');
        $paths[] = $quiz;

                $this->add_subplugin_structure('quizaccess', $quiz);

        $paths[] = new restore_path_element('quiz_question_instance',
                '/activity/quiz/question_instances/question_instance');
        $paths[] = new restore_path_element('quiz_section', '/activity/quiz/sections/section');
        $paths[] = new restore_path_element('quiz_feedback', '/activity/quiz/feedbacks/feedback');
        $paths[] = new restore_path_element('quiz_override', '/activity/quiz/overrides/override');

        if ($userinfo) {
            $paths[] = new restore_path_element('quiz_grade', '/activity/quiz/grades/grade');

            if ($this->task->get_old_moduleversion() > 2011010100) {
                                                $quizattempt = new restore_path_element('quiz_attempt',
                        '/activity/quiz/attempts/attempt');
                $paths[] = $quizattempt;

                                $this->add_question_usages($quizattempt, $paths);

                                $this->add_subplugin_structure('quizaccess', $quizattempt);

            } else {
                                                $quizattempt = new restore_path_element('quiz_attempt_legacy',
                        '/activity/quiz/attempts/attempt',
                        true);
                $paths[] = $quizattempt;
                $this->add_legacy_question_attempt_data($quizattempt, $paths);
            }
        }

                return $this->prepare_activity_structure($paths);
    }

    protected function process_quiz($data) {
        global $CFG, $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        if (property_exists($data, 'questions')) {
                        $this->oldquizlayout = $data->questions;
        }

                        if (isset($data->attempts_number)) {
            $data->attempts = $data->attempts_number;
            unset($data->attempts_number);
        }

                        if (!isset($data->preferredbehaviour)) {
            if (empty($data->optionflags)) {
                $data->preferredbehaviour = 'deferredfeedback';
            } else if (empty($data->penaltyscheme)) {
                $data->preferredbehaviour = 'adaptivenopenalty';
            } else {
                $data->preferredbehaviour = 'adaptive';
            }
            unset($data->optionflags);
            unset($data->penaltyscheme);
        }

                        if (isset($data->review)) {
            require_once($CFG->dirroot . '/mod/quiz/locallib.php');

            if (!defined('QUIZ_OLD_IMMEDIATELY')) {
                define('QUIZ_OLD_IMMEDIATELY', 0x3c003f);
                define('QUIZ_OLD_OPEN',        0x3c00fc0);
                define('QUIZ_OLD_CLOSED',      0x3c03f000);

                define('QUIZ_OLD_RESPONSES',        1*0x1041);
                define('QUIZ_OLD_SCORES',           2*0x1041);
                define('QUIZ_OLD_FEEDBACK',         4*0x1041);
                define('QUIZ_OLD_ANSWERS',          8*0x1041);
                define('QUIZ_OLD_SOLUTIONS',       16*0x1041);
                define('QUIZ_OLD_GENERALFEEDBACK', 32*0x1041);
                define('QUIZ_OLD_OVERALLFEEDBACK',  1*0x4440000);
            }

            $oldreview = $data->review;

            $data->reviewattempt =
                    mod_quiz_display_options::DURING |
                    ($oldreview & QUIZ_OLD_IMMEDIATELY & QUIZ_OLD_RESPONSES ?
                            mod_quiz_display_options::IMMEDIATELY_AFTER : 0) |
                    ($oldreview & QUIZ_OLD_OPEN & QUIZ_OLD_RESPONSES ?
                            mod_quiz_display_options::LATER_WHILE_OPEN : 0) |
                    ($oldreview & QUIZ_OLD_CLOSED & QUIZ_OLD_RESPONSES ?
                            mod_quiz_display_options::AFTER_CLOSE : 0);

            $data->reviewcorrectness =
                    mod_quiz_display_options::DURING |
                    ($oldreview & QUIZ_OLD_IMMEDIATELY & QUIZ_OLD_SCORES ?
                            mod_quiz_display_options::IMMEDIATELY_AFTER : 0) |
                    ($oldreview & QUIZ_OLD_OPEN & QUIZ_OLD_SCORES ?
                            mod_quiz_display_options::LATER_WHILE_OPEN : 0) |
                    ($oldreview & QUIZ_OLD_CLOSED & QUIZ_OLD_SCORES ?
                            mod_quiz_display_options::AFTER_CLOSE : 0);

            $data->reviewmarks =
                    mod_quiz_display_options::DURING |
                    ($oldreview & QUIZ_OLD_IMMEDIATELY & QUIZ_OLD_SCORES ?
                            mod_quiz_display_options::IMMEDIATELY_AFTER : 0) |
                    ($oldreview & QUIZ_OLD_OPEN & QUIZ_OLD_SCORES ?
                            mod_quiz_display_options::LATER_WHILE_OPEN : 0) |
                    ($oldreview & QUIZ_OLD_CLOSED & QUIZ_OLD_SCORES ?
                            mod_quiz_display_options::AFTER_CLOSE : 0);

            $data->reviewspecificfeedback =
                    ($oldreview & QUIZ_OLD_IMMEDIATELY & QUIZ_OLD_FEEDBACK ?
                            mod_quiz_display_options::DURING : 0) |
                    ($oldreview & QUIZ_OLD_IMMEDIATELY & QUIZ_OLD_FEEDBACK ?
                            mod_quiz_display_options::IMMEDIATELY_AFTER : 0) |
                    ($oldreview & QUIZ_OLD_OPEN & QUIZ_OLD_FEEDBACK ?
                            mod_quiz_display_options::LATER_WHILE_OPEN : 0) |
                    ($oldreview & QUIZ_OLD_CLOSED & QUIZ_OLD_FEEDBACK ?
                            mod_quiz_display_options::AFTER_CLOSE : 0);

            $data->reviewgeneralfeedback =
                    ($oldreview & QUIZ_OLD_IMMEDIATELY & QUIZ_OLD_GENERALFEEDBACK ?
                            mod_quiz_display_options::DURING : 0) |
                    ($oldreview & QUIZ_OLD_IMMEDIATELY & QUIZ_OLD_GENERALFEEDBACK ?
                            mod_quiz_display_options::IMMEDIATELY_AFTER : 0) |
                    ($oldreview & QUIZ_OLD_OPEN & QUIZ_OLD_GENERALFEEDBACK ?
                            mod_quiz_display_options::LATER_WHILE_OPEN : 0) |
                    ($oldreview & QUIZ_OLD_CLOSED & QUIZ_OLD_GENERALFEEDBACK ?
                            mod_quiz_display_options::AFTER_CLOSE : 0);

            $data->reviewrightanswer =
                    ($oldreview & QUIZ_OLD_IMMEDIATELY & QUIZ_OLD_ANSWERS ?
                            mod_quiz_display_options::DURING : 0) |
                    ($oldreview & QUIZ_OLD_IMMEDIATELY & QUIZ_OLD_ANSWERS ?
                            mod_quiz_display_options::IMMEDIATELY_AFTER : 0) |
                    ($oldreview & QUIZ_OLD_OPEN & QUIZ_OLD_ANSWERS ?
                            mod_quiz_display_options::LATER_WHILE_OPEN : 0) |
                    ($oldreview & QUIZ_OLD_CLOSED & QUIZ_OLD_ANSWERS ?
                            mod_quiz_display_options::AFTER_CLOSE : 0);

            $data->reviewoverallfeedback =
                    0 |
                    ($oldreview & QUIZ_OLD_IMMEDIATELY & QUIZ_OLD_OVERALLFEEDBACK ?
                            mod_quiz_display_options::IMMEDIATELY_AFTER : 0) |
                    ($oldreview & QUIZ_OLD_OPEN & QUIZ_OLD_OVERALLFEEDBACK ?
                            mod_quiz_display_options::LATER_WHILE_OPEN : 0) |
                    ($oldreview & QUIZ_OLD_CLOSED & QUIZ_OLD_OVERALLFEEDBACK ?
                            mod_quiz_display_options::AFTER_CLOSE : 0);
        }

                        if (!isset($data->browsersecurity)) {
            if (empty($data->popup)) {
                $data->browsersecurity = '-';
            } else if ($data->popup == 1) {
                $data->browsersecurity = 'securewindow';
            } else if ($data->popup == 2) {
                $data->browsersecurity = 'safebrowser';
            } else {
                $data->preferredbehaviour = '-';
            }
            unset($data->popup);
        }

        if (!isset($data->overduehandling)) {
            $data->overduehandling = get_config('quiz', 'overduehandling');
        }

                        $this->legacyshufflequestionsoption = !empty($data->shufflequestions);

                $newitemid = $DB->insert_record('quiz', $data);
                $this->apply_activity_instance($newitemid);
    }

    protected function process_quiz_question_instance($data) {
        global $DB;

        $data = (object)$data;

                if (!isset($data->questionid) && isset($data->question)) {
            $data->questionid = $data->question;
        }
        if (!isset($data->maxmark) && isset($data->grade)) {
            $data->maxmark = $data->grade;
        }

        if (!property_exists($data, 'slot')) {
            $page = 1;
            $slot = 1;
            foreach (explode(',', $this->oldquizlayout) as $item) {
                if ($item == 0) {
                    $page += 1;
                    continue;
                }
                if ($item == $data->questionid) {
                    $data->slot = $slot;
                    $data->page = $page;
                    break;
                }
                $slot += 1;
            }
        }

        if (!property_exists($data, 'slot')) {
                                    $this->log('question ' . $data->questionid . ' was associated with quiz ' .
                    $this->get_new_parentid('quiz') . ' but not actually used. ' .
                    'The instance has been ignored.', backup::LOG_INFO);
            return;
        }

        $data->quizid = $this->get_new_parentid('quiz');
        $data->questionid = $this->get_mappingid('question', $data->questionid);

        $DB->insert_record('quiz_slots', $data);
    }

    protected function process_quiz_section($data) {
        global $DB;

        $data = (object) $data;
        $data->quizid = $this->get_new_parentid('quiz');
        $newitemid = $DB->insert_record('quiz_sections', $data);
        $this->sectioncreated = true;
    }

    protected function process_quiz_feedback($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->quizid = $this->get_new_parentid('quiz');

        $newitemid = $DB->insert_record('quiz_feedback', $data);
        $this->set_mapping('quiz_feedback', $oldid, $newitemid, true);     }

    protected function process_quiz_override($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

                $userinfo = $this->get_setting_value('userinfo');

                if (!$userinfo && !is_null($data->userid)) {
            return;
        }

        $data->quiz = $this->get_new_parentid('quiz');

        if ($data->userid !== null) {
            $data->userid = $this->get_mappingid('user', $data->userid);
        }

        if ($data->groupid !== null) {
            $data->groupid = $this->get_mappingid('group', $data->groupid);
        }

        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);

        $newitemid = $DB->insert_record('quiz_overrides', $data);

                $this->set_mapping('quiz_override', $oldid, $newitemid);
    }

    protected function process_quiz_grade($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->quiz = $this->get_new_parentid('quiz');

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->grade = $data->gradeval;

        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $DB->insert_record('quiz_grades', $data);
    }

    protected function process_quiz_attempt($data) {
        $data = (object)$data;

        $data->quiz = $this->get_new_parentid('quiz');
        $data->attempt = $data->attemptnum;

        $data->userid = $this->get_mappingid('user', $data->userid);

        $data->timestart = $this->apply_date_offset($data->timestart);
        $data->timefinish = $this->apply_date_offset($data->timefinish);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        if (!empty($data->timecheckstate)) {
            $data->timecheckstate = $this->apply_date_offset($data->timecheckstate);
        } else {
            $data->timecheckstate = 0;
        }

                if (!isset($data->state)) {
            if ($data->timefinish > 0) {
                $data->state = 'finished';
            } else {
                $data->state = 'inprogress';
            }
        }

                $this->currentquizattempt = clone($data);
    }

    protected function process_quiz_attempt_legacy($data) {
        global $DB;

        $this->process_quiz_attempt($data);

        $quiz = $DB->get_record('quiz', array('id' => $this->get_new_parentid('quiz')));
        $quiz->oldquestions = $this->oldquizlayout;
        $this->process_legacy_quiz_attempt_data($data, $quiz);
    }

    protected function inform_new_usage_id($newusageid) {
        global $DB;

        $data = $this->currentquizattempt;

        $oldid = $data->id;
        $data->uniqueid = $newusageid;

        $newitemid = $DB->insert_record('quiz_attempts', $data);

                $this->set_mapping('quiz_attempt', $oldid, $newitemid, false);
    }

    protected function after_execute() {
        global $DB;

        parent::after_execute();
                $this->add_related_files('mod_quiz', 'intro', null);
                $this->add_related_files('mod_quiz', 'feedback', 'quiz_feedback');

        if (!$this->sectioncreated) {
            $DB->insert_record('quiz_sections', array(
                    'quizid' => $this->get_new_parentid('quiz'),
                    'firstslot' => 1, 'heading' => '',
                    'shufflequestions' => $this->legacyshufflequestionsoption));
        }
    }
}
