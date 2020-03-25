<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->dirroot . '/mod/quiz/accessmanager.php');
require_once($CFG->dirroot . '/mod/quiz/accessmanager_form.php');
require_once($CFG->dirroot . '/mod/quiz/renderer.php');
require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/eventslib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/questionlib.php');



define('QUIZ_SHOW_TIME_BEFORE_DEADLINE', '3600');


define('QUIZ_MIN_TIME_TO_CONTINUE', '2');


define('QUIZ_SHOWIMAGE_NONE', 0);


define('QUIZ_SHOWIMAGE_SMALL', 1);


define('QUIZ_SHOWIMAGE_LARGE', 2);




function quiz_create_attempt(quiz $quizobj, $attemptnumber, $lastattempt, $timenow, $ispreview = false, $userid = null) {
    global $USER;

    if ($userid === null) {
        $userid = $USER->id;
    }

    $quiz = $quizobj->get_quiz();
    if ($quiz->sumgrades < 0.000005 && $quiz->grade > 0.000005) {
        throw new moodle_exception('cannotstartgradesmismatch', 'quiz',
                new moodle_url('/mod/quiz/view.php', array('q' => $quiz->id)),
                    array('grade' => quiz_format_grade($quiz, $quiz->grade)));
    }

    if ($attemptnumber == 1 || !$quiz->attemptonlast) {
                $attempt = new stdClass();
        $attempt->quiz = $quiz->id;
        $attempt->userid = $userid;
        $attempt->preview = 0;
        $attempt->layout = '';
    } else {
                if (empty($lastattempt)) {
            print_error('cannotfindprevattempt', 'quiz');
        }
        $attempt = $lastattempt;
    }

    $attempt->attempt = $attemptnumber;
    $attempt->timestart = $timenow;
    $attempt->timefinish = 0;
    $attempt->timemodified = $timenow;
    $attempt->state = quiz_attempt::IN_PROGRESS;
    $attempt->currentpage = 0;
    $attempt->sumgrades = null;

        if ($ispreview) {
        $attempt->preview = 1;
    }

    $timeclose = $quizobj->get_access_manager($timenow)->get_end_time($attempt);
    if ($timeclose === false || $ispreview) {
        $attempt->timecheckstate = null;
    } else {
        $attempt->timecheckstate = $timeclose;
    }

    return $attempt;
}

function quiz_start_new_attempt($quizobj, $quba, $attempt, $attemptnumber, $timenow,
                                $questionids = array(), $forcedvariantsbyslot = array()) {

        $qubaids = new \mod_quiz\question\qubaids_for_users_attempts(
            $quizobj->get_quizid(), $attempt->userid);

        $quizobj->preload_questions();
    $quizobj->load_questions();

        $randomfound = false;
    $slot = 0;
    $questions = array();
    $maxmark = array();
    $page = array();
    foreach ($quizobj->get_questions() as $questiondata) {
        $slot += 1;
        $maxmark[$slot] = $questiondata->maxmark;
        $page[$slot] = $questiondata->page;
        if ($questiondata->qtype == 'random') {
            $randomfound = true;
            continue;
        }
        if (!$quizobj->get_quiz()->shuffleanswers) {
            $questiondata->options->shuffleanswers = false;
        }
        $questions[$slot] = question_bank::make_question($questiondata);
    }

        if ($randomfound) {
        $slot = 0;
        $usedquestionids = array();
        foreach ($questions as $question) {
            if (isset($usedquestions[$question->id])) {
                $usedquestionids[$question->id] += 1;
            } else {
                $usedquestionids[$question->id] = 1;
            }
        }
        $randomloader = new \core_question\bank\random_question_loader($qubaids, $usedquestionids);

        foreach ($quizobj->get_questions() as $questiondata) {
            $slot += 1;
            if ($questiondata->qtype != 'random') {
                continue;
            }

                        if (isset($questionids[$quba->next_slot_number()])) {
                if ($randomloader->is_question_available($questiondata->category,
                        (bool) $questiondata->questiontext, $questionids[$quba->next_slot_number()])) {
                    $questions[$slot] = question_bank::load_question(
                            $questionids[$quba->next_slot_number()], $quizobj->get_quiz()->shuffleanswers);
                    continue;
                } else {
                    throw new coding_exception('Forced question id not available.');
                }
            }

                        $questionid = $randomloader->get_next_question_id($questiondata->category,
                        (bool) $questiondata->questiontext);
            if ($questionid === null) {
                throw new moodle_exception('notenoughrandomquestions', 'quiz',
                                           $quizobj->view_url(), $questiondata);
            }

            $questions[$slot] = question_bank::load_question($questionid,
                    $quizobj->get_quiz()->shuffleanswers);
        }
    }

        ksort($questions);
    foreach ($questions as $slot => $question) {
        $newslot = $quba->add_question($question, $maxmark[$slot]);
        if ($newslot != $slot) {
            throw new coding_exception('Slot numbers have got confused.');
        }
    }

        $variantstrategy = new core_question\engine\variants\least_used_strategy($quba, $qubaids);

    if (!empty($forcedvariantsbyslot)) {
        $forcedvariantsbyseed = question_variant_forced_choices_selection_strategy::prepare_forced_choices_array(
            $forcedvariantsbyslot, $quba);
        $variantstrategy = new question_variant_forced_choices_selection_strategy(
            $forcedvariantsbyseed, $variantstrategy);
    }

    $quba->start_all_questions($variantstrategy, $timenow);

        $sections = $quizobj->get_sections();
    foreach ($sections as $i => $section) {
        if (isset($sections[$i + 1])) {
            $sections[$i]->lastslot = $sections[$i + 1]->firstslot - 1;
        } else {
            $sections[$i]->lastslot = count($questions);
        }
    }

    $layout = array();
    foreach ($sections as $section) {
        if ($section->shufflequestions) {
            $questionsinthissection = array();
            for ($slot = $section->firstslot; $slot <= $section->lastslot; $slot += 1) {
                $questionsinthissection[] = $slot;
            }
            shuffle($questionsinthissection);
            $questionsonthispage = 0;
            foreach ($questionsinthissection as $slot) {
                if ($questionsonthispage && $questionsonthispage == $quizobj->get_quiz()->questionsperpage) {
                    $layout[] = 0;
                    $questionsonthispage = 0;
                }
                $layout[] = $slot;
                $questionsonthispage += 1;
            }

        } else {
            $currentpage = $page[$section->firstslot];
            for ($slot = $section->firstslot; $slot <= $section->lastslot; $slot += 1) {
                if ($currentpage !== null && $page[$slot] != $currentpage) {
                    $layout[] = 0;
                }
                $layout[] = $slot;
                $currentpage = $page[$slot];
            }
        }

                $layout[] = 0;
    }
    $attempt->layout = implode(',', $layout);

    return $attempt;
}


function quiz_start_attempt_built_on_last($quba, $attempt, $lastattempt) {
    $oldquba = question_engine::load_questions_usage_by_activity($lastattempt->uniqueid);

    $oldnumberstonew = array();
    foreach ($oldquba->get_attempt_iterator() as $oldslot => $oldqa) {
        $newslot = $quba->add_question($oldqa->get_question(), $oldqa->get_max_mark());

        $quba->start_question_based_on($newslot, $oldqa);

        $oldnumberstonew[$oldslot] = $newslot;
    }

        $newlayout = array();
    foreach (explode(',', $lastattempt->layout) as $oldslot) {
        if ($oldslot != 0) {
            $newlayout[] = $oldnumberstonew[$oldslot];
        } else {
            $newlayout[] = 0;
        }
    }
    $attempt->layout = implode(',', $newlayout);
    return $attempt;
}


