<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/eventslib.php');
require_once($CFG->dirroot . '/calendar/lib.php');



define('QUIZ_MAX_ATTEMPT_OPTION', 10);
define('QUIZ_MAX_QPP_OPTION', 50);
define('QUIZ_MAX_DECIMAL_OPTION', 5);
define('QUIZ_MAX_Q_DECIMAL_OPTION', 7);



define('QUIZ_GRADEHIGHEST', '1');
define('QUIZ_GRADEAVERAGE', '2');
define('QUIZ_ATTEMPTFIRST', '3');
define('QUIZ_ATTEMPTLAST',  '4');



define('QUIZ_MAX_EVENT_LENGTH', 5*24*60*60); 

define('QUIZ_NAVMETHOD_FREE', 'free');
define('QUIZ_NAVMETHOD_SEQ',  'sequential');



function quiz_add_instance($quiz) {
    global $DB;
    $cmid = $quiz->coursemodule;

        $quiz->created = time();
    $result = quiz_process_options($quiz);
    if ($result && is_string($result)) {
        return $result;
    }

        $quiz->id = $DB->insert_record('quiz', $quiz);

        $DB->insert_record('quiz_sections', array('quizid' => $quiz->id,
            'firstslot' => 1, 'heading' => '', 'shufflequestions' => 0));

        quiz_after_add_or_update($quiz);

    return $quiz->id;
}


function quiz_update_instance($quiz, $mform) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');

        $result = quiz_process_options($quiz);
    if ($result && is_string($result)) {
        return $result;
    }

        $oldquiz = $DB->get_record('quiz', array('id' => $quiz->instance));

            $quiz->sumgrades = $oldquiz->sumgrades;
    $quiz->grade     = $oldquiz->grade;

        $quiz->id = $quiz->instance;
    $DB->update_record('quiz', $quiz);

        quiz_after_add_or_update($quiz);

    if ($oldquiz->grademethod != $quiz->grademethod) {
        quiz_update_all_final_grades($quiz);
        quiz_update_grades($quiz);
    }

    $quizdateschanged = $oldquiz->timelimit   != $quiz->timelimit
                     || $oldquiz->timeclose   != $quiz->timeclose
                     || $oldquiz->graceperiod != $quiz->graceperiod;
    if ($quizdateschanged) {
        quiz_update_open_attempts(array('quizid' => $quiz->id));
    }

        quiz_delete_previews($quiz);

        if (!empty($quiz->repaginatenow)) {
        quiz_repaginate_questions($quiz->id, $quiz->questionsperpage);
    }

    return true;
}


function quiz_delete_instance($id) {
    global $DB;

    $quiz = $DB->get_record('quiz', array('id' => $id), '*', MUST_EXIST);

    quiz_delete_all_attempts($quiz);
    quiz_delete_all_overrides($quiz);

        $sql = "SELECT q.id
              FROM {quiz_slots} slot
              JOIN {question} q ON q.id = slot.questionid
             WHERE slot.quizid = ? AND q.qtype = ?";
    $questionids = $DB->get_fieldset_sql($sql, array($quiz->id, 'random'));

        $DB->delete_records('quiz_slots', array('quizid' => $quiz->id));
    $DB->delete_records('quiz_sections', array('quizid' => $quiz->id));

    foreach ($questionids as $questionid) {
        question_delete_question($questionid);
    }

    $DB->delete_records('quiz_feedback', array('quizid' => $quiz->id));

    quiz_access_manager::delete_settings($quiz);

    $events = $DB->get_records('event', array('modulename' => 'quiz', 'instance' => $quiz->id));
    foreach ($events as $event) {
        $event = calendar_event::load($event);
        $event->delete();
    }

    quiz_grade_item_delete($quiz);
    $DB->delete_records('quiz', array('id' => $quiz->id));

    return true;
}


function quiz_delete_override($quiz, $overrideid) {
    global $DB;

    if (!isset($quiz->cmid)) {
        $cm = get_coursemodule_from_instance('quiz', $quiz->id, $quiz->course);
        $quiz->cmid = $cm->id;
    }

    $override = $DB->get_record('quiz_overrides', array('id' => $overrideid), '*', MUST_EXIST);

        $events = $DB->get_records('event', array('modulename' => 'quiz',
            'instance' => $quiz->id, 'groupid' => (int)$override->groupid,
            'userid' => (int)$override->userid));
    foreach ($events as $event) {
        $eventold = calendar_event::load($event);
        $eventold->delete();
    }

    $DB->delete_records('quiz_overrides', array('id' => $overrideid));

        $params = array(
        'objectid' => $override->id,
        'context' => context_module::instance($quiz->cmid),
        'other' => array(
            'quizid' => $override->quiz
        )
    );
        if (!empty($override->userid)) {
        $params['relateduserid'] = $override->userid;
        $event = \mod_quiz\event\user_override_deleted::create($params);
    } else {
        $params['other']['groupid'] = $override->groupid;
        $event = \mod_quiz\event\group_override_deleted::create($params);
    }

        $event->add_record_snapshot('quiz_overrides', $override);
    $event->trigger();

    return true;
}


function quiz_delete_all_overrides($quiz) {
    global $DB;

    $overrides = $DB->get_records('quiz_overrides', array('quiz' => $quiz->id), 'id');
    foreach ($overrides as $override) {
        quiz_delete_override($quiz, $override->id);
    }
}


