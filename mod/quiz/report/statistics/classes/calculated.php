<?php

namespace quiz_statistics;

defined('MOODLE_INTERNAL') || die();


class calculated {

    
    public function __construct($whichattempts = null) {
        if ($whichattempts !== null) {
            $this->whichattempts = $whichattempts;
        }
    }

    
    public $whichattempts;

    

    public $firstattemptscount = 0;

    public $allattemptscount = 0;

    public $lastattemptscount = 0;

    public $highestattemptscount = 0;

    public $firstattemptsavg;

    public $allattemptsavg;

    public $lastattemptsavg;

    public $highestattemptsavg;

    public $median;

    public $standarddeviation;

    public $skewness;

    public $kurtosis;

    public $cic;

    public $errorratio;

    public $standarderror;

    
    public $timemodified;

    
    public function s() {
        return $this->get_field('count');
    }

    
    public function avg() {
        return $this->get_field('avg');
    }

    
    protected function get_field($field) {
        $fieldname = calculator::using_attempts_string_id($this->whichattempts).$field;
        return $this->{$fieldname};
    }

    
    public function get_formatted_quiz_info_data($course, $cm, $quiz) {

                $todisplay = array('firstattemptscount' => 'number',
                           'allattemptscount' => 'number',
                           'firstattemptsavg' => 'summarks_as_percentage',
                           'allattemptsavg' => 'summarks_as_percentage',
                           'lastattemptsavg' => 'summarks_as_percentage',
                           'highestattemptsavg' => 'summarks_as_percentage',
                           'median' => 'summarks_as_percentage',
                           'standarddeviation' => 'summarks_as_percentage',
                           'skewness' => 'number_format',
                           'kurtosis' => 'number_format',
                           'cic' => 'number_format_percent',
                           'errorratio' => 'number_format_percent',
                           'standarderror' => 'summarks_as_percentage');

                $quizinfo = array();
        $quizinfo[get_string('quizname', 'quiz_statistics')] = format_string($quiz->name);
        $quizinfo[get_string('coursename', 'quiz_statistics')] = format_string($course->fullname);
        if ($cm->idnumber) {
            $quizinfo[get_string('idnumbermod')] = $cm->idnumber;
        }
        if ($quiz->timeopen) {
            $quizinfo[get_string('quizopen', 'quiz')] = userdate($quiz->timeopen);
        }
        if ($quiz->timeclose) {
            $quizinfo[get_string('quizclose', 'quiz')] = userdate($quiz->timeclose);
        }
        if ($quiz->timeopen && $quiz->timeclose) {
            $quizinfo[get_string('duration', 'quiz_statistics')] =
                format_time($quiz->timeclose - $quiz->timeopen);
        }

                foreach ($todisplay as $property => $format) {
            if (!isset($this->$property) || !$format) {
                continue;
            }
            $value = $this->$property;

            switch ($format) {
                case 'summarks_as_percentage':
                    $formattedvalue = quiz_report_scale_summarks_as_percentage($value, $quiz);
                    break;
                case 'number_format_percent':
                    $formattedvalue = quiz_format_grade($quiz, $value) . '%';
                    break;
                case 'number_format':
                                                            $formattedvalue = format_float($value, $quiz->decimalpoints + 2);
                    break;
                case 'number':
                    $formattedvalue = $value + 0;
                    break;
                default:
                    $formattedvalue = $value;
            }

            $quizinfo[get_string($property, 'quiz_statistics',
                                 calculator::using_attempts_lang_string($this->whichattempts))] = $formattedvalue;
        }

        return $quizinfo;
    }

    
    protected $fieldsindb = array('whichattempts', 'firstattemptscount', 'allattemptscount', 'firstattemptsavg', 'allattemptsavg',
                                    'lastattemptscount', 'highestattemptscount', 'lastattemptsavg', 'highestattemptsavg',
                                    'median', 'standarddeviation', 'skewness',
                                    'kurtosis', 'cic', 'errorratio', 'standarderror');

    
    public function cache($qubaids) {
        global $DB;

        $toinsert = new \stdClass();

        foreach ($this->fieldsindb as $field) {
            $toinsert->{$field} = $this->{$field};
        }

        $toinsert->hashcode = $qubaids->get_hash_code();
        $toinsert->timemodified = time();

                if (isset($toinsert->errorratio) && is_nan($toinsert->errorratio)) {
            $toinsert->errorratio = null;
        }
        if (isset($toinsert->standarderror) && is_nan($toinsert->standarderror)) {
            $toinsert->standarderror = null;
        }

                $DB->insert_record('quiz_statistics', $toinsert);

    }

    
    public function populate_from_record($record) {
        foreach ($this->fieldsindb as $field) {
            $this->$field = $record->$field;
        }
        $this->timemodified = $record->timemodified;
    }
}