function quiz_attempt_save_started($quizobj, $quba, $attempt) {
    global $DB;
        question_engine::save_questions_usage_by_activity($quba);
    $attempt->uniqueid = $quba->get_id();
    $attempt->id = $DB->insert_record('quiz_attempts', $attempt);

        $params = array(
        'objectid' => $attempt->id,
        'relateduserid' => $attempt->userid,
        'courseid' => $quizobj->get_courseid(),
        'context' => $quizobj->get_context()
    );
        if ($attempt->preview) {
        $params['other'] = array(
            'quizid' => $quizobj->get_quizid()
        );
        $event = \mod_quiz\event\attempt_preview_started::create($params);
    } else {
        $event = \mod_quiz\event\attempt_started::create($params);

    }

        $event->add_record_snapshot('quiz', $quizobj->get_quiz());
    $event->add_record_snapshot('quiz_attempts', $attempt);
    $event->trigger();

    return $attempt;
}


function quiz_get_user_attempt_unfinished($quizid, $userid) {
    $attempts = quiz_get_user_attempts($quizid, $userid, 'unfinished', true);
    if ($attempts) {
        return array_shift($attempts);
    } else {
        return false;
    }
}


function quiz_delete_attempt($attempt, $quiz) {
    global $DB;
    if (is_numeric($attempt)) {
        if (!$attempt = $DB->get_record('quiz_attempts', array('id' => $attempt))) {
            return;
        }
    }

    if ($attempt->quiz != $quiz->id) {
        debugging("Trying to delete attempt $attempt->id which belongs to quiz $attempt->quiz " .
                "but was passed quiz $quiz->id.");
        return;
    }

    if (!isset($quiz->cmid)) {
        $cm = get_coursemodule_from_instance('quiz', $quiz->id, $quiz->course);
        $quiz->cmid = $cm->id;
    }

    question_engine::delete_questions_usage_by_activity($attempt->uniqueid);
    $DB->delete_records('quiz_attempts', array('id' => $attempt->id));

        if (!$attempt->preview) {
        $params = array(
            'objectid' => $attempt->id,
            'relateduserid' => $attempt->userid,
            'context' => context_module::instance($quiz->cmid),
            'other' => array(
                'quizid' => $quiz->id
            )
        );
        $event = \mod_quiz\event\attempt_deleted::create($params);
        $event->add_record_snapshot('quiz_attempts', $attempt);
        $event->trigger();
    }

                $userid = $attempt->userid;
    if (!$DB->record_exists('quiz_attempts', array('userid' => $userid, 'quiz' => $quiz->id))) {
        $DB->delete_records('quiz_grades', array('userid' => $userid, 'quiz' => $quiz->id));
    } else {
        quiz_save_best_grade($quiz, $userid);
    }

    quiz_update_grades($quiz, $userid);
}


function quiz_delete_previews($quiz, $userid = null) {
    global $DB;
    $conditions = array('quiz' => $quiz->id, 'preview' => 1);
    if (!empty($userid)) {
        $conditions['userid'] = $userid;
    }
    $previewattempts = $DB->get_records('quiz_attempts', $conditions);
    foreach ($previewattempts as $attempt) {
        quiz_delete_attempt($attempt, $quiz);
    }
}


function quiz_has_attempts($quizid) {
    global $DB;
    return $DB->record_exists('quiz_attempts', array('quiz' => $quizid, 'preview' => 0));
}



function quiz_repaginate_questions($quizid, $slotsperpage) {
    global $DB;
    $trans = $DB->start_delegated_transaction();

    $sections = $DB->get_records('quiz_sections', array('quizid' => $quizid), 'firstslot ASC');
    $firstslots = array();
    foreach ($sections as $section) {
        if ((int)$section->firstslot === 1) {
            continue;
        }
        $firstslots[] = $section->firstslot;
    }

    $slots = $DB->get_records('quiz_slots', array('quizid' => $quizid),
            'slot');
    $currentpage = 1;
    $slotsonthispage = 0;
    foreach ($slots as $slot) {
        if (($firstslots && in_array($slot->slot, $firstslots)) ||
            ($slotsonthispage && $slotsonthispage == $slotsperpage)) {
            $currentpage += 1;
            $slotsonthispage = 0;
        }
        if ($slot->page != $currentpage) {
            $DB->set_field('quiz_slots', 'page', $currentpage, array('id' => $slot->id));
        }
        $slotsonthispage += 1;
    }

    $trans->allow_commit();
}



function quiz_rescale_grade($rawgrade, $quiz, $format = true) {
    if (is_null($rawgrade)) {
        $grade = null;
    } else if ($quiz->sumgrades >= 0.000005) {
        $grade = $rawgrade * $quiz->grade / $quiz->sumgrades;
    } else {
        $grade = 0;
    }
    if ($format === 'question') {
        $grade = quiz_format_question_grade($quiz, $grade);
    } else if ($format) {
        $grade = quiz_format_grade($quiz, $grade);
    }
    return $grade;
}


function quiz_feedback_record_for_grade($grade, $quiz) {
    global $DB;

            $grade = max($grade, 0);

    $feedback = $DB->get_record_select('quiz_feedback',
            'quizid = ? AND mingrade <= ? AND ? < maxgrade', array($quiz->id, $grade, $grade));

    return $feedback;
}


function quiz_feedback_for_grade($grade, $quiz, $context) {

    if (is_null($grade)) {
        return '';
    }

    $feedback = quiz_feedback_record_for_grade($grade, $quiz);

    if (empty($feedback->feedbacktext)) {
        return '';
    }

        $formatoptions = new stdClass();
    $formatoptions->noclean = true;
    $feedbacktext = file_rewrite_pluginfile_urls($feedback->feedbacktext, 'pluginfile.php',
            $context->id, 'mod_quiz', 'feedback', $feedback->id);
    $feedbacktext = format_text($feedbacktext, $feedback->feedbacktextformat, $formatoptions);

    return $feedbacktext;
}


function quiz_has_feedback($quiz) {
    global $DB;
    static $cache = array();
    if (!array_key_exists($quiz->id, $cache)) {
        $cache[$quiz->id] = quiz_has_grades($quiz) &&
                $DB->record_exists_select('quiz_feedback', "quizid = ? AND " .
                    $DB->sql_isnotempty('quiz_feedback', 'feedbacktext', false, true),
                array($quiz->id));
    }
    return $cache[$quiz->id];
}


function quiz_update_sumgrades($quiz) {
    global $DB;

    $sql = 'UPDATE {quiz}
            SET sumgrades = COALESCE((
                SELECT SUM(maxmark)
                FROM {quiz_slots}
                WHERE quizid = {quiz}.id
            ), 0)
            WHERE id = ?';
    $DB->execute($sql, array($quiz->id));
    $quiz->sumgrades = $DB->get_field('quiz', 'sumgrades', array('id' => $quiz->id));

    if ($quiz->sumgrades < 0.000005 && quiz_has_attempts($quiz->id)) {
                                quiz_set_grade(0, $quiz);
    }
}


function quiz_update_all_attempt_sumgrades($quiz) {
    global $DB;
    $dm = new question_engine_data_mapper();
    $timenow = time();

    $sql = "UPDATE {quiz_attempts}
            SET
                timemodified = :timenow,
                sumgrades = (
                    {$dm->sum_usage_marks_subquery('uniqueid')}
                )
            WHERE quiz = :quizid AND state = :finishedstate";
    $DB->execute($sql, array('timenow' => $timenow, 'quizid' => $quiz->id,
            'finishedstate' => quiz_attempt::FINISHED));
}


