<?php



defined('MOODLE_INTERNAL') || die();


class core_date_legacy_testcase extends advanced_testcase {
    public function test_settings() {
        global $CFG;
        $this->resetAfterTest();

        $this->assertNotEmpty($CFG->timezone);

        $this->assertSame('99', $CFG->forcetimezone);

        $user = $this->getDataGenerator()->create_user();
        $this->assertSame('99', $user->timezone);
    }

    public function test_get_user_timezone() {
        global $CFG, $USER;

        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        
        $this->setTimezone('Pacific/Auckland', 'Pacific/Auckland');
        $USER->timezone = 'Europe/Prague';

        $tz = get_user_timezone();
        $this->assertSame('Europe/Prague', $tz);

        $tz = get_user_timezone(99);
        $this->assertSame('Europe/Prague', $tz);
        $tz = get_user_timezone('99');
        $this->assertSame('Europe/Prague', $tz);

        $tz = get_user_timezone('Europe/Berlin');
        $this->assertSame('Europe/Berlin', $tz);

        
        $this->setTimezone('Pacific/Auckland', 'Pacific/Auckland');
        $USER->timezone = '99';

        $tz = get_user_timezone();
        $this->assertSame('Pacific/Auckland', $tz);

        $tz = get_user_timezone(99);
        $this->assertSame('Pacific/Auckland', $tz);
        $tz = get_user_timezone('99');
        $this->assertSame('Pacific/Auckland', $tz);

        $tz = get_user_timezone('Europe/Berlin');
        $this->assertSame('Europe/Berlin', $tz);

        
        $this->setTimezone('99', 'Pacific/Auckland');
        $USER->timezone = 'Europe/Prague';

        $tz = get_user_timezone();
        $this->assertSame('Europe/Prague', $tz);

        $tz = get_user_timezone(99);
        $this->assertSame('Europe/Prague', $tz);
        $tz = get_user_timezone('99');
        $this->assertSame('Europe/Prague', $tz);

        $tz = get_user_timezone('Europe/Berlin');
        $this->assertSame('Europe/Berlin', $tz);

        
        $this->setTimezone('99', 'Pacific/Auckland');
        $USER->timezone = '99';

        $tz = get_user_timezone();
        $this->assertSame(99.0, $tz);

        $tz = get_user_timezone(99);
        $this->assertSame(99.0, $tz);
        $tz = get_user_timezone('99');
        $this->assertSame(99.0, $tz);

        $tz = get_user_timezone('Europe/Berlin');
        $this->assertSame('Europe/Berlin', $tz);
    }

    public function test_dst_offset_on() {
        $time = gmmktime(1, 1, 1, 3, 1, 2015);
        $this->assertSame(3600, dst_offset_on($time, 'Pacific/Auckland'));
        $this->assertSame(0, dst_offset_on($time, 'Australia/Perth'));
        $this->assertSame(1800, dst_offset_on($time, 'Australia/Lord_Howe'));
        $this->assertSame(0, dst_offset_on($time, 'Europe/Prague'));
        $this->assertSame(0, dst_offset_on($time, 'America/New_York'));

        $time = gmmktime(1, 1, 1, 5, 1, 2015);
        $this->assertSame(0, dst_offset_on($time, 'Pacific/Auckland'));
        $this->assertSame(0, dst_offset_on($time, 'Australia/Perth'));
        $this->assertSame(0, dst_offset_on($time, 'Australia/Lord_Howe'));
        $this->assertSame(3600, dst_offset_on($time, 'Europe/Prague'));
        $this->assertSame(3600, dst_offset_on($time, 'America/New_York'));
    }