function quiz_update_effective_access($quiz, $userid) {
    global $DB;

        $override = $DB->get_record('quiz_overrides', array('quiz' => $quiz->id, 'userid' => $userid));

    if (!$override) {
        $override = new stdClass();
        $override->timeopen = null;
        $override->timeclose = null;
        $override->timelimit = null;
        $override->attempts = null;
        $override->password = null;
    }

        $groupings = groups_get_user_groups($quiz->course, $userid);

    if (!empty($groupings[0])) {
                list($extra, $params) = $DB->get_in_or_equal(array_values($groupings[0]));
        $sql = "SELECT * FROM {quiz_overrides}
                WHERE groupid $extra AND quiz = ?";
        $params[] = $quiz->id;
        $records = $DB->get_records_sql($sql, $params);

                $opens = array();
        $closes = array();
        $limits = array();
        $attempts = array();
        $passwords = array();

        foreach ($records as $gpoverride) {
            if (isset($gpoverride->timeopen)) {
                $opens[] = $gpoverride->timeopen;
            }
            if (isset($gpoverride->timeclose)) {
                $closes[] = $gpoverride->timeclose;
            }
            if (isset($gpoverride->timelimit)) {
                $limits[] = $gpoverride->timelimit;
            }
            if (isset($gpoverride->attempts)) {
                $attempts[] = $gpoverride->attempts;
            }
            if (isset($gpoverride->password)) {
                $passwords[] = $gpoverride->password;
            }
        }
                if (is_null($override->timeopen) && count($opens)) {
            $override->timeopen = min($opens);
        }
        if (is_null($override->timeclose) && count($closes)) {
            if (in_array(0, $closes)) {
                $override->timeclose = 0;
            } else {
                $override->timeclose = max($closes);
            }
        }
        if (is_null($override->timelimit) && count($limits)) {
            if (in_array(0, $limits)) {
                $override->timelimit = 0;
            } else {
                $override->timelimit = max($limits);
            }
        }
        if (is_null($override->attempts) && count($attempts)) {
            if (in_array(0, $attempts)) {
                $override->attempts = 0;
            } else {
                $override->attempts = max($attempts);
            }
        }
        if (is_null($override->password) && count($passwords)) {
            $override->password = array_shift($passwords);
            if (count($passwords)) {
                $override->extrapasswords = $passwords;
            }
        }

    }

        $keys = array('timeopen', 'timeclose', 'timelimit', 'attempts', 'password', 'extrapasswords');
    foreach ($keys as $key) {
        if (isset($override->{$key})) {
            $quiz->{$key} = $override->{$key};
        }
    }

    return $quiz;
}


function quiz_delete_all_attempts($quiz) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');
    question_engine::delete_questions_usage_by_activities(new qubaids_for_quiz($quiz->id));
    $DB->delete_records('quiz_attempts', array('quiz' => $quiz->id));
    $DB->delete_records('quiz_grades', array('quiz' => $quiz->id));
}


function quiz_get_best_grade($quiz, $userid) {
    global $DB;
    $grade = $DB->get_field('quiz_grades', 'grade',
            array('quiz' => $quiz->id, 'userid' => $userid));

        if ($grade === false) {
        return null;
    }

    return $grade + 0; }


function quiz_has_grades($quiz) {
    return $quiz->grade >= 0.000005 && $quiz->sumgrades >= 0.000005;
}


function quiz_allows_multiple_tries($quiz) {
    $bt = question_engine::get_behaviour_type($quiz->preferredbehaviour);
    return $bt->allows_multiple_submitted_responses();
}


function quiz_user_outline($course, $user, $mod, $quiz) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    $grades = grade_get_grades($course->id, 'mod', 'quiz', $quiz->id, $user->id);

    if (empty($grades->items[0]->grades)) {
        return null;
    } else {
        $grade = reset($grades->items[0]->grades);
    }

    $result = new stdClass();
        $gitem = grade_item::fetch(array('id' => $grades->items[0]->id));
    if (!$gitem->hidden || has_capability('moodle/grade:viewhidden', context_course::instance($course->id))) {
        $result->info = get_string('grade') . ': ' . $grade->str_long_grade;
    } else {
        $result->info = get_string('grade') . ': ' . get_string('hidden', 'grades');
    }

                    if ($grade->usermodified == $user->id || empty($grade->datesubmitted)) {
        $result->time = $grade->dategraded;
    } else {
        $result->time = $grade->datesubmitted;
    }

    return $result;
}


function quiz_user_complete($course, $user, $mod, $quiz) {
    global $DB, $CFG, $OUTPUT;
    require_once($CFG->libdir . '/gradelib.php');
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');

    $grades = grade_get_grades($course->id, 'mod', 'quiz', $quiz->id, $user->id);
    if (!empty($grades->items[0]->grades)) {
        $grade = reset($grades->items[0]->grades);
                $gitem = grade_item::fetch(array('id' => $grades->items[0]->id));
        if (!$gitem->hidden || has_capability('moodle/grade:viewhidden', context_course::instance($course->id))) {
            echo $OUTPUT->container(get_string('grade').': '.$grade->str_long_grade);
            if ($grade->str_feedback) {
                echo $OUTPUT->container(get_string('feedback').': '.$grade->str_feedback);
            }
        } else {
            echo $OUTPUT->container(get_string('grade') . ': ' . get_string('hidden', 'grades'));
            if ($grade->str_feedback) {
                echo $OUTPUT->container(get_string('feedback').': '.get_string('hidden', 'grades'));
            }
        }
    }

    if ($attempts = $DB->get_records('quiz_attempts',
            array('userid' => $user->id, 'quiz' => $quiz->id), 'attempt')) {
        foreach ($attempts as $attempt) {
            echo get_string('attempt', 'quiz', $attempt->attempt) . ': ';
            if ($attempt->state != quiz_attempt::FINISHED) {
                echo quiz_attempt_state_name($attempt->state);
            } else {
                if (!isset($gitem)) {
                    if (!empty($grades->items[0]->grades)) {
                        $gitem = grade_item::fetch(array('id' => $grades->items[0]->id));
                    } else {
                        $gitem = new stdClass();
                        $gitem->hidden = true;
                    }
                }
                if (!$gitem->hidden || has_capability('moodle/grade:viewhidden', context_course::instance($course->id))) {
                    echo quiz_format_grade($quiz, $attempt->sumgrades) . '/' . quiz_format_grade($quiz, $quiz->sumgrades);
                } else {
                    echo get_string('hidden', 'grades');
                }
            }
            echo ' - '.userdate($attempt->timemodified).'<br />';
        }
    } else {
        print_string('noattempts', 'quiz');
    }

    return true;
}


function quiz_cron() {
    global $CFG;

    require_once($CFG->dirroot . '/mod/quiz/cronlib.php');
    mtrace('');

    $timenow = time();
    $overduehander = new mod_quiz_overdue_attempt_updater();

    $processto = $timenow - get_config('quiz', 'graceperiodmin');

    mtrace('  Looking for quiz overdue quiz attempts...');

    list($count, $quizcount) = $overduehander->update_overdue_attempts($timenow, $processto);

    mtrace('  Considered ' . $count . ' attempts in ' . $quizcount . ' quizzes.');

        cron_execute_plugin_type('quiz', 'quiz reports');
    cron_execute_plugin_type('quizaccess', 'quiz access rules');

    return true;
}


