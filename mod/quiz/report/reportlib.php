<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->libdir . '/filelib.php');


function quiz_report_index_by_keys($datum, $keys, $keysunique = true) {
    if (!$datum) {
        return array();
    }
    $key = array_shift($keys);
    $datumkeyed = array();
    foreach ($datum as $data) {
        if ($keys || !$keysunique) {
            $datumkeyed[$data->{$key}][]= $data;
        } else {
            $datumkeyed[$data->{$key}]= $data;
        }
    }
    if ($keys) {
        foreach ($datumkeyed as $datakey => $datakeyed) {
            $datumkeyed[$datakey] = quiz_report_index_by_keys($datakeyed, $keys, $keysunique);
        }
    }
    return $datumkeyed;
}

function quiz_report_unindex($datum) {
    if (!$datum) {
        return $datum;
    }
    $datumunkeyed = array();
    foreach ($datum as $value) {
        if (is_array($value)) {
            $datumunkeyed = array_merge($datumunkeyed, quiz_report_unindex($value));
        } else {
            $datumunkeyed[] = $value;
        }
    }
    return $datumunkeyed;
}


function quiz_has_questions($quizid) {
    global $DB;
    return $DB->record_exists('quiz_slots', array('quizid' => $quizid));
}


function quiz_report_get_significant_questions($quiz) {
    global $DB;

    $qsbyslot = $DB->get_records_sql("
            SELECT slot.slot,
                   q.id,
                   q.length,
                   slot.maxmark

              FROM {question} q
              JOIN {quiz_slots} slot ON slot.questionid = q.id

             WHERE slot.quizid = ?
               AND q.length > 0

          ORDER BY slot.slot", array($quiz->id));

    $number = 1;
    foreach ($qsbyslot as $question) {
        $question->number = $number;
        $number += $question->length;
    }

    return $qsbyslot;
}


function quiz_report_can_filter_only_graded($quiz) {
    return $quiz->attempts != 1 && $quiz->grademethod != QUIZ_GRADEAVERAGE;
}


function quiz_report_qm_filter_select($quiz, $quizattemptsalias = 'quiza') {
    if ($quiz->attempts == 1) {
                return '';
    }
    return quiz_report_grade_method_sql($quiz->grademethod, $quizattemptsalias);
}


function quiz_report_grade_method_sql($grademethod, $quizattemptsalias = 'quiza') {
    switch ($grademethod) {
        case QUIZ_GRADEHIGHEST :
            return "($quizattemptsalias.state = 'finished' AND NOT EXISTS (
                           SELECT 1 FROM {quiz_attempts} qa2
                            WHERE qa2.quiz = $quizattemptsalias.quiz AND
                                qa2.userid = $quizattemptsalias.userid AND
                                 qa2.state = 'finished' AND (
                COALESCE(qa2.sumgrades, 0) > COALESCE($quizattemptsalias.sumgrades, 0) OR
               (COALESCE(qa2.sumgrades, 0) = COALESCE($quizattemptsalias.sumgrades, 0) AND qa2.attempt < $quizattemptsalias.attempt)
                                )))";

        case QUIZ_GRADEAVERAGE :
            return '';

        case QUIZ_ATTEMPTFIRST :
            return "($quizattemptsalias.state = 'finished' AND NOT EXISTS (
                           SELECT 1 FROM {quiz_attempts} qa2
                            WHERE qa2.quiz = $quizattemptsalias.quiz AND
                                qa2.userid = $quizattemptsalias.userid AND
                                 qa2.state = 'finished' AND
                               qa2.attempt < $quizattemptsalias.attempt))";

        case QUIZ_ATTEMPTLAST :
            return "($quizattemptsalias.state = 'finished' AND NOT EXISTS (
                           SELECT 1 FROM {quiz_attempts} qa2
                            WHERE qa2.quiz = $quizattemptsalias.quiz AND
                                qa2.userid = $quizattemptsalias.userid AND
                                 qa2.state = 'finished' AND
                               qa2.attempt > $quizattemptsalias.attempt))";
    }
}


function quiz_report_grade_bands($bandwidth, $bands, $quizid, $userids = array()) {
    global $DB;
    if (!is_int($bands)) {
        debugging('$bands passed to quiz_report_grade_bands must be an integer. (' .
                gettype($bands) . ' passed.)', DEBUG_DEVELOPER);
        $bands = (int) $bands;
    }

    if ($userids) {
        list($usql, $params) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'u');
        $usql = "qg.userid $usql AND";
    } else {
        $usql = '';
        $params = array();
    }
    $sql = "
SELECT band, COUNT(1)

FROM (
    SELECT FLOOR(qg.grade / :bandwidth) AS band
      FROM {quiz_grades} qg
     WHERE $usql qg.quiz = :quizid
) subquery

GROUP BY
    band