    public function test_make_timestamp() {
        global $CFG;

        $this->resetAfterTest();

                $timezones = array('Europe/Prague', 'Europe/London', 'Australia/Perth', 'Pacific/Auckland', 'America/New_York', '99');

        $dates = array(
            array(2, 1, 0, 40, 40),
            array(4, 3, 0, 30, 22),
            array(9, 5, 0, 20, 19),
            array(11, 28, 0, 10, 45),
        );
        $years = array(1999, 2009, 2014, 2018);

        $this->setTimezone('Pacific/Auckland', 'Pacific/Auckland');
        foreach ($timezones as $tz) {
            foreach ($years as $year) {
                foreach ($dates as $date) {
                    $result = make_timestamp($year, $date[0], $date[1], $date[2], $date[3], $date[4], $tz, true);
                    $expected = new DateTime('now', new DateTimeZone(($tz == 99 ? 'Pacific/Auckland' : $tz)));
                    $expected->setDate($year, $date[0], $date[1]);
                    $expected->setTime($date[2], $date[3], $date[4]);
                    $this->assertSame($expected->getTimestamp(), $result,
                        'Incorrect result for data ' . $expected->format("D, d M Y H:i:s O") . ' ' . $tz);
                }
            }
        }

        $this->setTimezone('99', 'Pacific/Auckland');
        foreach ($timezones as $tz) {
            foreach ($years as $year) {
                foreach ($dates as $date) {
                    $result = make_timestamp($year, $date[0], $date[1], $date[2], $date[3], $date[4], $tz, true);
                    $expected = new DateTime('now', new DateTimeZone(($tz == 99 ? 'Pacific/Auckland' : $tz)));
                    $expected->setDate($year, $date[0], $date[1]);
                    $expected->setTime($date[2], $date[3], $date[4]);
                    $this->assertSame($expected->getTimestamp(), $result,
                        'Incorrect result for data ' . $expected->format("D, d M Y H:i:s O") . ' ' . $tz);
                }
            }
        }
    }

    public function test_usergetdate() {
        global $CFG;

        $this->resetAfterTest();

                $timezones = array('Europe/Prague', 'Europe/London', 'Australia/Perth', 'Pacific/Auckland', 'America/New_York', '99');

        $dates = array(
            array(2, 1, 0, 40, 40),
            array(4, 3, 0, 30, 22),
            array(9, 5, 0, 20, 19),
            array(11, 28, 0, 10, 45),
        );
        $years = array(1999, 2009, 2014, 2018);

        $this->setTimezone('Pacific/Auckland', 'Pacific/Auckland');
        foreach ($timezones as $tz) {
            foreach ($years as $year) {
                foreach ($dates as $date) {
                    $expected = new DateTime('now', new DateTimeZone(($tz == 99 ? 'Pacific/Auckland' : $tz)));
                    $expected->setDate($year, $date[0], $date[1]);
                    $expected->setTime($date[2], $date[3], $date[4]);
                    $result = usergetdate($expected->getTimestamp(), $tz);
                    unset($result[0]);                     $ex = array(
                        'seconds' => $date[4],
                        'minutes' => $date[3],
                        'hours' => $date[2],
                        'mday' => $date[1],
                        'wday' => (int)$expected->format('w'),
                        'mon' => $date[0],
                        'year' => $year,
                        'yday' => (int)$expected->format('z'),
                        'weekday' => $expected->format('l'),
                        'month' => $expected->format('F'),
                    );
                    $this->assertSame($ex, $result,
                        'Incorrect result for data ' . $expected->format("D, d M Y H:i:s O") . ' ' . $tz);
                }
            }
        }

        $this->setTimezone('99', 'Pacific/Auckland');
        foreach ($timezones as $tz) {
            foreach ($years as $year) {
                foreach ($dates as $date) {
                    $expected = new DateTime('now', new DateTimeZone(($tz == 99 ? 'Pacific/Auckland' : $tz)));
                    $expected->setDate($year, $date[0], $date[1]);
                    $expected->setTime($date[2], $date[3], $date[4]);
                    $result = usergetdate($expected->getTimestamp(), $tz);
                    unset($result[0]);                     $ex = array(
                        'seconds' => $date[4],
                        'minutes' => $date[3],
                        'hours' => $date[2],
                        'mday' => $date[1],
                        'wday' => (int)$expected->format('w'),
                        'mon' => $date[0],
                        'year' => $year,
                        'yday' => (int)$expected->format('z'),
                        'weekday' => $expected->format('l'),
                        'month' => $expected->format('F'),
                    );
                    $this->assertSame($ex, $result,
                        'Incorrect result for data ' . $expected->format("D, d M Y H:i:s O") . ' ' . $tz);
                }
            }
        }
    }

