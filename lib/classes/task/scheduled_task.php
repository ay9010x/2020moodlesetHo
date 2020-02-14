<?php


namespace core\task;


abstract class scheduled_task extends task_base {

    
    const MINUTEMIN = 0;
    
    const MINUTEMAX = 59;

    
    const HOURMIN = 0;
    
    const HOURMAX = 23;

    
    const DAYOFWEEKMIN = 0;
    
    const DAYOFWEEKMAX = 6;

    
    private $hour = '*';

    
    private $minute = '*';

    
    private $day = '*';

    
    private $month = '*';

    
    private $dayofweek = '*';

    
    private $lastruntime = 0;

    
    private $customised = false;

    
    private $disabled = false;

    
    public function get_last_run_time() {
        return $this->lastruntime;
    }

    
    public function set_last_run_time($lastruntime) {
        $this->lastruntime = $lastruntime;
    }

    
    public function is_customised() {
        return $this->customised;
    }

    
    public function set_customised($customised) {
        $this->customised = $customised;
    }

    
    public function set_minute($minute) {
        if ($minute === 'R') {
            $minute = mt_rand(self::HOURMIN, self::HOURMAX);
        }
        $this->minute = $minute;
    }

    
    public function get_minute() {
        return $this->minute;
    }

    
    public function set_hour($hour) {
        if ($hour === 'R') {
            $hour = mt_rand(self::HOURMIN, self::HOURMAX);
        }
        $this->hour = $hour;
    }

    
    public function get_hour() {
        return $this->hour;
    }

    
    public function set_month($month) {
        $this->month = $month;
    }

    
    public function get_month() {
        return $this->month;
    }

    
    public function set_day($day) {
        $this->day = $day;
    }

    
    public function get_day() {
        return $this->day;
    }

    
    public function set_day_of_week($dayofweek) {
        if ($dayofweek === 'R') {
            $dayofweek = mt_rand(self::DAYOFWEEKMIN, self::DAYOFWEEKMAX);
        }
        $this->dayofweek = $dayofweek;
    }

    
    public function get_day_of_week() {
        return $this->dayofweek;
    }

    
    public function set_disabled($disabled) {
        $this->disabled = (bool)$disabled;
    }

    
    public function get_disabled() {
        return $this->disabled;
    }

    
    public function get_run_if_component_disabled() {
        return false;
    }

    
    public function eval_cron_field($field, $min, $max) {
                $field = trim($field);

                                                        
                $range = array();

        $matches = array();
        preg_match_all('@[0-9]+|\*|,|/|-@', $field, $matches);

        $last = 0;
        $inrange = false;
        $instep = false;

        foreach ($matches[0] as $match) {
            if ($match == '*') {
                array_push($range, range($min, $max));
            } else if ($match == '/') {
                $instep = true;
            } else if ($match == '-') {
                $inrange = true;
            } else if (is_numeric($match)) {
                if ($instep) {
                    $i = 0;
                    for ($i = 0; $i < count($range[count($range) - 1]); $i++) {
                        if (($i) % $match != 0) {
                            $range[count($range) - 1][$i] = -1;
                        }
                    }
                    $inrange = false;
                } else if ($inrange) {
                    if (count($range)) {
                        $range[count($range) - 1] = range($last, $match);
                    }
                    $inrange = false;
                } else {
                    if ($match >= $min && $match <= $max) {
                        array_push($range, $match);
                    }
                    $last = $match;
                }
            }
        }

                $result = array();
        foreach ($range as $r) {
            if (is_array($r)) {
                foreach ($r as $rr) {
                    if ($rr >= $min && $rr <= $max) {
                        $result[$rr] = 1;
                    }
                }
            } else if (is_numeric($r)) {
                if ($r >= $min && $r <= $max) {
                    $result[$r] = 1;
                }
            }
        }
        $result = array_keys($result);
        sort($result, SORT_NUMERIC);
        return $result;
    }

    
    private function next_in_list($current, $list) {
        foreach ($list as $l) {
            if ($l >= $current) {
                return $l;
            }
        }
        if (count($list)) {
            return $list[0];
        }

        return 0;
    }

    
    public function get_next_scheduled_time() {
        global $CFG;

        $validminutes = $this->eval_cron_field($this->minute, self::MINUTEMIN, self::MINUTEMAX);
        $validhours = $this->eval_cron_field($this->hour, self::HOURMIN, self::HOURMAX);

                \core_date::set_default_server_timezone();

        $daysinmonth = date("t");
        $validdays = $this->eval_cron_field($this->day, 1, $daysinmonth);
        $validdaysofweek = $this->eval_cron_field($this->dayofweek, 0, 7);
        $validmonths = $this->eval_cron_field($this->month, 1, 12);
        $nextvalidyear = date('Y');

        $currentminute = date("i") + 1;
        $currenthour = date("H");
        $currentday = date("j");
        $currentmonth = date("n");
        $currentdayofweek = date("w");

        $nextvalidminute = $this->next_in_list($currentminute, $validminutes);
        if ($nextvalidminute < $currentminute) {
            $currenthour += 1;
        }
        $nextvalidhour = $this->next_in_list($currenthour, $validhours);
        if ($nextvalidhour < $currenthour) {
            $currentdayofweek += 1;
            $currentday += 1;
        }
        $nextvaliddayofmonth = $this->next_in_list($currentday, $validdays);
        $nextvaliddayofweek = $this->next_in_list($currentdayofweek, $validdaysofweek);
        $daysincrementbymonth = $nextvaliddayofmonth - $currentday;
        if ($nextvaliddayofmonth < $currentday) {
            $daysincrementbymonth += $daysinmonth;
        }

        $daysincrementbyweek = $nextvaliddayofweek - $currentdayofweek;
        if ($nextvaliddayofweek < $currentdayofweek) {
            $daysincrementbyweek += 7;
        }

                                if ($this->dayofweek == '*') {
            $daysincrement = $daysincrementbymonth;
        } else if ($this->day == '*') {
            $daysincrement = $daysincrementbyweek;
        } else {
                        $daysincrement = $daysincrementbymonth;
            if ($daysincrementbyweek < $daysincrementbymonth) {
                $daysincrement = $daysincrementbyweek;
            }
        }

        $nextvaliddayofmonth = $currentday + $daysincrement;
        if ($nextvaliddayofmonth > $daysinmonth) {
            $currentmonth += 1;
            $nextvaliddayofmonth -= $daysinmonth;
        }

        $nextvalidmonth = $this->next_in_list($currentmonth, $validmonths);
        if ($nextvalidmonth < $currentmonth) {
            $nextvalidyear += 1;
        }

                $nexttime = mktime($nextvalidhour,
                           $nextvalidminute,
                           0,
                           $nextvalidmonth,
                           $nextvaliddayofmonth,
                           $nextvalidyear);

        return $nexttime;
    }

    
    public abstract function get_name();

}