function quiz_set_grade($newgrade, $quiz) {
    global $DB;
        if (abs($quiz->grade - $newgrade) < 1e-7) {
                return true;
    }

    $oldgrade = $quiz->grade;
    $quiz->grade = $newgrade;

        $transaction = $DB->start_delegated_transaction();

        $DB->set_field('quiz', 'grade', $newgrade, array('id' => $quiz->instance));

    if ($oldgrade < 1) {
                        quiz_update_all_final_grades($quiz);

    } else {
                $timemodified = time();
        $DB->execute("
                UPDATE {quiz_grades}
                SET grade = ? * grade, timemodified = ?
                WHERE quiz = ?
        ", array($newgrade/$oldgrade, $timemodified, $quiz->id));
    }

    if ($oldgrade > 1e-7) {
                $factor = $newgrade/$oldgrade;
        $DB->execute("
                UPDATE {quiz_feedback}
                SET mingrade = ? * mingrade, maxgrade = ? * maxgrade
                WHERE quizid = ?
        ", array($factor, $factor, $quiz->id));
    }

        quiz_grade_item_update($quiz);
    quiz_update_grades($quiz);

    $transaction->allow_commit();
    return true;
}


function quiz_save_best_grade($quiz, $userid = null, $attempts = array()) {
    global $DB, $OUTPUT, $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    if (!$attempts) {
                $attempts = quiz_get_user_attempts($quiz->id, $userid);
    }

        $bestgrade = quiz_calculate_best_grade($quiz, $attempts);
    $bestgrade = quiz_rescale_grade($bestgrade, $quiz, false);

        if (is_null($bestgrade)) {
        $DB->delete_records('quiz_grades', array('quiz' => $quiz->id, 'userid' => $userid));

    } else if ($grade = $DB->get_record('quiz_grades',
            array('quiz' => $quiz->id, 'userid' => $userid))) {
        $grade->grade = $bestgrade;
        $grade->timemodified = time();
        $DB->update_record('quiz_grades', $grade);

    } else {
        $grade = new stdClass();
        $grade->quiz = $quiz->id;
        $grade->userid = $userid;
        $grade->grade = $bestgrade;
        $grade->timemodified = time();
        $DB->insert_record('quiz_grades', $grade);
    }

    quiz_update_grades($quiz, $userid);
}


function quiz_calculate_best_grade($quiz, $attempts) {

    switch ($quiz->grademethod) {

        case QUIZ_ATTEMPTFIRST:
            $firstattempt = reset($attempts);
            return $firstattempt->sumgrades;

        case QUIZ_ATTEMPTLAST:
            $lastattempt = end($attempts);
            return $lastattempt->sumgrades;

        case QUIZ_GRADEAVERAGE:
            $sum = 0;
            $count = 0;
            foreach ($attempts as $attempt) {
                if (!is_null($attempt->sumgrades)) {
                    $sum += $attempt->sumgrades;
                    $count++;
                }
            }
            if ($count == 0) {
                return null;
            }
            return $sum / $count;

        case QUIZ_GRADEHIGHEST:
        default:
            $max = null;
            foreach ($attempts as $attempt) {
                if ($attempt->sumgrades > $max) {
                    $max = $attempt->sumgrades;
                }
            }
            return $max;
    }
}


function quiz_update_all_final_grades($quiz) {
    global $DB;

    if (!$quiz->sumgrades) {
        return;
    }

    $param = array('iquizid' => $quiz->id, 'istatefinished' => quiz_attempt::FINISHED);
    $firstlastattemptjoin = "JOIN (
            SELECT
                iquiza.userid,
                MIN(attempt) AS firstattempt,
                MAX(attempt) AS lastattempt

            FROM {quiz_attempts} iquiza

            WHERE
                iquiza.state = :istatefinished AND
                iquiza.preview = 0 AND
                iquiza.quiz = :iquizid

            GROUP BY iquiza.userid
        ) first_last_attempts ON first_last_attempts.userid = quiza.userid";

    switch ($quiz->grademethod) {
        case QUIZ_ATTEMPTFIRST:
                                    $select = 'MAX(quiza.sumgrades)';
            $join = $firstlastattemptjoin;
            $where = 'quiza.attempt = first_last_attempts.firstattempt AND';
            break;

        case QUIZ_ATTEMPTLAST:
                                    $select = 'MAX(quiza.sumgrades)';
            $join = $firstlastattemptjoin;
            $where = 'quiza.attempt = first_last_attempts.lastattempt AND';
            break;

        case QUIZ_GRADEAVERAGE:
            $select = 'AVG(quiza.sumgrades)';
            $join = '';
            $where = '';
            break;

        default:
        case QUIZ_GRADEHIGHEST:
            $select = 'MAX(quiza.sumgrades)';
            $join = '';
            $where = '';
            break;
    }

    if ($quiz->sumgrades >= 0.000005) {
        $finalgrade = $select . ' * ' . ($quiz->grade / $quiz->sumgrades);
    } else {
        $finalgrade = '0';
    }
    $param['quizid'] = $quiz->id;
    $param['quizid2'] = $quiz->id;
    $param['quizid3'] = $quiz->id;
    $param['quizid4'] = $quiz->id;
    $param['statefinished'] = quiz_attempt::FINISHED;
    $param['statefinished2'] = quiz_attempt::FINISHED;
    $finalgradesubquery = "
            SELECT quiza.userid, $finalgrade AS newgrade
            FROM {quiz_attempts} quiza
            $join
            WHERE
                $where
                quiza.state = :statefinished AND
                quiza.preview = 0 AND
                quiza.quiz = :quizid3
            GROUP BY quiza.userid";

    $changedgrades = $DB->get_records_sql("
            SELECT users.userid, qg.id, qg.grade, newgrades.newgrade

            FROM (
                SELECT userid
                FROM {quiz_grades} qg
                WHERE quiz = :quizid
            UNION
                SELECT DISTINCT userid
                FROM {quiz_attempts} quiza2
                WHERE
                    quiza2.state = :statefinished2 AND
                    quiza2.preview = 0 AND
                    quiza2.quiz = :quizid2
            ) users

            LEFT JOIN {quiz_grades} qg ON qg.userid = users.userid AND qg.quiz = :quizid4

            LEFT JOIN (
                $finalgradesubquery
            ) newgrades ON newgrades.userid = users.userid

            WHERE
                ABS(newgrades.newgrade - qg.grade) > 0.000005 OR
                ((newgrades.newgrade IS NULL OR qg.grade IS NULL) AND NOT
                          (newgrades.newgrade IS NULL AND qg.grade IS NULL))",
                                                                            $param);

    $timenow = time();
    $todelete = array();
    foreach ($changedgrades as $changedgrade) {

        if (is_null($changedgrade->newgrade)) {
            $todelete[] = $changedgrade->userid;

        } else if (is_null($changedgrade->grade)) {
            $toinsert = new stdClass();
            $toinsert->quiz = $quiz->id;
            $toinsert->userid = $changedgrade->userid;
            $toinsert->timemodified = $timenow;
            $toinsert->grade = $changedgrade->newgrade;
            $DB->insert_record('quiz_grades', $toinsert);

        } else {
            $toupdate = new stdClass();
            $toupdate->id = $changedgrade->id;
            $toupdate->grade = $changedgrade->newgrade;
            $toupdate->timemodified = $timenow;
            $DB->update_record('quiz_grades', $toupdate);
        }
    }

    if (!empty($todelete)) {
        list($test, $params) = $DB->get_in_or_equal($todelete);
        $DB->delete_records_select('quiz_grades', 'quiz = ? AND userid ' . $test,
                array_merge(array($quiz->id), $params));
    }
}


function quiz_update_open_attempts(array $conditions) {
    global $DB;

    foreach ($conditions as &$value) {
        if (!is_array($value)) {
            $value = array($value);
        }
    }

    $params = array();
    $wheres = array("quiza.state IN ('inprogress', 'overdue')");
    $iwheres = array("iquiza.state IN ('inprogress', 'overdue')");

    if (isset($conditions['courseid'])) {
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['courseid'], SQL_PARAMS_NAMED, 'cid');
        $params = array_merge($params, $inparams);
        $wheres[] = "quiza.quiz IN (SELECT q.id FROM {quiz} q WHERE q.course $incond)";
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['courseid'], SQL_PARAMS_NAMED, 'icid');
        $params = array_merge($params, $inparams);
        $iwheres[] = "iquiza.quiz IN (SELECT q.id FROM {quiz} q WHERE q.course $incond)";
    }

    if (isset($conditions['userid'])) {
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['userid'], SQL_PARAMS_NAMED, 'uid');
        $params = array_merge($params, $inparams);
        $wheres[] = "quiza.userid $incond";
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['userid'], SQL_PARAMS_NAMED, 'iuid');
        $params = array_merge($params, $inparams);
        $iwheres[] = "iquiza.userid $incond";
    }

    if (isset($conditions['quizid'])) {
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['quizid'], SQL_PARAMS_NAMED, 'qid');
        $params = array_merge($params, $inparams);
        $wheres[] = "quiza.quiz $incond";
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['quizid'], SQL_PARAMS_NAMED, 'iqid');
        $params = array_merge($params, $inparams);
        $iwheres[] = "iquiza.quiz $incond";
    }

    if (isset($conditions['groupid'])) {
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['groupid'], SQL_PARAMS_NAMED, 'gid');
        $params = array_merge($params, $inparams);
        $wheres[] = "quiza.quiz IN (SELECT qo.quiz FROM {quiz_overrides} qo WHERE qo.groupid $incond)";
        list ($incond, $inparams) = $DB->get_in_or_equal($conditions['groupid'], SQL_PARAMS_NAMED, 'igid');
        $params = array_merge($params, $inparams);
        $iwheres[] = "iquiza.quiz IN (SELECT qo.quiz FROM {quiz_overrides} qo WHERE qo.groupid $incond)";
    }

        $quizausersql = quiz_get_attempt_usertime_sql(
            implode("\n                AND ", $iwheres));

        $timecheckstatesql = "
          CASE WHEN quizauser.usertimelimit = 0 AND quizauser.usertimeclose = 0 THEN NULL
               WHEN quizauser.usertimelimit = 0 THEN quizauser.usertimeclose
               WHEN quizauser.usertimeclose = 0 THEN quiza.timestart + quizauser.usertimelimit
               WHEN quiza.timestart + quizauser.usertimelimit < quizauser.usertimeclose THEN quiza.timestart + quizauser.usertimelimit
               ELSE quizauser.usertimeclose END +
          CASE WHEN quiza.state = 'overdue' THEN quiz.graceperiod ELSE 0 END";

        $attemptselect = implode("\n                         AND ", $wheres);

   

    $dbfamily = $DB->get_dbfamily();
    if ($dbfamily == 'mysql') {
        $updatesql = "UPDATE {quiz_attempts} quiza
                        JOIN {quiz} quiz ON quiz.id = quiza.quiz
                        JOIN ( $quizausersql ) quizauser ON quizauser.id = quiza.id
                         SET quiza.timecheckstate = $timecheckstatesql
                       WHERE $attemptselect";
    } else if ($dbfamily == 'postgres') {
        $updatesql = "UPDATE {quiz_attempts} quiza
                         SET timecheckstate = $timecheckstatesql
                        FROM {quiz} quiz, ( $quizausersql ) quizauser
                       WHERE quiz.id = quiza.quiz
                         AND quizauser.id = quiza.id
                         AND $attemptselect";
    } else if ($dbfamily == 'mssql') {
        $updatesql = "UPDATE quiza
                         SET timecheckstate = $timecheckstatesql
                        FROM {quiz_attempts} quiza
                        JOIN {quiz} quiz ON quiz.id = quiza.quiz
                        JOIN ( $quizausersql ) quizauser ON quizauser.id = quiza.id
                       WHERE $attemptselect";
    } else {
                $updatesql = "UPDATE {quiz_attempts} quiza
                         SET timecheckstate = (
                           SELECT $timecheckstatesql
                             FROM {quiz} quiz, ( $quizausersql ) quizauser
                            WHERE quiz.id = quiza.quiz
                              AND quizauser.id = quiza.id
                         )
                         WHERE $attemptselect";
    }

    $DB->execute($updatesql, $params);
}