function quiz_get_user_attempts($quizids, $userid, $status = 'finished', $includepreviews = false) {
    global $DB, $CFG;
                    require_once($CFG->dirroot . '/mod/quiz/locallib.php');

    $params = array();
    switch ($status) {
        case 'all':
            $statuscondition = '';
            break;

        case 'finished':
            $statuscondition = ' AND state IN (:state1, :state2)';
            $params['state1'] = quiz_attempt::FINISHED;
            $params['state2'] = quiz_attempt::ABANDONED;
            break;

        case 'unfinished':
            $statuscondition = ' AND state IN (:state1, :state2)';
            $params['state1'] = quiz_attempt::IN_PROGRESS;
            $params['state2'] = quiz_attempt::OVERDUE;
            break;
    }

    $quizids = (array) $quizids;
    list($insql, $inparams) = $DB->get_in_or_equal($quizids, SQL_PARAMS_NAMED);
    $params += $inparams;
    $params['userid'] = $userid;

    $previewclause = '';
    if (!$includepreviews) {
        $previewclause = ' AND preview = 0';
    }

    return $DB->get_records_select('quiz_attempts',
            "quiz $insql AND userid = :userid" . $previewclause . $statuscondition,
            $params, 'quiz, attempt ASC');
}


function quiz_get_user_grades($quiz, $userid = 0) {
    global $CFG, $DB;

    $params = array($quiz->id);
    $usertest = '';
    if ($userid) {
        $params[] = $userid;
        $usertest = 'AND u.id = ?';
    }
    return $DB->get_records_sql("
            SELECT
                u.id,
                u.id AS userid,
                qg.grade AS rawgrade,
                qg.timemodified AS dategraded,
                MAX(qa.timefinish) AS datesubmitted

            FROM {user} u
            JOIN {quiz_grades} qg ON u.id = qg.userid
            JOIN {quiz_attempts} qa ON qa.quiz = qg.quiz AND qa.userid = u.id

            WHERE qg.quiz = ?
            $usertest
            GROUP BY u.id, qg.grade, qg.timemodified", $params);
}


function quiz_format_grade($quiz, $grade) {
    if (is_null($grade)) {
        return get_string('notyetgraded', 'quiz');
    }
    return format_float($grade, $quiz->decimalpoints);
}


function quiz_get_grade_format($quiz) {
    if (empty($quiz->questiondecimalpoints)) {
        $quiz->questiondecimalpoints = -1;
    }

    if ($quiz->questiondecimalpoints == -1) {
        return $quiz->decimalpoints;
    }

    return $quiz->questiondecimalpoints;
}


function quiz_format_question_grade($quiz, $grade) {
    return format_float($grade, quiz_get_grade_format($quiz));
}


function quiz_update_grades($quiz, $userid = 0, $nullifnone = true) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    if ($quiz->grade == 0) {
        quiz_grade_item_update($quiz);

    } else if ($grades = quiz_get_user_grades($quiz, $userid)) {
        quiz_grade_item_update($quiz, $grades);

    } else if ($userid && $nullifnone) {
        $grade = new stdClass();
        $grade->userid = $userid;
        $grade->rawgrade = null;
        quiz_grade_item_update($quiz, $grade);

    } else {
        quiz_grade_item_update($quiz);
    }
}


function quiz_grade_item_update($quiz, $grades = null) {
    global $CFG, $OUTPUT;
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');
    require_once($CFG->libdir . '/gradelib.php');

    if (array_key_exists('cmidnumber', $quiz)) {         $params = array('itemname' => $quiz->name, 'idnumber' => $quiz->cmidnumber);
    } else {
        $params = array('itemname' => $quiz->name);
    }

    if ($quiz->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $quiz->grade;
        $params['grademin']  = 0;

    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

                                $openreviewoptions = mod_quiz_display_options::make_from_quiz($quiz,
            mod_quiz_display_options::LATER_WHILE_OPEN);
    $closedreviewoptions = mod_quiz_display_options::make_from_quiz($quiz,
            mod_quiz_display_options::AFTER_CLOSE);
    if ($openreviewoptions->marks < question_display_options::MARK_AND_MAX &&
            $closedreviewoptions->marks < question_display_options::MARK_AND_MAX) {
        $params['hidden'] = 1;

    } else if ($openreviewoptions->marks < question_display_options::MARK_AND_MAX &&
            $closedreviewoptions->marks >= question_display_options::MARK_AND_MAX) {
        if ($quiz->timeclose) {
            $params['hidden'] = $quiz->timeclose;
        } else {
            $params['hidden'] = 1;
        }

    } else {
                                        $params['hidden'] = 0;
    }

    if (!$params['hidden']) {
                        if (property_exists($quiz, 'visible')) {
                        $params['hidden'] = !$quiz->visible;
        } else {
            $cm = get_coursemodule_from_instance('quiz', $quiz->id);
            $params['hidden'] = !$cm->visible;
        }
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    $gradebook_grades = grade_get_grades($quiz->course, 'mod', 'quiz', $quiz->id);
    if (!empty($gradebook_grades->items)) {
        $grade_item = $gradebook_grades->items[0];
        if ($grade_item->locked) {
                        $confirm_regrade = optional_param('confirm_regrade', 0, PARAM_INT);
            if (!$confirm_regrade) {
                if (!AJAX_SCRIPT) {
                    $message = get_string('gradeitemislocked', 'grades');
                    $back_link = $CFG->wwwroot . '/mod/quiz/report.php?q=' . $quiz->id .
                            '&amp;mode=overview';
                    $regrade_link = qualified_me() . '&amp;confirm_regrade=1';
                    echo $OUTPUT->box_start('generalbox', 'notice');
                    echo '<p>'. $message .'</p>';
                    echo $OUTPUT->container_start('buttons');
                    echo $OUTPUT->single_button($regrade_link, get_string('regradeanyway', 'grades'));
                    echo $OUTPUT->single_button($back_link,  get_string('cancel'));
                    echo $OUTPUT->container_end();
                    echo $OUTPUT->box_end();
                }
                return GRADE_UPDATE_ITEM_LOCKED;
            }
        }
    }

    return grade_update('mod/quiz', $quiz->course, 'mod', 'quiz', $quiz->id, 0, $grades, $params);
}


function quiz_grade_item_delete($quiz) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update('mod/quiz', $quiz->course, 'mod', 'quiz', $quiz->id, 0,
            null, array('deleted' => 1));
}


function quiz_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid == 0) {
        if (!$quizzes = $DB->get_records('quiz')) {
            return true;
        }
    } else {
        if (!$quizzes = $DB->get_records('quiz', array('course' => $courseid))) {
            return true;
        }
    }

    foreach ($quizzes as $quiz) {
        quiz_update_events($quiz);
    }

    return true;
}


