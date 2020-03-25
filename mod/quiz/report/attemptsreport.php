<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');



abstract class quiz_attempts_report extends quiz_default_report {
    
    const DEFAULT_PAGE_SIZE = 30;

    
    const ALL_WITH = 'all_with';
    
    const ENROLLED_WITH = 'enrolled_with';
    
    const ENROLLED_WITHOUT = 'enrolled_without';
    
    const ENROLLED_ALL = 'enrolled_any';

    
    protected $mode;

    
    protected $context;

    
    protected $form;

    
    protected $qmsubselect;

    
    protected $showgrades = null;

    
    protected function init($mode, $formclass, $quiz, $cm, $course) {
        $this->mode = $mode;

        $this->context = context_module::instance($cm->id);

        list($currentgroup, $students, $groupstudents, $allowed) =
                $this->load_relevant_students($cm, $course);

        $this->qmsubselect = quiz_report_qm_filter_select($quiz);

        $this->form = new $formclass($this->get_base_url(),
                array('quiz' => $quiz, 'currentgroup' => $currentgroup, 'context' => $this->context));

        return array($currentgroup, $students, $groupstudents, $allowed);
    }

    
    protected function get_base_url() {
        return new moodle_url('/mod/quiz/report.php',
                array('id' => $this->context->instanceid, 'mode' => $this->mode));
    }

    
    protected function load_relevant_students($cm, $course = null) {
        $currentgroup = $this->get_current_group($cm, $course, $this->context);

        if ($currentgroup == self::NO_GROUPS_ALLOWED) {
            return array($currentgroup, array(), array(), array());
        }

        if (!$students = get_users_by_capability($this->context,
                array('mod/quiz:reviewmyattempts', 'mod/quiz:attempt'),
                'u.id, 1', '', '', '', '', '', false)) {
            $students = array();
        } else {
            $students = array_keys($students);
        }

        if (empty($currentgroup)) {
            return array($currentgroup, $students, array(), $students);
        }

                if (!$groupstudents = get_users_by_capability($this->context,
                array('mod/quiz:reviewmyattempts', 'mod/quiz:attempt'),
                'u.id, 1', '', '', '', $currentgroup, '', false)) {
            $groupstudents = array();
        } else {
            $groupstudents = array_keys($groupstudents);
        }

        return array($currentgroup, $students, $groupstudents, $groupstudents);
    }

    
    protected function add_user_columns($table, &$columns, &$headers) {
        global $CFG;
        if (!$table->is_downloading() && $CFG->grade_report_showuserimage) {
            $columns[] = 'picture';
            $headers[] = '';
        }
        if (!$table->is_downloading()) {
            $columns[] = 'fullname';
            $headers[] = get_string('name');
        } else {
            $columns[] = 'lastname';
            $headers[] = get_string('lastname');
            $columns[] = 'firstname';
            $headers[] = get_string('firstname');
        }

                        $extrafields = get_extra_user_fields($this->context,
                $table->is_downloading() ? array('institution', 'department', 'email') : array());
        foreach ($extrafields as $field) {
            $columns[] = $field;
            $headers[] = get_user_field_name($field);
        }

        if ($table->is_downloading()) {
            $columns[] = 'institution';
            $headers[] = get_string('institution');

            $columns[] = 'department';
            $headers[] = get_string('department');

            $columns[] = 'email';
            $headers[] = get_string('email');
        }
    }

    
    protected function configure_user_columns($table) {
        $table->column_suppress('picture');
        $table->column_suppress('fullname');
        $extrafields = get_extra_user_fields($this->context);
        foreach ($extrafields as $field) {
            $table->column_suppress($field);
        }

        $table->column_class('picture', 'picture');
        $table->column_class('lastname', 'bold');
        $table->column_class('firstname', 'bold');
        $table->column_class('fullname', 'bold');
    }

    
    protected function add_state_column(&$columns, &$headers) {
        $columns[] = 'state';
        $headers[] = get_string('attemptstate', 'quiz');
    }

    
    protected function add_time_columns(&$columns, &$headers) {
        $columns[] = 'timestart';
        $headers[] = get_string('startedon', 'quiz');

        $columns[] = 'timefinish';
        $headers[] = get_string('timecompleted', 'quiz');

        $columns[] = 'duration';
        $headers[] = get_string('attemptduration', 'quiz');
    }

    
    protected function add_grade_columns($quiz, $usercanseegrades, &$columns, &$headers, $includefeedback = true) {
        if ($usercanseegrades) {
            $columns[] = 'sumgrades';
            $headers[] = get_string('grade', 'quiz') . '/' .
                    quiz_format_grade($quiz, $quiz->grade);
        }

        if ($includefeedback && quiz_has_feedback($quiz)) {
            $columns[] = 'feedbacktext';
            $headers[] = get_string('feedback', 'quiz');
        }
    }

    
    protected function set_up_table_columns($table, $columns, $headers, $reporturl,
            mod_quiz_attempts_report_options $options, $collapsible) {
        $table->define_columns($columns);
        $table->define_headers($headers);
        $table->sortable(true, 'uniqueid');

        $table->define_baseurl($options->get_url());

        $this->configure_user_columns($table);

        $table->no_sorting('feedbacktext');
        $table->column_class('sumgrades', 'bold');

        $table->set_attribute('id', 'attempts');

        $table->collapsible($collapsible);
    }

    
    protected function process_actions($quiz, $cm, $currentgroup, $groupstudents, $allowed, $redirecturl) {
        if (empty($currentgroup) || $groupstudents) {
            if (optional_param('delete', 0, PARAM_BOOL) && confirm_sesskey()) {
                if ($attemptids = optional_param_array('attemptid', array(), PARAM_INT)) {
                    require_capability('mod/quiz:deleteattempts', $this->context);
                    $this->delete_selected_attempts($quiz, $cm, $attemptids, $allowed);
                    redirect($redirecturl);
                }
            }
        }
    }

    
    protected function delete_selected_attempts($quiz, $cm, $attemptids, $allowed) {
        global $DB;

        foreach ($attemptids as $attemptid) {
            $attempt = $DB->get_record('quiz_attempts', array('id' => $attemptid));
            if (!$attempt || $attempt->quiz != $quiz->id || $attempt->preview != 0) {
                                continue;
            }
            if ($allowed && !in_array($attempt->userid, $allowed)) {
                                continue;
            }

                        $quiz->cmid = $cm->id;
            quiz_delete_attempt($attempt, $quiz);
        }
    }
}