function quiz_get_attempt_usertime_sql($redundantwhereclauses = '') {
    if ($redundantwhereclauses) {
        $redundantwhereclauses = 'WHERE ' . $redundantwhereclauses;
    }
            $quizausersql = "
          SELECT iquiza.id,
           COALESCE(MAX(quo.timeclose), MAX(qgo1.timeclose), MAX(qgo2.timeclose), iquiz.timeclose) AS usertimeclose,
           COALESCE(MAX(quo.timelimit), MAX(qgo3.timelimit), MAX(qgo4.timelimit), iquiz.timelimit) AS usertimelimit

           FROM {quiz_attempts} iquiza
           JOIN {quiz} iquiz ON iquiz.id = iquiza.quiz
      LEFT JOIN {quiz_overrides} quo ON quo.quiz = iquiza.quiz AND quo.userid = iquiza.userid
      LEFT JOIN {groups_members} gm ON gm.userid = iquiza.userid
      LEFT JOIN {quiz_overrides} qgo1 ON qgo1.quiz = iquiza.quiz AND qgo1.groupid = gm.groupid AND qgo1.timeclose = 0
      LEFT JOIN {quiz_overrides} qgo2 ON qgo2.quiz = iquiza.quiz AND qgo2.groupid = gm.groupid AND qgo2.timeclose > 0
      LEFT JOIN {quiz_overrides} qgo3 ON qgo3.quiz = iquiza.quiz AND qgo3.groupid = gm.groupid AND qgo3.timelimit = 0
      LEFT JOIN {quiz_overrides} qgo4 ON qgo4.quiz = iquiza.quiz AND qgo4.groupid = gm.groupid AND qgo4.timelimit > 0
          $redundantwhereclauses
       GROUP BY iquiza.id, iquiz.id, iquiz.timeclose, iquiz.timelimit";
    return $quizausersql;
}


function quiz_calculate_best_attempt($quiz, $attempts) {

    switch ($quiz->grademethod) {

        case QUIZ_ATTEMPTFIRST:
            foreach ($attempts as $attempt) {
                return $attempt;
            }
            break;

        case QUIZ_GRADEAVERAGE:         case QUIZ_ATTEMPTLAST:
            foreach ($attempts as $attempt) {
                $final = $attempt;
            }
            return $final;

        default:
        case QUIZ_GRADEHIGHEST:
            $max = -1;
            foreach ($attempts as $attempt) {
                if ($attempt->sumgrades > $max) {
                    $max = $attempt->sumgrades;
                    $maxattempt = $attempt;
                }
            }
            return $maxattempt;
    }
}