function quiz_get_recent_mod_activity(&$activities, &$index, $timestart,
        $courseid, $cmid, $userid = 0, $groupid = 0) {
    global $CFG, $USER, $DB;
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');

    $course = get_course($courseid);
    $modinfo = get_fast_modinfo($course);

    $cm = $modinfo->cms[$cmid];
    $quiz = $DB->get_record('quiz', array('id' => $cm->instance));

    if ($userid) {
        $userselect = "AND u.id = :userid";
        $params['userid'] = $userid;
    } else {
        $userselect = '';
    }

    if ($groupid) {
        $groupselect = 'AND gm.groupid = :groupid';
        $groupjoin   = 'JOIN {groups_members} gm ON  gm.userid=u.id';
        $params['groupid'] = $groupid;
    } else {
        $groupselect = '';
        $groupjoin   = '';
    }

    $params['timestart'] = $timestart;
    $params['quizid'] = $quiz->id;

    $ufields = user_picture::fields('u', null, 'useridagain');
    if (!$attempts = $DB->get_records_sql("
              SELECT qa.*,
                     {$ufields}
                FROM {quiz_attempts} qa
                     JOIN {user} u ON u.id = qa.userid
                     $groupjoin
               WHERE qa.timefinish > :timestart
                 AND qa.quiz = :quizid
                 AND qa.preview = 0
                     $userselect
                     $groupselect
            ORDER BY qa.timefinish ASC", $params)) {
        return;
    }

    $context         = context_module::instance($cm->id);
    $accessallgroups = has_capability('moodle/site:accessallgroups', $context);
    $viewfullnames   = has_capability('moodle/site:viewfullnames', $context);
    $grader          = has_capability('mod/quiz:viewreports', $context);
    $groupmode       = groups_get_activity_groupmode($cm, $course);

    $usersgroups = null;
    $aname = format_string($cm->name, true);
    foreach ($attempts as $attempt) {
        if ($attempt->userid != $USER->id) {
            if (!$grader) {
                                continue;
            }

            if ($groupmode == SEPARATEGROUPS and !$accessallgroups) {
                $usersgroups = groups_get_all_groups($course->id,
                        $attempt->userid, $cm->groupingid);
                $usersgroups = array_keys($usersgroups);
                if (!array_intersect($usersgroups, $modinfo->get_groups($cm->groupingid))) {
                    continue;
                }
            }
        }

        $options = quiz_get_review_options($quiz, $attempt, $context);

        $tmpactivity = new stdClass();

        $tmpactivity->type       = 'quiz';
        $tmpactivity->cmid       = $cm->id;
        $tmpactivity->name       = $aname;
        $tmpactivity->sectionnum = $cm->sectionnum;
        $tmpactivity->timestamp  = $attempt->timefinish;

        $tmpactivity->content = new stdClass();
        $tmpactivity->content->attemptid = $attempt->id;
        $tmpactivity->content->attempt   = $attempt->attempt;
        if (quiz_has_grades($quiz) && $options->marks >= question_display_options::MARK_AND_MAX) {
            $tmpactivity->content->sumgrades = quiz_format_grade($quiz, $attempt->sumgrades);
            $tmpactivity->content->maxgrade  = quiz_format_grade($quiz, $quiz->sumgrades);
        } else {
            $tmpactivity->content->sumgrades = null;
            $tmpactivity->content->maxgrade  = null;
        }

        $tmpactivity->user = user_picture::unalias($attempt, null, 'useridagain');
        $tmpactivity->user->fullname  = fullname($tmpactivity->user, $viewfullnames);

        $activities[$index++] = $tmpactivity;
    }
}

function quiz_print_recent_mod_activity($activity, $courseid, $detail, $modnames) {
    global $CFG, $OUTPUT;

    echo '<table border="0" cellpadding="3" cellspacing="0" class="forum-recent">';

    echo '<tr><td class="userpicture" valign="top">';
    echo $OUTPUT->user_picture($activity->user, array('courseid' => $courseid));
    echo '</td><td>';

    if ($detail) {
        $modname = $modnames[$activity->type];
        echo '<div class="title">';
        echo '<img src="' . $OUTPUT->pix_url('icon', $activity->type) . '" ' .
                'class="icon" alt="' . $modname . '" />';
        echo '<a href="' . $CFG->wwwroot . '/mod/quiz/view.php?id=' .
                $activity->cmid . '">' . $activity->name . '</a>';
        echo '</div>';
    }

    echo '<div class="grade">';
    echo  get_string('attempt', 'quiz', $activity->content->attempt);
    if (isset($activity->content->maxgrade)) {
        $grades = $activity->content->sumgrades . ' / ' . $activity->content->maxgrade;
        echo ': (<a href="' . $CFG->wwwroot . '/mod/quiz/review.php?attempt=' .
                $activity->content->attemptid . '">' . $grades . '</a>)';
    }
    echo '</div>';

    echo '<div class="user">';
    echo '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $activity->user->id .
            '&amp;course=' . $courseid . '">' . $activity->user->fullname .
            '</a> - ' . userdate($activity->timestamp);
    echo '</div>';

    echo '</td></tr></table>';

    return;
}


function quiz_process_options($quiz) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');
    require_once($CFG->libdir . '/questionlib.php');

    $quiz->timemodified = time();

        if (!empty($quiz->name)) {
        $quiz->name = trim($quiz->name);
    }

            $quiz->password = $quiz->quizpassword;
    unset($quiz->quizpassword);

        if (isset($quiz->feedbacktext)) {
                for ($i = 0; $i < count($quiz->feedbacktext); $i += 1) {
            if (empty($quiz->feedbacktext[$i]['text'])) {
                $quiz->feedbacktext[$i]['text'] = '';
            } else {
                $quiz->feedbacktext[$i]['text'] = trim($quiz->feedbacktext[$i]['text']);
            }
        }

                $i = 0;
        while (!empty($quiz->feedbackboundaries[$i])) {
            $boundary = trim($quiz->feedbackboundaries[$i]);
            if (!is_numeric($boundary)) {
                if (strlen($boundary) > 0 && $boundary[strlen($boundary) - 1] == '%') {
                    $boundary = trim(substr($boundary, 0, -1));
                    if (is_numeric($boundary)) {
                        $boundary = $boundary * $quiz->grade / 100.0;
                    } else {
                        return get_string('feedbackerrorboundaryformat', 'quiz', $i + 1);
                    }
                }
            }
            if ($boundary <= 0 || $boundary >= $quiz->grade) {
                return get_string('feedbackerrorboundaryoutofrange', 'quiz', $i + 1);
            }
            if ($i > 0 && $boundary >= $quiz->feedbackboundaries[$i - 1]) {
                return get_string('feedbackerrororder', 'quiz', $i + 1);
            }
            $quiz->feedbackboundaries[$i] = $boundary;
            $i += 1;
        }
        $numboundaries = $i;

                if (!empty($quiz->feedbackboundaries)) {
            for ($i = $numboundaries; $i < count($quiz->feedbackboundaries); $i += 1) {
                if (!empty($quiz->feedbackboundaries[$i]) &&
                        trim($quiz->feedbackboundaries[$i]) != '') {
                    return get_string('feedbackerrorjunkinboundary', 'quiz', $i + 1);
                }
            }
        }
        for ($i = $numboundaries + 1; $i < count($quiz->feedbacktext); $i += 1) {
            if (!empty($quiz->feedbacktext[$i]['text']) &&
                    trim($quiz->feedbacktext[$i]['text']) != '') {
                return get_string('feedbackerrorjunkinfeedback', 'quiz', $i + 1);
            }
        }
                $quiz->feedbackboundaries[-1] = $quiz->grade + 1;
        $quiz->feedbackboundaries[$numboundaries] = 0;
        $quiz->feedbackboundarycount = $numboundaries;
    } else {
        $quiz->feedbackboundarycount = -1;
    }

        $quiz->reviewattempt = quiz_review_option_form_to_db($quiz, 'attempt');
    $quiz->reviewcorrectness = quiz_review_option_form_to_db($quiz, 'correctness');
    $quiz->reviewmarks = quiz_review_option_form_to_db($quiz, 'marks');
    $quiz->reviewspecificfeedback = quiz_review_option_form_to_db($quiz, 'specificfeedback');
    $quiz->reviewgeneralfeedback = quiz_review_option_form_to_db($quiz, 'generalfeedback');
    $quiz->reviewrightanswer = quiz_review_option_form_to_db($quiz, 'rightanswer');
    $quiz->reviewoverallfeedback = quiz_review_option_form_to_db($quiz, 'overallfeedback');
    $quiz->reviewattempt |= mod_quiz_display_options::DURING;
    $quiz->reviewoverallfeedback &= ~mod_quiz_display_options::DURING;
}