ORDER BY
    band";

    $params['quizid'] = $quizid;
    $params['bandwidth'] = $bandwidth;

    $data = $DB->get_records_sql_menu($sql, $params);

        $data = $data + array_fill(0, $bands + 1, 0);
    ksort($data);

                $data[$bands - 1] += $data[$bands];
    unset($data[$bands]);

    return $data;
}

function quiz_report_highlighting_grading_method($quiz, $qmsubselect, $qmfilter) {
    if ($quiz->attempts == 1) {
        return '<p>' . get_string('onlyoneattemptallowed', 'quiz_overview') . '</p>';

    } else if (!$qmsubselect) {
        return '<p>' . get_string('allattemptscontributetograde', 'quiz_overview') . '</p>';

    } else if ($qmfilter) {
        return '<p>' . get_string('showinggraded', 'quiz_overview') . '</p>';

    } else {
        return '<p>' . get_string('showinggradedandungraded', 'quiz_overview',
                '<span class="gradedattempt">' . quiz_get_grading_option_name($quiz->grademethod) .
                '</span>') . '</p>';
    }
}


function quiz_report_feedback_for_grade($grade, $quizid, $context) {
    global $DB;

    static $feedbackcache = array();

    if (!isset($feedbackcache[$quizid])) {
        $feedbackcache[$quizid] = $DB->get_records('quiz_feedback', array('quizid' => $quizid));
    }

            $grade = max($grade, 0);

    $feedbacks = $feedbackcache[$quizid];
    $feedbackid = 0;
    $feedbacktext = '';
    $feedbacktextformat = FORMAT_MOODLE;
    foreach ($feedbacks as $feedback) {
        if ($feedback->mingrade <= $grade && $grade < $feedback->maxgrade) {
            $feedbackid = $feedback->id;
            $feedbacktext = $feedback->feedbacktext;
            $feedbacktextformat = $feedback->feedbacktextformat;
            break;
        }
    }

        $formatoptions = new stdClass();
    $formatoptions->noclean = true;
    $feedbacktext = file_rewrite_pluginfile_urls($feedbacktext, 'pluginfile.php',
            $context->id, 'mod_quiz', 'feedback', $feedbackid);
    $feedbacktext = format_text($feedbacktext, $feedbacktextformat, $formatoptions);

    return $feedbacktext;
}


function quiz_report_scale_summarks_as_percentage($rawmark, $quiz, $round = true) {
    if ($quiz->sumgrades == 0) {
        return '';
    }
    if (!is_numeric($rawmark)) {
        return $rawmark;
    }

    $mark = $rawmark * 100 / $quiz->sumgrades;
    if ($round) {
        $mark = quiz_format_grade($quiz, $mark);
    }
    return $mark . '%';
}


function quiz_report_list($context) {
    global $DB;
    static $reportlist = null;
    if (!is_null($reportlist)) {
        return $reportlist;
    }

    $reports = $DB->get_records('quiz_reports', null, 'displayorder DESC', 'name, capability');
    $reportdirs = core_component::get_plugin_list('quiz');

        $reportcaps = array();
    foreach ($reports as $key => $report) {
        if (array_key_exists($report->name, $reportdirs)) {
            $reportcaps[$report->name] = $report->capability;
        }
    }

        foreach ($reportdirs as $reportname => $notused) {
        if (!isset($reportcaps[$reportname])) {
            $reportcaps[$reportname] = null;
        }
    }
    $reportlist = array();
    foreach ($reportcaps as $name => $capability) {
        if (empty($capability)) {
            $capability = 'mod/quiz:viewreports';
        }
        if (has_capability($capability, $context)) {
            $reportlist[] = $name;
        }
    }
    return $reportlist;
}


function quiz_report_download_filename($report, $courseshortname, $quizname) {
    return $courseshortname . '-' . format_string($quizname, true) . '-' . $report;
}


function quiz_report_default_report($context) {
    $reports = quiz_report_list($context);
    return reset($reports);
}


function quiz_no_questions_message($quiz, $cm, $context) {
    global $OUTPUT;

    $output = '';
    $output .= $OUTPUT->notification(get_string('noquestions', 'quiz'));
    if (has_capability('mod/quiz:manage', $context)) {
        $output .= $OUTPUT->single_button(new moodle_url('/mod/quiz/edit.php',
        array('cmid' => $cm->id)), get_string('editquiz', 'quiz'), 'get');
    }

    return $output;
}


function quiz_report_should_show_grades($quiz, context $context) {
    if ($quiz->timeclose && time() > $quiz->timeclose) {
        $when = mod_quiz_display_options::AFTER_CLOSE;
    } else {
        $when = mod_quiz_display_options::LATER_WHILE_OPEN;
    }
    $reviewoptions = mod_quiz_display_options::make_from_quiz($quiz, $when);

    return quiz_has_grades($quiz) &&
            ($reviewoptions->marks >= question_display_options::MARK_AND_MAX ||
            has_capability('moodle/grade:viewhidden', $context));
}
