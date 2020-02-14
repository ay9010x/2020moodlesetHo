<?php



defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');


class mod_quiz_external extends external_api {

    
    public static function get_quizzes_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    
    public static function get_quizzes_by_courses($courseids = array()) {
        global $USER;

        $warnings = array();
        $returnedquizzes = array();

        $params = array(
            'courseids' => $courseids,
        );
        $params = self::validate_parameters(self::get_quizzes_by_courses_parameters(), $params);

        $mycourses = array();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

                if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

                                    $quizzes = get_all_instances_in_courses("quiz", $courses);
            foreach ($quizzes as $quiz) {
                $context = context_module::instance($quiz->coursemodule);

                                $quiz = quiz_update_effective_access($quiz, $USER->id);

                                $quizdetails = array();
                                $quizdetails['id'] = $quiz->id;
                $quizdetails['coursemodule']      = $quiz->coursemodule;
                $quizdetails['course']            = $quiz->course;
                $quizdetails['name']              = external_format_string($quiz->name, $context->id);

                if (has_capability('mod/quiz:view', $context)) {
                                        list($quizdetails['intro'], $quizdetails['introformat']) = external_format_text($quiz->intro,
                                                                    $quiz->introformat, $context->id, 'mod_quiz', 'intro', null);

                    $viewablefields = array('timeopen', 'timeclose', 'grademethod', 'section', 'visible', 'groupmode',
                                            'groupingid');

                    $timenow = time();
                    $quizobj = quiz::create($quiz->id, $USER->id);
                    $accessmanager = new quiz_access_manager($quizobj, $timenow, has_capability('mod/quiz:ignoretimelimits',
                                                                $context, null, false));

                                        if (!$accessmanager->prevent_access()) {
                                                $hasfeedback = quiz_has_feedback($quiz);
                        $quizdetails['hasfeedback'] = (!empty($hasfeedback)) ? 1 : 0;
                        $quizdetails['hasquestions'] = (int) $quizobj->has_questions();
                        $quizdetails['autosaveperiod'] = get_config('quiz', 'autosaveperiod');

                        $additionalfields = array('timelimit', 'attempts', 'attemptonlast', 'grademethod', 'decimalpoints',
                                                    'questiondecimalpoints', 'reviewattempt', 'reviewcorrectness', 'reviewmarks',
                                                    'reviewspecificfeedback', 'reviewgeneralfeedback', 'reviewrightanswer',
                                                    'reviewoverallfeedback', 'questionsperpage', 'navmethod', 'sumgrades', 'grade',
                                                    'browsersecurity', 'delay1', 'delay2', 'showuserpicture', 'showblocks',
                                                    'completionattemptsexhausted', 'completionpass', 'overduehandling',
                                                    'graceperiod', 'preferredbehaviour', 'canredoquestions');
                        $viewablefields = array_merge($viewablefields, $additionalfields);
                    }

                                        if (has_capability('moodle/course:manageactivities', $context)) {
                        $additionalfields = array('shuffleanswers', 'timecreated', 'timemodified', 'password', 'subnet');
                        $viewablefields = array_merge($viewablefields, $additionalfields);
                    }

                    foreach ($viewablefields as $field) {
                        $quizdetails[$field] = $quiz->{$field};
                    }
                }
                $returnedquizzes[] = $quizdetails;
            }
        }
        $result = array();
        $result['quizzes'] = $returnedquizzes;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_quizzes_by_courses_returns() {
        return new external_single_structure(
            array(
                'quizzes' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Standard Moodle primary key.'),
                            'course' => new external_value(PARAM_INT, 'Foreign key reference to the course this quiz is part of.'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module id.'),
                            'name' => new external_value(PARAM_RAW, 'Quiz name.'),
                            'intro' => new external_value(PARAM_RAW, 'Quiz introduction text.', VALUE_OPTIONAL),
                            'introformat' => new external_format_value('intro', VALUE_OPTIONAL),
                            'timeopen' => new external_value(PARAM_INT, 'The time when this quiz opens. (0 = no restriction.)',
                                                                VALUE_OPTIONAL),
                            'timeclose' => new external_value(PARAM_INT, 'The time when this quiz closes. (0 = no restriction.)',
                                                                VALUE_OPTIONAL),
                            'timelimit' => new external_value(PARAM_INT, 'The time limit for quiz attempts, in seconds.',
                                                                VALUE_OPTIONAL),
                            'overduehandling' => new external_value(PARAM_ALPHA, 'The method used to handle overdue attempts.
                                                                    \'autosubmit\', \'graceperiod\' or \'autoabandon\'.',
                                                                    VALUE_OPTIONAL),
                            'graceperiod' => new external_value(PARAM_INT, 'The amount of time (in seconds) after the time limit
                                                                runs out during which attempts can still be submitted,
                                                                if overduehandling is set to allow it.', VALUE_OPTIONAL),
                            'preferredbehaviour' => new external_value(PARAM_ALPHANUMEXT, 'The behaviour to ask questions to use.',
                                                                        VALUE_OPTIONAL),
                            'canredoquestions' => new external_value(PARAM_INT, 'Allows students to redo any completed question
                                                                        within a quiz attempt.', VALUE_OPTIONAL),
                            'attempts' => new external_value(PARAM_INT, 'The maximum number of attempts a student is allowed.',
                                                                VALUE_OPTIONAL),
                            'attemptonlast' => new external_value(PARAM_INT, 'Whether subsequent attempts start from the answer
                                                                    to the previous attempt (1) or start blank (0).',
                                                                    VALUE_OPTIONAL),
                            'grademethod' => new external_value(PARAM_INT, 'One of the values QUIZ_GRADEHIGHEST, QUIZ_GRADEAVERAGE,
                                                                    QUIZ_ATTEMPTFIRST or QUIZ_ATTEMPTLAST.', VALUE_OPTIONAL),
                            'decimalpoints' => new external_value(PARAM_INT, 'Number of decimal points to use when displaying
                                                                    grades.', VALUE_OPTIONAL),
                            'questiondecimalpoints' => new external_value(PARAM_INT, 'Number of decimal points to use when
                                                                            displaying question grades.
                                                                            (-1 means use decimalpoints.)', VALUE_OPTIONAL),
                            'reviewattempt' => new external_value(PARAM_INT, 'Whether users are allowed to review their quiz
                                                                    attempts at various times. This is a bit field, decoded by the
                                                                    mod_quiz_display_options class. It is formed by ORing together
                                                                    the constants defined there.', VALUE_OPTIONAL),
                            'reviewcorrectness' => new external_value(PARAM_INT, 'Whether users are allowed to review their quiz
                                                                        attempts at various times.
                                                                        A bit field, like reviewattempt.', VALUE_OPTIONAL),
                            'reviewmarks' => new external_value(PARAM_INT, 'Whether users are allowed to review their quiz attempts
                                                                at various times. A bit field, like reviewattempt.',
                                                                VALUE_OPTIONAL),
                            'reviewspecificfeedback' => new external_value(PARAM_INT, 'Whether users are allowed to review their
                                                                            quiz attempts at various times. A bit field, like
                                                                            reviewattempt.', VALUE_OPTIONAL),
                            'reviewgeneralfeedback' => new external_value(PARAM_INT, 'Whether users are allowed to review their
                                                                            quiz attempts at various times. A bit field, like
                                                                            reviewattempt.', VALUE_OPTIONAL),
                            'reviewrightanswer' => new external_value(PARAM_INT, 'Whether users are allowed to review their quiz
                                                                        attempts at various times. A bit field, like
                                                                        reviewattempt.', VALUE_OPTIONAL),
                            'reviewoverallfeedback' => new external_value(PARAM_INT, 'Whether users are allowed to review their quiz
                                                                            attempts at various times. A bit field, like
                                                                            reviewattempt.', VALUE_OPTIONAL),
                            'questionsperpage' => new external_value(PARAM_INT, 'How often to insert a page break when editing
                                                                        the quiz, or when shuffling the question order.',
                                                                        VALUE_OPTIONAL),
                            'navmethod' => new external_value(PARAM_ALPHA, 'Any constraints on how the user is allowed to navigate
                                                                around the quiz. Currently recognised values are
                                                                \'free\' and \'seq\'.', VALUE_OPTIONAL),
                            'shuffleanswers' => new external_value(PARAM_INT, 'Whether the parts of the question should be shuffled,
                                                                    in those question types that support it.', VALUE_OPTIONAL),
                            'sumgrades' => new external_value(PARAM_FLOAT, 'The total of all the question instance maxmarks.',
                                                                VALUE_OPTIONAL),
                            'grade' => new external_value(PARAM_FLOAT, 'The total that the quiz overall grade is scaled to be
                                                            out of.', VALUE_OPTIONAL),
                            'timecreated' => new external_value(PARAM_INT, 'The time when the quiz was added to the course.',
                                                                VALUE_OPTIONAL),
                            'timemodified' => new external_value(PARAM_INT, 'Last modified time.',
                                                                    VALUE_OPTIONAL),
                            'password' => new external_value(PARAM_RAW, 'A password that the student must enter before starting or
                                                                continuing a quiz attempt.', VALUE_OPTIONAL),
                            'subnet' => new external_value(PARAM_RAW, 'Used to restrict the IP addresses from which this quiz can
                                                            be attempted. The format is as requried by the address_in_subnet
                                                            function.', VALUE_OPTIONAL),
                            'browsersecurity' => new external_value(PARAM_ALPHANUMEXT, 'Restriciton on the browser the student must
                                                                    use. E.g. \'securewindow\'.', VALUE_OPTIONAL),
                            'delay1' => new external_value(PARAM_INT, 'Delay that must be left between the first and second attempt,
                                                            in seconds.', VALUE_OPTIONAL),
                            'delay2' => new external_value(PARAM_INT, 'Delay that must be left between the second and subsequent
                                                            attempt, in seconds.', VALUE_OPTIONAL),
                            'showuserpicture' => new external_value(PARAM_INT, 'Option to show the user\'s picture during the
                                                                    attempt and on the review page.', VALUE_OPTIONAL),
                            'showblocks' => new external_value(PARAM_INT, 'Whether blocks should be shown on the attempt.php and
                                                                review.php pages.', VALUE_OPTIONAL),
                            'completionattemptsexhausted' => new external_value(PARAM_INT, 'Mark quiz complete when the student has
                                                                                exhausted the maximum number of attempts',
                                                                                VALUE_OPTIONAL),
                            'completionpass' => new external_value(PARAM_INT, 'Whether to require passing grade', VALUE_OPTIONAL),
                            'autosaveperiod' => new external_value(PARAM_INT, 'Auto-save delay', VALUE_OPTIONAL),
                            'hasfeedback' => new external_value(PARAM_INT, 'Whether the quiz has any non-blank feedback text',
                                                                VALUE_OPTIONAL),
                            'hasquestions' => new external_value(PARAM_INT, 'Whether the quiz has questions', VALUE_OPTIONAL),
                            'section' => new external_value(PARAM_INT, 'Course section id', VALUE_OPTIONAL),
                            'visible' => new external_value(PARAM_INT, 'Module visibility', VALUE_OPTIONAL),
                            'groupmode' => new external_value(PARAM_INT, 'Group mode', VALUE_OPTIONAL),
                            'groupingid' => new external_value(PARAM_INT, 'Grouping id', VALUE_OPTIONAL),
                        )
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }


    
    protected static function validate_quiz($quizid) {
        global $DB;

                $quiz = $DB->get_record('quiz', array('id' => $quizid), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($quiz, 'quiz');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        return array($quiz, $course, $cm, $context);
    }

    
    public static function view_quiz_parameters() {
        return new external_function_parameters (
            array(
                'quizid' => new external_value(PARAM_INT, 'quiz instance id'),
            )
        );
    }

    
    public static function view_quiz($quizid) {
        global $DB;

        $params = self::validate_parameters(self::view_quiz_parameters(), array('quizid' => $quizid));
        $warnings = array();

        list($quiz, $course, $cm, $context) = self::validate_quiz($params['quizid']);

                quiz_view($quiz, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function view_quiz_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function get_user_attempts_parameters() {
        return new external_function_parameters (
            array(
                'quizid' => new external_value(PARAM_INT, 'quiz instance id'),
                'userid' => new external_value(PARAM_INT, 'user id, empty for current user', VALUE_DEFAULT, 0),
                'status' => new external_value(PARAM_ALPHA, 'quiz status: all, finished or unfinished', VALUE_DEFAULT, 'finished'),
                'includepreviews' => new external_value(PARAM_BOOL, 'whether to include previews or not', VALUE_DEFAULT, false),

            )
        );
    }

    
    public static function get_user_attempts($quizid, $userid = 0, $status = 'finished', $includepreviews = false) {
        global $DB, $USER;

        $warnings = array();

        $params = array(
            'quizid' => $quizid,
            'userid' => $userid,
            'status' => $status,
            'includepreviews' => $includepreviews,
        );
        $params = self::validate_parameters(self::get_user_attempts_parameters(), $params);

        list($quiz, $course, $cm, $context) = self::validate_quiz($params['quizid']);

        if (!in_array($params['status'], array('all', 'finished', 'unfinished'))) {
            throw new invalid_parameter_exception('Invalid status value');
        }

                if (empty($params['userid'])) {
            $params['userid'] = $USER->id;
        }

        $user = core_user::get_user($params['userid'], '*', MUST_EXIST);
        core_user::require_active_user($user);

                if ($USER->id != $user->id) {
            require_capability('mod/quiz:viewreports', $context);
        }

        $attempts = quiz_get_user_attempts($quiz->id, $user->id, $params['status'], $params['includepreviews']);

        $result = array();
        $result['attempts'] = $attempts;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    private static function attempt_structure() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Attempt id.', VALUE_OPTIONAL),
                'quiz' => new external_value(PARAM_INT, 'Foreign key reference to the quiz that was attempted.',
                                                VALUE_OPTIONAL),
                'userid' => new external_value(PARAM_INT, 'Foreign key reference to the user whose attempt this is.',
                                                VALUE_OPTIONAL),
                'attempt' => new external_value(PARAM_INT, 'Sequentially numbers this students attempts at this quiz.',
                                                VALUE_OPTIONAL),
                'uniqueid' => new external_value(PARAM_INT, 'Foreign key reference to the question_usage that holds the
                                                    details of the the question_attempts that make up this quiz
                                                    attempt.', VALUE_OPTIONAL),
                'layout' => new external_value(PARAM_RAW, 'Attempt layout.', VALUE_OPTIONAL),
                'currentpage' => new external_value(PARAM_INT, 'Attempt current page.', VALUE_OPTIONAL),
                'preview' => new external_value(PARAM_INT, 'Whether is a preview attempt or not.', VALUE_OPTIONAL),
                'state' => new external_value(PARAM_ALPHA, 'The current state of the attempts. \'inprogress\',
                                                \'overdue\', \'finished\' or \'abandoned\'.', VALUE_OPTIONAL),
                'timestart' => new external_value(PARAM_INT, 'Time when the attempt was started.', VALUE_OPTIONAL),
                'timefinish' => new external_value(PARAM_INT, 'Time when the attempt was submitted.
                                                    0 if the attempt has not been submitted yet.', VALUE_OPTIONAL),
                'timemodified' => new external_value(PARAM_INT, 'Last modified time.', VALUE_OPTIONAL),
                'timecheckstate' => new external_value(PARAM_INT, 'Next time quiz cron should check attempt for
                                                        state changes.  NULL means never check.', VALUE_OPTIONAL),
                'sumgrades' => new external_value(PARAM_FLOAT, 'Total marks for this attempt.', VALUE_OPTIONAL),
            )
        );
    }

    
    public static function get_user_attempts_returns() {
        return new external_single_structure(
            array(
                'attempts' => new external_multiple_structure(self::attempt_structure()),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function get_user_best_grade_parameters() {
        return new external_function_parameters (
            array(
                'quizid' => new external_value(PARAM_INT, 'quiz instance id'),
                'userid' => new external_value(PARAM_INT, 'user id', VALUE_DEFAULT, 0),
            )
        );
    }

    
    public static function get_user_best_grade($quizid, $userid = 0) {
        global $DB, $USER;

        $warnings = array();

        $params = array(
            'quizid' => $quizid,
            'userid' => $userid,
        );
        $params = self::validate_parameters(self::get_user_best_grade_parameters(), $params);

        list($quiz, $course, $cm, $context) = self::validate_quiz($params['quizid']);

                if (empty($params['userid'])) {
            $params['userid'] = $USER->id;
        }

        $user = core_user::get_user($params['userid'], '*', MUST_EXIST);
        core_user::require_active_user($user);

                if ($USER->id != $user->id) {
            require_capability('mod/quiz:viewreports', $context);
        }

        $result = array();
        $grade = quiz_get_best_grade($quiz, $user->id);

        if ($grade === null) {
            $result['hasgrade'] = false;
        } else {
            $result['hasgrade'] = true;
            $result['grade'] = $grade;
        }
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_user_best_grade_returns() {
        return new external_single_structure(
            array(
                'hasgrade' => new external_value(PARAM_BOOL, 'Whether the user has a grade on the given quiz.'),
                'grade' => new external_value(PARAM_FLOAT, 'The grade (only if the user has a grade).', VALUE_OPTIONAL),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function get_combined_review_options_parameters() {
        return new external_function_parameters (
            array(
                'quizid' => new external_value(PARAM_INT, 'quiz instance id'),
                'userid' => new external_value(PARAM_INT, 'user id (empty for current user)', VALUE_DEFAULT, 0),

            )
        );
    }

    
    public static function get_combined_review_options($quizid, $userid = 0) {
        global $DB, $USER;

        $warnings = array();

        $params = array(
            'quizid' => $quizid,
            'userid' => $userid,
        );
        $params = self::validate_parameters(self::get_combined_review_options_parameters(), $params);

        list($quiz, $course, $cm, $context) = self::validate_quiz($params['quizid']);

                if (empty($params['userid'])) {
            $params['userid'] = $USER->id;
        }

        $user = core_user::get_user($params['userid'], '*', MUST_EXIST);
        core_user::require_active_user($user);

                if ($USER->id != $user->id) {
            require_capability('mod/quiz:viewreports', $context);
        }

        $attempts = quiz_get_user_attempts($quiz->id, $user->id, 'all', true);

        $result = array();
        $result['someoptions'] = [];
        $result['alloptions'] = [];

        list($someoptions, $alloptions) = quiz_get_combined_reviewoptions($quiz, $attempts);

        foreach (array('someoptions', 'alloptions') as $typeofoption) {
            foreach ($$typeofoption as $key => $value) {
                $result[$typeofoption][] = array(
                    "name" => $key,
                    "value" => (!empty($value)) ? $value : 0
                );
            }
        }

        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_combined_review_options_returns() {
        return new external_single_structure(
            array(
                'someoptions' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_ALPHANUMEXT, 'option name'),
                            'value' => new external_value(PARAM_INT, 'option value'),
                        )
                    )
                ),
                'alloptions' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_ALPHANUMEXT, 'option name'),
                            'value' => new external_value(PARAM_INT, 'option value'),
                        )
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function start_attempt_parameters() {
        return new external_function_parameters (
            array(
                'quizid' => new external_value(PARAM_INT, 'quiz instance id'),
                'preflightdata' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_ALPHANUMEXT, 'data name'),
                            'value' => new external_value(PARAM_RAW, 'data value'),
                        )
                    ), 'Preflight required data (like passwords)', VALUE_DEFAULT, array()
                ),
                'forcenew' => new external_value(PARAM_BOOL, 'Whether to force a new attempt or not.', VALUE_DEFAULT, false),

            )
        );
    }

    
    public static function start_attempt($quizid, $preflightdata = array(), $forcenew = false) {
        global $DB, $USER;

        $warnings = array();
        $attempt = array();

        $params = array(
            'quizid' => $quizid,
            'preflightdata' => $preflightdata,
            'forcenew' => $forcenew,
        );
        $params = self::validate_parameters(self::start_attempt_parameters(), $params);
        $forcenew = $params['forcenew'];

        list($quiz, $course, $cm, $context) = self::validate_quiz($params['quizid']);

        $quizobj = quiz::create($cm->instance, $USER->id);

                if (!$quizobj->has_questions()) {
            throw new moodle_quiz_exception($quizobj, 'noquestionsfound');
        }

                $timenow = time();
        $accessmanager = $quizobj->get_access_manager($timenow);

                list($currentattemptid, $attemptnumber, $lastattempt, $messages, $page) =
            quiz_validate_new_attempt($quizobj, $accessmanager, $forcenew, -1, false);

                if (!$quizobj->is_preview_user() && $messages) {
                        foreach ($messages as $message) {
                $warnings[] = array(
                    'item' => 'quiz',
                    'itemid' => $quiz->id,
                    'warningcode' => '1',
                    'message' => clean_text($message, PARAM_TEXT)
                );
            }
        } else {
            if ($accessmanager->is_preflight_check_required($currentattemptid)) {
                
                $provideddata = array();
                foreach ($params['preflightdata'] as $data) {
                    $provideddata[$data['name']] = $data['value'];
                }

                $errors = $accessmanager->validate_preflight_check($provideddata, [], $currentattemptid);

                if (!empty($errors)) {
                    throw new moodle_quiz_exception($quizobj, array_shift($errors));
                }

                                $accessmanager->notify_preflight_check_passed($currentattemptid);
            }

            if ($currentattemptid) {
                if ($lastattempt->state == quiz_attempt::OVERDUE) {
                    throw new moodle_quiz_exception($quizobj, 'stateoverdue');
                } else {
                    throw new moodle_quiz_exception($quizobj, 'attemptstillinprogress');
                }
            }
            $attempt = quiz_prepare_and_start_new_attempt($quizobj, $attemptnumber, $lastattempt);
        }

        $result = array();
        $result['attempt'] = $attempt;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function start_attempt_returns() {
        return new external_single_structure(
            array(
                'attempt' => self::attempt_structure(),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    protected static function validate_attempt($params, $checkaccessrules = true, $failifoverdue = true) {
        global $USER;

        $attemptobj = quiz_attempt::create($params['attemptid']);

        $context = context_module::instance($attemptobj->get_cm()->id);
        self::validate_context($context);

                if ($attemptobj->get_userid() != $USER->id) {
            throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'notyourattempt');
        }

                $ispreviewuser = $attemptobj->is_preview_user();
        if (!$ispreviewuser) {
            $attemptobj->require_capability('mod/quiz:attempt');
        }

                $accessmanager = $attemptobj->get_access_manager(time());
        $messages = array();
        if ($checkaccessrules) {
                        $attemptobj->handle_if_time_expired(time(), true);

            $messages = $accessmanager->prevent_access();
            if (!$ispreviewuser && $messages) {
                throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'attempterror');
            }
        }

                if ($attemptobj->is_finished()) {
            throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'attemptalreadyclosed');
        } else if ($failifoverdue && $attemptobj->get_state() == quiz_attempt::OVERDUE) {
            throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'stateoverdue');
        }

                if ($accessmanager->is_preflight_check_required($attemptobj->get_attemptid())) {
            $provideddata = array();
            foreach ($params['preflightdata'] as $data) {
                $provideddata[$data['name']] = $data['value'];
            }

            $errors = $accessmanager->validate_preflight_check($provideddata, [], $params['attemptid']);
            if (!empty($errors)) {
                throw new moodle_quiz_exception($attemptobj->get_quizobj(), array_shift($errors));
            }
                        $accessmanager->notify_preflight_check_passed($params['attemptid']);
        }

        if (isset($params['page'])) {
                        if ($params['page'] != $attemptobj->force_page_number_into_range($params['page'])) {
                throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'Invalid page number');
            }

                        if (!$attemptobj->check_page_access($params['page'])) {
                throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'Out of sequence access');
            }

                        $slots = $attemptobj->get_slots($params['page']);

            if (empty($slots)) {
                throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'noquestionsfound');
            }
        }

        return array($attemptobj, $messages);
    }

    
    private static function question_structure() {
        return new external_single_structure(
            array(
                'slot' => new external_value(PARAM_INT, 'slot number'),
                'type' => new external_value(PARAM_ALPHANUMEXT, 'question type, i.e: multichoice'),
                'page' => new external_value(PARAM_INT, 'page of the quiz this question appears on'),
                'html' => new external_value(PARAM_RAW, 'the question rendered'),
                'flagged' => new external_value(PARAM_BOOL, 'whether the question is flagged or not'),
                'number' => new external_value(PARAM_INT, 'question ordering number in the quiz', VALUE_OPTIONAL),
                'state' => new external_value(PARAM_ALPHA, 'the state where the question is in', VALUE_OPTIONAL),
                'status' => new external_value(PARAM_RAW, 'current formatted state of the question', VALUE_OPTIONAL),
                'mark' => new external_value(PARAM_RAW, 'the mark awarded', VALUE_OPTIONAL),
                'maxmark' => new external_value(PARAM_FLOAT, 'the maximum mark possible for this question attempt', VALUE_OPTIONAL),
            )
        );
    }

    
    private static function get_attempt_questions_data(quiz_attempt $attemptobj, $review, $page = 'all') {
        global $PAGE;

        $questions = array();
        $contextid = $attemptobj->get_quizobj()->get_context()->id;
        $displayoptions = $attemptobj->get_display_options($review);
        $renderer = $PAGE->get_renderer('mod_quiz');

        foreach ($attemptobj->get_slots($page) as $slot) {

            $question = array(
                'slot' => $slot,
                'type' => $attemptobj->get_question_type_name($slot),
                'page' => $attemptobj->get_question_page($slot),
                'flagged' => $attemptobj->is_question_flagged($slot),
                'html' => $attemptobj->render_question($slot, $review, $renderer) . $PAGE->requires->get_end_code()
            );

            if ($attemptobj->is_real_question($slot)) {
                $question['number'] = $attemptobj->get_question_number($slot);
                $question['state'] = (string) $attemptobj->get_question_state($slot);
                $question['status'] = $attemptobj->get_question_status($slot, $displayoptions->correctness);
            }
            if ($displayoptions->marks >= question_display_options::MAX_ONLY) {
                $question['maxmark'] = $attemptobj->get_question_attempt($slot)->get_max_mark();
            }
            if ($displayoptions->marks >= question_display_options::MARK_AND_MAX) {
                $question['mark'] = $attemptobj->get_question_mark($slot);
            }

            $questions[] = $question;
        }
        return $questions;
    }

    
    public static function get_attempt_data_parameters() {
        return new external_function_parameters (
            array(
                'attemptid' => new external_value(PARAM_INT, 'attempt id'),
                'page' => new external_value(PARAM_INT, 'page number'),
                'preflightdata' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_ALPHANUMEXT, 'data name'),
                            'value' => new external_value(PARAM_RAW, 'data value'),
                        )
                    ), 'Preflight required data (like passwords)', VALUE_DEFAULT, array()
                )
            )
        );
    }

    
    public static function get_attempt_data($attemptid, $page, $preflightdata = array()) {

        $warnings = array();

        $params = array(
            'attemptid' => $attemptid,
            'page' => $page,
            'preflightdata' => $preflightdata,
        );
        $params = self::validate_parameters(self::get_attempt_data_parameters(), $params);

        list($attemptobj, $messages) = self::validate_attempt($params);

        if ($attemptobj->is_last_page($params['page'])) {
            $nextpage = -1;
        } else {
            $nextpage = $params['page'] + 1;
        }

        $result = array();
        $result['attempt'] = $attemptobj->get_attempt();
        $result['messages'] = $messages;
        $result['nextpage'] = $nextpage;
        $result['warnings'] = $warnings;
        $result['questions'] = self::get_attempt_questions_data($attemptobj, false, $params['page']);

        return $result;
    }

    
    public static function get_attempt_data_returns() {
        return new external_single_structure(
            array(
                'attempt' => self::attempt_structure(),
                'messages' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'access message'),
                    'access messages, will only be returned for users with mod/quiz:preview capability,
                    for other users this method will throw an exception if there are messages'),
                'nextpage' => new external_value(PARAM_INT, 'next page number'),
                'questions' => new external_multiple_structure(self::question_structure()),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function get_attempt_summary_parameters() {
        return new external_function_parameters (
            array(
                'attemptid' => new external_value(PARAM_INT, 'attempt id'),
                'preflightdata' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_ALPHANUMEXT, 'data name'),
                            'value' => new external_value(PARAM_RAW, 'data value'),
                        )
                    ), 'Preflight required data (like passwords)', VALUE_DEFAULT, array()
                )
            )
        );
    }

    
    public static function get_attempt_summary($attemptid, $preflightdata = array()) {

        $warnings = array();

        $params = array(
            'attemptid' => $attemptid,
            'preflightdata' => $preflightdata,
        );
        $params = self::validate_parameters(self::get_attempt_summary_parameters(), $params);

        list($attemptobj, $messages) = self::validate_attempt($params, true, false);

        $result = array();
        $result['warnings'] = $warnings;
        $result['questions'] = self::get_attempt_questions_data($attemptobj, false, 'all');

        return $result;
    }

    
    public static function get_attempt_summary_returns() {
        return new external_single_structure(
            array(
                'questions' => new external_multiple_structure(self::question_structure()),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function save_attempt_parameters() {
        return new external_function_parameters (
            array(
                'attemptid' => new external_value(PARAM_INT, 'attempt id'),
                'data' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_RAW, 'data name'),
                            'value' => new external_value(PARAM_RAW, 'data value'),
                        )
                    ), 'the data to be saved'
                ),
                'preflightdata' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_ALPHANUMEXT, 'data name'),
                            'value' => new external_value(PARAM_RAW, 'data value'),
                        )
                    ), 'Preflight required data (like passwords)', VALUE_DEFAULT, array()
                )
            )
        );
    }

    
    public static function save_attempt($attemptid, $data, $preflightdata = array()) {
        global $DB;

        $warnings = array();

        $params = array(
            'attemptid' => $attemptid,
            'data' => $data,
            'preflightdata' => $preflightdata,
        );
        $params = self::validate_parameters(self::save_attempt_parameters(), $params);

                list($attemptobj, $messages) = self::validate_attempt($params);

        $transaction = $DB->start_delegated_transaction();
                $_POST = array();
        foreach ($data as $element) {
            $_POST[$element['name']] = $element['value'];
        }
        $timenow = time();
        $attemptobj->process_auto_save($timenow);
        $transaction->allow_commit();

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function save_attempt_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function process_attempt_parameters() {
        return new external_function_parameters (
            array(
                'attemptid' => new external_value(PARAM_INT, 'attempt id'),
                'data' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_RAW, 'data name'),
                            'value' => new external_value(PARAM_RAW, 'data value'),
                        )
                    ),
                    'the data to be saved', VALUE_DEFAULT, array()
                ),
                'finishattempt' => new external_value(PARAM_BOOL, 'whether to finish or not the attempt', VALUE_DEFAULT, false),
                'timeup' => new external_value(PARAM_BOOL, 'whether the WS was called by a timer when the time is up',
                                                VALUE_DEFAULT, false),
                'preflightdata' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_ALPHANUMEXT, 'data name'),
                            'value' => new external_value(PARAM_RAW, 'data value'),
                        )
                    ), 'Preflight required data (like passwords)', VALUE_DEFAULT, array()
                )
            )
        );
    }

    
    public static function process_attempt($attemptid, $data, $finishattempt = false, $timeup = false, $preflightdata = array()) {

        $warnings = array();

        $params = array(
            'attemptid' => $attemptid,
            'data' => $data,
            'finishattempt' => $finishattempt,
            'timeup' => $timeup,
            'preflightdata' => $preflightdata,
        );
        $params = self::validate_parameters(self::process_attempt_parameters(), $params);

                list($attemptobj, $messages) = self::validate_attempt($params, false);

                $_POST = array();
        foreach ($params['data'] as $element) {
            $_POST[$element['name']] = $element['value'];
        }
        $timenow = time();
        $finishattempt = $params['finishattempt'];
        $timeup = $params['timeup'];

        $result = array();
        $result['state'] = $attemptobj->process_attempt($timenow, $finishattempt, $timeup, 0);
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function process_attempt_returns() {
        return new external_single_structure(
            array(
                'state' => new external_value(PARAM_ALPHANUMEXT, 'state: the new attempt state:
                                                                    inprogress, finished, overdue, abandoned'),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    protected static function validate_attempt_review($params) {

        $attemptobj = quiz_attempt::create($params['attemptid']);
        $attemptobj->check_review_capability();

        $displayoptions = $attemptobj->get_display_options(true);
        if ($attemptobj->is_own_attempt()) {
            if (!$attemptobj->is_finished()) {
                throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'attemptclosed');
            } else if (!$displayoptions->attempt) {
                throw new moodle_exception($attemptobj->cannot_review_message());
            }
        } else if (!$attemptobj->is_review_allowed()) {
            throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'noreviewattempt');
        }
        return array($attemptobj, $displayoptions);
    }

    
    public static function get_attempt_review_parameters() {
        return new external_function_parameters (
            array(
                'attemptid' => new external_value(PARAM_INT, 'attempt id'),
                'page' => new external_value(PARAM_INT, 'page number, empty for all the questions in all the pages',
                                                VALUE_DEFAULT, -1),
            )
        );
    }

    
    public static function get_attempt_review($attemptid, $page = -1) {
        global $PAGE;

        $warnings = array();

        $params = array(
            'attemptid' => $attemptid,
            'page' => $page,
        );
        $params = self::validate_parameters(self::get_attempt_review_parameters(), $params);

        list($attemptobj, $displayoptions) = self::validate_attempt_review($params);

        if ($params['page'] !== -1) {
            $page = $attemptobj->force_page_number_into_range($params['page']);
        } else {
            $page = 'all';
        }

                $result = array();
        $result['attempt'] = $attemptobj->get_attempt();
        $result['questions'] = self::get_attempt_questions_data($attemptobj, true, $page, true);

        $result['additionaldata'] = array();
                $summarydata = $attemptobj->get_additional_summary_data($displayoptions);
        foreach ($summarydata as $key => $data) {
                        $result['additionaldata'][] = array(
                'id' => $key,
                'title' => $data['title'], $attemptobj->get_quizobj()->get_context()->id,
                'content' => $data['content'],
            );
        }

                $grade = quiz_rescale_grade($attemptobj->get_attempt()->sumgrades, $attemptobj->get_quiz(), false);

        $feedback = $attemptobj->get_overall_feedback($grade);
        if ($displayoptions->overallfeedback && $feedback) {
            $result['additionaldata'][] = array(
                'id' => 'feedback',
                'title' => get_string('feedback', 'quiz'),
                'content' => $feedback,
            );
        }

        $result['grade'] = $grade;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_attempt_review_returns() {
        return new external_single_structure(
            array(
                'grade' => new external_value(PARAM_RAW, 'grade for the quiz (or empty or "notyetgraded")'),
                'attempt' => self::attempt_structure(),
                'additionaldata' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_ALPHANUMEXT, 'id of the data'),
                            'title' => new external_value(PARAM_TEXT, 'data title'),
                            'content' => new external_value(PARAM_RAW, 'data content'),
                        )
                    )
                ),
                'questions' => new external_multiple_structure(self::question_structure()),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function view_attempt_parameters() {
        return new external_function_parameters (
            array(
                'attemptid' => new external_value(PARAM_INT, 'attempt id'),
                'page' => new external_value(PARAM_INT, 'page number'),
                'preflightdata' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_ALPHANUMEXT, 'data name'),
                            'value' => new external_value(PARAM_RAW, 'data value'),
                        )
                    ), 'Preflight required data (like passwords)', VALUE_DEFAULT, array()
                )
            )
        );
    }

    
    public static function view_attempt($attemptid, $page, $preflightdata = array()) {

        $warnings = array();

        $params = array(
            'attemptid' => $attemptid,
            'page' => $page,
            'preflightdata' => $preflightdata,
        );
        $params = self::validate_parameters(self::view_attempt_parameters(), $params);
        list($attemptobj, $messages) = self::validate_attempt($params);

                $attemptobj->fire_attempt_viewed_event();

                if (!$attemptobj->set_currentpage($params['page'])) {
            throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'Out of sequence access');
        }

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function view_attempt_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function view_attempt_summary_parameters() {
        return new external_function_parameters (
            array(
                'attemptid' => new external_value(PARAM_INT, 'attempt id'),
                'preflightdata' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_ALPHANUMEXT, 'data name'),
                            'value' => new external_value(PARAM_RAW, 'data value'),
                        )
                    ), 'Preflight required data (like passwords)', VALUE_DEFAULT, array()
                )
            )
        );
    }

    
    public static function view_attempt_summary($attemptid, $preflightdata = array()) {

        $warnings = array();

        $params = array(
            'attemptid' => $attemptid,
            'preflightdata' => $preflightdata,
        );
        $params = self::validate_parameters(self::view_attempt_summary_parameters(), $params);
        list($attemptobj, $messages) = self::validate_attempt($params);

                $attemptobj->fire_attempt_summary_viewed_event();

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function view_attempt_summary_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function view_attempt_review_parameters() {
        return new external_function_parameters (
            array(
                'attemptid' => new external_value(PARAM_INT, 'attempt id'),
            )
        );
    }

    
    public static function view_attempt_review($attemptid) {

        $warnings = array();

        $params = array(
            'attemptid' => $attemptid,
        );
        $params = self::validate_parameters(self::view_attempt_review_parameters(), $params);
        list($attemptobj, $displayoptions) = self::validate_attempt_review($params);

                $attemptobj->fire_attempt_reviewed_event();

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function view_attempt_review_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function get_quiz_feedback_for_grade_parameters() {
        return new external_function_parameters (
            array(
                'quizid' => new external_value(PARAM_INT, 'quiz instance id'),
                'grade' => new external_value(PARAM_FLOAT, 'the grade to check'),
            )
        );
    }

    
    public static function get_quiz_feedback_for_grade($quizid, $grade) {
        global $DB;

        $params = array(
            'quizid' => $quizid,
            'grade' => $grade,
        );
        $params = self::validate_parameters(self::get_quiz_feedback_for_grade_parameters(), $params);
        $warnings = array();

        list($quiz, $course, $cm, $context) = self::validate_quiz($params['quizid']);

        $result = array();
        $result['feedbacktext'] = '';
        $result['feedbacktextformat'] = FORMAT_MOODLE;

        $feedback = quiz_feedback_record_for_grade($params['grade'], $quiz);
        if (!empty($feedback->feedbacktext)) {
            list($text, $format) = external_format_text($feedback->feedbacktext, $feedback->feedbacktextformat, $context->id,
                                                        'mod_quiz', 'feedback', $feedback->id);
            $result['feedbacktext'] = $text;
            $result['feedbacktextformat'] = $format;
        }

        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_quiz_feedback_for_grade_returns() {
        return new external_single_structure(
            array(
                'feedbacktext' => new external_value(PARAM_RAW, 'the comment that corresponds to this grade (empty for none)'),
                'feedbacktextformat' => new external_format_value('feedbacktext', VALUE_OPTIONAL),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function get_quiz_access_information_parameters() {
        return new external_function_parameters (
            array(
                'quizid' => new external_value(PARAM_INT, 'quiz instance id')
            )
        );
    }

    
    public static function get_quiz_access_information($quizid) {
        global $DB, $USER;

        $warnings = array();

        $params = array(
            'quizid' => $quizid
        );
        $params = self::validate_parameters(self::get_quiz_access_information_parameters(), $params);

        list($quiz, $course, $cm, $context) = self::validate_quiz($params['quizid']);

        $result = array();
                $result['canattempt'] = has_capability('mod/quiz:attempt', $context);;
        $result['canmanage'] = has_capability('mod/quiz:manage', $context);;
        $result['canpreview'] = has_capability('mod/quiz:preview', $context);;
        $result['canreviewmyattempts'] = has_capability('mod/quiz:reviewmyattempts', $context);;
        $result['canviewreports'] = has_capability('mod/quiz:viewreports', $context);;

                $quizobj = quiz::create($cm->instance, $USER->id);
        $ignoretimelimits = has_capability('mod/quiz:ignoretimelimits', $context, null, false);
        $timenow = time();
        $accessmanager = new quiz_access_manager($quizobj, $timenow, $ignoretimelimits);

        $result['accessrules'] = $accessmanager->describe_rules();
        $result['activerulenames'] = $accessmanager->get_active_rule_names();
        $result['preventaccessreasons'] = $accessmanager->prevent_access();

        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_quiz_access_information_returns() {
        return new external_single_structure(
            array(
                'canattempt' => new external_value(PARAM_BOOL, 'Whether the user can do the quiz or not.'),
                'canmanage' => new external_value(PARAM_BOOL, 'Whether the user can edit the quiz settings or not.'),
                'canpreview' => new external_value(PARAM_BOOL, 'Whether the user can preview the quiz or not.'),
                'canreviewmyattempts' => new external_value(PARAM_BOOL, 'Whether the users can review their previous attempts
                                                                or not.'),
                'canviewreports' => new external_value(PARAM_BOOL, 'Whether the user can view the quiz reports or not.'),
                'accessrules' => new external_multiple_structure(
                                    new external_value(PARAM_TEXT, 'rule description'), 'list of rules'),
                'activerulenames' => new external_multiple_structure(
                                    new external_value(PARAM_PLUGIN, 'rule plugin names'), 'list of active rules'),
                'preventaccessreasons' => new external_multiple_structure(
                                            new external_value(PARAM_TEXT, 'access restriction description'), 'list of reasons'),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function get_attempt_access_information_parameters() {
        return new external_function_parameters (
            array(
                'quizid' => new external_value(PARAM_INT, 'quiz instance id'),
                'attemptid' => new external_value(PARAM_INT, 'attempt id, 0 for the user last attempt if exists', VALUE_DEFAULT, 0),
            )
        );
    }

    
    public static function get_attempt_access_information($quizid, $attemptid = 0) {
        global $DB, $USER;

        $warnings = array();

        $params = array(
            'quizid' => $quizid,
            'attemptid' => $attemptid,
        );
        $params = self::validate_parameters(self::get_attempt_access_information_parameters(), $params);

        list($quiz, $course, $cm, $context) = self::validate_quiz($params['quizid']);

        $attempttocheck = 0;
        if (!empty($params['attemptid'])) {
            $attemptobj = quiz_attempt::create($params['attemptid']);
            if ($attemptobj->get_userid() != $USER->id) {
                throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'notyourattempt');
            }
            $attempttocheck = $attemptobj->get_attempt();
        }

                $quizobj = quiz::create($cm->instance, $USER->id);
        $ignoretimelimits = has_capability('mod/quiz:ignoretimelimits', $context, null, false);
        $timenow = time();
        $accessmanager = new quiz_access_manager($quizobj, $timenow, $ignoretimelimits);

        $attempts = quiz_get_user_attempts($quiz->id, $USER->id, 'finished', true);
        $lastfinishedattempt = end($attempts);
        if ($unfinishedattempt = quiz_get_user_attempt_unfinished($quiz->id, $USER->id)) {
            $attempts[] = $unfinishedattempt;

                        $quizobj->create_attempt_object($unfinishedattempt)->handle_if_time_expired(time(), false);

            if ($unfinishedattempt->state != quiz_attempt::IN_PROGRESS and $unfinishedattempt->state != quiz_attempt::OVERDUE) {
                $lastfinishedattempt = $unfinishedattempt;
            }
        }
        $numattempts = count($attempts);

        if (!$attempttocheck) {
            $attempttocheck = $unfinishedattempt ? $unfinishedattempt : $lastfinishedattempt;
        }

        $result = array();
        $result['isfinished'] = $accessmanager->is_finished($numattempts, $lastfinishedattempt);
        $result['preventnewattemptreasons'] = $accessmanager->prevent_new_attempt($numattempts, $lastfinishedattempt);

        if ($attempttocheck) {
            $endtime = $accessmanager->get_end_time($attempttocheck);
            $result['endtime'] = ($endtime === false) ? 0 : $endtime;
            $attemptid = $unfinishedattempt ? $unfinishedattempt->id : null;
            $result['ispreflightcheckrequired'] = $accessmanager->is_preflight_check_required($attemptid);
        }

        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_attempt_access_information_returns() {
        return new external_single_structure(
            array(
                'endtime' => new external_value(PARAM_INT, 'When the attempt must be submitted (determined by rules).',
                                                VALUE_OPTIONAL),
                'isfinished' => new external_value(PARAM_BOOL, 'Whether there is no way the user will ever be allowed to attempt.'),
                'ispreflightcheckrequired' => new external_value(PARAM_BOOL, 'whether a check is required before the user
                                                                    starts/continues his attempt.', VALUE_OPTIONAL),
                'preventnewattemptreasons' => new external_multiple_structure(
                                                new external_value(PARAM_TEXT, 'access restriction description'),
                                                                    'list of reasons'),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function get_quiz_required_qtypes_parameters() {
        return new external_function_parameters (
            array(
                'quizid' => new external_value(PARAM_INT, 'quiz instance id')
            )
        );
    }

    
    public static function get_quiz_required_qtypes($quizid) {
        global $DB, $USER;

        $warnings = array();

        $params = array(
            'quizid' => $quizid
        );
        $params = self::validate_parameters(self::get_quiz_required_qtypes_parameters(), $params);

        list($quiz, $course, $cm, $context) = self::validate_quiz($params['quizid']);

        $quizobj = quiz::create($cm->instance, $USER->id);
        $quizobj->preload_questions();
        $quizobj->load_questions();

                $result = array();
        $result['questiontypes'] = $quizobj->get_all_question_types_used(true);
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_quiz_required_qtypes_returns() {
        return new external_single_structure(
            array(
                'questiontypes' => new external_multiple_structure(
                                    new external_value(PARAM_PLUGIN, 'question type'), 'list of question types used in the quiz'),
                'warnings' => new external_warnings(),
            )
        );
    }

}