    public function test_userdate() {
        global $CFG;

        $this->resetAfterTest();

        $dates = array(
            array(2, 1, 0, 40, 40),
            array(4, 3, 0, 30, 22),
            array(9, 5, 0, 20, 19),
            array(11, 28, 0, 10, 45),
        );
        $years = array(1999, 2009, 2014, 2018);

        $users = array();
        $users[] = $this->getDataGenerator()->create_user(array('timezone' => 99));
        $users[] = $this->getDataGenerator()->create_user(array('timezone' => 'Europe/Prague'));
        $users[] = $this->getDataGenerator()->create_user(array('timezone' => 'Pacific/Auckland'));
        $users[] = $this->getDataGenerator()->create_user(array('timezone' => 'Australia/Perth'));
        $users[] = $this->getDataGenerator()->create_user(array('timezone' => 'America/New_York'));

        $format = get_string('strftimedaydatetime', 'langconfig');

        $this->setTimezone('Pacific/Auckland', 'Pacific/Auckland');
        foreach ($years as $year) {
            foreach ($dates as $date) {
                $expected = new DateTime('now', new DateTimeZone('UTC'));
                $expected->setDate($year, $date[0], $date[1]);
                $expected->setTime($date[2], $date[3], $date[4]);

                foreach ($users as $user) {
                    $this->setUser($user);
                    $expected->setTimezone(new DateTimeZone(($user->timezone == 99 ? 'Pacific/Auckland' : $user->timezone)));
                    $result = userdate($expected->getTimestamp(), '', 99, false, false);
                    date_default_timezone_set($expected->getTimezone()->getName());
                    $ex = strftime($format, $expected->getTimestamp());
                    date_default_timezone_set($CFG->timezone);
                    $this->assertSame($ex, $result);
                }
            }
        }
    }

    public function test_usertime() {
        
        $time = gmmktime(1, 1, 1, 3, 1, 2015);
        $this->assertSame($time - (60 * 60 * 1), usertime($time, '1'));
        $this->assertSame($time - (60 * 60 * -1), usertime($time, '-1'));
        $this->assertSame($time - (60 * 60 * 1), usertime($time, 'Europe/Prague'));
        $this->assertSame($time - (60 * 60 * 8), usertime($time, 'Australia/Perth'));
        $this->assertSame($time - (60 * 60 * 12), usertime($time, 'Pacific/Auckland'));
        $this->assertSame($time - (60 * 60 * -5), usertime($time, 'America/New_York'));

        $time = gmmktime(1, 1, 1, 5, 1, 2015);
        $this->assertSame($time - (60 * 60 * 1), usertime($time, '1'));
        $this->assertSame($time - (60 * 60 * -1), usertime($time, '-1'));
        $this->assertSame($time - (60 * 60 * 1), usertime($time, 'Europe/Prague'));
        $this->assertSame($time - (60 * 60 * 8), usertime($time, 'Australia/Perth'));
        $this->assertSame($time - (60 * 60 * 12), usertime($time, 'Pacific/Auckland'));
        $this->assertSame($time - (60 * 60 * -5), usertime($time, 'America/New_York'));
    }

    public function test_usertimezone() {
        global $USER;
        $this->resetAfterTest();

        $this->setTimezone('Pacific/Auckland', 'Pacific/Auckland');

        $USER->timezone = 'Europe/Prague';
        $this->assertSame('Europe/Prague', usertimezone());

        $USER->timezone = '1';
        $this->assertSame('UTC+1', usertimezone());

        $USER->timezone = '0';
        $this->assertSame('UTC', usertimezone());

        $USER->timezone = '99';
        $this->assertSame('Pacific/Auckland', usertimezone());

        $USER->timezone = '99';
        $this->assertSame('Europe/Berlin', usertimezone('Europe/Berlin'));

        $USER->timezone = '99';
        $this->assertSame('Pacific/Auckland', usertimezone('99'));

        $USER->timezone = 'Europe/Prague';
        $this->assertSame('Europe/Prague', usertimezone('99'));

                        $USER->timezone = '-9.23';
        $this->assertSame('UTC-9', usertimezone('99'));
    }
}
