<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');


class quiz_statistics_question_table extends flexible_table {
    
    protected $questiondata;

    
    protected $s;

    
    public function __construct($qid) {
        parent::__construct('mod-quiz-report-statistics-question-table' . $qid);
    }

    
    public function question_setup($reporturl, $questiondata, $s, $responseanalysis) {
        $this->questiondata = $questiondata;
        $this->s = $s;

        $this->define_baseurl($reporturl->out());
        $this->collapsible(false);
        $this->set_attribute('class', 'generaltable generalbox boxaligncenter quizresponseanalysis');

                $columns = array();
        $headers = array();

        if ($responseanalysis->has_subparts()) {
            $columns[] = 'part';
            $headers[] = get_string('partofquestion', 'quiz_statistics');
        }

        if ($responseanalysis->has_multiple_response_classes()) {
            $columns[] = 'responseclass';
            $headers[] = get_string('modelresponse', 'quiz_statistics');

            if ($responseanalysis->has_actual_responses()) {
                $columns[] = 'response';
                $headers[] = get_string('actualresponse', 'quiz_statistics');
            }

        } else {
            $columns[] = 'response';
            $headers[] = get_string('response', 'quiz_statistics');
        }

        $columns[] = 'fraction';
        $headers[] = get_string('optiongrade', 'quiz_statistics');

        if (!$responseanalysis->has_multiple_tries_data()) {
            $columns[] = 'totalcount';
            $headers[] = get_string('count', 'quiz_statistics');
        } else {
            $countcolumns = range(1, $responseanalysis->get_maximum_tries());
            foreach ($countcolumns as $countcolumn) {
                $columns[] = 'trycount'.$countcolumn;
                $headers[] = get_string('counttryno', 'quiz_statistics', $countcolumn);
            }
        }

        $columns[] = 'frequency';
        $headers[] = get_string('frequency', 'quiz_statistics');

        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->sortable(false);

        $this->column_class('fraction', 'numcol');
        $this->column_class('count', 'numcol');
        $this->column_class('frequency', 'numcol');

        $this->column_suppress('part');
        $this->column_suppress('responseclass');

        parent::setup();
    }

    
    protected function format_percentage($fraction) {
        return format_float($fraction * 100, 2) . '%';
    }

    
    protected function col_fraction($response) {
        if (is_null($response->fraction)) {
            return '';
        }

        return $this->format_percentage($response->fraction);
    }

    
    protected function col_frequency($response) {
        if (!$this->s) {
            return '';
        }
        return $this->format_percentage($response->totalcount / $this->s);
    }

    
    public function other_cols($colname, $response) {
        if (preg_match('/^trycount(\d+)$/', $colname, $matches)) {
            if (isset($response->trycount[$matches[1]])) {
                return $response->trycount[$matches[1]];
            } else {
                return 0;
            }
        } else if ($colname == 'part' || $colname == 'responseclass' || $colname == 'response') {
            return s($response->$colname);
        } else {
            return null;
        }
    }
}
