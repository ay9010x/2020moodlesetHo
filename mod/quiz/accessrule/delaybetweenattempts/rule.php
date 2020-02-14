<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');



class quizaccess_delaybetweenattempts extends quiz_access_rule_base {

    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {
        if (empty($quizobj->get_quiz()->delay1) && empty($quizobj->get_quiz()->delay2)) {
            return null;
        }

        return new self($quizobj, $timenow);
    }

    public function prevent_new_attempt($numprevattempts, $lastattempt) {
        if ($this->quiz->attempts > 0 && $numprevattempts >= $this->quiz->attempts) {
                        return false;
        }
        if ($this->quiz->timeclose != 0 && $this->timenow > $this->quiz->timeclose) {
                        return false;
        }
        $nextstarttime = $this->compute_next_start_time($numprevattempts, $lastattempt);
        if ($this->timenow < $nextstarttime) {
            if ($this->quiz->timeclose == 0 || $nextstarttime <= $this->quiz->timeclose) {
                return get_string('youmustwait', 'quizaccess_delaybetweenattempts',
                        userdate($nextstarttime));
            } else {
                return get_string('youcannotwait', 'quizaccess_delaybetweenattempts');
            }
        }
        return false;
    }

    
    protected function compute_next_start_time($numprevattempts, $lastattempt) {
        if ($numprevattempts == 0) {
            return 0;
        }

        $lastattemptfinish = $lastattempt->timefinish;
        if ($this->quiz->timelimit > 0) {
            $lastattemptfinish = min($lastattemptfinish,
                    $lastattempt->timestart + $this->quiz->timelimit);
        }

        if ($numprevattempts == 1 && $this->quiz->delay1) {
            return $lastattemptfinish + $this->quiz->delay1;
        } else if ($numprevattempts > 1 && $this->quiz->delay2) {
            return $lastattemptfinish + $this->quiz->delay2;
        }
        return 0;
    }

    public function is_finished($numprevattempts, $lastattempt) {
        $nextstarttime = $this->compute_next_start_time($numprevattempts, $lastattempt);
        return $this->timenow <= $nextstarttime &&
        $this->quiz->timeclose != 0 && $nextstarttime >= $this->quiz->timeclose;
    }
}
