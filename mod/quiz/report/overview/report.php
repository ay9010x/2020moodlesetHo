<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport.php');
require_once($CFG->dirroot . '/mod/quiz/report/overview/overview_options.php');
require_once($CFG->dirroot . '/mod/quiz/report/overview/overview_form.php');
require_once($CFG->dirroot . '/mod/quiz/report/overview/overview_table.php');



class quiz_overview_report extends quiz_attempts_report {

    public function display($quiz, $cm, $course) {
        global $CFG, $DB, $OUTPUT, $PAGE;

        list($currentgroup, $students, $groupstudents, $allowed) =
                $this->init('overview', 'quiz_overview_settings_form', $quiz, $cm, $course);
        $options = new quiz_overview_options('overview', $quiz, $cm, $course);

        if ($fromform = $this->form->get_data()) {
            $options->process_settings_from_form($fromform);

        } else {
            $options->process_settings_from_params();
        }

        $this->form->set_data($options->get_initial_form_data());

        if ($options->attempts == self::ALL_WITH) {
                                                $allowed = array();
        }

                $questions = quiz_report_get_significant_questions($quiz);

                $courseshortname = format_string($course->shortname, true,
                array('context' => context_course::instance($course->id)));
        $table = new quiz_overview_table($quiz, $this->context, $this->qmsubselect,
                $options, $groupstudents, $students, $questions, $options->get_url());
        $filename = quiz_report_download_filename(get_string('overviewfilename', 'quiz_overview'),
                $courseshortname, $quiz->name);
        $table->is_downloading($options->download, $filename,
                $courseshortname . ' ' . format_string($quiz->name, true));
        if ($table->is_downloading()) {
            raise_memory_limit(MEMORY_EXTRA);
        }

        $this->course = $course;         $this->process_actions($quiz, $cm, $currentgroup, $groupstudents, $allowed, $options->get_url());

                if (!$table->is_downloading()) {
                        $this->print_header_and_tabs($cm, $course, $quiz, $this->mode);
        }

        if ($groupmode = groups_get_activity_groupmode($cm)) {
                        if (!$table->is_downloading()) {
                groups_print_activity_menu($cm, $options->get_url());
            }
        }

                if (!$table->is_downloading()) {
                        if ($strattemptnum = quiz_num_attempt_summary($quiz, $cm, true, $currentgroup)) {
                echo '<div class="quizattemptcounts">' . $strattemptnum . '</div>';
            }
        }

        $hasquestions = quiz_has_questions($quiz->id);
        if (!$table->is_downloading()) {
            if (!$hasquestions) {
                echo quiz_no_questions_message($quiz, $cm, $this->context);
            } else if (!$students) {
                echo $OUTPUT->notification(get_string('nostudentsyet'));
            } else if ($currentgroup && !$groupstudents) {
                echo $OUTPUT->notification(get_string('nostudentsingroup'));
            }

                        $this->form->display();
        }

        $hasstudents = $students && (!$currentgroup || $groupstudents);
        if ($hasquestions && ($hasstudents || $options->attempts == self::ALL_WITH)) {
                        $fields = $DB->sql_concat('u.id', "'#'", 'COALESCE(quiza.attempt, 0)') .
                    ' AS uniqueid, ';

            list($fields, $from, $where, $params) = $table->base_sql($allowed);

            $table->set_count_sql("SELECT COUNT(1) FROM $from WHERE $where", $params);

                        $fields .= ", COALESCE((
                                SELECT MAX(qqr.regraded)
                                  FROM {quiz_overview_regrades} qqr
                                 WHERE qqr.questionusageid = quiza.uniqueid
                          ), -1) AS regraded";
            if ($options->onlyregraded) {
                $where .= " AND COALESCE((
                                    SELECT MAX(qqr.regraded)
                                      FROM {quiz_overview_regrades} qqr
                                     WHERE qqr.questionusageid = quiza.uniqueid
                                ), -1) <> -1";
            }
            $table->set_sql($fields, $from, $where, $params);

            if (!$table->is_downloading()) {
                                if (has_capability('mod/quiz:regrade', $this->context)) {
                    $regradesneeded = $this->count_question_attempts_needing_regrade(
                            $quiz, $groupstudents);
                    if ($currentgroup) {
                        $a= new stdClass();
                        $a->groupname = groups_get_group_name($currentgroup);
                        $a->coursestudents = get_string('participants');
                        $a->countregradeneeded = $regradesneeded;
                        $regradealldrydolabel =
                                get_string('regradealldrydogroup', 'quiz_overview', $a);
                        $regradealldrylabel =
                                get_string('regradealldrygroup', 'quiz_overview', $a);
                        $regradealllabel =
                                get_string('regradeallgroup', 'quiz_overview', $a);
                    } else {
                        $regradealldrydolabel =
                                get_string('regradealldrydo', 'quiz_overview', $regradesneeded);
                        $regradealldrylabel =
                                get_string('regradealldry', 'quiz_overview');
                        $regradealllabel =
                                get_string('regradeall', 'quiz_overview');
                    }
                    $displayurl = new moodle_url($options->get_url(), array('sesskey' => sesskey()));
                    echo '<div class="mdl-align">';
                    echo '<form action="'.$displayurl->out_omit_querystring().'">';
                    echo '<div>';
                    echo html_writer::input_hidden_params($displayurl);
                    echo '<input type="submit" name="regradeall" value="'.$regradealllabel.'"/>';
                    echo '<input type="submit" name="regradealldry" value="' .
                            $regradealldrylabel . '"/>';
                    if ($regradesneeded) {
                        echo '<input type="submit" name="regradealldrydo" value="' .
                                $regradealldrydolabel . '"/>';
                    }
                    echo '</div>';
                    echo '</form>';
                    echo '</div>';
                }
                                if ($strattempthighlight = quiz_report_highlighting_grading_method(
                        $quiz, $this->qmsubselect, $options->onlygraded)) {
                    echo '<div class="quizattemptcounts">' . $strattempthighlight . '</div>';
                }
            }

                        $columns = array();
            $headers = array();

            if (!$table->is_downloading() && $options->checkboxcolumn) {
                $columns[] = 'checkbox';
                $headers[] = null;
            }

            $this->add_user_columns($table, $columns, $headers);
            $this->add_state_column($columns, $headers);
            $this->add_time_columns($columns, $headers);

            $this->add_grade_columns($quiz, $options->usercanseegrades, $columns, $headers, false);

            if (!$table->is_downloading() && has_capability('mod/quiz:regrade', $this->context) &&
                    $this->has_regraded_questions($from, $where, $params)) {
                $columns[] = 'regraded';
                $headers[] = get_string('regrade', 'quiz_overview');
            }

            if ($options->slotmarks) {
                foreach ($questions as $slot => $question) {
                                        $columns[] = 'qsgrade' . $slot;
                    $header = get_string('qbrief', 'quiz', $question->number);
                    if (!$table->is_downloading()) {
                        $header .= '<br />';
                    } else {
                        $header .= ' ';
                    }
                    $header .= '/' . quiz_rescale_grade($question->maxmark, $quiz, 'question');
                    $headers[] = $header;
                }
            }

            $this->set_up_table_columns($table, $columns, $headers, $this->get_base_url(), $options, false);
            $table->set_attribute('class', 'generaltable generalbox grades');

            $table->out($options->pagesize, true);
        }

        if (!$table->is_downloading() && $options->usercanseegrades) {
            $output = $PAGE->get_renderer('mod_quiz');
            if ($currentgroup && $groupstudents) {
                list($usql, $params) = $DB->get_in_or_equal($groupstudents);
                $params[] = $quiz->id;
                if ($DB->record_exists_select('quiz_grades', "userid $usql AND quiz = ?",
                        $params)) {
                    $imageurl = new moodle_url('/mod/quiz/report/overview/overviewgraph.php',
                            array('id' => $quiz->id, 'groupid' => $currentgroup));
                    $graphname = get_string('overviewreportgraphgroup', 'quiz_overview',
                            groups_get_group_name($currentgroup));
                    echo $output->graph($imageurl, $graphname);
                }
            }

            if ($DB->record_exists('quiz_grades', array('quiz'=> $quiz->id))) {
                $imageurl = new moodle_url('/mod/quiz/report/overview/overviewgraph.php',
                        array('id' => $quiz->id));
                $graphname = get_string('overviewreportgraph', 'quiz_overview');
                echo $output->graph($imageurl, $graphname);
            }
        }
        return true;
    }

    protected function process_actions($quiz, $cm, $currentgroup, $groupstudents, $allowed, $redirecturl) {
        parent::process_actions($quiz, $cm, $currentgroup, $groupstudents, $allowed, $redirecturl);

        if (empty($currentgroup) || $groupstudents) {
            if (optional_param('regrade', 0, PARAM_BOOL) && confirm_sesskey()) {
                if ($attemptids = optional_param_array('attemptid', array(), PARAM_INT)) {
                    $this->start_regrade($quiz, $cm);
                    $this->regrade_attempts($quiz, false, $groupstudents, $attemptids);
                    $this->finish_regrade($redirecturl);
                }
            }
        }

        if (optional_param('regradeall', 0, PARAM_BOOL) && confirm_sesskey()) {
            $this->start_regrade($quiz, $cm);
            $this->regrade_attempts($quiz, false, $groupstudents);
            $this->finish_regrade($redirecturl);

        } else if (optional_param('regradealldry', 0, PARAM_BOOL) && confirm_sesskey()) {
            $this->start_regrade($quiz, $cm);
            $this->regrade_attempts($quiz, true, $groupstudents);
            $this->finish_regrade($redirecturl);

        } else if (optional_param('regradealldrydo', 0, PARAM_BOOL) && confirm_sesskey()) {
            $this->start_regrade($quiz, $cm);
            $this->regrade_attempts_needing_it($quiz, $groupstudents);
            $this->finish_regrade($redirecturl);
        }
    }

    
    protected function start_regrade($quiz, $cm) {
        global $OUTPUT, $PAGE;
        require_capability('mod/quiz:regrade', $this->context);
        $this->print_header_and_tabs($cm, $this->course, $quiz, $this->mode);
    }

    
    protected function finish_regrade($nexturl) {
        global $OUTPUT;
        \core\notification::success(get_string('regradecomplete', 'quiz_overview'));
        echo $OUTPUT->continue_button($nexturl);
        echo $OUTPUT->footer();
        die();
    }

    
    protected function unlock_session() {
        \core\session\manager::write_close();
        ignore_user_abort(true);
    }

    
    protected function regrade_attempt($attempt, $dryrun = false, $slots = null) {
        global $DB;
                core_php_time_limit::raise(300);

        $transaction = $DB->start_delegated_transaction();

        $quba = question_engine::load_questions_usage_by_activity($attempt->uniqueid);

        if (is_null($slots)) {
            $slots = $quba->get_slots();
        }

        $finished = $attempt->state == quiz_attempt::FINISHED;
        foreach ($slots as $slot) {
            $qqr = new stdClass();
            $qqr->oldfraction = $quba->get_question_fraction($slot);

            $quba->regrade_question($slot, $finished);

            $qqr->newfraction = $quba->get_question_fraction($slot);

            if (abs($qqr->oldfraction - $qqr->newfraction) > 1e-7) {
                $qqr->questionusageid = $quba->get_id();
                $qqr->slot = $slot;
                $qqr->regraded = empty($dryrun);
                $qqr->timemodified = time();
                $DB->insert_record('quiz_overview_regrades', $qqr, false);
            }
        }

        if (!$dryrun) {
            question_engine::save_questions_usage_by_activity($quba);
        }

        $transaction->allow_commit();

                $quba = null;
        $transaction = null;
        gc_collect_cycles();
    }

    
    protected function regrade_attempts($quiz, $dryrun = false,
            $groupstudents = array(), $attemptids = array()) {
        global $DB;
        $this->unlock_session();

        $where = "quiz = ? AND preview = 0";
        $params = array($quiz->id);

        if ($groupstudents) {
            list($usql, $uparams) = $DB->get_in_or_equal($groupstudents);
            $where .= " AND userid $usql";
            $params = array_merge($params, $uparams);
        }

        if ($attemptids) {
            list($asql, $aparams) = $DB->get_in_or_equal($attemptids);
            $where .= " AND id $asql";
            $params = array_merge($params, $aparams);
        }

        $attempts = $DB->get_records_select('quiz_attempts', $where, $params);
        if (!$attempts) {
            return;
        }

        $this->clear_regrade_table($quiz, $groupstudents);

        $progressbar = new progress_bar('quiz_overview_regrade', 500, true);
        $a = array(
            'count' => count($attempts),
            'done'  => 0,
        );
        foreach ($attempts as $attempt) {
            $this->regrade_attempt($attempt, $dryrun);
            $a['done']++;
            $progressbar->update($a['done'], $a['count'],
                    get_string('regradingattemptxofy', 'quiz_overview', $a));
        }

        if (!$dryrun) {
            $this->update_overall_grades($quiz);
        }
    }

    
    protected function regrade_attempts_needing_it($quiz, $groupstudents) {
        global $DB;
        $this->unlock_session();

        $where = "quiza.quiz = ? AND quiza.preview = 0 AND qqr.regraded = 0";
        $params = array($quiz->id);

                if ($groupstudents) {
            list($usql, $uparams) = $DB->get_in_or_equal($groupstudents);
            $where .= " AND quiza.userid $usql";
            $params = array_merge($params, $uparams);
        }

        $toregrade = $DB->get_recordset_sql("
                SELECT quiza.uniqueid, qqr.slot
                FROM {quiz_attempts} quiza
                JOIN {quiz_overview_regrades} qqr ON qqr.questionusageid = quiza.uniqueid
                WHERE $where", $params);

        $attemptquestions = array();
        foreach ($toregrade as $row) {
            $attemptquestions[$row->uniqueid][] = $row->slot;
        }
        $toregrade->close();

        if (!$attemptquestions) {
            return;
        }

        $attempts = $DB->get_records_list('quiz_attempts', 'uniqueid',
                array_keys($attemptquestions));

        $this->clear_regrade_table($quiz, $groupstudents);

        $progressbar = new progress_bar('quiz_overview_regrade', 500, true);
        $a = array(
            'count' => count($attempts),
            'done'  => 0,
        );
        foreach ($attempts as $attempt) {
            $this->regrade_attempt($attempt, false, $attemptquestions[$attempt->uniqueid]);
            $a['done']++;
            $progressbar->update($a['done'], $a['count'],
                    get_string('regradingattemptxofy', 'quiz_overview', $a));
        }

        $this->update_overall_grades($quiz);
    }

    
    protected function count_question_attempts_needing_regrade($quiz, $groupstudents) {
        global $DB;

        $usertest = '';
        $params = array();
        if ($groupstudents) {
            list($usql, $params) = $DB->get_in_or_equal($groupstudents);
            $usertest = "quiza.userid $usql AND ";
        }

        $params[] = $quiz->id;
        $sql = "SELECT COUNT(DISTINCT quiza.id)
                FROM {quiz_attempts} quiza
                JOIN {quiz_overview_regrades} qqr ON quiza.uniqueid = qqr.questionusageid
                WHERE
                    $usertest
                    quiza.quiz = ? AND
                    quiza.preview = 0 AND
                    qqr.regraded = 0";
        return $DB->count_records_sql($sql, $params);
    }

    
    protected function has_regraded_questions($from, $where, $params) {
        global $DB;
        return $DB->record_exists_sql("
                SELECT 1
                  FROM {$from}
                  JOIN {quiz_overview_regrades} qor ON qor.questionusageid = quiza.uniqueid
                 WHERE {$where}", $params);
    }

    
    protected function clear_regrade_table($quiz, $groupstudents) {
        global $DB;

                $where = '';
        $params = array();
        if ($groupstudents) {
            list($usql, $params) = $DB->get_in_or_equal($groupstudents);
            $where = "userid $usql AND ";
        }

        $params[] = $quiz->id;
        $DB->delete_records_select('quiz_overview_regrades',
                "questionusageid IN (
                    SELECT uniqueid
                    FROM {quiz_attempts}
                    WHERE $where quiz = ?
                )", $params);
    }

    
    protected function update_overall_grades($quiz) {
        quiz_update_all_attempt_sumgrades($quiz);
        quiz_update_all_final_grades($quiz);
        quiz_update_grades($quiz);
    }
}
