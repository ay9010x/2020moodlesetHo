<?php




defined('MOODLE_INTERNAL') || die();



class moodle_quiz_exception extends moodle_exception {
    public function __construct($quizobj, $errorcode, $a = null, $link = '', $debuginfo = null) {
        if (!$link) {
            $link = $quizobj->view_url();
        }
        parent::__construct($errorcode, 'quiz', $link, $a, $debuginfo);
    }
}



class quiz {
    
    protected $course;
    
    protected $cm;
    
    protected $quiz;
    
    protected $context;

    
    protected $questions = null;
    
    protected $sections = null;
    
    protected $accessmanager = null;
    
    protected $ispreviewuser = null;

        
    public function __construct($quiz, $cm, $course, $getcontext = true) {
        $this->quiz = $quiz;
        $this->cm = $cm;
        $this->quiz->cmid = $this->cm->id;
        $this->course = $course;
        if ($getcontext && !empty($cm->id)) {
            $this->context = context_module::instance($cm->id);
        }
    }

    
    public static function create($quizid, $userid = null) {
        global $DB;

        $quiz = quiz_access_manager::load_quiz_and_settings($quizid);
        $course = $DB->get_record('course', array('id' => $quiz->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('quiz', $quiz->id, $course->id, false, MUST_EXIST);

                if ($userid) {
            $quiz = quiz_update_effective_access($quiz, $userid);
        }

        return new quiz($quiz, $cm, $course);
    }

    
    public function create_attempt_object($attemptdata) {
        return new quiz_attempt($attemptdata, $this->quiz, $this->cm, $this->course);
    }

    
    
    public function preload_questions() {
        $this->questions = question_preload_questions(null,
                'slot.maxmark, slot.id AS slotid, slot.slot, slot.page',
                '{quiz_slots} slot ON slot.quizid = :quizid AND q.id = slot.questionid',
                array('quizid' => $this->quiz->id), 'slot.slot');
    }

    
    public function load_questions($questionids = null) {
        if ($this->questions === null) {
            throw new coding_exception('You must call preload_questions before calling load_questions.');
        }
        if (is_null($questionids)) {
            $questionids = array_keys($this->questions);
        }
        $questionstoprocess = array();
        foreach ($questionids as $id) {
            if (array_key_exists($id, $this->questions)) {
                $questionstoprocess[$id] = $this->questions[$id];
            }
        }
        get_question_options($questionstoprocess);
    }

    
    public function get_structure() {
        return \mod_quiz\structure::create_for_quiz($this);
    }

        
    public function get_courseid() {
        return $this->course->id;
    }

    
    public function get_course() {
        return $this->course;
    }

    
    public function get_quizid() {
        return $this->quiz->id;
    }

    
    public function get_quiz() {
        return $this->quiz;
    }

    
    public function get_quiz_name() {
        return $this->quiz->name;
    }

    
    public function get_navigation_method() {
        return $this->quiz->navmethod;
    }

    
    public function get_num_attempts_allowed() {
        return $this->quiz->attempts;
    }

    
    public function get_cmid() {
        return $this->cm->id;
    }

    
    public function get_cm() {
        return $this->cm;
    }

    
    public function get_context() {
        return $this->context;
    }

    
    public function is_preview_user() {
        if (is_null($this->ispreviewuser)) {
            $this->ispreviewuser = has_capability('mod/quiz:preview', $this->context);
        }
        return $this->ispreviewuser;
    }

    
    public function has_questions() {
        if ($this->questions === null) {
            $this->preload_questions();
        }
        return !empty($this->questions);
    }

    
    public function get_question($id) {
        return $this->questions[$id];
    }

    
    public function get_questions($questionids = null) {
        if (is_null($questionids)) {
            $questionids = array_keys($this->questions);
        }
        $questions = array();
        foreach ($questionids as $id) {
            if (!array_key_exists($id, $this->questions)) {
                throw new moodle_exception('cannotstartmissingquestion', 'quiz', $this->view_url());
            }
            $questions[$id] = $this->questions[$id];
            $this->ensure_question_loaded($id);
        }
        return $questions;
    }

    
    public function get_sections() {
        global $DB;
        if ($this->sections === null) {
            $this->sections = array_values($DB->get_records('quiz_sections',
                    array('quizid' => $this->get_quizid()), 'firstslot'));
        }
        return $this->sections;
    }

    
    public function get_access_manager($timenow) {
        if (is_null($this->accessmanager)) {
            $this->accessmanager = new quiz_access_manager($this, $timenow,
                    has_capability('mod/quiz:ignoretimelimits', $this->context, null, false));
        }
        return $this->accessmanager;
    }

    
    public function has_capability($capability, $userid = null, $doanything = true) {
        return has_capability($capability, $this->context, $userid, $doanything);
    }

    
    public function require_capability($capability, $userid = null, $doanything = true) {
        return require_capability($capability, $this->context, $userid, $doanything);
    }

        
    public function view_url() {
        global $CFG;
        return $CFG->wwwroot . '/mod/quiz/view.php?id=' . $this->cm->id;
    }

    
    public function edit_url() {
        global $CFG;
        return $CFG->wwwroot . '/mod/quiz/edit.php?cmid=' . $this->cm->id;
    }

    
    public function attempt_url($attemptid, $page = 0) {
        global $CFG;
        $url = $CFG->wwwroot . '/mod/quiz/attempt.php?attempt=' . $attemptid;
        if ($page) {
            $url .= '&page=' . $page;
        }
        return $url;
    }

    
    public function start_attempt_url($page = 0) {
        $params = array('cmid' => $this->cm->id, 'sesskey' => sesskey());
        if ($page) {
            $params['page'] = $page;
        }
        return new moodle_url('/mod/quiz/startattempt.php', $params);
    }

    
    public function review_url($attemptid) {
        return new moodle_url('/mod/quiz/review.php', array('attempt' => $attemptid));
    }

    
    public function summary_url($attemptid) {
        return new moodle_url('/mod/quiz/summary.php', array('attempt' => $attemptid));
    }

    
    
    public function confirm_start_attempt_message($notused) {
        debugging('confirm_start_attempt_message is deprecated. ' .
                'This sort of functionality is now entirely handled by quiz access rules.');
        return '';
    }

    
    public function cannot_review_message($when, $short = false) {

        if ($short) {
            $langstrsuffix = 'short';
            $dateformat = get_string('strftimedatetimeshort', 'langconfig');
        } else {
            $langstrsuffix = '';
            $dateformat = '';
        }

        if ($when == mod_quiz_display_options::DURING ||
                $when == mod_quiz_display_options::IMMEDIATELY_AFTER) {
            return '';
        } else if ($when == mod_quiz_display_options::LATER_WHILE_OPEN && $this->quiz->timeclose &&
                $this->quiz->reviewattempt & mod_quiz_display_options::AFTER_CLOSE) {
            return get_string('noreviewuntil' . $langstrsuffix, 'quiz',
                    userdate($this->quiz->timeclose, $dateformat));
        } else {
            return get_string('noreview' . $langstrsuffix, 'quiz');
        }
    }

    
    public function navigation($title) {
        global $PAGE;
        $PAGE->navbar->add($title);
        return '';
    }

        
    protected function ensure_question_loaded($id) {
        if (isset($this->questions[$id]->_partiallyloaded)) {
            throw new moodle_quiz_exception($this, 'questionnotloaded', $id);
        }
    }

    
    public function get_all_question_types_used($includepotential = false) {
        $questiontypes = array();

                $qcategories = array();

                        foreach ($this->get_questions() as $questiondata) {
            if ($questiondata->qtype == 'random' and $includepotential) {
                $includesubcategories = (bool) $questiondata->questiontext;
                if (!isset($qcategories[$questiondata->category])) {
                    $qcategories[$questiondata->category] = false;
                }
                if ($includesubcategories) {
                    $qcategories[$questiondata->category] = true;
                }
            } else {
                if (!in_array($questiondata->qtype, $questiontypes)) {
                    $questiontypes[] = $questiondata->qtype;
                }
            }
        }

        if (!empty($qcategories)) {
                        $categoriestolook = array();
            foreach ($qcategories as $cat => $includesubcats) {
                if ($includesubcats) {
                    $categoriestolook = array_merge($categoriestolook, question_categorylist($cat));
                } else {
                    $categoriestolook[] = $cat;
                }
            }
            $questiontypesincategories = question_bank::get_all_question_types_in_categories($categoriestolook);
            $questiontypes = array_merge($questiontypes, $questiontypesincategories);
        }
        $questiontypes = array_unique($questiontypes);
        sort($questiontypes);

        return $questiontypes;
    }
}



class quiz_attempt {

    
    const IN_PROGRESS = 'inprogress';
    
    const OVERDUE     = 'overdue';
    
    const FINISHED    = 'finished';
    
    const ABANDONED   = 'abandoned';

    
    const MAX_SLOTS_FOR_DEFAULT_REVIEW_SHOW_ALL = 50;

    
    protected $quizobj;

    
    protected $attempt;

    
    protected $quba;

    
    protected $slots;

    
    protected $sections;

    
    protected $pagelayout;

    
    protected $questionnumbers;

    
    protected $questionpages;

    
    protected $reviewoptions = null;

        
    public function __construct($attempt, $quiz, $cm, $course, $loadquestions = true) {
        global $DB;

        $this->attempt = $attempt;
        $this->quizobj = new quiz($quiz, $cm, $course);

        if (!$loadquestions) {
            return;
        }

        $this->quba = question_engine::load_questions_usage_by_activity($this->attempt->uniqueid);
        $this->slots = $DB->get_records('quiz_slots',
                array('quizid' => $this->get_quizid()), 'slot',
                'slot, requireprevious, questionid');
        $this->sections = array_values($DB->get_records('quiz_sections',
                array('quizid' => $this->get_quizid()), 'firstslot'));

        $this->link_sections_and_slots();
        $this->determine_layout();
        $this->number_questions();
    }

    
    protected static function create_helper($conditions) {
        global $DB;

        $attempt = $DB->get_record('quiz_attempts', $conditions, '*', MUST_EXIST);
        $quiz = quiz_access_manager::load_quiz_and_settings($attempt->quiz);
        $course = $DB->get_record('course', array('id' => $quiz->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('quiz', $quiz->id, $course->id, false, MUST_EXIST);

                $quiz = quiz_update_effective_access($quiz, $attempt->userid);

        return new quiz_attempt($attempt, $quiz, $cm, $course);
    }

    
    public static function create($attemptid) {
        return self::create_helper(array('id' => $attemptid));
    }

    
    public static function create_from_usage_id($usageid) {
        return self::create_helper(array('uniqueid' => $usageid));
    }

    
    public static function state_name($state) {
        return quiz_attempt_state_name($state);
    }

    
    protected function link_sections_and_slots() {
        foreach ($this->sections as $i => $section) {
            if (isset($this->sections[$i + 1])) {
                $section->lastslot = $this->sections[$i + 1]->firstslot - 1;
            } else {
                $section->lastslot = count($this->slots);
            }
            for ($slot = $section->firstslot; $slot <= $section->lastslot; $slot += 1) {
                $this->slots[$slot]->section = $section;
            }
        }
    }

    
    protected function determine_layout() {
        $this->pagelayout = array();

                $pagelayouts = explode(',0', $this->attempt->layout);

                if (end($pagelayouts) == '') {
            array_pop($pagelayouts);
        }

                                                $unseensections = $this->sections;
        $this->pagelayout = array();
        foreach ($pagelayouts as $page => $pagelayout) {
            $pagelayout = trim($pagelayout, ',');
            if ($pagelayout == '') {
                continue;
            }
            $this->pagelayout[$page] = explode(',', $pagelayout);
            foreach ($this->pagelayout[$page] as $slot) {
                $sectionkey = array_search($this->slots[$slot]->section, $unseensections);
                if ($sectionkey !== false) {
                    $this->slots[$slot]->firstinsection = true;
                    unset($unseensections[$sectionkey]);
                } else {
                    $this->slots[$slot]->firstinsection = false;
                }
            }
        }
    }

    
    protected function number_questions() {
        $number = 1;
        foreach ($this->pagelayout as $page => $slots) {
            foreach ($slots as $slot) {
                if ($length = $this->is_real_question($slot)) {
                    $this->questionnumbers[$slot] = $number;
                    $number += $length;
                } else {
                    $this->questionnumbers[$slot] = get_string('infoshort', 'quiz');
                }
                $this->questionpages[$slot] = $page;
            }
        }
    }

    
    public function force_page_number_into_range($page) {
        return min(max($page, 0), count($this->pagelayout) - 1);
    }

        public function get_quiz() {
        return $this->quizobj->get_quiz();
    }

    public function get_quizobj() {
        return $this->quizobj;
    }

    
    public function get_courseid() {
        return $this->quizobj->get_courseid();
    }

    
    public function get_course() {
        return $this->quizobj->get_course();
    }

    
    public function get_quizid() {
        return $this->quizobj->get_quizid();
    }

    
    public function get_quiz_name() {
        return $this->quizobj->get_quiz_name();
    }

    
    public function get_navigation_method() {
        return $this->quizobj->get_navigation_method();
    }

    
    public function get_cm() {
        return $this->quizobj->get_cm();
    }

    
    public function get_cmid() {
        return $this->quizobj->get_cmid();
    }

    
    public function is_preview_user() {
        return $this->quizobj->is_preview_user();
    }

    
    public function get_num_attempts_allowed() {
        return $this->quizobj->get_num_attempts_allowed();
    }

    
    public function get_num_pages() {
        return count($this->pagelayout);
    }

    
    public function get_access_manager($timenow) {
        return $this->quizobj->get_access_manager($timenow);
    }

    
    public function get_attemptid() {
        return $this->attempt->id;
    }

    
    public function get_uniqueid() {
        return $this->attempt->uniqueid;
    }

    
    public function get_attempt() {
        return $this->attempt;
    }

    
    public function get_attempt_number() {
        return $this->attempt->attempt;
    }

    
    public function get_state() {
        return $this->attempt->state;
    }

    
    public function get_userid() {
        return $this->attempt->userid;
    }

    
    public function get_currentpage() {
        return $this->attempt->currentpage;
    }

    public function get_sum_marks() {
        return $this->attempt->sumgrades;
    }

    
    public function is_finished() {
        return $this->attempt->state == self::FINISHED || $this->attempt->state == self::ABANDONED;
    }

    
    public function is_preview() {
        return $this->attempt->preview;
    }

    
    public function is_own_attempt() {
        global $USER;
        return $this->attempt->userid == $USER->id;
    }

    
    public function is_own_preview() {
        global $USER;
        return $this->is_own_attempt() &&
                $this->is_preview_user() && $this->attempt->preview;
    }

    
    public function is_review_allowed() {
        if (!$this->has_capability('mod/quiz:viewreports')) {
            return false;
        }

        $cm = $this->get_cm();
        if ($this->has_capability('moodle/site:accessallgroups') ||
                groups_get_activity_groupmode($cm) != SEPARATEGROUPS) {
            return true;
        }

                $teachersgroups = groups_get_activity_allowed_groups($cm);
        $studentsgroups = groups_get_all_groups(
                $cm->course, $this->attempt->userid, $cm->groupingid);
        return $teachersgroups && $studentsgroups &&
                array_intersect(array_keys($teachersgroups), array_keys($studentsgroups));
    }

    
    public function has_response_to_at_least_one_graded_question() {
        foreach ($this->quba->get_attempt_iterator() as $qa) {
            if ($qa->get_max_mark() == 0) {
                continue;
            }
            if ($qa->get_num_steps() > 1) {
                return true;
            }
        }
        return false;
    }

    
    public function get_additional_summary_data(question_display_options $options) {
        return $this->quba->get_summary_information($options);
    }

    
    public function get_overall_feedback($grade) {
        return quiz_feedback_for_grade($grade, $this->get_quiz(),
                $this->quizobj->get_context());
    }

    
    public function has_capability($capability, $userid = null, $doanything = true) {
        return $this->quizobj->has_capability($capability, $userid, $doanything);
    }

    
    public function require_capability($capability, $userid = null, $doanything = true) {
        return $this->quizobj->require_capability($capability, $userid, $doanything);
    }

    
    public function check_review_capability() {
        if ($this->get_attempt_state() == mod_quiz_display_options::IMMEDIATELY_AFTER) {
            $capability = 'mod/quiz:attempt';
        } else {
            $capability = 'mod/quiz:reviewmyattempts';
        }

                        
        if ($this->has_capability($capability)) {
                        return;
        }

        if ($this->has_capability('mod/quiz:viewreports') ||
                $this->has_capability('mod/quiz:preview')) {
                        return;
        }

                                $this->require_capability($capability);
    }

    
    public function can_navigate_to($slot) {
        switch ($this->get_navigation_method()) {
            case QUIZ_NAVMETHOD_FREE:
                return true;
                break;
            case QUIZ_NAVMETHOD_SEQ:
                return false;
                break;
        }
        return true;
    }

    
    public function get_attempt_state() {
        return quiz_attempt_state($this->get_quiz(), $this->attempt);
    }

    
    public function get_display_options($reviewing) {
        if ($reviewing) {
            if (is_null($this->reviewoptions)) {
                $this->reviewoptions = quiz_get_review_options($this->get_quiz(),
                        $this->attempt, $this->quizobj->get_context());
                if ($this->is_own_preview()) {
                                                            $this->reviewoptions->attempt = true;
                }
            }
            return $this->reviewoptions;

        } else {
            $options = mod_quiz_display_options::make_from_quiz($this->get_quiz(),
                    mod_quiz_display_options::DURING);
            $options->flags = quiz_get_flag_option($this->attempt, $this->quizobj->get_context());
            return $options;
        }
    }

    
    public function get_display_options_with_edit_link($reviewing, $slot, $thispageurl) {
        $options = clone($this->get_display_options($reviewing));

        if (!$thispageurl) {
            return $options;
        }

        if (!($reviewing || $this->is_preview())) {
            return $options;
        }

        $question = $this->quba->get_question($slot);
        if (!question_has_capability_on($question, 'edit', $question->category)) {
            return $options;
        }

        $options->editquestionparams['cmid'] = $this->get_cmid();
        $options->editquestionparams['returnurl'] = $thispageurl;

        return $options;
    }

    
    public function is_last_page($page) {
        return $page == count($this->pagelayout) - 1;
    }

    
    public function get_slots($page = 'all') {
        if ($page === 'all') {
            $numbers = array();
            foreach ($this->pagelayout as $numbersonpage) {
                $numbers = array_merge($numbers, $numbersonpage);
            }
            return $numbers;
        } else {
            return $this->pagelayout[$page];
        }
    }

    
    public function get_active_slots($page = 'all') {
        $activeslots = array();
        foreach ($this->get_slots($page) as $slot) {
            if (!$this->is_blocked_by_previous_question($slot)) {
                $activeslots[] = $slot;
            }
        }
        return $activeslots;
    }

    
    public function get_question_attempt($slot) {
        return $this->quba->get_question_attempt($slot);
    }

    
    public function all_question_attempts_originally_in_slot($slot) {
        $qas = array();
        foreach ($this->quba->get_attempt_iterator() as $qa) {
            if ($qa->get_metadata('originalslot') == $slot) {
                $qas[] = $qa;
            }
        }
        $qas[] = $this->quba->get_question_attempt($slot);
        return $qas;
    }

    
    public function is_real_question($slot) {
        return $this->quba->get_question($slot)->length;
    }

    
    public function is_question_flagged($slot) {
        return $this->quba->get_question_attempt($slot)->is_flagged();
    }

    
    public function is_blocked_by_previous_question($slot) {
        return $slot > 1 && isset($this->slots[$slot]) && $this->slots[$slot]->requireprevious &&
                !$this->slots[$slot]->section->shufflequestions &&
                !$this->slots[$slot - 1]->section->shufflequestions &&
                $this->get_navigation_method() != QUIZ_NAVMETHOD_SEQ &&
                !$this->get_question_state($slot - 1)->is_finished() &&
                $this->quba->can_question_finish_during_attempt($slot - 1);
    }

    
    public function can_question_be_redone_now($slot) {
        return $this->get_quiz()->canredoquestions && !$this->is_finished() &&
                $this->get_question_state($slot)->is_finished();
    }

    
    public function get_original_slot($slot) {
        $originalslot = $this->quba->get_question_attempt_metadata($slot, 'originalslot');
        if ($originalslot) {
            return $originalslot;
        } else {
            return $slot;
        }
    }

    
    public function get_question_number($slot) {
        return $this->questionnumbers[$slot];
    }

    
    public function get_heading_before_slot($slot) {
        if ($this->slots[$slot]->firstinsection) {
            return $this->slots[$slot]->section->heading;
        } else {
            return null;
        }
    }

    
    public function get_question_page($slot) {
        return $this->questionpages[$slot];
    }

    
    public function get_question_name($slot) {
        return $this->quba->get_question($slot)->name;
    }

    
    public function get_question_state($slot) {
        return $this->quba->get_question_state($slot);
    }

    
    public function get_question_status($slot, $showcorrectness) {
        return $this->quba->get_question_state_string($slot, $showcorrectness);
    }

    
    public function get_question_state_class($slot, $showcorrectness) {
        return $this->quba->get_question_state_class($slot, $showcorrectness);
    }

    
    public function get_question_mark($slot) {
        return quiz_format_question_grade($this->get_quiz(), $this->quba->get_question_mark($slot));
    }

    
    public function get_question_action_time($slot) {
        return $this->quba->get_question_action_time($slot);
    }

    
    public function get_question_type_name($slot) {
        return $this->quba->get_question($slot)->get_type_name();
    }

    
    public function get_time_left_display($timenow) {
        if ($this->attempt->state != self::IN_PROGRESS) {
            return false;
        }
        return $this->get_access_manager($timenow)->get_time_left_display($this->attempt, $timenow);
    }


    
    public function get_submitted_date() {
        return $this->attempt->timefinish;
    }

    
    public function get_due_date() {
        $deadlines = array();
        if ($this->quizobj->get_quiz()->timelimit) {
            $deadlines[] = $this->attempt->timestart + $this->quizobj->get_quiz()->timelimit;
        }
        if ($this->quizobj->get_quiz()->timeclose) {
            $deadlines[] = $this->quizobj->get_quiz()->timeclose;
        }
        if ($deadlines) {
            $duedate = min($deadlines);
        } else {
            return false;
        }

        switch ($this->attempt->state) {
            case self::IN_PROGRESS:
                return $duedate;

            case self::OVERDUE:
                return $duedate + $this->quizobj->get_quiz()->graceperiod;

            default:
                throw new coding_exception('Unexpected state: ' . $this->attempt->state);
        }
    }

        
    public function view_url() {
        return $this->quizobj->view_url();
    }

    
    public function start_attempt_url($slot = null, $page = -1) {
        if ($page == -1 && !is_null($slot)) {
            $page = $this->get_question_page($slot);
        } else {
            $page = 0;
        }
        return $this->quizobj->start_attempt_url($page);
    }

    
    public function attempt_url($slot = null, $page = -1, $thispage = -1) {
        return $this->page_and_question_url('attempt', $slot, $page, false, $thispage);
    }

    
    public function summary_url() {
        return new moodle_url('/mod/quiz/summary.php', array('attempt' => $this->attempt->id));
    }

    
    public function processattempt_url() {
        return new moodle_url('/mod/quiz/processattempt.php');
    }

    
    public function review_url($slot = null, $page = -1, $showall = null, $thispage = -1) {
        return $this->page_and_question_url('review', $slot, $page, $showall, $thispage);
    }

    
    public function get_default_show_all($script) {
        return $script == 'review' && count($this->questionpages) < self::MAX_SLOTS_FOR_DEFAULT_REVIEW_SHOW_ALL;
    }

    
    
    public function cannot_review_message($short = false) {
        return $this->quizobj->cannot_review_message(
                $this->get_attempt_state(), $short);
    }

    
    public function get_html_head_contributions($page = 'all', $showall = false) {
        if ($showall) {
            $page = 'all';
        }
        $result = '';
        foreach ($this->get_slots($page) as $slot) {
            $result .= $this->quba->render_question_head_html($slot);
        }
        $result .= question_engine::initialise_js();
        return $result;
    }

    
    public function get_question_html_head_contributions($slot) {
        return $this->quba->render_question_head_html($slot) .
                question_engine::initialise_js();
    }

    
    public function restart_preview_button() {
        global $OUTPUT;
        if ($this->is_preview() && $this->is_preview_user()) {
            return $OUTPUT->single_button(new moodle_url(
                    $this->start_attempt_url(), array('forcenew' => true)),
                    get_string('startnewpreview', 'quiz'));
        } else {
            return '';
        }
    }

    
    public function render_question($slot, $reviewing, mod_quiz_renderer $renderer, $thispageurl = null) {
        if ($this->is_blocked_by_previous_question($slot)) {
            $placeholderqa = $this->make_blocked_question_placeholder($slot);

            $displayoptions = $this->get_display_options($reviewing);
            $displayoptions->manualcomment = question_display_options::HIDDEN;
            $displayoptions->history = question_display_options::HIDDEN;
            $displayoptions->readonly = true;

            return html_writer::div($placeholderqa->render($displayoptions,
                    $this->get_question_number($this->get_original_slot($slot))),
                    'mod_quiz-blocked_question_warning');
        }

        return $this->render_question_helper($slot, $reviewing, $thispageurl, $renderer, null);
    }

    
    protected function render_question_helper($slot, $reviewing, $thispageurl, mod_quiz_renderer $renderer, $seq) {
        $originalslot = $this->get_original_slot($slot);
        $number = $this->get_question_number($originalslot);
        $displayoptions = $this->get_display_options_with_edit_link($reviewing, $slot, $thispageurl);

        if ($slot != $originalslot) {
            $originalmaxmark = $this->get_question_attempt($slot)->get_max_mark();
            $this->get_question_attempt($slot)->set_max_mark($this->get_question_attempt($originalslot)->get_max_mark());
        }

        if ($this->can_question_be_redone_now($slot)) {
            $displayoptions->extrainfocontent = $renderer->redo_question_button(
                    $slot, $displayoptions->readonly);
        }

        if ($displayoptions->history && $displayoptions->questionreviewlink) {
            $links = $this->links_to_other_redos($slot, $displayoptions->questionreviewlink);
            if ($links) {
                $displayoptions->extrahistorycontent = html_writer::tag('p',
                        get_string('redoesofthisquestion', 'quiz', $renderer->render($links)));
            }
        }

        if ($seq === null) {
            $output = $this->quba->render_question($slot, $displayoptions, $number);
        } else {
            $output = $this->quba->render_question_at_step($slot, $seq, $displayoptions, $number);
        }

        if ($slot != $originalslot) {
            $this->get_question_attempt($slot)->set_max_mark($originalmaxmark);
        }

        return $output;
    }

    
    protected function make_blocked_question_placeholder($slot) {
        $replacedquestion = $this->get_question_attempt($slot)->get_question();

        question_bank::load_question_definition_classes('description');
        $question = new qtype_description_question();
        $question->id = $replacedquestion->id;
        $question->category = null;
        $question->parent = 0;
        $question->qtype = question_bank::get_qtype('description');
        $question->name = '';
        $question->questiontext = get_string('questiondependsonprevious', 'quiz');
        $question->questiontextformat = FORMAT_HTML;
        $question->generalfeedback = '';
        $question->defaultmark = $this->quba->get_question_max_mark($slot);
        $question->length = $replacedquestion->length;
        $question->penalty = 0;
        $question->stamp = '';
        $question->version = 0;
        $question->hidden = 0;
        $question->timecreated = null;
        $question->timemodified = null;
        $question->createdby = null;
        $question->modifiedby = null;

        $placeholderqa = new question_attempt($question, $this->quba->get_id(),
                null, $this->quba->get_question_max_mark($slot));
        $placeholderqa->set_slot($slot);
        $placeholderqa->start($this->get_quiz()->preferredbehaviour, 1);
        $placeholderqa->set_flagged($this->is_question_flagged($slot));
        return $placeholderqa;
    }

    
    public function render_question_at_step($slot, $seq, $reviewing, mod_quiz_renderer $renderer, $thispageurl = '') {
        return $this->render_question_helper($slot, $reviewing, $thispageurl, $renderer, $seq);
    }

    
    public function render_question_for_commenting($slot) {
        $options = $this->get_display_options(true);
        $options->hide_all_feedback();
        $options->manualcomment = question_display_options::EDITABLE;
        return $this->quba->render_question($slot, $options,
                $this->get_question_number($slot));
    }

    
    public function check_file_access($slot, $reviewing, $contextid, $component,
            $filearea, $args, $forcedownload) {
        $options = $this->get_display_options($reviewing);

                        if ($reviewing && $this->is_own_attempt() && !$options->attempt) {
            return false;
        }

        if ($reviewing && !$this->is_own_attempt() && !$this->is_review_allowed()) {
            return false;
        }

        return $this->quba->check_file_access($slot, $options,
                $component, $filearea, $args, $forcedownload);
    }

    
    public function get_navigation_panel(mod_quiz_renderer $output,
             $panelclass, $page, $showall = false) {
        $panel = new $panelclass($this, $this->get_display_options(true), $page, $showall);

        $bc = new block_contents();
        $bc->attributes['id'] = 'mod_quiz_navblock';
        $bc->attributes['role'] = 'navigation';
        $bc->attributes['aria-labelledby'] = 'mod_quiz_navblock_title';
        $bc->title = html_writer::span(get_string('quiznavigation', 'quiz'), '', array('id' => 'mod_quiz_navblock_title'));
        $bc->content = $output->navigation_panel($panel);
        return $bc;
    }

    
    public function links_to_other_attempts(moodle_url $url) {
        $attempts = quiz_get_user_attempts($this->get_quiz()->id, $this->attempt->userid, 'all');
        if (count($attempts) <= 1) {
            return false;
        }

        $links = new mod_quiz_links_to_other_attempts();
        foreach ($attempts as $at) {
            if ($at->id == $this->attempt->id) {
                $links->links[$at->attempt] = null;
            } else {
                $links->links[$at->attempt] = new moodle_url($url, array('attempt' => $at->id));
            }
        }
        return $links;
    }

    
    public function links_to_other_redos($slot, moodle_url $baseurl) {
        $originalslot = $this->get_original_slot($slot);

        $qas = $this->all_question_attempts_originally_in_slot($originalslot);
        if (count($qas) <= 1) {
            return null;
        }

        $links = new mod_quiz_links_to_other_attempts();
        $index = 1;
        foreach ($qas as $qa) {
            if ($qa->get_slot() == $slot) {
                $links->links[$index] = null;
            } else {
                $url = new moodle_url($baseurl, array('slot' => $qa->get_slot()));
                $links->links[$index] = new action_link($url, $index,
                        new popup_action('click', $url, 'reviewquestion',
                                array('width' => 450, 'height' => 650)),
                        array('title' => get_string('reviewresponse', 'question')));
            }
            $index++;
        }
        return $links;
    }

    
    
    public function handle_if_time_expired($timestamp, $studentisonline) {
        global $DB;

        $timeclose = $this->get_access_manager($timestamp)->get_end_time($this->attempt);

        if ($timeclose === false || $this->is_preview()) {
            $this->update_timecheckstate(null);
            return;         }
        if ($timestamp < $timeclose) {
            $this->update_timecheckstate($timeclose);
            return;         }

                if ($this->attempt->state == self::OVERDUE) {
            $timeoverdue = $timestamp - $timeclose;
            $graceperiod = $this->quizobj->get_quiz()->graceperiod;
            if ($timeoverdue >= $graceperiod) {
                $this->process_abandon($timestamp, $studentisonline);
            } else {
                                $this->update_timecheckstate($timeclose + $graceperiod);
            }
            return;         }

        if ($this->attempt->state != self::IN_PROGRESS) {
            $this->update_timecheckstate(null);
            return;         }

                        switch ($this->quizobj->get_quiz()->overduehandling) {
            case 'autosubmit':
                $this->process_finish($timestamp, false);
                return;

            case 'graceperiod':
                $this->process_going_overdue($timestamp, $studentisonline);
                return;

            case 'autoabandon':
                $this->process_abandon($timestamp, $studentisonline);
                return;
        }

                $this->process_abandon($timestamp, $studentisonline);
        return;
    }

    
    public function process_submitted_actions($timestamp, $becomingoverdue = false, $simulatedresponses = null) {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        if ($simulatedresponses !== null) {
            $simulatedpostdata = $this->quba->prepare_simulated_post_data($simulatedresponses);
        } else {
            $simulatedpostdata = null;
        }

        $this->quba->process_all_actions($timestamp, $simulatedpostdata);
        question_engine::save_questions_usage_by_activity($this->quba);

        $this->attempt->timemodified = $timestamp;
        if ($this->attempt->state == self::FINISHED) {
            $this->attempt->sumgrades = $this->quba->get_total_mark();
        }
        if ($becomingoverdue) {
            $this->process_going_overdue($timestamp, true);
        } else {
            $DB->update_record('quiz_attempts', $this->attempt);
        }

        if (!$this->is_preview() && $this->attempt->state == self::FINISHED) {
            quiz_save_best_grade($this->get_quiz(), $this->get_userid());
        }

        $transaction->allow_commit();
    }

    
    public function process_redo_question($slot, $timestamp) {
        global $DB;

        if (!$this->can_question_be_redone_now($slot)) {
            throw new coding_exception('Attempt to restart the question in slot ' . $slot .
                    ' when it is not in a state to be restarted.');
        }

        $qubaids = new \mod_quiz\question\qubaids_for_users_attempts(
                $this->get_quizid(), $this->get_userid());

        $transaction = $DB->start_delegated_transaction();

                $questiondata = $DB->get_record('question',
                array('id' => $this->slots[$slot]->questionid));
        if ($questiondata->qtype != 'random') {
            $newqusetionid = $questiondata->id;
        } else {
            $randomloader = new \core_question\bank\random_question_loader($qubaids, array());
            $newqusetionid = $randomloader->get_next_question_id($questiondata->category,
                    (bool) $questiondata->questiontext);
            if ($newqusetionid === null) {
                throw new moodle_exception('notenoughrandomquestions', 'quiz',
                        $quizobj->view_url(), $questiondata);
            }
        }

                $newquestion = question_bank::load_question($newqusetionid);
        $newslot = $this->quba->add_question_in_place_of_other($slot, $newquestion);

                if ($newquestion->get_num_variants() == 1) {
            $variant = 1;
        } else {
            $variantstrategy = new core_question\engine\variants\least_used_strategy(
                    $this->quba, $qubaids);
            $variant = $variantstrategy->choose_variant($newquestion->get_num_variants(),
                    $newquestion->get_variants_selection_seed());
        }

                $this->quba->start_question($slot, $variant);
        $this->quba->set_max_mark($newslot, 0);
        $this->quba->set_question_attempt_metadata($newslot, 'originalslot', $slot);
        question_engine::save_questions_usage_by_activity($this->quba);

        $transaction->allow_commit();
    }

    
    public function process_auto_save($timestamp) {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        $this->quba->process_all_autosaves($timestamp);
        question_engine::save_questions_usage_by_activity($this->quba);

        $transaction->allow_commit();
    }

    
    public function save_question_flags() {
        global $DB;

        $transaction = $DB->start_delegated_transaction();
        $this->quba->update_question_flags();
        question_engine::save_questions_usage_by_activity($this->quba);
        $transaction->allow_commit();
    }

    public function process_finish($timestamp, $processsubmitted) {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        if ($processsubmitted) {
            $this->quba->process_all_actions($timestamp);
        }
        $this->quba->finish_all_questions($timestamp);

        question_engine::save_questions_usage_by_activity($this->quba);

        $this->attempt->timemodified = $timestamp;
        $this->attempt->timefinish = $timestamp;
        $this->attempt->sumgrades = $this->quba->get_total_mark();
        $this->attempt->state = self::FINISHED;
        $this->attempt->timecheckstate = null;
        $DB->update_record('quiz_attempts', $this->attempt);

        if (!$this->is_preview()) {
            quiz_save_best_grade($this->get_quiz(), $this->attempt->userid);

                        $this->fire_state_transition_event('\mod_quiz\event\attempt_submitted', $timestamp);

                        $this->get_access_manager($timestamp)->current_attempt_finished();
        }

        $transaction->allow_commit();
    }

    
    public function update_timecheckstate($time) {
        global $DB;
        if ($this->attempt->timecheckstate !== $time) {
            $this->attempt->timecheckstate = $time;
            $DB->set_field('quiz_attempts', 'timecheckstate', $time, array('id' => $this->attempt->id));
        }
    }

    
    public function process_going_overdue($timestamp, $studentisonline) {
        global $DB;

        $transaction = $DB->start_delegated_transaction();
        $this->attempt->timemodified = $timestamp;
        $this->attempt->state = self::OVERDUE;
                        $this->attempt->timecheckstate = $timestamp;
        $DB->update_record('quiz_attempts', $this->attempt);

        $this->fire_state_transition_event('\mod_quiz\event\attempt_becameoverdue', $timestamp);

        $transaction->allow_commit();

        quiz_send_overdue_message($this);
    }

    
    public function process_abandon($timestamp, $studentisonline) {
        global $DB;

        $transaction = $DB->start_delegated_transaction();
        $this->attempt->timemodified = $timestamp;
        $this->attempt->state = self::ABANDONED;
        $this->attempt->timecheckstate = null;
        $DB->update_record('quiz_attempts', $this->attempt);

        $this->fire_state_transition_event('\mod_quiz\event\attempt_abandoned', $timestamp);

        $transaction->allow_commit();
    }

    
    protected function fire_state_transition_event($eventclass, $timestamp) {
        global $USER;
        $quizrecord = $this->get_quiz();
        $params = array(
            'context' => $this->get_quizobj()->get_context(),
            'courseid' => $this->get_courseid(),
            'objectid' => $this->attempt->id,
            'relateduserid' => $this->attempt->userid,
            'other' => array(
                'submitterid' => CLI_SCRIPT ? null : $USER->id,
                'quizid' => $quizrecord->id
            )
        );

        $event = $eventclass::create($params);
        $event->add_record_snapshot('quiz', $this->get_quiz());
        $event->add_record_snapshot('quiz_attempts', $this->get_attempt());
        $event->trigger();
    }

    
    
    protected function page_and_question_url($script, $slot, $page, $showall, $thispage) {

        $defaultshowall = $this->get_default_show_all($script);
        if ($showall === null && ($page == 0 || $page == -1)) {
            $showall = $defaultshowall;
        }

                if ($page == -1) {
            if ($slot !== null && !$showall) {
                $page = $this->get_question_page($slot);
            } else {
                $page = 0;
            }
        }

        if ($showall) {
            $page = 0;
        }

                $fragment = '';
        if ($slot !== null) {
            if ($slot == reset($this->pagelayout[$page])) {
                                $fragment = '#';
            } else {
                $fragment = '#q' . $slot;
            }
        }

                if ($thispage == $page) {
            return new moodle_url($fragment);

        } else {
            $url = new moodle_url('/mod/quiz/' . $script . '.php' . $fragment,
                    array('attempt' => $this->attempt->id));
            if ($page == 0 && $showall != $defaultshowall) {
                $url->param('showall', (int) $showall);
            } else if ($page > 0) {
                $url->param('page', $page);
            }
            return $url;
        }
    }

    
    public function process_attempt($timenow, $finishattempt, $timeup, $thispage) {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

                        $graceperiodmin = null;
        $accessmanager = $this->get_access_manager($timenow);
        $timeclose = $accessmanager->get_end_time($this->get_attempt());

                if ($this->is_preview()) {
            $timeclose = false;
        }
        $toolate = false;
        if ($timeclose !== false && $timenow > $timeclose - QUIZ_MIN_TIME_TO_CONTINUE) {
            $timeup = true;
            $graceperiodmin = get_config('quiz', 'graceperiodmin');
            if ($timenow > $timeclose + $graceperiodmin) {
                $toolate = true;
            }
        }

                $becomingoverdue = false;
        $becomingabandoned = false;
        if ($timeup) {
            if ($this->get_quiz()->overduehandling == 'graceperiod') {
                if (is_null($graceperiodmin)) {
                    $graceperiodmin = get_config('quiz', 'graceperiodmin');
                }
                if ($timenow > $timeclose + $this->get_quiz()->graceperiod + $graceperiodmin) {
                                        $finishattempt = true;
                    $becomingabandoned = true;
                } else {
                    $becomingoverdue = true;
                }
            } else {
                $finishattempt = true;
            }
        }

        
        if (!$finishattempt) {
                        if (!$toolate) {
                try {
                    $this->process_submitted_actions($timenow, $becomingoverdue);

                } catch (question_out_of_sequence_exception $e) {
                    throw new moodle_exception('submissionoutofsequencefriendlymessage', 'question',
                            $this->attempt_url(null, $thispage));

                } catch (Exception $e) {
                                                            $debuginfo = '';
                    if (!empty($e->debuginfo)) {
                        $debuginfo = $e->debuginfo;
                    }
                    throw new moodle_exception('errorprocessingresponses', 'question',
                            $this->attempt_url(null, $thispage), $e->getMessage(), $debuginfo);
                }

                if (!$becomingoverdue) {
                    foreach ($this->get_slots() as $slot) {
                        if (optional_param('redoslot' . $slot, false, PARAM_BOOL)) {
                            $this->process_redo_question($slot, $timenow);
                        }
                    }
                }

            } else {
                                $this->process_going_overdue($timenow, true);
            }

            $transaction->allow_commit();

            return $becomingoverdue ? self::OVERDUE : self::IN_PROGRESS;
        }

                try {
            if ($becomingabandoned) {
                $this->process_abandon($timenow, true);
            } else {
                $this->process_finish($timenow, !$toolate);
            }

        } catch (question_out_of_sequence_exception $e) {
            throw new moodle_exception('submissionoutofsequencefriendlymessage', 'question',
                    $this->attempt_url(null, $thispage));

        } catch (Exception $e) {
                                    $debuginfo = '';
            if (!empty($e->debuginfo)) {
                $debuginfo = $e->debuginfo;
            }
            throw new moodle_exception('errorprocessingresponses', 'question',
                    $this->attempt_url(null, $thispage), $e->getMessage(), $debuginfo);
        }

                $transaction->allow_commit();

        return $becomingabandoned ? self::ABANDONED : self::FINISHED;
    }

    
    public function check_page_access($page) {
        global $DB;

        if ($this->get_currentpage() != $page) {
            if ($this->get_navigation_method() == QUIZ_NAVMETHOD_SEQ && $this->get_currentpage() > $page) {
                return false;
            }
        }
        return true;
    }

    
    public function set_currentpage($page) {
        global $DB;

        if ($this->check_page_access($page)) {
            $DB->set_field('quiz_attempts', 'currentpage', $page, array('id' => $this->get_attemptid()));
            return true;
        }
        return false;
    }

    
    public function fire_attempt_viewed_event() {
        $params = array(
            'objectid' => $this->get_attemptid(),
            'relateduserid' => $this->get_userid(),
            'courseid' => $this->get_courseid(),
            'context' => context_module::instance($this->get_cmid()),
            'other' => array(
                'quizid' => $this->get_quizid()
            )
        );
        $event = \mod_quiz\event\attempt_viewed::create($params);
        $event->add_record_snapshot('quiz_attempts', $this->get_attempt());
        $event->trigger();
    }

    
    public function fire_attempt_summary_viewed_event() {

        $params = array(
            'objectid' => $this->get_attemptid(),
            'relateduserid' => $this->get_userid(),
            'courseid' => $this->get_courseid(),
            'context' => context_module::instance($this->get_cmid()),
            'other' => array(
                'quizid' => $this->get_quizid()
            )
        );
        $event = \mod_quiz\event\attempt_summary_viewed::create($params);
        $event->add_record_snapshot('quiz_attempts', $this->get_attempt());
        $event->trigger();
    }

    
    public function fire_attempt_reviewed_event() {

        $params = array(
            'objectid' => $this->get_attemptid(),
            'relateduserid' => $this->get_userid(),
            'courseid' => $this->get_courseid(),
            'context' => context_module::instance($this->get_cmid()),
            'other' => array(
                'quizid' => $this->get_quizid()
            )
        );
        $event = \mod_quiz\event\attempt_reviewed::create($params);
        $event->add_record_snapshot('quiz_attempts', $this->get_attempt());
        $event->trigger();
    }

}



class quiz_nav_section_heading implements renderable {
    
    public $heading;

    
    public function __construct($heading) {
        $this->heading = $heading;
    }
}



class quiz_nav_question_button implements renderable {
    
    public $id;
    
    public $number;
    
    public $stateclass;
    
    public $statestring;
    
    public $page;
    
    public $currentpage;
    
    public $flagged;
    
    public $url;
}



abstract class quiz_nav_panel_base {
    
    protected $attemptobj;
    
    protected $options;
    
    protected $page;
    
    protected $showall;

    public function __construct(quiz_attempt $attemptobj,
            question_display_options $options, $page, $showall) {
        $this->attemptobj = $attemptobj;
        $this->options = $options;
        $this->page = $page;
        $this->showall = $showall;
    }

    
    public function get_question_buttons() {
        $buttons = array();
        foreach ($this->attemptobj->get_slots() as $slot) {
            if ($heading = $this->attemptobj->get_heading_before_slot($slot)) {
                $buttons[] = new quiz_nav_section_heading(format_string($heading));
            }

            $qa = $this->attemptobj->get_question_attempt($slot);
            $showcorrectness = $this->options->correctness && $qa->has_marks();

            $button = new quiz_nav_question_button();
            $button->id          = 'quiznavbutton' . $slot;
            $button->number      = $this->attemptobj->get_question_number($slot);
            $button->stateclass  = $qa->get_state_class($showcorrectness);
            $button->navmethod   = $this->attemptobj->get_navigation_method();
            if (!$showcorrectness && $button->stateclass == 'notanswered') {
                $button->stateclass = 'complete';
            }
            $button->statestring = $this->get_state_string($qa, $showcorrectness);
            $button->page        = $this->attemptobj->get_question_page($slot);
            $button->currentpage = $this->showall || $button->page == $this->page;
            $button->flagged     = $qa->is_flagged();
            $button->url         = $this->get_question_url($slot);
            if ($this->attemptobj->is_blocked_by_previous_question($slot)) {
                $button->url = null;
                $button->stateclass = 'blocked';
                $button->statestring = get_string('questiondependsonprevious', 'quiz');
            }
            $buttons[] = $button;
        }

        return $buttons;
    }

    protected function get_state_string(question_attempt $qa, $showcorrectness) {
        if ($qa->get_question()->length > 0) {
            return $qa->get_state_string($showcorrectness);
        }

                if ($qa->get_state() == question_state::$todo) {
            return get_string('notyetviewed', 'quiz');
        } else {
            return get_string('viewed', 'quiz');
        }
    }

    public function render_before_button_bits(mod_quiz_renderer $output) {
        return '';
    }

    abstract public function render_end_bits(mod_quiz_renderer $output);

    protected function render_restart_preview_link($output) {
        if (!$this->attemptobj->is_own_preview()) {
            return '';
        }
        return $output->restart_preview_button(new moodle_url(
                $this->attemptobj->start_attempt_url(), array('forcenew' => true)));
    }

    protected abstract function get_question_url($slot);

    public function user_picture() {
        global $DB;
        if ($this->attemptobj->get_quiz()->showuserpicture == QUIZ_SHOWIMAGE_NONE) {
            return null;
        }
        $user = $DB->get_record('user', array('id' => $this->attemptobj->get_userid()));
        $userpicture = new user_picture($user);
        $userpicture->courseid = $this->attemptobj->get_courseid();
        if ($this->attemptobj->get_quiz()->showuserpicture == QUIZ_SHOWIMAGE_LARGE) {
            $userpicture->size = true;
        }
        return $userpicture;
    }

    
    public function get_button_container_class() {
                if ($this->showall) {
            return 'allquestionsononepage';
        }
                return 'multipages';
    }
}



class quiz_attempt_nav_panel extends quiz_nav_panel_base {
    public function get_question_url($slot) {
        if ($this->attemptobj->can_navigate_to($slot)) {
            return $this->attemptobj->attempt_url($slot, -1, $this->page);
        } else {
            return null;
        }
    }

    public function render_before_button_bits(mod_quiz_renderer $output) {
        return html_writer::tag('div', get_string('navnojswarning', 'quiz'),
                array('id' => 'quiznojswarning'));
    }

    public function render_end_bits(mod_quiz_renderer $output) {
        return html_writer::link($this->attemptobj->summary_url(),
                get_string('endtest', 'quiz'), array('class' => 'endtestlink')) .
                $output->countdown_timer($this->attemptobj, time()) .
                $this->render_restart_preview_link($output);
    }
}



class quiz_review_nav_panel extends quiz_nav_panel_base {
    public function get_question_url($slot) {
        return $this->attemptobj->review_url($slot, -1, $this->showall, $this->page);
    }

    public function render_end_bits(mod_quiz_renderer $output) {
        $html = '';
        if ($this->attemptobj->get_num_pages() > 1) {
            if ($this->showall) {
                $html .= html_writer::link($this->attemptobj->review_url(null, 0, false),
                        get_string('showeachpage', 'quiz'));
            } else {
                $html .= html_writer::link($this->attemptobj->review_url(null, 0, true),
                        get_string('showall', 'quiz'));
            }
        }
        $html .= $output->finish_review_link($this->attemptobj);
        $html .= $this->render_restart_preview_link($output);
        return $html;
    }
}