function quiz_review_option_form_to_db($fromform, $field) {
    static $times = array(
        'during' => mod_quiz_display_options::DURING,
        'immediately' => mod_quiz_display_options::IMMEDIATELY_AFTER,
        'open' => mod_quiz_display_options::LATER_WHILE_OPEN,
        'closed' => mod_quiz_display_options::AFTER_CLOSE,
    );

    $review = 0;
    foreach ($times as $whenname => $when) {
        $fieldname = $field . $whenname;
        if (isset($fromform->$fieldname)) {
            $review |= $when;
            unset($fromform->$fieldname);
        }
    }

    return $review;
}


function quiz_after_add_or_update($quiz) {
    global $DB;
    $cmid = $quiz->coursemodule;

        $DB->set_field('course_modules', 'instance', $quiz->id, array('id'=>$cmid));
    $context = context_module::instance($cmid);

        $DB->delete_records('quiz_feedback', array('quizid' => $quiz->id));

    for ($i = 0; $i <= $quiz->feedbackboundarycount; $i++) {
        $feedback = new stdClass();
        $feedback->quizid = $quiz->id;
        $feedback->feedbacktext = $quiz->feedbacktext[$i]['text'];
        $feedback->feedbacktextformat = $quiz->feedbacktext[$i]['format'];
        $feedback->mingrade = $quiz->feedbackboundaries[$i];
        $feedback->maxgrade = $quiz->feedbackboundaries[$i - 1];
        $feedback->id = $DB->insert_record('quiz_feedback', $feedback);
        $feedbacktext = file_save_draft_area_files((int)$quiz->feedbacktext[$i]['itemid'],
                $context->id, 'mod_quiz', 'feedback', $feedback->id,
                array('subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0),
                $quiz->feedbacktext[$i]['text']);
        $DB->set_field('quiz_feedback', 'feedbacktext', $feedbacktext,
                array('id' => $feedback->id));
    }

        quiz_access_manager::save_settings($quiz);

        quiz_update_events($quiz);

        quiz_grade_item_update($quiz);
}


function quiz_update_events($quiz, $override = null) {
    global $DB;

        $conds = array('modulename'=>'quiz',
                   'instance'=>$quiz->id);
    if (!empty($override)) {
                if (isset($override->userid)) {
            $conds['userid'] = $override->userid;
        } else if (isset($override->groupid)) {
            $conds['groupid'] = $override->groupid;
        }
    }
    $oldevents = $DB->get_records('event', $conds);

        if (empty($override)) {
                        $overrides = $DB->get_records('quiz_overrides', array('quiz' => $quiz->id));
                $overrides[] = new stdClass();
    } else {
                $overrides = array($override);
    }

    foreach ($overrides as $current) {
        $groupid   = isset($current->groupid)?  $current->groupid : 0;
        $userid    = isset($current->userid)? $current->userid : 0;
        $timeopen  = isset($current->timeopen)?  $current->timeopen : $quiz->timeopen;
        $timeclose = isset($current->timeclose)? $current->timeclose : $quiz->timeclose;

                $addopen  = empty($current->id) || !empty($current->timeopen);
        $addclose = empty($current->id) || !empty($current->timeclose);

        if (!empty($quiz->coursemodule)) {
            $cmid = $quiz->coursemodule;
        } else {
            $cmid = get_coursemodule_from_instance('quiz', $quiz->id, $quiz->course)->id;
        }

        $event = new stdClass();
        $event->description = format_module_intro('quiz', $quiz, $cmid);
                $event->courseid    = ($userid) ? 0 : $quiz->course;
        $event->groupid     = $groupid;
        $event->userid      = $userid;
        $event->modulename  = 'quiz';
        $event->instance    = $quiz->id;
        $event->timestart   = $timeopen;
        $event->timeduration = max($timeclose - $timeopen, 0);
        $event->visible     = instance_is_visible('quiz', $quiz);
        $event->eventtype   = 'open';

                if ($groupid) {
            $params = new stdClass();
            $params->quiz = $quiz->name;
            $params->group = groups_get_group_name($groupid);
            if ($params->group === false) {
                                continue;
            }
            $eventname = get_string('overridegroupeventname', 'quiz', $params);
        } else if ($userid) {
            $params = new stdClass();
            $params->quiz = $quiz->name;
            $eventname = get_string('overrideusereventname', 'quiz', $params);
        } else {
            $eventname = $quiz->name;
        }
        if ($addopen or $addclose) {
            if ($timeclose and $timeopen and $event->timeduration <= QUIZ_MAX_EVENT_LENGTH) {
                                if ($oldevent = array_shift($oldevents)) {
                    $event->id = $oldevent->id;
                } else {
                    unset($event->id);
                }
                $event->name = $eventname;
                                calendar_event::create($event);
            } else {
                                $event->timeduration  = 0;
                if ($timeopen && $addopen) {
                    if ($oldevent = array_shift($oldevents)) {
                        $event->id = $oldevent->id;
                    } else {
                        unset($event->id);
                    }
                    $event->name = $eventname.' ('.get_string('quizopens', 'quiz').')';
                                        calendar_event::create($event);
                }
                if ($timeclose && $addclose) {
                    if ($oldevent = array_shift($oldevents)) {
                        $event->id = $oldevent->id;
                    } else {
                        unset($event->id);
                    }
                    $event->name      = $eventname.' ('.get_string('quizcloses', 'quiz').')';
                    $event->timestart = $timeclose;
                    $event->eventtype = 'close';
                    calendar_event::create($event);
                }
            }
        }
    }

        foreach ($oldevents as $badevent) {
        $badevent = calendar_event::load($badevent);
        $badevent->delete();
    }
}


function quiz_get_view_actions() {
    return array('view', 'view all', 'report', 'review');
}


function quiz_get_post_actions() {
    return array('attempt', 'close attempt', 'preview', 'editquestions',
            'delete attempt', 'manualgrade');
}


function quiz_questions_in_use($questionids) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/questionlib.php');
    list($test, $params) = $DB->get_in_or_equal($questionids);
    return $DB->record_exists_select('quiz_slots',
            'questionid ' . $test, $params) || question_engine::questions_in_use(
            $questionids, new qubaid_join('{quiz_attempts} quiza',
            'quiza.uniqueid', 'quiza.preview = 0'));
}


