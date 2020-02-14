<?php



defined('MOODLE_INTERNAL') || die();


class quiz_first_or_all_responses_table extends quiz_last_responses_table {

    
    protected $questionusagesbyactivity;

    protected function field_from_extra_data($tablerow, $slot, $field) {
        $questionattempt = $this->get_question_attempt($tablerow->usageid, $slot);
        switch($field) {
            case 'questionsummary' :
                return $questionattempt->get_question_summary();
            case 'responsesummary' :
                return $this->get_summary_after_try($tablerow, $slot);
            case 'rightanswer' :
                return $questionattempt->get_right_answer_summary();
            default :
                throw new coding_exception('Unknown question attempt field.');
        }
    }


    protected function load_extra_data() {
        if (count($this->rawdata) === 0) {
            return;
        }
        $qubaids = $this->get_qubaids_condition();
        $dm = new question_engine_data_mapper();
        $this->questionusagesbyactivity = $dm->load_questions_usages_by_activity($qubaids);

                $newrawdata = array();
        foreach ($this->rawdata as $attempt) {
            $maxtriesinanyslot = 1;
            foreach ($this->questionusagesbyactivity[$attempt->usageid]->get_slots() as $slot) {
                $tries = $this->get_no_of_tries($attempt, $slot);
                $maxtriesinanyslot = max($maxtriesinanyslot, $tries);
            }
            for ($try = 1; $try <= $maxtriesinanyslot; $try++) {
                $newtablerow = clone($attempt);
                $newtablerow->lasttryforallparts = ($try == $maxtriesinanyslot);
                if ($try !== $maxtriesinanyslot) {
                    $newtablerow->state = quiz_attempt::IN_PROGRESS;
                }
                $newtablerow->try = $try;
                $newrawdata[] = $newtablerow;
                if ($this->options->whichtries == question_attempt::FIRST_TRY) {
                    break;
                }
            }
        }
        $this->rawdata = $newrawdata;
    }

    
    protected function get_question_attempt($questionusagesid, $slot) {
        return $this->questionusagesbyactivity[$questionusagesid]->get_question_attempt($slot);
    }

    
    protected function slot_state($tablerow, $slot) {
        $qa = $this->get_question_attempt($tablerow->usageid, $slot);
        $submissionsteps = $qa->get_steps_with_submitted_response_iterator();
        $step = $submissionsteps[$tablerow->try];
        if ($step === null) {
            return null;
        }
        if ($this->is_last_try($tablerow, $slot, $tablerow->try)) {
                                    return $qa->get_state();
        }
        return $step->get_state();
    }


    
    public function get_summary_after_try($tablerow, $slot) {
        $qa = $this->get_question_attempt($tablerow->usageid, $slot);
        if (!($qa->get_question() instanceof question_manually_gradable)) {
                        return null;
        }
        $submissionsteps = $qa->get_steps_with_submitted_response_iterator();
        $step = $submissionsteps[$tablerow->try];
        if ($step === null) {
            return null;
        }
        $qtdata = $step->get_qt_data();
        return $qa->get_question()->summarise_response($qtdata);
    }

    
    protected function is_flagged($questionusageid, $slot) {
        return $this->get_question_attempt($questionusageid, $slot)->is_flagged();
    }

    
    protected function slot_fraction($tablerow, $slot) {
        $qa = $this->get_question_attempt($tablerow->usageid, $slot);
        $submissionsteps = $qa->get_steps_with_submitted_response_iterator();
        $step = $submissionsteps[$tablerow->try];
        if ($step === null) {
            return null;
        }
        if ($this->is_last_try($tablerow, $slot, $tablerow->try)) {
                                    return $qa->get_fraction();
        }
        return $step->get_fraction();
    }

    
    protected function is_last_try($tablerow, $slot, $tryno) {
        return $tryno == $this->get_no_of_tries($tablerow, $slot);
    }

    
    public function get_no_of_tries($tablerow, $slot) {
        return count($this->get_question_attempt($tablerow->usageid, $slot)->get_steps_with_submitted_response_iterator());
    }


    
    protected function step_no_for_try($questionusageid, $slot, $tryno) {
        $qa = $this->get_question_attempt($questionusageid, $slot);
        return $qa->get_steps_with_submitted_response_iterator()->step_no_for_try($tryno);
    }

    public function col_checkbox($tablerow) {
        if ($tablerow->try != 1) {
            return '';
        } else {
            return parent::col_checkbox($tablerow);
        }
    }

    
    public function col_email($tablerow) {
        if ($tablerow->try != 1) {
            return '';
        } else {
            return $tablerow->email;
        }
    }

    
    public function col_sumgrades($tablerow) {
        if (!$tablerow->lasttryforallparts) {
            return '';
        } else {
            return parent::col_sumgrades($tablerow);
        }
    }


    public function col_state($tablerow) {
        if (!$tablerow->lasttryforallparts) {
            return '';
        } else {
            return parent::col_state($tablerow);
        }
    }

    public function get_row_class($tablerow) {
        if ($this->options->whichtries == question_attempt::ALL_TRIES && $tablerow->lasttryforallparts) {
            return 'lastrowforattempt';
        } else {
            return '';
        }
    }

    public function make_review_link($data, $tablerow, $slot) {
        if ($this->slot_state($tablerow, $slot) === null) {
            return $data;
        } else {
            return parent::make_review_link($data, $tablerow, $slot);
        }
    }
}


