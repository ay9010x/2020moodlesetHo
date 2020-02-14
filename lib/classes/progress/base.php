<?php

namespace core\progress;

defined('MOODLE_INTERNAL') || die();


abstract class base {
    
    const INDETERMINATE = -1;

    
    const TIME_LIMIT_WITHOUT_PROGRESS = 3600;

    
    protected $lastprogresstime;

    
    protected $count;

    
    protected $descriptions = array();

    
    protected $maxes = array();

    
    protected $currents = array();

    
    protected $parentcounts = array();

    
    public function start_progress($description, $max = self::INDETERMINATE,
            $parentcount = 1) {
        if ($max != self::INDETERMINATE && $max < 0) {
            throw new \coding_exception(
                    'start_progress() max value cannot be negative');
        }
        if ($parentcount < 1) {
            throw new \coding_exception(
                    'start_progress() parent progress count must be at least 1');
        }
        if (!empty($this->descriptions)) {
            $prevmax = end($this->maxes);
            if ($prevmax !== self::INDETERMINATE) {
                $prevcurrent = end($this->currents);
                if ($prevcurrent + $parentcount > $prevmax) {
                    throw new \coding_exception(
                            'start_progress() parent progress would exceed max');
                }
            }
        } else {
            if ($parentcount != 1) {
                throw new \coding_exception(
                        'start_progress() progress count must be 1 when no parent');
            }
        }
        $this->descriptions[] = $description;
        $this->maxes[] = $max;
        $this->currents[] = 0;
        $this->parentcounts[] = $parentcount;
        $this->update_progress();
    }

    
    public function end_progress() {
        if (!count($this->descriptions)) {
            throw new \coding_exception('end_progress() without start_progress()');
        }
        array_pop($this->descriptions);
        array_pop($this->maxes);
        array_pop($this->currents);
        $parentcount = array_pop($this->parentcounts);
        if (!empty($this->descriptions)) {
            $lastmax = end($this->maxes);
            if ($lastmax != self::INDETERMINATE) {
                $lastvalue = end($this->currents);
                $this->currents[key($this->currents)] = $lastvalue + $parentcount;
            }
        }
        $this->update_progress();
    }

    
    public function progress($progress = self::INDETERMINATE) {
                $max = end($this->maxes);
        if ($max === false) {
            throw new \coding_exception(
                    'progress() without start_progress');
        }

                if ($progress === self::INDETERMINATE) {
                        if ($max !== self::INDETERMINATE) {
                throw new \coding_exception(
                        'progress() INDETERMINATE, expecting value');
            }
        } else {
                        $current = end($this->currents);
            if ($max === self::INDETERMINATE) {
                throw new \coding_exception(
                        'progress() with value, expecting INDETERMINATE');
            } else if ($progress < 0 || $progress > $max) {
                throw new \coding_exception(
                        'progress() value out of range');
            } else if ($progress < $current) {
                throw new \coding_exception(
                        'progress() value may not go backwards');
            }
            $this->currents[key($this->currents)] = $progress;
        }

                $now = $this->get_time();
        if ($now === $this->lastprogresstime) {
            return;
        }

                $this->count++;
        $this->lastprogresstime = $now;

                \core_php_time_limit::raise(self::TIME_LIMIT_WITHOUT_PROGRESS);
        $this->update_progress();
    }

    
    public function increment_progress($incby = 1) {
        $current = end($this->currents);
        $this->progress($current + $incby);
    }

    
    protected function get_time() {
        return time();
    }

    
    protected abstract function update_progress();

    
    public function is_in_progress_section() {
        return !empty($this->descriptions);
    }

    
    public function get_current_max() {
        $max = end($this->maxes);
        if ($max === false) {
            throw new \coding_exception('Not inside progress section');
        }
        return $max;
    }

    
    public function get_current_description() {
        $description = end($this->descriptions);
        if ($description === false) {
            throw new \coding_exception('Not inside progress section');
        }
        return $description;
    }

    
    public function get_progress_proportion_range() {
                if (empty($this->currents)) {
            return array(1.0, 1.0);
        }
        $count = count($this->currents);
        $min = 0.0;
        $max = 1.0;
        for ($i = 0; $i < $count; $i++) {
                                    $sectionmax = $this->maxes[$i];
            if ($sectionmax === self::INDETERMINATE) {
                return array($min, $max);
            }

                                    $sectioncurrent = $this->currents[$i];
            if ($sectioncurrent === $sectionmax) {
                return array($max, $max);
            }

                                                $newmin = ($sectioncurrent / $sectionmax) * ($max - $min) + $min;
            $nextcurrent = $sectioncurrent + 1;
            if ($i + 1 < $count) {
                $weight = $this->parentcounts[$i + 1];
                $nextcurrent = $sectioncurrent + $weight;
            }
            $newmax = ($nextcurrent / $sectionmax) * ($max - $min) + $min;
            $min = $newmin;
            $max = $newmax;
        }

                return array($min, $min);
    }

    
    public function get_progress_count() {
        return $this->count;
    }
}
