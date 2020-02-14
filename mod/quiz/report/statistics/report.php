<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/statistics/statistics_form.php');
require_once($CFG->dirroot . '/mod/quiz/report/statistics/statistics_table.php');
require_once($CFG->dirroot . '/mod/quiz/report/statistics/statistics_question_table.php');
require_once($CFG->dirroot . '/mod/quiz/report/statistics/statisticslib.php');

class quiz_statistics_report extends quiz_default_report {

    
    protected $context;

    
    protected $table;

    
    protected $progress = null;

    
    public function display($quiz, $cm, $course) {
        global $OUTPUT;

        raise_memory_limit(MEMORY_HUGE);

        $this->context = context_module::instance($cm->id);

        if (!quiz_has_questions($quiz->id)) {
            $this->print_header_and_tabs($cm, $course, $quiz, 'statistics');
            echo quiz_no_questions_message($quiz, $cm, $this->context);
            return true;
        }

                $download = optional_param('download', '', PARAM_ALPHA);
        $everything = optional_param('everything', 0, PARAM_BOOL);
        $recalculate = optional_param('recalculate', 0, PARAM_BOOL);
                $qid = optional_param('qid', 0, PARAM_INT);
        $slot = optional_param('slot', 0, PARAM_INT);
        $variantno = optional_param('variant', null, PARAM_INT);
        $whichattempts = optional_param('whichattempts', $quiz->grademethod, PARAM_INT);
        $whichtries = optional_param('whichtries', question_attempt::LAST_TRY, PARAM_ALPHA);

        $pageoptions = array();
        $pageoptions['id'] = $cm->id;
        $pageoptions['mode'] = 'statistics';

        $reporturl = new moodle_url('/mod/quiz/report.php', $pageoptions);

        $mform = new quiz_statistics_settings_form($reporturl, compact('quiz'));

        $mform->set_data(array('whichattempts' => $whichattempts, 'whichtries' => $whichtries));

        if ($whichattempts != $quiz->grademethod) {
            $reporturl->param('whichattempts', $whichattempts);
        }

        if ($whichtries != question_attempt::LAST_TRY) {
            $reporturl->param('whichtries', $whichtries);
        }

                $currentgroup = $this->get_current_group($cm, $course, $this->context);
        $nostudentsingroup = false;         if (empty($currentgroup)) {
            $currentgroup = 0;
            $groupstudents = array();

        } else if ($currentgroup == self::NO_GROUPS_ALLOWED) {
            $groupstudents = array();
            $nostudentsingroup = true;

        } else {
                        $groupstudents = get_users_by_capability($this->context,
                    array('mod/quiz:reviewmyattempts', 'mod/quiz:attempt'),
                    '', '', '', '', $currentgroup, '', false);
            if (!$groupstudents) {
                $nostudentsingroup = true;
            }
        }

        $qubaids = quiz_statistics_qubaids_condition($quiz->id, $groupstudents, $whichattempts);

                if ($recalculate && confirm_sesskey()) {
            $this->clear_cached_data($qubaids);
            redirect($reporturl);
        }

                $this->table = new quiz_statistics_table();
        if ($everything) {
            $report = get_string('completestatsfilename', 'quiz_statistics');
        } else {
            $report = get_string('questionstatsfilename', 'quiz_statistics');
        }
        $courseshortname = format_string($course->shortname, true,
                array('context' => context_course::instance($course->id)));
        $filename = quiz_report_download_filename($report, $courseshortname, $quiz->name);
        $this->table->is_downloading($download, $filename,
                get_string('quizstructureanalysis', 'quiz_statistics'));
        $questions = $this->load_and_initialise_questions_for_calculations($quiz);

                if (!$this->table->is_downloading()) {
            $this->print_header_and_tabs($cm, $course, $quiz, 'statistics');
        }

        if (!$nostudentsingroup) {
                        $progress = $this->get_progress_trace_instance();
            list($quizstats, $questionstats) =
                $this->get_all_stats_and_analysis($quiz, $whichattempts, $whichtries, $groupstudents, $questions, $progress);
        } else {
                        $quizstats = new \quiz_statistics\calculated($whichattempts);
            $questionstats = new \core_question\statistics\questions\all_calculated_for_qubaid_condition();
        }

                if ($quizstats->s()) {
            $this->table->statistics_setup($quiz, $cm->id, $reporturl, $quizstats->s());
        }

                if (!$this->table->is_downloading()) {

            if (groups_get_activity_groupmode($cm)) {
                groups_print_activity_menu($cm, $reporturl->out());
                if ($currentgroup && !$groupstudents) {
                    $OUTPUT->notification(get_string('nostudentsingroup', 'quiz_statistics'));
                }
            }

            if (!$this->table->is_downloading() && $quizstats->s() == 0) {
                echo $OUTPUT->notification(get_string('nogradedattempts', 'quiz_statistics'));
            }

            foreach ($questionstats->any_error_messages() as $errormessage) {
                echo $OUTPUT->notification($errormessage);
            }

                        $mform->display();
        }

        if ($everything) {                         $quizinfo = $quizstats->get_formatted_quiz_info_data($course, $cm, $quiz);
            $this->download_quiz_info_table($quizinfo);

            if ($quizstats->s()) {
                $this->output_quiz_structure_analysis_table($questionstats);

                if ($this->table->is_downloading() == 'xhtml' && $quizstats->s() != 0) {
                    $this->output_statistics_graph($quiz->id, $currentgroup, $whichattempts);
                }

                $this->output_all_question_response_analysis($qubaids, $questions, $questionstats, $reporturl, $whichtries);
            }

            $this->table->export_class_instance()->finish_document();

        } else if ($qid) {
                        if (is_null($questionstats->for_subq($qid, $variantno))) {
                print_error('questiondoesnotexist', 'question');
            }

            $this->output_individual_question_data($quiz, $questionstats->for_subq($qid, $variantno));
            $this->output_individual_question_response_analysis($questionstats->for_subq($qid, $variantno)->question,
                                                                $variantno,
                                                                $questionstats->for_subq($qid, $variantno)->s,
                                                                $reporturl,
                                                                $qubaids,
                                                                $whichtries);
                        echo $OUTPUT->box('<a href="' . $reporturl->out() . '">' .
                              get_string('backtoquizreport', 'quiz_statistics') . '</a>',
                              'boxaligncenter generalbox boxwidthnormal mdl-align');
        } else if ($slot) {
                        if (!isset($questions[$slot])) {
                print_error('questiondoesnotexist', 'question');
            }

            if ($variantno === null &&
                                ($questionstats->for_slot($slot)->get_sub_question_ids()
                                || $questionstats->for_slot($slot)->get_variants())) {
                if (!$this->table->is_downloading()) {
                    $number = $questionstats->for_slot($slot)->question->number;
                    echo $OUTPUT->heading(get_string('slotstructureanalysis', 'quiz_statistics', $number), 3);
                }
                $this->table->define_baseurl(new moodle_url($reporturl, array('slot' => $slot)));
                $this->table->format_and_add_array_of_rows($questionstats->structure_analysis_for_one_slot($slot));
            } else {
                $this->output_individual_question_data($quiz, $questionstats->for_slot($slot, $variantno));
                $this->output_individual_question_response_analysis($questions[$slot],
                                                                    $variantno,
                                                                    $questionstats->for_slot($slot, $variantno)->s,
                                                                    $reporturl,
                                                                    $qubaids,
                                                                    $whichtries);
            }
            if (!$this->table->is_downloading()) {
                                echo $OUTPUT->box('<a href="' . $reporturl->out() . '">' .
                        get_string('backtoquizreport', 'quiz_statistics') . '</a>',
                        'backtomainstats boxaligncenter generalbox boxwidthnormal mdl-align');
            } else {
                $this->table->finish_output();
            }

        } else if ($this->table->is_downloading()) {
                        $quizinfo = $quizstats->get_formatted_quiz_info_data($course, $cm, $quiz);
            $this->download_quiz_info_table($quizinfo);
            if ($quizstats->s()) {
                $this->output_quiz_structure_analysis_table($questionstats);
            }
            $this->table->finish_output();

        } else {
                        echo $OUTPUT->heading(get_string('quizinformation', 'quiz_statistics'), 3);
            echo $this->output_caching_info($quizstats->timemodified, $quiz->id, $groupstudents, $whichattempts, $reporturl);
            echo $this->everything_download_options($reporturl);
            $quizinfo = $quizstats->get_formatted_quiz_info_data($course, $cm, $quiz);
            echo $this->output_quiz_info_table($quizinfo);
            if ($quizstats->s()) {
                echo $OUTPUT->heading(get_string('quizstructureanalysis', 'quiz_statistics'), 3);
                $this->output_quiz_structure_analysis_table($questionstats);
                $this->output_statistics_graph($quiz->id, $currentgroup, $whichattempts);
            }
        }

        return true;
    }

    
    protected function output_individual_question_data($quiz, $questionstat) {
        global $OUTPUT;

                        $datumfromtable = $this->table->format_row($questionstat);

                $questioninfotable = new html_table();
        $questioninfotable->align = array('center', 'center');
        $questioninfotable->width = '60%';
        $questioninfotable->attributes['class'] = 'generaltable titlesleft';

        $questioninfotable->data = array();
        $questioninfotable->data[] = array(get_string('modulename', 'quiz'), $quiz->name);
        $questioninfotable->data[] = array(get_string('questionname', 'quiz_statistics'),
                $questionstat->question->name.'&nbsp;'.$datumfromtable['actions']);

        if ($questionstat->variant !== null) {
            $questioninfotable->data[] = array(get_string('variant', 'quiz_statistics'), $questionstat->variant);

        }
        $questioninfotable->data[] = array(get_string('questiontype', 'quiz_statistics'),
                $datumfromtable['icon'] . '&nbsp;' .
                question_bank::get_qtype($questionstat->question->qtype, false)->menu_name() . '&nbsp;' .
                $datumfromtable['icon']);
        $questioninfotable->data[] = array(get_string('positions', 'quiz_statistics'),
                $questionstat->positions);

                $questionstatstable = new html_table();
        $questionstatstable->align = array('center', 'center');
        $questionstatstable->width = '60%';
        $questionstatstable->attributes['class'] = 'generaltable titlesleft';

        unset($datumfromtable['number']);
        unset($datumfromtable['icon']);
        $actions = $datumfromtable['actions'];
        unset($datumfromtable['actions']);
        unset($datumfromtable['name']);
        $labels = array(
            's' => get_string('attempts', 'quiz_statistics'),
            'facility' => get_string('facility', 'quiz_statistics'),
            'sd' => get_string('standarddeviationq', 'quiz_statistics'),
            'random_guess_score' => get_string('random_guess_score', 'quiz_statistics'),
            'intended_weight' => get_string('intended_weight', 'quiz_statistics'),
            'effective_weight' => get_string('effective_weight', 'quiz_statistics'),
            'discrimination_index' => get_string('discrimination_index', 'quiz_statistics'),
            'discriminative_efficiency' =>
                                get_string('discriminative_efficiency', 'quiz_statistics')
        );
        foreach ($datumfromtable as $item => $value) {
            $questionstatstable->data[] = array($labels[$item], $value);
        }

                echo $OUTPUT->heading(get_string('questioninformation', 'quiz_statistics'), 3);
        echo html_writer::table($questioninfotable);
        echo $this->render_question_text($questionstat->question);
        echo $OUTPUT->heading(get_string('questionstatistics', 'quiz_statistics'), 3);
        echo html_writer::table($questionstatstable);
    }

    
    protected function render_question_text($question) {
        global $OUTPUT;

        $text = question_rewrite_question_preview_urls($question->questiontext, $question->id,
                $question->contextid, 'question', 'questiontext', $question->id,
                $this->context->id, 'quiz_statistics');

        return $OUTPUT->box(format_text($text, $question->questiontextformat,
                array('noclean' => true, 'para' => false, 'overflowdiv' => true)),
                'questiontext boxaligncenter generalbox boxwidthnormal mdl-align');
    }

    
    protected function output_individual_question_response_analysis($question, $variantno, $s, $reporturl, $qubaids,
                                                                    $whichtries = question_attempt::LAST_TRY) {
        global $OUTPUT;

        if (!question_bank::get_qtype($question->qtype, false)->can_analyse_responses()) {
            return;
        }

        $qtable = new quiz_statistics_question_table($question->id);
        $exportclass = $this->table->export_class_instance();
        $qtable->export_class_instance($exportclass);
        if (!$this->table->is_downloading()) {
                        echo $OUTPUT->heading(get_string('analysisofresponses', 'quiz_statistics'), 3);

        } else {
                        $a = clone($question);
            $a->variant = $variantno;

            if (!empty($question->number) && !is_null($variantno)) {
                $questiontabletitle = get_string('analysisnovariant', 'quiz_statistics', $a);
            } else if (!empty($question->number)) {
                $questiontabletitle = get_string('analysisno', 'quiz_statistics', $a);
            } else if (!is_null($variantno)) {
                $questiontabletitle = get_string('analysisvariant', 'quiz_statistics', $a);
            } else {
                $questiontabletitle = get_string('analysisnameonly', 'quiz_statistics', $a);
            }

            if ($this->table->is_downloading() == 'xhtml') {
                $questiontabletitle = get_string('analysisofresponsesfor', 'quiz_statistics', $questiontabletitle);
            }

                        $exportclass->start_table($questiontabletitle);

            if ($this->table->is_downloading() == 'xhtml') {
                echo $this->render_question_text($question);
            }
        }

        $responesanalyser = new \core_question\statistics\responses\analyser($question, $whichtries);
        $responseanalysis = $responesanalyser->load_cached($qubaids, $whichtries);

        $qtable->question_setup($reporturl, $question, $s, $responseanalysis);
        if ($this->table->is_downloading()) {
            $exportclass->output_headers($qtable->headers);
        }

                if ($variantno === null) {
            $variantno = 1;
        }
        foreach ($responseanalysis->get_subpart_ids($variantno) as $partid) {
            $subpart = $responseanalysis->get_analysis_for_subpart($variantno, $partid);
            foreach ($subpart->get_response_class_ids() as $responseclassid) {
                $responseclass = $subpart->get_response_class($responseclassid);
                $tabledata = $responseclass->data_for_question_response_table($subpart->has_multiple_response_classes(), $partid);
                foreach ($tabledata as $row) {
                    $qtable->add_data_keyed($qtable->format_row($row));
                }
            }
        }

        $qtable->finish_output(!$this->table->is_downloading());
    }

    
    protected function output_quiz_structure_analysis_table($questionstats) {
        $tooutput = array();
        $limitvariants = !$this->table->is_downloading();
        foreach ($questionstats->get_all_slots() as $slot) {
                        $tooutput = array_merge($tooutput, $questionstats->structure_analysis_for_one_slot($slot, $limitvariants));
        }
        $this->table->format_and_add_array_of_rows($tooutput);
    }

    
    protected function output_quiz_info_table($quizinfo) {

        $quizinfotable = new html_table();
        $quizinfotable->align = array('center', 'center');
        $quizinfotable->width = '60%';
        $quizinfotable->attributes['class'] = 'generaltable titlesleft';
        $quizinfotable->data = array();

        foreach ($quizinfo as $heading => $value) {
             $quizinfotable->data[] = array($heading, $value);
        }

        return html_writer::table($quizinfotable);
    }

    
    protected function download_quiz_info_table($quizinfo) {
        global $OUTPUT;

                if ($this->table->is_downloading() == 'xhtml') {
            echo $OUTPUT->heading(get_string('quizinformation', 'quiz_statistics'), 3);
            echo $this->output_quiz_info_table($quizinfo);
            return;
        }

                $headers = array();
        $row = array();
        foreach ($quizinfo as $heading => $value) {
            $headers[] = $heading;
            $row[] = $value;
        }

                $exportclass = $this->table->export_class_instance();
        $exportclass->start_table(get_string('quizinformation', 'quiz_statistics'));
        $exportclass->output_headers($headers);
        $exportclass->add_data($row);
        $exportclass->finish_table();
    }

    
    protected function output_statistics_graph($quizid, $currentgroup, $whichattempts) {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_quiz');
        $imageurl = new moodle_url('/mod/quiz/report/statistics/statistics_graph.php',
                                    compact('quizid', 'currentgroup', 'whichattempts'));
        $graphname = get_string('statisticsreportgraph', 'quiz_statistics');
        echo $output->graph($imageurl, $graphname);
    }

    
    public function get_all_stats_and_analysis($quiz, $whichattempts, $whichtries, $groupstudents, $questions, $progress = null) {

        if ($progress === null) {
            $progress = new \core\progress\none();
        }

        $qubaids = quiz_statistics_qubaids_condition($quiz->id, $groupstudents, $whichattempts);

        $qcalc = new \core_question\statistics\questions\calculator($questions, $progress);

        $quizcalc = new \quiz_statistics\calculator($progress);

        $progress->start_progress('', 3);
        if ($quizcalc->get_last_calculated_time($qubaids) === false) {

                        $questionstats = $qcalc->calculate($qubaids);
            $progress->progress(1);

            $quizstats = $quizcalc->calculate($quiz->id, $whichattempts, $groupstudents, count($questions),
                                              $qcalc->get_sum_of_mark_variance());
            $progress->progress(2);
        } else {
            $quizstats = $quizcalc->get_cached($qubaids);
            $progress->progress(1);
            $questionstats = $qcalc->get_cached($qubaids);
            $progress->progress(2);
        }

        if ($quizstats->s()) {
            $subquestions = $questionstats->get_sub_questions();
            $this->analyse_responses_for_all_questions_and_subquestions($questions,
                                                                        $subquestions,
                                                                        $qubaids,
                                                                        $whichtries,
                                                                        $progress);
        }
        $progress->progress(3);
        $progress->end_progress();

        return array($quizstats, $questionstats);
    }

    
    protected function get_progress_trace_instance() {
        if ($this->progress === null) {
            if (!$this->table->is_downloading()) {
                $this->progress = new \core\progress\display_if_slow(get_string('calculatingallstats', 'quiz_statistics'));
                $this->progress->set_display_names();
            } else {
                $this->progress = new \core\progress\none();
            }
        }
        return $this->progress;
    }

    
    protected function analyse_responses_for_all_questions_and_subquestions($questions, $subquestions, $qubaids,
                                                                            $whichtries, $progress = null) {
        if ($progress === null) {
            $progress = new \core\progress\none();
        }

                $progress->start_progress('', count($questions) + count($subquestions));

        $done = $this->analyse_responses_for_questions($questions, $qubaids, $whichtries, $progress);

        $this->analyse_responses_for_questions($subquestions, $qubaids, $whichtries, $progress, $done);

                $progress->end_progress();
    }

    
    protected function analyse_responses_for_questions($questions, $qubaids, $whichtries, $progress = null, $done = array()) {
        $countquestions = count($questions);
        if (!$countquestions) {
            return array();
        }
        if ($progress === null) {
            $progress = new \core\progress\none();
        }
        $progress->start_progress('', $countquestions, $countquestions);
        foreach ($questions as $question) {
            $progress->increment_progress();
            if (question_bank::get_qtype($question->qtype, false)->can_analyse_responses()  && !isset($done[$question->id])) {
                $responesstats = new \core_question\statistics\responses\analyser($question, $whichtries);
                if ($responesstats->get_last_analysed_time($qubaids, $whichtries) === false) {
                    $responesstats->calculate($qubaids, $whichtries);
                }
            }
            $done[$question->id] = 1;
        }
        $progress->end_progress();
        return $done;
    }

    
    protected function everything_download_options(moodle_url $reporturl) {
        global $OUTPUT;
        return $OUTPUT->download_dataformat_selector(get_string('downloadeverything', 'quiz_statistics'),
            $reporturl->out_omit_querystring(), 'download', $reporturl->params() + array('everything' => 1));
    }

    
    protected function output_caching_info($lastcachetime, $quizid, $groupstudents, $whichattempts, $reporturl) {
        global $DB, $OUTPUT;

        if (empty($lastcachetime)) {
            return '';
        }

                list($fromqa, $whereqa, $qaparams) = quiz_statistics_attempts_sql($quizid, $groupstudents, $whichattempts, true);
        $count = $DB->count_records_sql("
                SELECT COUNT(1)
                FROM $fromqa
                WHERE $whereqa
                AND quiza.timefinish > {$lastcachetime}", $qaparams);

        if (!$count) {
            $count = 0;
        }

                $a = new stdClass();
        $a->lastcalculated = format_time(time() - $lastcachetime);
        $a->count = $count;

        $recalcualteurl = new moodle_url($reporturl,
                array('recalculate' => 1, 'sesskey' => sesskey()));
        $output = '';
        $output .= $OUTPUT->box_start(
                'boxaligncenter generalbox boxwidthnormal mdl-align', 'cachingnotice');
        $output .= get_string('lastcalculated', 'quiz_statistics', $a);
        $output .= $OUTPUT->single_button($recalcualteurl,
                get_string('recalculatenow', 'quiz_statistics'));
        $output .= $OUTPUT->box_end(true);

        return $output;
    }

    
    protected function clear_cached_data($qubaids) {
        global $DB;
        $DB->delete_records('quiz_statistics', array('hashcode' => $qubaids->get_hash_code()));
        $DB->delete_records('question_statistics', array('hashcode' => $qubaids->get_hash_code()));
        $DB->delete_records('question_response_analysis', array('hashcode' => $qubaids->get_hash_code()));
    }

    
    public function load_and_initialise_questions_for_calculations($quiz) {
                $questions = quiz_report_get_significant_questions($quiz);
        $questionids = array();
        foreach ($questions as $question) {
            $questionids[] = $question->id;
        }
        $fullquestions = question_load_questions($questionids);
        foreach ($questions as $qno => $question) {
            $q = $fullquestions[$question->id];
            $q->maxmark = $question->maxmark;
            $q->slot = $qno;
            $q->number = $question->number;
            $questions[$qno] = $q;
        }
        return $questions;
    }

    
    protected function output_all_question_response_analysis($qubaids,
                                                             $questions,
                                                             $questionstats,
                                                             $reporturl,
                                                             $whichtries = question_attempt::LAST_TRY) {
        foreach ($questions as $slot => $question) {
            if (question_bank::get_qtype(
                $question->qtype, false)->can_analyse_responses()
            ) {
                if ($questionstats->for_slot($slot)->get_variants()) {
                    foreach ($questionstats->for_slot($slot)->get_variants() as $variantno) {
                        $this->output_individual_question_response_analysis($question,
                                                                            $variantno,
                                                                            $questionstats->for_slot($slot, $variantno)->s,
                                                                            $reporturl,
                                                                            $qubaids,
                                                                            $whichtries);
                    }
                } else {
                    $this->output_individual_question_response_analysis($question,
                                                                        null,
                                                                        $questionstats->for_slot($slot)->s,
                                                                        $reporturl,
                                                                        $qubaids,
                                                                        $whichtries);
                }
            } else if ($subqids = $questionstats->for_slot($slot)->get_sub_question_ids()) {
                foreach ($subqids as $subqid) {
                    if ($variants = $questionstats->for_subq($subqid)->get_variants()) {
                        foreach ($variants as $variantno) {
                            $this->output_individual_question_response_analysis(
                                $questionstats->for_subq($subqid, $variantno)->question,
                                $variantno,
                                $questionstats->for_subq($subqid, $variantno)->s,
                                $reporturl,
                                $qubaids,
                                $whichtries);
                        }
                    } else {
                        $this->output_individual_question_response_analysis(
                            $questionstats->for_subq($subqid)->question,
                            null,
                            $questionstats->for_subq($subqid)->s,
                            $reporturl,
                            $qubaids,
                            $whichtries);

                    }
                }
            }
        }
    }
}