function quiz_get_grading_options() {
    return array(
        QUIZ_GRADEHIGHEST => get_string('gradehighest', 'quiz'),
        QUIZ_GRADEAVERAGE => get_string('gradeaverage', 'quiz'),
        QUIZ_ATTEMPTFIRST => get_string('attemptfirst', 'quiz'),
        QUIZ_ATTEMPTLAST  => get_string('attemptlast', 'quiz')
    );
}


function quiz_get_grading_option_name($option) {
    $strings = quiz_get_grading_options();
    return $strings[$option];
}


function quiz_get_overdue_handling_options() {
    return array(
        'autosubmit'  => get_string('overduehandlingautosubmit', 'quiz'),
        'graceperiod' => get_string('overduehandlinggraceperiod', 'quiz'),
        'autoabandon' => get_string('overduehandlingautoabandon', 'quiz'),
    );
}


function quiz_get_user_image_options() {
    return array(
        QUIZ_SHOWIMAGE_NONE  => get_string('shownoimage', 'quiz'),
        QUIZ_SHOWIMAGE_SMALL => get_string('showsmallimage', 'quiz'),
        QUIZ_SHOWIMAGE_LARGE => get_string('showlargeimage', 'quiz'),
    );
}


function quiz_questions_per_page_options() {
    $pageoptions = array();
    $pageoptions[0] = get_string('neverallononepage', 'quiz');
    $pageoptions[1] = get_string('everyquestion', 'quiz');
    for ($i = 2; $i <= QUIZ_MAX_QPP_OPTION; ++$i) {
        $pageoptions[$i] = get_string('everynquestions', 'quiz', $i);
    }
    return $pageoptions;
}


function quiz_attempt_state_name($state) {
    switch ($state) {
        case quiz_attempt::IN_PROGRESS:
            return get_string('stateinprogress', 'quiz');
        case quiz_attempt::OVERDUE:
            return get_string('stateoverdue', 'quiz');
        case quiz_attempt::FINISHED:
            return get_string('statefinished', 'quiz');
        case quiz_attempt::ABANDONED:
            return get_string('stateabandoned', 'quiz');
        default:
            throw new coding_exception('Unknown quiz attempt state.');
    }
}



function quiz_question_action_icons($quiz, $cmid, $question, $returnurl, $variant = null) {
    $html = quiz_question_preview_button($quiz, $question, false, $variant) . ' ' .
            quiz_question_edit_button($cmid, $question, $returnurl);
    return $html;
}


function quiz_question_edit_button($cmid, $question, $returnurl, $contentaftericon = '') {
    global $CFG, $OUTPUT;

        static $stredit = null;
    static $strview = null;
    if ($stredit === null) {
        $stredit = get_string('edit');
        $strview = get_string('view');
    }

        $action = '';
    if (!empty($question->id) &&
            (question_has_capability_on($question, 'edit', $question->category) ||
                    question_has_capability_on($question, 'move', $question->category))) {
        $action = $stredit;
        $icon = '/t/edit';
    } else if (!empty($question->id) &&
            question_has_capability_on($question, 'view', $question->category)) {
        $action = $strview;
        $icon = '/i/info';
    }

        if ($action) {
        if ($returnurl instanceof moodle_url) {
            $returnurl = $returnurl->out_as_local_url(false);
        }
        $questionparams = array('returnurl' => $returnurl, 'cmid' => $cmid, 'id' => $question->id);
        $questionurl = new moodle_url("$CFG->wwwroot/question/question.php", $questionparams);
        return '<a title="' . $action . '" href="' . $questionurl->out() . '" class="questioneditbutton"><img src="' .
                $OUTPUT->pix_url($icon) . '" alt="' . $action . '" />' . $contentaftericon .
                '</a>';
    } else if ($contentaftericon) {
        return '<span class="questioneditbutton">' . $contentaftericon . '</span>';
    } else {
        return '';
    }
}


function quiz_question_preview_url($quiz, $question, $variant = null) {
        $displayoptions = mod_quiz_display_options::make_from_quiz($quiz,
            mod_quiz_display_options::DURING);

    $maxmark = null;
    if (isset($question->maxmark)) {
        $maxmark = $question->maxmark;
    }

        return question_preview_url($question->id, $quiz->preferredbehaviour,
            $maxmark, $displayoptions, $variant);
}


function quiz_question_preview_button($quiz, $question, $label = false, $variant = null) {
    global $PAGE;
    if (!question_has_capability_on($question, 'use', $question->category)) {
        return '';
    }

    return $PAGE->get_renderer('mod_quiz', 'edit')->question_preview_icon($quiz, $question, $label, $variant);
}


function quiz_get_flag_option($attempt, $context) {
    global $USER;
    if (!has_capability('moodle/question:flag', $context)) {
        return question_display_options::HIDDEN;
    } else if ($attempt->userid == $USER->id) {
        return question_display_options::EDITABLE;
    } else {
        return question_display_options::VISIBLE;
    }
}


function quiz_attempt_state($quiz, $attempt) {
    if ($attempt->state == quiz_attempt::IN_PROGRESS) {
        return mod_quiz_display_options::DURING;
    } else if ($quiz->timeclose && time() >= $quiz->timeclose) {
        return mod_quiz_display_options::AFTER_CLOSE;
    } else if (time() < $attempt->timefinish + 120) {
        return mod_quiz_display_options::IMMEDIATELY_AFTER;
    } else {
        return mod_quiz_display_options::LATER_WHILE_OPEN;
    }
}


function quiz_get_review_options($quiz, $attempt, $context) {
    $options = mod_quiz_display_options::make_from_quiz($quiz, quiz_attempt_state($quiz, $attempt));

    $options->readonly = true;
    $options->flags = quiz_get_flag_option($attempt, $context);
    if (!empty($attempt->id)) {
        $options->questionreviewlink = new moodle_url('/mod/quiz/reviewquestion.php',
                array('attempt' => $attempt->id));
    }

        if (!empty($attempt->id) && $attempt->state == quiz_attempt::FINISHED && !$attempt->preview &&
            !is_null($context) && has_capability('mod/quiz:grade', $context)) {
        $options->manualcomment = question_display_options::VISIBLE;
        $options->manualcommentlink = new moodle_url('/mod/quiz/comment.php',
                array('attempt' => $attempt->id));
    }

    if (!is_null($context) && !$attempt->preview &&
            has_capability('mod/quiz:viewreports', $context) &&
            has_capability('moodle/grade:viewhidden', $context)) {
                        $options->attempt = question_display_options::VISIBLE;
        $options->correctness = question_display_options::VISIBLE;
        $options->marks = question_display_options::MARK_AND_MAX;
        $options->feedback = question_display_options::VISIBLE;
        $options->numpartscorrect = question_display_options::VISIBLE;
        $options->manualcomment = question_display_options::VISIBLE;
        $options->generalfeedback = question_display_options::VISIBLE;
        $options->rightanswer = question_display_options::VISIBLE;
        $options->overallfeedback = question_display_options::VISIBLE;
        $options->history = question_display_options::VISIBLE;

    }

    return $options;
}


