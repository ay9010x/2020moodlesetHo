<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');



abstract class quiz_attempts_report_table extends table_sql {
    public $useridfield = 'userid';

    
    protected $reporturl;

    
    protected $displayoptions;

    
    protected $lateststeps = null;

    
    protected $quiz;

    
    protected $context;

    
    protected $qmsubselect;

    
    protected $options;

    
    protected $groupstudents;

    
    protected $students;

    
    protected $questions;

    
    protected $includecheckboxes;

    
    public function __construct($uniqueid, $quiz, $context, $qmsubselect,
            mod_quiz_attempts_report_options $options, $groupstudents, $students,
            $questions, $reporturl) {
        parent::__construct($uniqueid);
        $this->quiz = $quiz;
        $this->context = $context;
        $this->qmsubselect = $qmsubselect;
        $this->groupstudents = $groupstudents;
        $this->students = $students;
        $this->questions = $questions;
        $this->includecheckboxes = $options->checkboxcolumn;
        $this->reporturl = $reporturl;
        $this->options = $options;
    }

    
    public function col_checkbox($attempt) {
        if ($attempt->attempt) {
            return '<input type="checkbox" name="attemptid[]" value="'.$attempt->attempt.'" />';
        } else {
            return '';
        }
    }

    
    public function col_picture($attempt) {
        global $OUTPUT;
        $user = new stdClass();
        $additionalfields = explode(',', user_picture::fields());
        $user = username_load_fields_from_object($user, $attempt, null, $additionalfields);
        $user->id = $attempt->userid;
        return $OUTPUT->user_picture($user);
    }

    
    public function col_fullname($attempt) {
        $html = parent::col_fullname($attempt);
        if ($this->is_downloading() || empty($attempt->attempt)) {
            return $html;
        }

        return $html . html_writer::empty_tag('br') . html_writer::link(
                new moodle_url('/mod/quiz/review.php', array('attempt' => $attempt->attempt)),
                get_string('reviewattempt', 'quiz'), array('class' => 'reviewlink'));
    }

    
    public function col_state($attempt) {
        if (!is_null($attempt->attempt)) {
            return quiz_attempt::state_name($attempt->state);
        } else {
            return  '-';
        }
    }

    
    public function col_timestart($attempt) {
        if ($attempt->attempt) {
            return userdate($attempt->timestart, $this->strtimeformat);
        } else {
            return  '-';
        }
    }

    
    public function col_timefinish($attempt) {
        if ($attempt->attempt && $attempt->timefinish) {
            return userdate($attempt->timefinish, $this->strtimeformat);
        } else {
            return  '-';
        }
    }

    
    public function col_duration($attempt) {
        if ($attempt->timefinish) {
            return format_time($attempt->timefinish - $attempt->timestart);
        } else {
            return '-';
        }
    }

    
    public function col_feedbacktext($attempt) {
        if ($attempt->state != quiz_attempt::FINISHED) {
            return '-';
        }

        $feedback = quiz_report_feedback_for_grade(
                quiz_rescale_grade($attempt->sumgrades, $this->quiz, false),
                $this->quiz->id, $this->context);

        if ($this->is_downloading()) {
            $feedback = strip_tags($feedback);
        }

        return $feedback;
    }

