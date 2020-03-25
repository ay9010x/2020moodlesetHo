<?php



defined('MOODLE_INTERNAL') || die();


class core_php_time_limit {
    
    protected static $currentend = -1;

    
    protected static $unittestdata = array();

    
    public static function raise($newlimit = 0) {
        global $CFG;

                if (PHPUNIT_TEST) {
            self::$unittestdata[] = $newlimit;
        }

                                if (self::$currentend === 0 || CLI_SCRIPT) {
            return;
        }

                                        if (!empty($CFG->maxtimelimit)) {
            $realtimeout = max(1, $CFG->maxtimelimit);
            if ($newlimit === 0) {
                $newlimit = $realtimeout;
            } else {
                $newlimit = min($newlimit, $realtimeout);
            }
        }

                if ($newlimit === 0) {
            self::$currentend = 0;
            @set_time_limit(0);
            return;
        }

                $now = time();
        $newend = $now + $newlimit;
        if (self::$currentend !== -1 && self::$currentend > $newend) {
                        return;
        }

                @set_time_limit($newlimit);
        self::$currentend = $newend;
    }

    
    public static function get_and_clear_unit_test_data() {
        $data = self::$unittestdata;
        self::$unittestdata = array();
        return $data;
    }
}