function quiz_get_combined_reviewoptions($quiz, $attempts) {
    $fields = array('feedback', 'generalfeedback', 'rightanswer', 'overallfeedback');
    $someoptions = new stdClass();
    $alloptions = new stdClass();
    foreach ($fields as $field) {
        $someoptions->$field = false;
        $alloptions->$field = true;
    }
    $someoptions->marks = question_display_options::HIDDEN;
    $alloptions->marks = question_display_options::MARK_AND_MAX;

        if (empty($attempts)) {
        return array($someoptions, $someoptions);
    }

    foreach ($attempts as $attempt) {
        $attemptoptions = mod_quiz_display_options::make_from_quiz($quiz,
                quiz_attempt_state($quiz, $attempt));
        foreach ($fields as $field) {
            $someoptions->$field = $someoptions->$field || $attemptoptions->$field;
            $alloptions->$field = $alloptions->$field && $attemptoptions->$field;
        }
        $someoptions->marks = max($someoptions->marks, $attemptoptions->marks);
        $alloptions->marks = min($alloptions->marks, $attemptoptions->marks);
    }
    return array($someoptions, $alloptions);
}



function quiz_send_confirmation($recipient, $a) {

            $a->username     = fullname($recipient);
    $a->userusername = $recipient->username;

        $eventdata = new stdClass();
    $eventdata->component         = 'mod_quiz';
    $eventdata->name              = 'confirmation';
    $eventdata->notification      = 1;

    $eventdata->userfrom          = core_user::get_noreply_user();
    $eventdata->userto            = $recipient;
    $eventdata->subject           = get_string('emailconfirmsubject', 'quiz', $a);
    $eventdata->fullmessage       = get_string('emailconfirmbody', 'quiz', $a);
    $eventdata->fullmessageformat = FORMAT_PLAIN;
    $eventdata->fullmessagehtml   = '';

    $eventdata->smallmessage      = get_string('emailconfirmsmall', 'quiz', $a);
    $eventdata->contexturl        = $a->quizurl;
    $eventdata->contexturlname    = $a->quizname;

        return message_send($eventdata);
}


function quiz_send_notification($recipient, $submitter, $a) {

        $a->useridnumber = $recipient->idnumber;
    $a->username     = fullname($recipient);
    $a->userusername = $recipient->username;

        $eventdata = new stdClass();
    $eventdata->component         = 'mod_quiz';
    $eventdata->name              = 'submission';
    $eventdata->notification      = 1;

    $eventdata->userfrom          = $submitter;
    $eventdata->userto            = $recipient;
    $eventdata->subject           = get_string('emailnotifysubject', 'quiz', $a);
    $eventdata->fullmessage       = get_string('emailnotifybody', 'quiz', $a);
    $eventdata->fullmessageformat = FORMAT_PLAIN;
    $eventdata->fullmessagehtml   = '';

    $eventdata->smallmessage      = get_string('emailnotifysmall', 'quiz', $a);
    $eventdata->contexturl        = $a->quizreviewurl;
    $eventdata->contexturlname    = $a->quizname;

        return message_send($eventdata);
}


function quiz_send_notification_messages($course, $quiz, $attempt, $context, $cm) {
    global $CFG, $DB;

        if (empty($course) or empty($quiz) or empty($attempt) or empty($context)) {
        throw new coding_exception('$course, $quiz, $attempt, $context and $cm must all be set.');
    }

    $submitter = $DB->get_record('user', array('id' => $attempt->userid), '*', MUST_EXIST);

        $sendconfirm = false;
    $notifyexcludeusers = '';
    if (has_capability('mod/quiz:emailconfirmsubmission', $context, $submitter, false)) {
        $notifyexcludeusers = $submitter->id;
        $sendconfirm = true;
    }

        $notifyfields = 'u.id, u.username, u.idnumber, u.email, u.emailstop, u.lang,
            u.timezone, u.mailformat, u.maildisplay, u.auth, u.suspended, u.deleted, ';
    $notifyfields .= get_all_user_name_fields(true, 'u');
    $groups = groups_get_all_groups($course->id, $submitter->id, $cm->groupingid);
    if (is_array($groups) && count($groups) > 0) {
        $groups = array_keys($groups);
    } else if (groups_get_activity_groupmode($cm, $course) != NOGROUPS) {
                                $groups = -1;
    } else {
        $groups = '';
    }
    $userstonotify = get_users_by_capability($context, 'mod/quiz:emailnotifysubmission',
            $notifyfields, '', '', '', $groups, $notifyexcludeusers, false, false, true);

    if (empty($userstonotify) && !$sendconfirm) {
        return true;     }

    $a = new stdClass();
        $a->coursename      = $course->fullname;
    $a->courseshortname = $course->shortname;
        $a->quizname        = $quiz->name;
    $a->quizreporturl   = $CFG->wwwroot . '/mod/quiz/report.php?id=' . $cm->id;
    $a->quizreportlink  = '<a href="' . $a->quizreporturl . '">' .
            format_string($quiz->name) . ' report</a>';
    $a->quizurl         = $CFG->wwwroot . '/mod/quiz/view.php?id=' . $cm->id;
    $a->quizlink        = '<a href="' . $a->quizurl . '">' . format_string($quiz->name) . '</a>';
        $a->submissiontime  = userdate($attempt->timefinish);
    $a->timetaken       = format_time($attempt->timefinish - $attempt->timestart);
    $a->quizreviewurl   = $CFG->wwwroot . '/mod/quiz/review.php?attempt=' . $attempt->id;
    $a->quizreviewlink  = '<a href="' . $a->quizreviewurl . '">' .
            format_string($quiz->name) . ' review</a>';
        $a->studentidnumber = $submitter->idnumber;
    $a->studentname     = fullname($submitter);
    $a->studentusername = $submitter->username;

    $allok = true;

        if (!empty($userstonotify)) {
        foreach ($userstonotify as $recipient) {
            $allok = $allok && quiz_send_notification($recipient, $submitter, $a);
        }
    }

                    if ($sendconfirm) {
        $allok = $allok && quiz_send_confirmation($submitter, $a);
    }

    return $allok;
}


function quiz_send_overdue_message($attemptobj) {
    global $CFG, $DB;

    $submitter = $DB->get_record('user', array('id' => $attemptobj->get_userid()), '*', MUST_EXIST);

    if (!$attemptobj->has_capability('mod/quiz:emailwarnoverdue', $submitter->id, false)) {
        return;     }

    if (!$attemptobj->has_response_to_at_least_one_graded_question()) {
        return;     }

            $quizname = format_string($attemptobj->get_quiz_name());

    $deadlines = array();
    if ($attemptobj->get_quiz()->timelimit) {
        $deadlines[] = $attemptobj->get_attempt()->timestart + $attemptobj->get_quiz()->timelimit;
    }
    if ($attemptobj->get_quiz()->timeclose) {
        $deadlines[] = $attemptobj->get_quiz()->timeclose;
    }
    $duedate = min($deadlines);
    $graceend = $duedate + $attemptobj->get_quiz()->graceperiod;

    $a = new stdClass();
        $a->coursename         = format_string($attemptobj->get_course()->fullname);
    $a->courseshortname    = format_string($attemptobj->get_course()->shortname);
        $a->quizname           = $quizname;
    $a->quizurl            = $attemptobj->view_url();
    $a->quizlink           = '<a href="' . $a->quizurl . '">' . $quizname . '</a>';
        $a->attemptduedate     = userdate($duedate);
    $a->attemptgraceend    = userdate($graceend);
    $a->attemptsummaryurl  = $attemptobj->summary_url()->out(false);
    $a->attemptsummarylink = '<a href="' . $a->attemptsummaryurl . '">' . $quizname . ' review</a>';
        $a->studentidnumber    = $submitter->idnumber;
    $a->studentname        = fullname($submitter);
    $a->studentusername    = $submitter->username;

        $eventdata = new stdClass();
    $eventdata->component         = 'mod_quiz';
    $eventdata->name              = 'attempt_overdue';
    $eventdata->notification      = 1;

    $eventdata->userfrom          = core_user::get_noreply_user();
    $eventdata->userto            = $submitter;
    $eventdata->subject           = get_string('emailoverduesubject', 'quiz', $a);
    $eventdata->fullmessage       = get_string('emailoverduebody', 'quiz', $a);
    $eventdata->fullmessageformat = FORMAT_PLAIN;
    $eventdata->fullmessagehtml   = '';

    $eventdata->smallmessage      = get_string('emailoverduesmall', 'quiz', $a);
    $eventdata->contexturl        = $a->quizurl;
    $eventdata->contexturlname    = $a->quizname;

        return message_send($eventdata);
}