function quiz_reset_course_form_definition($mform) {
    $mform->addElement('header', 'quizheader', get_string('modulenameplural', 'quiz'));
    $mform->addElement('advcheckbox', 'reset_quiz_attempts',
            get_string('removeallquizattempts', 'quiz'));
    $mform->addElement('advcheckbox', 'reset_quiz_user_overrides',
            get_string('removealluseroverrides', 'quiz'));
    $mform->addElement('advcheckbox', 'reset_quiz_group_overrides',
            get_string('removeallgroupoverrides', 'quiz'));
}


function quiz_reset_course_form_defaults($course) {
    return array('reset_quiz_attempts' => 1,
                 'reset_quiz_group_overrides' => 1,
                 'reset_quiz_user_overrides' => 1);
}


function quiz_reset_gradebook($courseid, $type='') {
    global $CFG, $DB;

    $quizzes = $DB->get_records_sql("
            SELECT q.*, cm.idnumber as cmidnumber, q.course as courseid
            FROM {modules} m
            JOIN {course_modules} cm ON m.id = cm.module
            JOIN {quiz} q ON cm.instance = q.id
            WHERE m.name = 'quiz' AND cm.course = ?", array($courseid));

    foreach ($quizzes as $quiz) {
        quiz_grade_item_update($quiz, 'reset');
    }
}


function quiz_reset_userdata($data) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/questionlib.php');

    $componentstr = get_string('modulenameplural', 'quiz');
    $status = array();

        if (!empty($data->reset_quiz_attempts)) {
        question_engine::delete_questions_usage_by_activities(new qubaid_join(
                '{quiz_attempts} quiza JOIN {quiz} quiz ON quiza.quiz = quiz.id',
                'quiza.uniqueid', 'quiz.course = :quizcourseid',
                array('quizcourseid' => $data->courseid)));

        $DB->delete_records_select('quiz_attempts',
                'quiz IN (SELECT id FROM {quiz} WHERE course = ?)', array($data->courseid));
        $status[] = array(
            'component' => $componentstr,
            'item' => get_string('attemptsdeleted', 'quiz'),
            'error' => false);

                $DB->delete_records_select('quiz_grades',
                'quiz IN (SELECT id FROM {quiz} WHERE course = ?)', array($data->courseid));
        if (empty($data->reset_gradebook_grades)) {
            quiz_reset_gradebook($data->courseid);
        }
        $status[] = array(
            'component' => $componentstr,
            'item' => get_string('gradesdeleted', 'quiz'),
            'error' => false);
    }

        if (!empty($data->reset_quiz_user_overrides)) {
        $DB->delete_records_select('quiz_overrides',
                'quiz IN (SELECT id FROM {quiz} WHERE course = ?) AND userid IS NOT NULL', array($data->courseid));
        $status[] = array(
            'component' => $componentstr,
            'item' => get_string('useroverridesdeleted', 'quiz'),
            'error' => false);
    }
        if (!empty($data->reset_quiz_group_overrides)) {
        $DB->delete_records_select('quiz_overrides',
                'quiz IN (SELECT id FROM {quiz} WHERE course = ?) AND groupid IS NOT NULL', array($data->courseid));
        $status[] = array(
            'component' => $componentstr,
            'item' => get_string('groupoverridesdeleted', 'quiz'),
            'error' => false);
    }

        if ($data->timeshift) {
        $DB->execute("UPDATE {quiz_overrides}
                         SET timeopen = timeopen + ?
                       WHERE quiz IN (SELECT id FROM {quiz} WHERE course = ?)
                         AND timeopen <> 0", array($data->timeshift, $data->courseid));
        $DB->execute("UPDATE {quiz_overrides}
                         SET timeclose = timeclose + ?
                       WHERE quiz IN (SELECT id FROM {quiz} WHERE course = ?)
                         AND timeclose <> 0", array($data->timeshift, $data->courseid));

        shift_course_mod_dates('quiz', array('timeopen', 'timeclose'),
                $data->timeshift, $data->courseid);

        $status[] = array(
            'component' => $componentstr,
            'item' => get_string('openclosedatesupdated', 'quiz'),
            'error' => false);
    }

    return $status;
}


function quiz_print_overview($courses, &$htmlarray) {
    global $USER, $CFG;
        if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return array();
    }

    if (!$quizzes = get_all_instances_in_courses('quiz', $courses)) {
        return;
    }

        $attemptsinfo = [];
    $quizids = [];
    foreach ($quizzes as $quiz) {
        $quizids[] = $quiz->id;
        $attemptsinfo[$quiz->id] = ['count' => 0, 'hasfinished' => false];
    }
    $attempts = quiz_get_user_attempts($quizids, $USER->id);
    foreach ($attempts as $attempt) {
        $attemptsinfo[$attempt->quiz]['count']++;
        $attemptsinfo[$attempt->quiz]['hasfinished'] = true;
    }
    unset($attempts);

        $strquiz = get_string('modulename', 'quiz');
    $strnoattempts = get_string('noattempts', 'quiz');

            $now = time();
    foreach ($quizzes as $quiz) {
        if ($quiz->timeclose >= $now && $quiz->timeopen < $now) {
            $str = '';

                        $context = context_module::instance($quiz->coursemodule);
            if (has_capability('mod/quiz:viewreports', $context)) {
                                                                $str .= '<div class="info">' . quiz_num_attempt_summary($quiz, $quiz, true) . '</div>';

            } else if (has_any_capability(array('mod/quiz:reviewmyattempts', 'mod/quiz:attempt'), $context)) {                 
                if (isset($USER->id)) {
                    if ($attemptsinfo[$quiz->id]['hasfinished']) {
                                                continue;
                    }

                    if ($attemptsinfo[$quiz->id]['count'] > 0) {
                        $str .= '<div class="info">' .
                            get_string('numattemptsmade', 'quiz', $attemptsinfo[$quiz->id]['count']) . '</div>';
                    } else {
                        $str .= '<div class="info">' . $strnoattempts . '</div>';
                    }

                } else {
                    $str .= '<div class="info">' . $strnoattempts . '</div>';
                }

            } else {
                                continue;
            }

                        $html = '<div class="quiz overview">' .
                    '<div class="name">' . $strquiz . ': <a ' .
                    ($quiz->visible ? '' : ' class="dimmed"') .
                    ' href="' . $CFG->wwwroot . '/mod/quiz/view.php?id=' .
                    $quiz->coursemodule . '">' .
                    $quiz->name . '</a></div>';
            $html .= '<div class="info">' . get_string('quizcloseson', 'quiz',
                    userdate($quiz->timeclose)) . '</div>';
            $html .= $str;
            $html .= '</div>';
            if (empty($htmlarray[$quiz->course]['quiz'])) {
                $htmlarray[$quiz->course]['quiz'] = $html;
            } else {
                $htmlarray[$quiz->course]['quiz'] .= $html;
            }
        }
    }
}


function quiz_num_attempt_summary($quiz, $cm, $returnzero = false, $currentgroup = 0) {
    global $DB, $USER;
    $numattempts = $DB->count_records('quiz_attempts', array('quiz'=> $quiz->id, 'preview'=>0));
    if ($numattempts || $returnzero) {
        if (groups_get_activity_groupmode($cm)) {
            $a = new stdClass();
            $a->total = $numattempts;
            if ($currentgroup) {
                $a->group = $DB->count_records_sql('SELECT COUNT(DISTINCT qa.id) FROM ' .
                        '{quiz_attempts} qa JOIN ' .
                        '{groups_members} gm ON qa.userid = gm.userid ' .
                        'WHERE quiz = ? AND preview = 0 AND groupid = ?',
                        array($quiz->id, $currentgroup));
                return get_string('attemptsnumthisgroup', 'quiz', $a);
            } else if ($groups = groups_get_all_groups($cm->course, $USER->id, $cm->groupingid)) {
                list($usql, $params) = $DB->get_in_or_equal(array_keys($groups));
                $a->group = $DB->count_records_sql('SELECT COUNT(DISTINCT qa.id) FROM ' .
                        '{quiz_attempts} qa JOIN ' .
                        '{groups_members} gm ON qa.userid = gm.userid ' .
                        'WHERE quiz = ? AND preview = 0 AND ' .
                        "groupid $usql", array_merge(array($quiz->id), $params));
                return get_string('attemptsnumyourgroups', 'quiz', $a);
            }
        }
        return get_string('attemptsnum', 'quiz', $numattempts);
    }
    return '';
}


function quiz_attempt_summary_link_to_reports($quiz, $cm, $context, $returnzero = false,
        $currentgroup = 0) {
    global $CFG;
    $summary = quiz_num_attempt_summary($quiz, $cm, $returnzero, $currentgroup);
    if (!$summary) {
        return '';
    }

    require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
    $url = new moodle_url('/mod/quiz/report.php', array(
            'id' => $cm->id, 'mode' => quiz_report_default_report($context)));
    return html_writer::link($url, $summary);
}


function quiz_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                    return true;
        case FEATURE_GROUPINGS:                 return true;
        case FEATURE_MOD_INTRO:                 return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:   return true;
        case FEATURE_COMPLETION_HAS_RULES:      return true;
        case FEATURE_GRADE_HAS_GRADE:           return true;
        case FEATURE_GRADE_OUTCOMES:            return true;
        case FEATURE_BACKUP_MOODLE2:            return true;
        case FEATURE_SHOW_DESCRIPTION:          return true;
        case FEATURE_CONTROLS_GRADE_VISIBILITY: return true;
        case FEATURE_USES_QUESTIONS:            return true;

        default: return null;
    }
}