    public function get_row_class($attempt) {
        if ($this->qmsubselect && $attempt->gradedattempt) {
            return 'gradedattempt';
        } else {
            return '';
        }
    }

    
    public function make_review_link($data, $attempt, $slot) {
        global $OUTPUT;

        $flag = '';
        if ($this->is_flagged($attempt->usageid, $slot)) {
            $flag = $OUTPUT->pix_icon('i/flagged', get_string('flagged', 'question'),
                    'moodle', array('class' => 'questionflag'));
        }

        $feedbackimg = '';
        $state = $this->slot_state($attempt, $slot);
        if ($state->is_finished() && $state != question_state::$needsgrading) {
            $feedbackimg = $this->icon_for_fraction($this->slot_fraction($attempt, $slot));
        }

        $output = html_writer::tag('span', $feedbackimg . html_writer::tag('span',
                $data, array('class' => $state->get_state_class(true))) . $flag, array('class' => 'que'));

        $reviewparams = array('attempt' => $attempt->attempt, 'slot' => $slot);
        if (isset($attempt->try)) {
            $reviewparams['step'] = $this->step_no_for_try($attempt->usageid, $slot, $attempt->try);
        }
        $url = new moodle_url('/mod/quiz/reviewquestion.php', $reviewparams);
        $output = $OUTPUT->action_link($url, $output,
                new popup_action('click', $url, 'reviewquestion',
                        array('height' => 450, 'width' => 650)),
                array('title' => get_string('reviewresponse', 'quiz')));

        return $output;
    }

    
    protected function slot_state($attempt, $slot) {
        $stepdata = $this->lateststeps[$attempt->usageid][$slot];
        return question_state::get($stepdata->state);
    }

    
    protected function is_flagged($questionusageid, $slot) {
        $stepdata = $this->lateststeps[$questionusageid][$slot];
        return $stepdata->flagged;
    }


    
    protected function slot_fraction($attempt, $slot) {
        $stepdata = $this->lateststeps[$attempt->usageid][$slot];
        return $stepdata->fraction;
    }

    
    protected function icon_for_fraction($fraction) {
        global $OUTPUT;

        $feedbackclass = question_state::graded_state_for_fraction($fraction)->get_feedback_class();
        return $OUTPUT->pix_icon('i/grade_' . $feedbackclass, get_string($feedbackclass, 'question'),
                'moodle', array('class' => 'icon'));
    }

    
    protected function load_extra_data() {
        $this->lateststeps = $this->load_question_latest_steps();
    }

    
    protected function load_question_latest_steps(qubaid_condition $qubaids = null) {
        if ($qubaids === null) {
            $qubaids = $this->get_qubaids_condition();
        }
        $dm = new question_engine_data_mapper();
        $latesstepdata = $dm->load_questions_usages_latest_steps(
                $qubaids, array_keys($this->questions));

        $lateststeps = array();
        foreach ($latesstepdata as $step) {
            $lateststeps[$step->questionusageid][$step->slot] = $step;
        }

        return $lateststeps;
    }

    
    protected function requires_extra_data() {
        return $this->requires_latest_steps_loaded();
    }

    
    protected function requires_latest_steps_loaded() {
        return false;
    }

    
    protected function is_latest_step_column($column) {
        return false;
    }

    
    protected function get_required_latest_state_fields($slot, $alias) {
        return '';
    }

    
    public function base_sql($reportstudents) {
        global $DB;

        $fields = $DB->sql_concat('u.id', "'#'", 'COALESCE(quiza.attempt, 0)') . ' AS uniqueid,';

        if ($this->qmsubselect) {
            $fields .= "\n(CASE WHEN $this->qmsubselect THEN 1 ELSE 0 END) AS gradedattempt,";
        }

        $extrafields = get_extra_user_fields_sql($this->context, 'u', '',
                array('id', 'idnumber', 'firstname', 'lastname', 'picture',
                'imagealt', 'institution', 'department', 'email'));
        $allnames = get_all_user_name_fields(true, 'u');
        $fields .= '
                quiza.uniqueid AS usageid,
                quiza.id AS attempt,
                u.id AS userid,
                u.idnumber, ' . $allnames . ',
                u.picture,
                u.imagealt,
                u.institution,
                u.department,
                u.email' . $extrafields . ',
                quiza.state,
                quiza.sumgrades,
                quiza.timefinish,
                quiza.timestart,
                CASE WHEN quiza.timefinish = 0 THEN null
                     WHEN quiza.timefinish > quiza.timestart THEN quiza.timefinish - quiza.timestart
                     ELSE 0 END AS duration';
                                    
                $from = "\n{user} u";
        $from .= "\nLEFT JOIN {quiz_attempts} quiza ON
                                    quiza.userid = u.id AND quiza.quiz = :quizid";
        $params = array('quizid' => $this->quiz->id);

        if ($this->qmsubselect && $this->options->onlygraded) {
            $from .= " AND (quiza.state <> :finishedstate OR $this->qmsubselect)";
            $params['finishedstate'] = quiz_attempt::FINISHED;
        }

        switch ($this->options->attempts) {
            case quiz_attempts_report::ALL_WITH:
                                $where = 'quiza.id IS NOT NULL AND quiza.preview = 0';
                break;
            case quiz_attempts_report::ENROLLED_WITH:
                                list($usql, $uparams) = $DB->get_in_or_equal(
                        $reportstudents, SQL_PARAMS_NAMED, 'u');
                $params += $uparams;
                $where = "u.id $usql AND quiza.preview = 0 AND quiza.id IS NOT NULL";
                break;
            case quiz_attempts_report::ENROLLED_WITHOUT:
                                list($usql, $uparams) = $DB->get_in_or_equal(
                        $reportstudents, SQL_PARAMS_NAMED, 'u');
                $params += $uparams;
                $where = "u.id $usql AND quiza.id IS NULL";
                break;
            case quiz_attempts_report::ENROLLED_ALL:
                                list($usql, $uparams) = $DB->get_in_or_equal(
                        $reportstudents, SQL_PARAMS_NAMED, 'u');
                $params += $uparams;
                $where = "u.id $usql AND (quiza.preview = 0 OR quiza.preview IS NULL)";
                break;
        }

        if ($this->options->states) {
            list($statesql, $stateparams) = $DB->get_in_or_equal($this->options->states,
                    SQL_PARAMS_NAMED, 'state');
            $params += $stateparams;
            $where .= " AND (quiza.state $statesql OR quiza.state IS NULL)";
        }

        return array($fields, $from, $where, $params);
    }

    
    protected function add_latest_state_join($slot) {
        $alias = 'qa' . $slot;

        $fields = $this->get_required_latest_state_fields($slot, $alias);
        if (!$fields) {
            return;
        }

                                        $qubaids = new qubaid_join("{quiz_attempts} {$alias}quiza", "{$alias}quiza.uniqueid",
                "{$alias}quiza.quiz = :{$alias}quizid", array("{$alias}quizid" => $this->sql->params['quizid']));

        $dm = new question_engine_data_mapper();
        list($inlineview, $viewparams) = $dm->question_attempt_latest_state_view($alias, $qubaids);

        $this->sql->fields .= ",\n$fields";
        $this->sql->from .= "\nLEFT JOIN $inlineview ON " .
                "$alias.questionusageid = quiza.uniqueid AND $alias.slot = :{$alias}slot";
        $this->sql->params[$alias . 'slot'] = $slot;
        $this->sql->params = array_merge($this->sql->params, $viewparams);
    }

    
    protected function get_qubaids_condition() {
        if (is_null($this->rawdata)) {
            throw new coding_exception(
                    'Cannot call get_qubaids_condition until the main data has been loaded.');
        }

        if ($this->is_downloading()) {
                        return new qubaid_join($this->sql->from, 'quiza.uniqueid',
                    $this->sql->where, $this->sql->params);
        }

        $qubaids = array();
        foreach ($this->rawdata as $attempt) {
            if ($attempt->usageid > 0) {
                $qubaids[] = $attempt->usageid;
            }
        }

        return new qubaid_list($qubaids);
    }