function quiz_attempt_submitted_handler($event) {
    global $DB;

    $course  = $DB->get_record('course', array('id' => $event->courseid));
    $attempt = $event->get_record_snapshot('quiz_attempts', $event->objectid);
    $quiz    = $event->get_record_snapshot('quiz', $attempt->quiz);
    $cm      = get_coursemodule_from_id('quiz', $event->get_context()->instanceid, $event->courseid);

    if (!($course && $quiz && $cm && $attempt)) {
                        return true;
    }

        $completion = new completion_info($course);
    if ($completion->is_enabled($cm) && ($quiz->completionattemptsexhausted || $quiz->completionpass)) {
        $completion->update_state($cm, COMPLETION_COMPLETE, $event->userid);
    }
    return quiz_send_notification_messages($course, $quiz, $attempt,
            context_module::instance($cm->id), $cm);
}


function quiz_groups_member_added_handler($event) {
    debugging('quiz_groups_member_added_handler() is deprecated, please use ' .
        '\mod_quiz\group_observers::group_member_added() instead.', DEBUG_DEVELOPER);
    quiz_update_open_attempts(array('userid'=>$event->userid, 'groupid'=>$event->groupid));
}


function quiz_groups_member_removed_handler($event) {
    debugging('quiz_groups_member_removed_handler() is deprecated, please use ' .
        '\mod_quiz\group_observers::group_member_removed() instead.', DEBUG_DEVELOPER);
    quiz_update_open_attempts(array('userid'=>$event->userid, 'groupid'=>$event->groupid));
}


function quiz_groups_group_deleted_handler($event) {
    global $DB;
    debugging('quiz_groups_group_deleted_handler() is deprecated, please use ' .
        '\mod_quiz\group_observers::group_deleted() instead.', DEBUG_DEVELOPER);
    quiz_process_group_deleted_in_course($event->courseid);
}


function quiz_process_group_deleted_in_course($courseid) {
    global $DB;

            $sql = "SELECT o.id, o.quiz
              FROM {quiz_overrides} o
              JOIN {quiz} quiz ON quiz.id = o.quiz
         LEFT JOIN {groups} grp ON grp.id = o.groupid
             WHERE quiz.course = :courseid
               AND o.groupid IS NOT NULL
               AND grp.id IS NULL";
    $params = array('courseid' => $courseid);
    $records = $DB->get_records_sql_menu($sql, $params);
    if (!$records) {
        return;     }
    $DB->delete_records_list('quiz_overrides', 'id', array_keys($records));
    quiz_update_open_attempts(array('quizid' => array_unique(array_values($records))));
}


function quiz_groups_members_removed_handler($event) {
    debugging('quiz_groups_members_removed_handler() is deprecated, please use ' .
        '\mod_quiz\group_observers::group_member_removed() instead.', DEBUG_DEVELOPER);
    if ($event->userid == 0) {
        quiz_update_open_attempts(array('courseid'=>$event->courseid));
    } else {
        quiz_update_open_attempts(array('courseid'=>$event->courseid, 'userid'=>$event->userid));
    }
}


function quiz_get_js_module() {
    global $PAGE;

    return array(
        'name' => 'mod_quiz',
        'fullpath' => '/mod/quiz/module.js',
        'requires' => array('base', 'dom', 'event-delegate', 'event-key',
                'core_question_engine', 'moodle-core-formchangechecker'),
        'strings' => array(
            array('cancel', 'moodle'),
            array('flagged', 'question'),
            array('functiondisabledbysecuremode', 'quiz'),
            array('startattempt', 'quiz'),
            array('timesup', 'quiz'),
            array('changesmadereallygoaway', 'moodle'),
        ),
    );
}



class mod_quiz_display_options extends question_display_options {
    
    const DURING =            0x10000;
    const IMMEDIATELY_AFTER = 0x01000;
    const LATER_WHILE_OPEN =  0x00100;
    const AFTER_CLOSE =       0x00010;
    

    
    public $attempt = true;

    
    public $overallfeedback = self::VISIBLE;

    
    public static function make_from_quiz($quiz, $when) {
        $options = new self();

        $options->attempt = self::extract($quiz->reviewattempt, $when, true, false);
        $options->correctness = self::extract($quiz->reviewcorrectness, $when);
        $options->marks = self::extract($quiz->reviewmarks, $when,
                self::MARK_AND_MAX, self::MAX_ONLY);
        $options->feedback = self::extract($quiz->reviewspecificfeedback, $when);
        $options->generalfeedback = self::extract($quiz->reviewgeneralfeedback, $when);
        $options->rightanswer = self::extract($quiz->reviewrightanswer, $when);
        $options->overallfeedback = self::extract($quiz->reviewoverallfeedback, $when);

        $options->numpartscorrect = $options->feedback;
        $options->manualcomment = $options->feedback;

        if ($quiz->questiondecimalpoints != -1) {
            $options->markdp = $quiz->questiondecimalpoints;
        } else {
            $options->markdp = $quiz->decimalpoints;
        }

        return $options;
    }

    protected static function extract($bitmask, $bit,
            $whenset = self::VISIBLE, $whennotset = self::HIDDEN) {
        if ($bitmask & $bit) {
            return $whenset;
        } else {
            return $whennotset;
        }
    }
}



class qubaids_for_quiz extends qubaid_join {
    public function __construct($quizid, $includepreviews = true, $onlyfinished = false) {
        $where = 'quiza.quiz = :quizaquiz';
        $params = array('quizaquiz' => $quizid);

        if (!$includepreviews) {
            $where .= ' AND preview = 0';
        }

        if ($onlyfinished) {
            $where .= ' AND state == :statefinished';
            $params['statefinished'] = quiz_attempt::FINISHED;
        }

        parent::__construct('{quiz_attempts} quiza', 'quiza.uniqueid', $where, $params);
    }
}


function quiz_question_tostring($question, $showicon = false, $showquestiontext = true) {
    $result = '';

    $name = shorten_text(format_string($question->name), 200);
    if ($showicon) {
        $name .= print_question_icon($question) . ' ' . $name;
    }
    $result .= html_writer::span($name, 'questionname');

    if ($showquestiontext) {
        $questiontext = question_utils::to_plain_text($question->questiontext,
                $question->questiontextformat, array('noclean' => true, 'para' => false));
        $questiontext = shorten_text($questiontext, 200);
        if ($questiontext) {
            $result .= ' ' . html_writer::span(s($questiontext), 'questiontext');
        }
    }

    return $result;
}


function quiz_require_question_use($questionid) {
    global $DB;
    $question = $DB->get_record('question', array('id' => $questionid), '*', MUST_EXIST);
    question_require_capability_on($question, 'use');
}


