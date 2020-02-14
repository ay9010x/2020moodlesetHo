<?php



namespace mod_quiz;
defined('MOODLE_INTERNAL') || die();


class structure {
    
    protected $quizobj = null;

    
    protected $questions = array();

    
    protected $slots = array();

    
    protected $slotsinorder = array();

    
    protected $sections = array();

    
    protected $canbeedited = null;

    
    public static function create() {
        return new self();
    }

    
    public static function create_for_quiz($quizobj) {
        $structure = self::create();
        $structure->quizobj = $quizobj;
        $structure->populate_structure($quizobj->get_quiz());
        return $structure;
    }

    
    public function has_questions() {
        return !empty($this->questions);
    }

    
    public function get_question_count() {
        return count($this->questions);
    }

    
    public function get_question_by_id($questionid) {
        return $this->questions[$questionid];
    }

    
    public function get_question_in_slot($slotnumber) {
        return $this->questions[$this->slotsinorder[$slotnumber]->questionid];
    }

    
    public function get_displayed_number_for_slot($slotnumber) {
        return $this->slotsinorder[$slotnumber]->displayednumber;
    }

    
    public function get_page_number_for_slot($slotnumber) {
        return $this->slotsinorder[$slotnumber]->page;
    }

    
    public function get_slot_id_for_slot($slotnumber) {
        return $this->slotsinorder[$slotnumber]->id;
    }

    
    public function get_question_type_for_slot($slotnumber) {
        return $this->questions[$this->slotsinorder[$slotnumber]->questionid]->qtype;
    }

    
    public function can_question_depend_on_previous_slot($slotnumber) {
        return $slotnumber > 1 && $this->can_finish_during_the_attempt($slotnumber - 1);
    }

    
    public function can_finish_during_the_attempt($slotnumber) {
        if ($this->quizobj->get_navigation_method() == QUIZ_NAVMETHOD_SEQ) {
            return false;
        }

        if ($this->slotsinorder[$slotnumber]->section->shufflequestions) {
            return false;
        }

        if (in_array($this->get_question_type_for_slot($slotnumber), array('random', 'missingtype'))) {
            return \question_engine::can_questions_finish_during_the_attempt(
                    $this->quizobj->get_quiz()->preferredbehaviour);
        }

        if (isset($this->slotsinorder[$slotnumber]->canfinish)) {
            return $this->slotsinorder[$slotnumber]->canfinish;
        }

        try {
            $quba = \question_engine::make_questions_usage_by_activity('mod_quiz', $this->quizobj->get_context());
            $tempslot = $quba->add_question(\question_bank::load_question(
                    $this->slotsinorder[$slotnumber]->questionid));
            $quba->set_preferred_behaviour($this->quizobj->get_quiz()->preferredbehaviour);
            $quba->start_all_questions();

            $this->slotsinorder[$slotnumber]->canfinish = $quba->can_question_finish_during_attempt($tempslot);
            return $this->slotsinorder[$slotnumber]->canfinish;
        } catch (\Exception $e) {
                        return false;
        }
    }

    
    public function is_question_dependent_on_previous_slot($slotnumber) {
        return $this->slotsinorder[$slotnumber]->requireprevious;
    }

    
    public function is_real_question($slotnumber) {
        return $this->get_question_in_slot($slotnumber)->length != 0;
    }

    
    public function get_courseid() {
        return $this->quizobj->get_courseid();
    }

    
    public function get_cmid() {
        return $this->quizobj->get_cmid();
    }

    
    public function get_quizid() {
        return $this->quizobj->get_quizid();
    }

    
    public function get_quiz() {
        return $this->quizobj->get_quiz();
    }

    
    public function can_be_repaginated() {
        return $this->can_be_edited() && $this->get_question_count() >= 2;
    }

    
    public function can_be_edited() {
        if ($this->canbeedited === null) {
            $this->canbeedited = !quiz_has_attempts($this->quizobj->get_quizid());
        }
        return $this->canbeedited;
    }

    
    public function check_can_be_edited() {
        if (!$this->can_be_edited()) {
            $reportlink = quiz_attempt_summary_link_to_reports($this->get_quiz(),
                    $this->quizobj->get_cm(), $this->quizobj->get_context());
            throw new \moodle_exception('cannoteditafterattempts', 'quiz',
                    new \moodle_url('/mod/quiz/edit.php', array('cmid' => $this->get_cmid())), $reportlink);
        }
    }

    
    public function get_questions_per_page() {
        return $this->quizobj->get_quiz()->questionsperpage;
    }

    
    public function get_slots() {
        return $this->slots;
    }

    
    public function is_first_slot_on_page($slotnumber) {
        if ($slotnumber == 1) {
            return true;
        }
        return $this->slotsinorder[$slotnumber]->page != $this->slotsinorder[$slotnumber - 1]->page;
    }

    
    public function is_last_slot_on_page($slotnumber) {
        if (!isset($this->slotsinorder[$slotnumber + 1])) {
            return true;
        }
        return $this->slotsinorder[$slotnumber]->page != $this->slotsinorder[$slotnumber + 1]->page;
    }

    
    public function is_last_slot_in_section($slotnumber) {
        return $slotnumber == $this->slotsinorder[$slotnumber]->section->lastslot;
    }

    
    public function is_only_slot_in_section($slotnumber) {
        return $this->slotsinorder[$slotnumber]->section->firstslot ==
                $this->slotsinorder[$slotnumber]->section->lastslot;
    }

    
    public function is_last_slot_in_quiz($slotnumber) {
        end($this->slotsinorder);
        return $slotnumber == key($this->slotsinorder);
    }

    
    public function is_first_section($section) {
        return $section->firstslot == 1;
    }

    
    public function is_last_section($section) {
        return $section->id == end($this->sections)->id;
    }

    
    public function is_only_one_slot_in_section($section) {
        return $section->firstslot == $section->lastslot;
    }

    
    public function get_last_slot() {
        return end($this->slotsinorder);
    }

    
    public function get_slot_by_id($slotid) {
        if (!array_key_exists($slotid, $this->slots)) {
            throw new \coding_exception('The \'slotid\' could not be found.');
        }
        return $this->slots[$slotid];
    }

    
    public function can_add_section_heading($pagenumber) {
                        if ($pagenumber == 1) {
            return false;
        }
                $firstslots = array();
        foreach ($this->sections as $section) {
            $firstslots[] = $section->firstslot;
        }
        foreach ($this->slotsinorder as $slot) {
            if ($slot->page == $pagenumber) {
                if (in_array($slot->slot, $firstslots)) {
                    return false;
                }
            }
        }
                if ($pagenumber == 0) {
            return false;
        }
        return true;
    }

    
    public function get_slots_in_section($sectionid) {
        $slots = array();
        foreach ($this->slotsinorder as $slot) {
            if ($slot->section->id == $sectionid) {
                $slots[] = $slot->slot;
            }
        }
        return $slots;
    }

    
    public function get_sections() {
        return $this->sections;
    }

    
    public function get_section_by_id($sectionid) {
        return $this->sections[$sectionid];
    }

    
    public function get_section_count() {
        return count($this->sections);
    }

    
    public function formatted_quiz_grade() {
        return quiz_format_grade($this->get_quiz(), $this->get_quiz()->grade);
    }

    
    public function formatted_question_grade($slotnumber) {
        return quiz_format_question_grade($this->get_quiz(), $this->slotsinorder[$slotnumber]->maxmark);
    }

    
    public function get_decimal_places_for_grades() {
        return $this->get_quiz()->decimalpoints;
    }

    
    public function get_decimal_places_for_question_marks() {
        return quiz_get_grade_format($this->get_quiz());
    }

    
    public function get_edit_page_warnings() {
        $warnings = array();

        if (quiz_has_attempts($this->quizobj->get_quizid())) {
            $reviewlink = quiz_attempt_summary_link_to_reports($this->quizobj->get_quiz(),
                    $this->quizobj->get_cm(), $this->quizobj->get_context());
            $warnings[] = get_string('cannoteditafterattempts', 'quiz', $reviewlink);
        }

        return $warnings;
    }

    
    public function get_dates_summary() {
        $timenow = time();
        $quiz = $this->quizobj->get_quiz();

                $dates = array();
        if ($quiz->timeopen > 0) {
            if ($timenow > $quiz->timeopen) {
                $dates[] = get_string('quizopenedon', 'quiz', userdate($quiz->timeopen));
            } else {
                $dates[] = get_string('quizwillopen', 'quiz', userdate($quiz->timeopen));
            }
        }
        if ($quiz->timeclose > 0) {
            if ($timenow > $quiz->timeclose) {
                $dates[] = get_string('quizclosed', 'quiz', userdate($quiz->timeclose));
            } else {
                $dates[] = get_string('quizcloseson', 'quiz', userdate($quiz->timeclose));
            }
        }
        if (empty($dates)) {
            $dates[] = get_string('alwaysavailable', 'quiz');
        }
        $explanation = implode(', ', $dates);

                if ($timenow < $quiz->timeopen) {
            $currentstatus = get_string('quizisclosedwillopen', 'quiz',
                    userdate($quiz->timeopen, get_string('strftimedatetimeshort', 'langconfig')));
        } else if ($quiz->timeclose && $timenow <= $quiz->timeclose) {
            $currentstatus = get_string('quizisopenwillclose', 'quiz',
                    userdate($quiz->timeclose, get_string('strftimedatetimeshort', 'langconfig')));
        } else if ($quiz->timeclose && $timenow > $quiz->timeclose) {
            $currentstatus = get_string('quizisclosed', 'quiz');
        } else {
            $currentstatus = get_string('quizisopen', 'quiz');
        }

        return array($currentstatus, $explanation);
    }

    
    public function populate_structure($quiz) {
        global $DB;

        $slots = $DB->get_records_sql("
                SELECT slot.id AS slotid, slot.slot, slot.questionid, slot.page, slot.maxmark,
                        slot.requireprevious, q.*, qc.contextid
                  FROM {quiz_slots} slot
                  LEFT JOIN {question} q ON q.id = slot.questionid
                  LEFT JOIN {question_categories} qc ON qc.id = q.category
                 WHERE slot.quizid = ?
              ORDER BY slot.slot", array($quiz->id));

        $slots = $this->populate_missing_questions($slots);

        $this->questions = array();
        $this->slots = array();
        $this->slotsinorder = array();
        foreach ($slots as $slotdata) {
            $this->questions[$slotdata->questionid] = $slotdata;

            $slot = new \stdClass();
            $slot->id = $slotdata->slotid;
            $slot->slot = $slotdata->slot;
            $slot->quizid = $quiz->id;
            $slot->page = $slotdata->page;
            $slot->questionid = $slotdata->questionid;
            $slot->maxmark = $slotdata->maxmark;
            $slot->requireprevious = $slotdata->requireprevious;

            $this->slots[$slot->id] = $slot;
            $this->slotsinorder[$slot->slot] = $slot;
        }

                $this->sections = $DB->get_records('quiz_sections', array('quizid' => $quiz->id), 'firstslot ASC');
        $this->populate_slots_with_sections();
        $this->populate_question_numbers();
    }

    
    protected function populate_missing_questions($slots) {
                foreach ($slots as $slot) {
            if ($slot->qtype === null) {
                                $slot->id = $slot->questionid;
                $slot->category = 0;
                $slot->qtype = 'missingtype';
                $slot->name = get_string('missingquestion', 'quiz');
                $slot->slot = $slot->slot;
                $slot->maxmark = 0;
                $slot->requireprevious = 0;
                $slot->questiontext = ' ';
                $slot->questiontextformat = FORMAT_HTML;
                $slot->length = 1;

            } else if (!\question_bank::qtype_exists($slot->qtype)) {
                $slot->qtype = 'missingtype';
            }
        }

        return $slots;
    }

    
    public function populate_slots_with_sections() {
        $sections = array_values($this->sections);
        foreach ($sections as $i => $section) {
            if (isset($sections[$i + 1])) {
                $section->lastslot = $sections[$i + 1]->firstslot - 1;
            } else {
                $section->lastslot = count($this->slotsinorder);
            }
            for ($slot = $section->firstslot; $slot <= $section->lastslot; $slot += 1) {
                $this->slotsinorder[$slot]->section = $section;
            }
        }
    }

    
    protected function populate_question_numbers() {
        $number = 1;
        foreach ($this->slots as $slot) {
            if ($this->questions[$slot->questionid]->length == 0) {
                $slot->displayednumber = get_string('infoshort', 'quiz');
            } else {
                $slot->displayednumber = $number;
                $number += 1;
            }
        }
    }

    
    public function move_slot($idmove, $idmoveafter, $page) {
        global $DB;

        $this->check_can_be_edited();

        $movingslot = $this->slots[$idmove];
        if (empty($movingslot)) {
            throw new \moodle_exception('Bad slot ID ' . $idmove);
        }
        $movingslotnumber = (int) $movingslot->slot;

                if (empty($idmoveafter)) {
            $moveafterslotnumber = 0;
        } else {
            $moveafterslotnumber = (int) $this->slots[$idmoveafter]->slot;
        }

                        if ($moveafterslotnumber == $movingslotnumber) {
            $moveafterslotnumber = $moveafterslotnumber - 1;
        }

        $followingslotnumber = $moveafterslotnumber + 1;
        if ($followingslotnumber == $movingslotnumber) {
            $followingslotnumber += 1;
        }

                if ($page == 0) {
            $page = 1;
        }
        if (($moveafterslotnumber > 0 && $page < $this->get_page_number_for_slot($moveafterslotnumber)) ||
                $page < 1) {
            throw new \coding_exception('The target page number is too small.');
        } else if (!$this->is_last_slot_in_quiz($moveafterslotnumber) &&
                $page > $this->get_page_number_for_slot($followingslotnumber)) {
            throw new \coding_exception('The target page number is too large.');
        }

                $slotreorder = array();
        if ($moveafterslotnumber > $movingslotnumber) {
                        $slotreorder[$movingslotnumber] = $moveafterslotnumber;
            for ($i = $movingslotnumber; $i < $moveafterslotnumber; $i++) {
                $slotreorder[$i + 1] = $i;
            }

            $headingmoveafter = $movingslotnumber;
            if ($this->is_last_slot_in_quiz($moveafterslotnumber) ||
                    $page == $this->get_page_number_for_slot($moveafterslotnumber + 1)) {
                                                $headingmovebefore = $moveafterslotnumber + 1;
            } else {
                $headingmovebefore = $moveafterslotnumber;
            }
            $headingmovedirection = -1;

        } else if ($moveafterslotnumber < $movingslotnumber - 1) {
                        $slotreorder[$movingslotnumber] = $moveafterslotnumber + 1;
            for ($i = $moveafterslotnumber + 1; $i < $movingslotnumber; $i++) {
                $slotreorder[$i] = $i + 1;
            }

            if ($page == $this->get_page_number_for_slot($moveafterslotnumber + 1)) {
                                $headingmoveafter = $moveafterslotnumber + 1;
            } else {
                                $headingmoveafter = $moveafterslotnumber;
            }
            $headingmovebefore = $movingslotnumber + 1;
            $headingmovedirection = 1;
        } else {
                        if ($page > $movingslot->page) {
                $headingmoveafter = $movingslotnumber;
                $headingmovebefore = $movingslotnumber + 2;
                $headingmovedirection = -1;
            } else if ($page < $movingslot->page) {
                $headingmoveafter = $movingslotnumber - 1;
                $headingmovebefore = $movingslotnumber + 1;
                $headingmovedirection = 1;
            } else {
                return;             }
        }

        if ($this->is_only_slot_in_section($movingslotnumber)) {
            throw new \coding_exception('You cannot remove the last slot in a section.');
        }

        $trans = $DB->start_delegated_transaction();

                if ($slotreorder) {
            update_field_with_unique_index('quiz_slots', 'slot', $slotreorder,
                    array('quizid' => $this->get_quizid()));
        }

                if ($movingslot->page != $page) {
            $DB->set_field('quiz_slots', 'page', $page,
                    array('id' => $movingslot->id));
        }

                $DB->execute("
                UPDATE {quiz_sections}
                   SET firstslot = firstslot + ?
                 WHERE quizid = ?
                   AND firstslot > ?
                   AND firstslot < ?
                ", array($headingmovedirection, $this->get_quizid(),
                        $headingmoveafter, $headingmovebefore));

                $emptypages = $DB->get_fieldset_sql("
                SELECT DISTINCT page - 1
                  FROM {quiz_slots} slot
                 WHERE quizid = ?
                   AND page > 1
                   AND NOT EXISTS (SELECT 1 FROM {quiz_slots} WHERE quizid = ? AND page = slot.page - 1)
              ORDER BY page - 1 DESC
                ", array($this->get_quizid(), $this->get_quizid()));

        foreach ($emptypages as $page) {
            $DB->execute("
                    UPDATE {quiz_slots}
                       SET page = page - 1
                     WHERE quizid = ?
                       AND page > ?
                    ", array($this->get_quizid(), $page));
        }

        $trans->allow_commit();
    }

    
    public function refresh_page_numbers($slots = array()) {
        global $DB;
                if (!count($slots)) {
            $slots = $DB->get_records('quiz_slots', array('quizid' => $this->get_quizid()), 'slot, page');
        }

                $pagenumbers = array('new' => 0, 'old' => 0);

        foreach ($slots as $slot) {
            if ($slot->page !== $pagenumbers['old']) {
                $pagenumbers['old'] = $slot->page;
                ++$pagenumbers['new'];
            }

            if ($pagenumbers['new'] == $slot->page) {
                continue;
            }
            $slot->page = $pagenumbers['new'];
        }

        return $slots;
    }

    
    public function refresh_page_numbers_and_update_db() {
        global $DB;
        $this->check_can_be_edited();

        $slots = $this->refresh_page_numbers();

                foreach ($slots as $slot) {
            $DB->set_field('quiz_slots', 'page', $slot->page,
                    array('id' => $slot->id));
        }

        return $slots;
    }

    
    public function remove_slot($slotnumber) {
        global $DB;

        $this->check_can_be_edited();

        if ($this->is_only_slot_in_section($slotnumber) && $this->get_section_count() > 1) {
            throw new \coding_exception('You cannot remove the last slot in a section.');
        }

        $slot = $DB->get_record('quiz_slots', array('quizid' => $this->get_quizid(), 'slot' => $slotnumber));
        if (!$slot) {
            return;
        }
        $maxslot = $DB->get_field_sql('SELECT MAX(slot) FROM {quiz_slots} WHERE quizid = ?', array($this->get_quizid()));

        $trans = $DB->start_delegated_transaction();
        $DB->delete_records('quiz_slots', array('id' => $slot->id));
        for ($i = $slot->slot + 1; $i <= $maxslot; $i++) {
            $DB->set_field('quiz_slots', 'slot', $i - 1,
                    array('quizid' => $this->get_quizid(), 'slot' => $i));
        }

        $qtype = $DB->get_field('question', 'qtype', array('id' => $slot->questionid));
        if ($qtype === 'random') {
                        question_delete_question($slot->questionid);
        }

        $DB->execute("
                UPDATE {quiz_sections}
                   SET firstslot = firstslot - 1
                 WHERE quizid = ?
                   AND firstslot > ?
                ", array($this->get_quizid(), $slotnumber));
        unset($this->questions[$slot->questionid]);

        $this->refresh_page_numbers_and_update_db();

        $trans->allow_commit();
    }

    
    public function update_slot_maxmark($slot, $maxmark) {
        global $DB;

        if (abs($maxmark - $slot->maxmark) < 1e-7) {
                        return false;
        }

        $trans = $DB->start_delegated_transaction();
        $slot->maxmark = $maxmark;
        $DB->update_record('quiz_slots', $slot);
        \question_engine::set_max_mark_in_attempts(new \qubaids_for_quiz($slot->quizid),
                $slot->slot, $maxmark);
        $trans->allow_commit();

        return true;
    }

    
    public function update_question_dependency($slotid, $requireprevious) {
        global $DB;
        $DB->set_field('quiz_slots', 'requireprevious', $requireprevious, array('id' => $slotid));
    }

    
    public function update_page_break($slotid, $type) {
        global $DB;

        $this->check_can_be_edited();

        $quizslots = $DB->get_records('quiz_slots', array('quizid' => $this->get_quizid()), 'slot');
        $repaginate = new \mod_quiz\repaginate($this->get_quizid(), $quizslots);
        $repaginate->repaginate_slots($quizslots[$slotid]->slot, $type);
        $slots = $this->refresh_page_numbers_and_update_db();

        return $slots;
    }

    
    public function add_section_heading($pagenumber, $heading = 'Section heading ...') {
        global $DB;
        $section = new \stdClass();
        $section->heading = $heading;
        $section->quizid = $this->get_quizid();
        $slotsonpage = $DB->get_records('quiz_slots', array('quizid' => $this->get_quizid(), 'page' => $pagenumber), 'slot DESC');
        $section->firstslot = end($slotsonpage)->slot;
        $section->shufflequestions = 0;
        return $DB->insert_record('quiz_sections', $section);
    }

    
    public function set_section_heading($id, $newheading) {
        global $DB;
        $section = $DB->get_record('quiz_sections', array('id' => $id), '*', MUST_EXIST);
        $section->heading = $newheading;
        $DB->update_record('quiz_sections', $section);
    }

    
    public function set_section_shuffle($id, $shuffle) {
        global $DB;
        $section = $DB->get_record('quiz_sections', array('id' => $id), '*', MUST_EXIST);
        $section->shufflequestions = $shuffle;
        $DB->update_record('quiz_sections', $section);
    }

    
    public function remove_section_heading($sectionid) {
        global $DB;
        $section = $DB->get_record('quiz_sections', array('id' => $sectionid), '*', MUST_EXIST);
        if ($section->firstslot == 1) {
            throw new \coding_exception('Cannot remove the first section in a quiz.');
        }
        $DB->delete_records('quiz_sections', array('id' => $sectionid));
    }
}