    public function query_db($pagesize, $useinitialsbar = true) {
        $doneslots = array();
        foreach ($this->get_sort_columns() as $column => $notused) {
            $slot = $this->is_latest_step_column($column);
            if ($slot && !in_array($slot, $doneslots)) {
                $this->add_latest_state_join($slot);
                $doneslots[] = $slot;
            }
        }

        parent::query_db($pagesize, $useinitialsbar);

        if ($this->requires_extra_data()) {
            $this->load_extra_data();
        }
    }

    public function get_sort_columns() {
                        $sortcolumns = parent::get_sort_columns();
        $sortcolumns['quiza.id'] = SORT_ASC;
        return $sortcolumns;
    }

    public function wrap_html_start() {
        if ($this->is_downloading() || !$this->includecheckboxes) {
            return;
        }

        $url = $this->options->get_url();
        $url->param('sesskey', sesskey());

        echo '<div id="tablecontainer">';
        echo '<form id="attemptsform" method="post" action="' . $url->out_omit_querystring() . '">';

        echo html_writer::input_hidden_params($url);
        echo '<div>';
    }

    public function wrap_html_finish() {
        if ($this->is_downloading() || !$this->includecheckboxes) {
            return;
        }

        echo '<div id="commands">';
        echo '<a href="javascript:select_all_in(\'DIV\', null, \'tablecontainer\');">' .
                get_string('selectall', 'quiz') . '</a> / ';
        echo '<a href="javascript:deselect_all_in(\'DIV\', null, \'tablecontainer\');">' .
                get_string('selectnone', 'quiz') . '</a> ';
        echo '&nbsp;&nbsp;';
        $this->submit_buttons();
        echo '</div>';

                echo '</div>';
        echo '</form></div>';
    }

    
    protected function submit_buttons() {
        global $PAGE;
        if (has_capability('mod/quiz:deleteattempts', $this->context)) {
            echo '<input type="submit" id="deleteattemptsbutton" name="delete" value="' .
                    get_string('deleteselected', 'quiz_overview') . '"/>';
            $PAGE->requires->event_handler('#deleteattemptsbutton', 'click', 'M.util.show_confirm_dialog',
                    array('message' => get_string('deleteattemptcheck', 'quiz')));
        }
    }
}