function quiz_get_extra_capabilities() {
    global $CFG;
    require_once($CFG->libdir . '/questionlib.php');
    $caps = question_get_all_capabilities();
    $caps[] = 'moodle/site:accessallgroups';
    return $caps;
}


function quiz_extend_settings_navigation($settings, $quiznode) {
    global $PAGE, $CFG;

            require_once($CFG->libdir . '/questionlib.php');

            $keys = $quiznode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false and array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    if (has_capability('mod/quiz:manageoverrides', $PAGE->cm->context)) {
        $url = new moodle_url('/mod/quiz/overrides.php', array('cmid'=>$PAGE->cm->id));
        $node = navigation_node::create(get_string('groupoverrides', 'quiz'),
                new moodle_url($url, array('mode'=>'group')),
                navigation_node::TYPE_SETTING, null, 'mod_quiz_groupoverrides');
        $quiznode->add_node($node, $beforekey);

        $node = navigation_node::create(get_string('useroverrides', 'quiz'),
                new moodle_url($url, array('mode'=>'user')),
                navigation_node::TYPE_SETTING, null, 'mod_quiz_useroverrides');
        $quiznode->add_node($node, $beforekey);
    }

    if (has_capability('mod/quiz:manage', $PAGE->cm->context)) {
        $node = navigation_node::create(get_string('editquiz', 'quiz'),
                new moodle_url('/mod/quiz/edit.php', array('cmid'=>$PAGE->cm->id)),
                navigation_node::TYPE_SETTING, null, 'mod_quiz_edit',
                new pix_icon('t/edit', ''));
        $quiznode->add_node($node, $beforekey);
    }

    if (has_capability('mod/quiz:preview', $PAGE->cm->context)) {
        $url = new moodle_url('/mod/quiz/startattempt.php',
                array('cmid'=>$PAGE->cm->id, 'sesskey'=>sesskey()));
        $node = navigation_node::create(get_string('preview', 'quiz'), $url,
                navigation_node::TYPE_SETTING, null, 'mod_quiz_preview',
                new pix_icon('i/preview', ''));
        $quiznode->add_node($node, $beforekey);
    }

    if (has_any_capability(array('mod/quiz:viewreports', 'mod/quiz:grade'), $PAGE->cm->context)) {
        require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
        $reportlist = quiz_report_list($PAGE->cm->context);

        $url = new moodle_url('/mod/quiz/report.php',
                array('id' => $PAGE->cm->id, 'mode' => reset($reportlist)));
        $reportnode = $quiznode->add_node(navigation_node::create(get_string('results', 'quiz'), $url,
                navigation_node::TYPE_SETTING,
                null, null, new pix_icon('i/report', '')), $beforekey);

        foreach ($reportlist as $report) {
            $url = new moodle_url('/mod/quiz/report.php',
                    array('id' => $PAGE->cm->id, 'mode' => $report));
            $reportnode->add_node(navigation_node::create(get_string($report, 'quiz_'.$report), $url,
                    navigation_node::TYPE_SETTING,
                    null, 'quiz_report_' . $report, new pix_icon('i/item', '')));
        }
    }

    question_extend_settings_navigation($quiznode, $PAGE->cm->context)->trim_if_empty();
}


