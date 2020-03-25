<?php

namespace quiz_statistics;
defined('MOODLE_INTERNAL') || die();


class calculator {

    
    protected $progress;

    public function __construct(\core\progress\base $progress = null) {
        if ($progress === null) {
            $progress = new \core\progress\none();
        }
        $this->progress = $progress;
    }

    
    public function calculate($quizid, $whichattempts, $groupstudents, $p, $sumofmarkvariance) {

        $this->progress->start_progress('', 3);

        $quizstats = new calculated($whichattempts);

        $countsandaverages = $this->attempt_counts_and_averages($quizid, $groupstudents);
        $this->progress->progress(1);

        foreach ($countsandaverages as $propertyname => $value) {
            $quizstats->{$propertyname} = $value;
        }

        $s = $quizstats->s();
        if ($s != 0) {

                        list($fromqa, $whereqa, $qaparams) =
                quiz_statistics_attempts_sql($quizid, $groupstudents, $whichattempts);

            $quizstats->median = $this->median($s, $fromqa, $whereqa, $qaparams);
            $this->progress->progress(2);

            if ($s > 1) {

                $powers = $this->sum_of_powers_of_difference_to_mean($quizstats->avg(), $fromqa, $whereqa, $qaparams);
                $this->progress->progress(3);

                $quizstats->standarddeviation = sqrt($powers->power2 / ($s - 1));

                                if ($s > 2) {
                                        $m2 = $powers->power2 / $s;
                    $m3 = $powers->power3 / $s;
                    $m4 = $powers->power4 / $s;

                    $k2 = $s * $m2 / ($s - 1);
                    $k3 = $s * $s * $m3 / (($s - 1) * ($s - 2));
                    if ($k2 != 0) {
                        $quizstats->skewness = $k3 / (pow($k2, 3 / 2));

                                                if ($s > 3) {
                            $k4 = $s * $s * ((($s + 1) * $m4) - (3 * ($s - 1) * $m2 * $m2)) / (($s - 1) * ($s - 2) * ($s - 3));
                            $quizstats->kurtosis = $k4 / ($k2 * $k2);
                        }

                        if ($p > 1) {
                            $quizstats->cic = (100 * $p / ($p - 1)) * (1 - ($sumofmarkvariance / $k2));
                            $quizstats->errorratio = 100 * sqrt(1 - ($quizstats->cic / 100));
                            $quizstats->standarderror = $quizstats->errorratio *
                                $quizstats->standarddeviation / 100;
                        }
                    }

                }
            }

            $quizstats->cache(quiz_statistics_qubaids_condition($quizid, $groupstudents, $whichattempts));
        }
        $this->progress->end_progress();
        return $quizstats;
    }

    
    const TIME_TO_CACHE = 900; 
    
    public function get_cached($qubaids) {
        global $DB;

        $timemodified = time() - self::TIME_TO_CACHE;
        $fromdb = $DB->get_record_select('quiz_statistics', 'hashcode = ? AND timemodified > ?',
                                         array($qubaids->get_hash_code(), $timemodified));
        $stats = new calculated();
        $stats->populate_from_record($fromdb);
        return $stats;
    }

    
    public function get_last_calculated_time($qubaids) {
        global $DB;

        $timemodified = time() - self::TIME_TO_CACHE;
        return $DB->get_field_select('quiz_statistics', 'timemodified', 'hashcode = ? AND timemodified > ?',
                                         array($qubaids->get_hash_code(), $timemodified));
    }

    
    public static function using_attempts_lang_string($whichattempts) {
         return get_string(static::using_attempts_string_id($whichattempts), 'quiz_statistics');
    }

    
    public static function using_attempts_string_id($whichattempts) {
        switch ($whichattempts) {
            case QUIZ_ATTEMPTFIRST :
                return 'firstattempts';
            case QUIZ_GRADEHIGHEST :
                return 'highestattempts';
            case QUIZ_ATTEMPTLAST :
                return 'lastattempts';
            case QUIZ_GRADEAVERAGE :
                return 'allattempts';
        }
    }

    
    protected function attempt_counts_and_averages($quizid, $groupstudents) {
        global $DB;

        $attempttotals = new \stdClass();
        foreach (array_keys(quiz_get_grading_options()) as $which) {

            list($fromqa, $whereqa, $qaparams) = quiz_statistics_attempts_sql($quizid, $groupstudents, $which);

            $fromdb = $DB->get_record_sql("SELECT COUNT(*) AS rcount, AVG(sumgrades) AS average FROM $fromqa WHERE $whereqa",
                                            $qaparams);
            $fieldprefix = static::using_attempts_string_id($which);
            $attempttotals->{$fieldprefix.'avg'} = $fromdb->average;
            $attempttotals->{$fieldprefix.'count'} = $fromdb->rcount;
        }
        return $attempttotals;
    }

    
    protected function median($s, $fromqa, $whereqa, $qaparams) {
        global $DB;

        if ($s % 2 == 0) {
                        $limitoffset = $s / 2 - 1;
            $limit = 2;
        } else {
            $limitoffset = floor($s / 2);
            $limit = 1;
        }
        $sql = "SELECT id, sumgrades
                FROM $fromqa
                WHERE $whereqa
                ORDER BY sumgrades";

        $medianmarks = $DB->get_records_sql_menu($sql, $qaparams, $limitoffset, $limit);

        return array_sum($medianmarks) / count($medianmarks);
    }

    
    protected function sum_of_powers_of_difference_to_mean($mean, $fromqa, $whereqa, $qaparams) {
        global $DB;

        $sql = "SELECT
                    SUM(POWER((quiza.sumgrades - $mean), 2)) AS power2,
                    SUM(POWER((quiza.sumgrades - $mean), 3)) AS power3,
                    SUM(POWER((quiza.sumgrades - $mean), 4)) AS power4
                    FROM $fromqa
                    WHERE $whereqa";
        $params = array('mean1' => $mean, 'mean2' => $mean, 'mean3' => $mean) + $qaparams;

        return $DB->get_record_sql($sql, $params, MUST_EXIST);
    }

}