function quiz_has_question_use($quiz, $slot) {
    global $DB;
    $question = $DB->get_record_sql("
            SELECT q.*
              FROM {quiz_slots} slot
              JOIN {question} q ON q.id = slot.questionid
             WHERE slot.quizid = ? AND slot.slot = ?", array($quiz->id, $slot));
    if (!$question) {
        return false;
    }
    return question_has_capability_on($question, 'use');
}


function quiz_add_quiz_question($questionid, $quiz, $page = 0, $maxmark = null) {
    global $DB;
    $slots = $DB->get_records('quiz_slots', array('quizid' => $quiz->id),
            'slot', 'questionid, slot, page, id');
    if (array_key_exists($questionid, $slots)) {
        return false;
    }

    $trans = $DB->start_delegated_transaction();

    $maxpage = 1;
    $numonlastpage = 0;
    foreach ($slots as $slot) {
        if ($slot->page > $maxpage) {
            $maxpage = $slot->page;
            $numonlastpage = 1;
        } else {
            $numonlastpage += 1;
        }
    }

        $slot = new stdClass();
    $slot->quizid = $quiz->id;
    $slot->questionid = $questionid;

    if ($maxmark !== null) {
        $slot->maxmark = $maxmark;
    } else {
        $slot->maxmark = $DB->get_field('question', 'defaultmark', array('id' => $questionid));
    }

    if (is_int($page) && $page >= 1) {
                $lastslotbefore = 0;
        foreach (array_reverse($slots) as $otherslot) {
            if ($otherslot->page > $page) {
                $DB->set_field('quiz_slots', 'slot', $otherslot->slot + 1, array('id' => $otherslot->id));
            } else {
                $lastslotbefore = $otherslot->slot;
                break;
            }
        }
        $slot->slot = $lastslotbefore + 1;
        $slot->page = min($page, $maxpage + 1);

        $DB->execute("
                UPDATE {quiz_sections}
                   SET firstslot = firstslot + 1
                 WHERE quizid = ?
                   AND firstslot > ?
                ", array($quiz->id, max($lastslotbefore, 1)));

    } else {
        $lastslot = end($slots);
        if ($lastslot) {
            $slot->slot = $lastslot->slot + 1;
        } else {
            $slot->slot = 1;
        }
        if ($quiz->questionsperpage && $numonlastpage >= $quiz->questionsperpage) {
            $slot->page = $maxpage + 1;
        } else {
            $slot->page = $maxpage;
        }
    }

    $DB->insert_record('quiz_slots', $slot);
    $trans->allow_commit();
}


function quiz_add_random_questions($quiz, $addonpage, $categoryid, $number,
        $includesubcategories) {
    global $DB;

    $category = $DB->get_record('question_categories', array('id' => $categoryid));
    if (!$category) {
        print_error('invalidcategoryid', 'error');
    }

    $catcontext = context::instance_by_id($category->contextid);
    require_capability('moodle/question:useall', $catcontext);

            if ($existingquestions = $DB->get_records_sql(
            "SELECT q.id, q.qtype FROM {question} q
            WHERE qtype = 'random'
                AND category = ?
                AND " . $DB->sql_compare_text('questiontext') . " = ?
                AND NOT EXISTS (
                        SELECT *
                          FROM {quiz_slots}
                         WHERE questionid = q.id)
            ORDER BY id", array($category->id, ($includesubcategories ? '1' : '0')))) {
                    while (($existingquestion = array_shift($existingquestions)) && $number > 0) {
            quiz_add_quiz_question($existingquestion->id, $quiz, $addonpage);
            $number -= 1;
        }
    }

    if ($number <= 0) {
        return;
    }

        for ($i = 0; $i < $number; $i += 1) {
        $form = new stdClass();
        $form->questiontext = array('text' => ($includesubcategories ? '1' : '0'), 'format' => 0);
        $form->category = $category->id . ',' . $category->contextid;
        $form->defaultmark = 1;
        $form->hidden = 1;
        $form->stamp = make_unique_id_code();         $question = new stdClass();
        $question->qtype = 'random';
        $question = question_bank::get_qtype('random')->save_question($question, $form);
        if (!isset($question->id)) {
            print_error('cannotinsertrandomquestion', 'quiz');
        }
        quiz_add_quiz_question($question->id, $quiz, $addonpage);
    }
}


function quiz_view($quiz, $course, $cm, $context) {

    $params = array(
        'objectid' => $quiz->id,
        'context' => $context
    );

    $event = \mod_quiz\event\course_module_viewed::create($params);
    $event->add_record_snapshot('quiz', $quiz);
    $event->trigger();

        $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}


function quiz_validate_new_attempt(quiz $quizobj, quiz_access_manager $accessmanager, $forcenew, $page, $redirect) {
    global $DB, $USER;
    $timenow = time();

    if ($quizobj->is_preview_user() && $forcenew) {
        $accessmanager->current_attempt_finished();
    }

        if (!$quizobj->is_preview_user()) {
        $quizobj->require_capability('mod/quiz:attempt');
    }

        if ($quizobj->is_preview_user() && $forcenew) {
                        $DB->set_field('quiz_attempts', 'state', quiz_attempt::FINISHED,
                array('quiz' => $quizobj->get_quizid(), 'userid' => $USER->id));
    }

        $attempts = quiz_get_user_attempts($quizobj->get_quizid(), $USER->id, 'all', true);
    $lastattempt = end($attempts);

    $attemptnumber = null;
        if ($lastattempt && ($lastattempt->state == quiz_attempt::IN_PROGRESS ||
            $lastattempt->state == quiz_attempt::OVERDUE)) {
        $currentattemptid = $lastattempt->id;
        $messages = $accessmanager->prevent_access();

                $quizobj->create_attempt_object($lastattempt)->handle_if_time_expired($timenow, true);

                if ($lastattempt->state == quiz_attempt::ABANDONED || $lastattempt->state == quiz_attempt::FINISHED) {
            if ($redirect) {
                redirect($quizobj->review_url($lastattempt->id));
            } else {
                throw new moodle_quiz_exception($quizobj, 'attemptalreadyclosed');
            }
        }

                if ($page == -1) {
            $page = $lastattempt->currentpage;
        }

    } else {
        while ($lastattempt && $lastattempt->preview) {
            $lastattempt = array_pop($attempts);
        }

                if ($lastattempt) {
            $attemptnumber = $lastattempt->attempt + 1;
        } else {
            $lastattempt = false;
            $attemptnumber = 1;
        }
        $currentattemptid = null;

        $messages = $accessmanager->prevent_access() +
            $accessmanager->prevent_new_attempt(count($attempts), $lastattempt);

        if ($page == -1) {
            $page = 0;
        }
    }
    return array($currentattemptid, $attemptnumber, $lastattempt, $messages, $page);
}


function quiz_prepare_and_start_new_attempt(quiz $quizobj, $attemptnumber, $lastattempt) {
    global $DB, $USER;

        quiz_delete_previews($quizobj->get_quiz(), $USER->id);

    $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
    $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $timenow = time();     $attempt = quiz_create_attempt($quizobj, $attemptnumber, $lastattempt, $timenow, $quizobj->is_preview_user());

    if (!($quizobj->get_quiz()->attemptonlast && $lastattempt)) {
        $attempt = quiz_start_new_attempt($quizobj, $quba, $attempt, $attemptnumber, $timenow);
    } else {
        $attempt = quiz_start_attempt_built_on_last($quba, $attempt, $lastattempt);
    }

    $transaction = $DB->start_delegated_transaction();

    $attempt = quiz_attempt_save_started($quizobj, $quba, $attempt);

    $transaction->allow_commit();

    return $attempt;
}
