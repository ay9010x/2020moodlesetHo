<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');


class quiz_statistics_table extends flexible_table {
    
    protected $quiz;

    
    protected $cmid;

    
    public function __construct() {
        parent::__construct('mod-quiz-report-statistics-report');
    }

    
    public function statistics_setup($quiz, $cmid, $reporturl, $s) {
        $this->quiz = $quiz;
        $this->cmid = $cmid;

                $columns = array();
        $headers = array();

        $columns[] = 'number';
        $headers[] = get_string('questionnumber', 'quiz_statistics');

        if (!$this->is_downloading()) {
            $columns[] = 'icon';
            $headers[] = '';
            $columns[] = 'actions';
            $headers[] = '';
        } else {
            $columns[] = 'qtype';
            $headers[] = get_string('questiontype', 'quiz_statistics');
        }

        $columns[] = 'name';
        $headers[] = get_string('questionname', 'quiz');

        $columns[] = 's';
        $headers[] = get_string('attempts', 'quiz_statistics');

        if ($s > 1) {
            $columns[] = 'facility';
            $headers[] = get_string('facility', 'quiz_statistics');

            $columns[] = 'sd';
            $headers[] = get_string('standarddeviationq', 'quiz_statistics');
        }

        $columns[] = 'random_guess_score';
        $headers[] = get_string('random_guess_score', 'quiz_statistics');

        $columns[] = 'intended_weight';
        $headers[] = get_string('intended_weight', 'quiz_statistics');

        $columns[] = 'effective_weight';
        $headers[] = get_string('effective_weight', 'quiz_statistics');

        $columns[] = 'discrimination_index';
        $headers[] = get_string('discrimination_index', 'quiz_statistics');

        $columns[] = 'discriminative_efficiency';
        $headers[] = get_string('discriminative_efficiency', 'quiz_statistics');

        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->sortable(false);

        $this->column_class('s', 'numcol');
        $this->column_class('facility', 'numcol');
        $this->column_class('sd', 'numcol');
        $this->column_class('random_guess_score', 'numcol');
        $this->column_class('intended_weight', 'numcol');
        $this->column_class('effective_weight', 'numcol');
        $this->column_class('discrimination_index', 'numcol');
        $this->column_class('discriminative_efficiency', 'numcol');

                $this->define_baseurl($reporturl->out());

        $this->collapsible(true);

        $this->set_attribute('id', 'questionstatistics');
        $this->set_attribute('class', 'generaltable generalbox boxaligncenter');

        parent::setup();
    }

    
    protected function col_number($questionstat) {
        if (!isset($questionstat->question->number)) {
            return '';
        }
        $number = $questionstat->question->number;

        if (isset($questionstat->subqdisplayorder)) {
            $number = $number . '.'.$questionstat->subqdisplayorder;
        }

        if ($questionstat->question->qtype != 'random' && !is_null($questionstat->variant)) {
            $number = $number . '.'.$questionstat->variant;
        }

        return $number;
    }

    
    protected function col_icon($questionstat) {
        return print_question_icon($questionstat->question, true);
    }

    
    protected function col_actions($questionstat) {
        return quiz_question_action_icons($this->quiz, $this->cmid,
                $questionstat->question, $this->baseurl, $questionstat->variant);
    }

    
    protected function col_qtype($questionstat) {
        return question_bank::get_qtype_name($questionstat->question->qtype);
    }

    
    protected function col_name($questionstat) {
        $name = $questionstat->question->name;

        if (!is_null($questionstat->variant)) {
            $a = new stdClass();
            $a->name = $name;
            $a->variant = $questionstat->variant;
            $name = get_string('nameforvariant', 'quiz_statistics', $a);
        }

        if ($this->is_downloading()) {
            return $name;
        }

        $baseurl = new moodle_url($this->baseurl);
        if (!is_null($questionstat->variant)) {
            if ($questionstat->subquestion) {
                                $url = new moodle_url($baseurl, array('qid' => $questionstat->questionid, 'variant' => $questionstat->variant));
                $name = html_writer::link($url, $name, array('title' => get_string('detailedanalysisforvariant',
                                                                                   'quiz_statistics',
                                                                                   $questionstat->variant)));
            } else if ($questionstat->slot) {
                                $url = new moodle_url($baseurl, array('slot' => $questionstat->slot, 'variant' => $questionstat->variant));
                $name = html_writer::link($url, $name, array('title' => get_string('detailedanalysisforvariant',
                                                                                   'quiz_statistics',
                                                                                   $questionstat->variant)));
            }
        } else {
            if ($questionstat->subquestion && !$questionstat->get_variants()) {
                                $url = new moodle_url($baseurl, array('qid' => $questionstat->questionid));
                $name = html_writer::link($url, $name, array('title' => get_string('detailedanalysis', 'quiz_statistics')));
            } else if ($baseurl->param('slot') === null && $questionstat->slot) {
                                                $number = $questionstat->question->number;
                $url = new moodle_url($baseurl, array('slot' => $questionstat->slot));
                if ($questionstat->get_variants() || $questionstat->get_sub_question_ids()) {
                                        $name = html_writer::link($url,
                                              $name,
                                              array('title' => get_string('slotstructureanalysis', 'quiz_statistics', $number)));
                } else {
                                        $name = html_writer::link($url,
                                              $name,
                                              array('title' => get_string('detailedanalysis', 'quiz_statistics')));
                }
            }
        }


        if ($this->is_dubious_question($questionstat)) {
            $name = html_writer::tag('div', $name, array('class' => 'dubious'));
        }

        if (!empty($questionstat->minmedianmaxnotice)) {
            $name = get_string($questionstat->minmedianmaxnotice, 'quiz_statistics') . '<br />' . $name;
        }

        return $name;
    }

    
    protected function col_s($questionstat) {
        if (!isset($questionstat->s)) {
            return 0;
        }

        return $questionstat->s;
    }

    
    protected function col_facility($questionstat) {
        if (is_null($questionstat->facility)) {
            return '';
        }

        return number_format($questionstat->facility*100, 2) . '%';
    }

    
    protected function col_sd($questionstat) {
        if (is_null($questionstat->sd) || $questionstat->maxmark == 0) {
            return '';
        }

        return number_format($questionstat->sd*100 / $questionstat->maxmark, 2) . '%';
    }

    
    protected function col_random_guess_score($questionstat) {
        if (is_null($questionstat->randomguessscore)) {
            return '';
        }

        return number_format($questionstat->randomguessscore * 100, 2).'%';
    }

    
    protected function col_intended_weight($questionstat) {
        return quiz_report_scale_summarks_as_percentage($questionstat->maxmark, $this->quiz);
    }

    
    protected function col_effective_weight($questionstat) {
        global $OUTPUT;

        if (is_null($questionstat->effectiveweight)) {
            return '';
        }

        if ($questionstat->negcovar) {
            $negcovar = get_string('negcovar', 'quiz_statistics');

            if (!$this->is_downloading()) {
                $negcovar = html_writer::tag('div',
                        $negcovar . $OUTPUT->help_icon('negcovar', 'quiz_statistics'),
                        array('class' => 'negcovar'));
            }

            return $negcovar;
        }

        return number_format($questionstat->effectiveweight, 2) . '%';
    }

    
    protected function col_discrimination_index($questionstat) {
        if (!is_numeric($questionstat->discriminationindex)) {
            return $questionstat->discriminationindex;
        }

        return number_format($questionstat->discriminationindex, 2) . '%';
    }

    
    protected function col_discriminative_efficiency($questionstat) {
        if (!is_numeric($questionstat->discriminativeefficiency)) {
            return '';
        }

        return number_format($questionstat->discriminativeefficiency, 2) . '%';
    }

    
    protected function is_dubious_question($questionstat) {
        if (!is_numeric($questionstat->discriminativeefficiency)) {
            return false;
        }

        return $questionstat->discriminativeefficiency < 15;
    }

    public function  wrap_html_start() {
                if (!$this->is_downloading()) {
            echo html_writer::start_tag('div', array('id' => 'tablecontainer',
                    'class' => 'statistics-tablecontainer'));
        }
    }

    public function wrap_html_finish() {
        if (!$this->is_downloading()) {
            echo html_writer::end_tag('div');
        }
    }
}