function quiz_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, false, $cm);

    if (!$quiz = $DB->get_record('quiz', array('id'=>$cm->instance))) {
        return false;
    }

        $fileareas = array('feedback');
    if (!in_array($filearea, $fileareas)) {
        return false;
    }

    $feedbackid = (int)array_shift($args);
    if (!$feedback = $DB->get_record('quiz_feedback', array('id'=>$feedbackid))) {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_quiz/$filearea/$feedbackid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    send_stored_file($file, 0, 0, true, $options);
}


function quiz_question_pluginfile($course, $context, $component,
        $filearea, $qubaid, $slot, $args, $forcedownload, array $options=array()) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');

    $attemptobj = quiz_attempt::create_from_usage_id($qubaid);
    require_login($attemptobj->get_course(), false, $attemptobj->get_cm());

    if ($attemptobj->is_own_attempt() && !$attemptobj->is_finished()) {
                if (!$attemptobj->is_preview_user()) {
            $attemptobj->require_capability('mod/quiz:attempt');
        }
        $isreviewing = false;

    } else {
                $attemptobj->check_review_capability();
        $isreviewing = true;
    }

    if (!$attemptobj->check_file_access($slot, $isreviewing, $context->id,
            $component, $filearea, $args, $forcedownload)) {
        send_file_not_found();
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/$component/$filearea/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        send_file_not_found();
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}


function quiz_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array(
        'mod-quiz-*'       => get_string('page-mod-quiz-x', 'quiz'),
        'mod-quiz-view'    => get_string('page-mod-quiz-view', 'quiz'),
        'mod-quiz-attempt' => get_string('page-mod-quiz-attempt', 'quiz'),
        'mod-quiz-summary' => get_string('page-mod-quiz-summary', 'quiz'),
        'mod-quiz-review'  => get_string('page-mod-quiz-review', 'quiz'),
        'mod-quiz-edit'    => get_string('page-mod-quiz-edit', 'quiz'),
        'mod-quiz-report'  => get_string('page-mod-quiz-report', 'quiz'),
    );
    return $module_pagetype;
}


function quiz_get_navigation_options() {
    return array(
        QUIZ_NAVMETHOD_FREE => get_string('navmethod_free', 'quiz'),
        QUIZ_NAVMETHOD_SEQ  => get_string('navmethod_seq', 'quiz')
    );
}


function quiz_get_completion_state($course, $cm, $userid, $type) {
    global $DB;
    global $CFG;

    $quiz = $DB->get_record('quiz', array('id' => $cm->instance), '*', MUST_EXIST);
    if (!$quiz->completionattemptsexhausted && !$quiz->completionpass) {
        return $type;
    }

        if ($quiz->completionattemptsexhausted) {
        $attempts = quiz_get_user_attempts($quiz->id, $userid, 'finished', true);
        if ($attempts) {
            $lastfinishedattempt = end($attempts);
            $context = context_module::instance($cm->id);
            $quizobj = quiz::create($quiz->id, $userid);
            $accessmanager = new quiz_access_manager($quizobj, time(),
                    has_capability('mod/quiz:ignoretimelimits', $context, $userid, false));
            if ($accessmanager->is_finished(count($attempts), $lastfinishedattempt)) {
                return true;
            }
        }
    }

        if ($quiz->completionpass) {
        require_once($CFG->libdir . '/gradelib.php');
        $item = grade_item::fetch(array('courseid' => $course->id, 'itemtype' => 'mod',
                'itemmodule' => 'quiz', 'iteminstance' => $cm->instance, 'outcomeid' => null));
        if ($item) {
            $grades = grade_grade::fetch_users_grades($item, array($userid), false);
            if (!empty($grades[$userid])) {
                return $grades[$userid]->is_passed($item);
            }
        }
    }
    return false;
}
