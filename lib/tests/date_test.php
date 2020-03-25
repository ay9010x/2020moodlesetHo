<?php



defined('MOODLE_INTERNAL') || die();


class core_date_testcase extends advanced_testcase {
    public function test_get_default_php_timezone() {
        $this->resetAfterTest();

        $origtz = core_date::get_default_php_timezone();
        $this->assertNotEmpty($origtz);

        $this->setTimezone('Pacific/Auckland', 'Europe/Prague');
        $this->assertSame('Europe/Prague', core_date::get_default_php_timezone());

        $this->setTimezone('Pacific/Auckland', 'UTC');
        $this->assertSame('UTC', core_date::get_default_php_timezone());

        $this->setTimezone('Pacific/Auckland', 'GMT');
        $this->assertSame('GMT', core_date::get_default_php_timezone());
    }

    public function test_normalise_timezone() {
        $this->resetAfterTest();

        $this->setTimezone('Pacific/Auckland');
        $this->assertSame('Pacific/Auckland', core_date::normalise_timezone('Pacific/Auckland'));
        $this->assertSame('Pacific/Auckland', core_date::normalise_timezone('99'));
        $this->assertSame('Pacific/Auckland', core_date::normalise_timezone(99));
        $this->assertSame('GMT', core_date::normalise_timezone('GMT'));
        $this->assertSame('UTC', core_date::normalise_timezone('UTC'));
        $this->assertSame('Pacific/Auckland', core_date::normalise_timezone('xxxxxxxx'));
        $this->assertSame('Europe/Berlin', core_date::normalise_timezone('Central European Time'));
        $this->assertSame('Etc/GMT', core_date::normalise_timezone('0'));
        $this->assertSame('Etc/GMT', core_date::normalise_timezone('0.0'));
        $this->assertSame('Etc/GMT-2', core_date::normalise_timezone(2));
        $this->assertSame('Etc/GMT-2', core_date::normalise_timezone('2.0'));
        $this->assertSame('Etc/GMT+2', core_date::normalise_timezone(-2));
        $this->assertSame('Etc/GMT+2', core_date::normalise_timezone('-2.0'));
        $this->assertSame('Etc/GMT+4', core_date::normalise_timezone(-4));
        $this->assertSame('Etc/GMT-2', core_date::normalise_timezone('UTC+2'));
        $this->assertSame('Etc/GMT+2', core_date::normalise_timezone('UTC-2'));
        $this->assertSame('Etc/GMT-2', core_date::normalise_timezone('GMT+2'));
        $this->assertSame('Etc/GMT+2', core_date::normalise_timezone('GMT-2'));
        $this->assertSame('Etc/GMT+12', core_date::normalise_timezone(-12));
        $this->assertSame('Pacific/Auckland', core_date::normalise_timezone(-13));
        $this->assertSame('Pacific/Auckland', core_date::normalise_timezone(-14));
        $this->assertSame('Etc/GMT-12', core_date::normalise_timezone(12));
        $this->assertSame('Etc/GMT-13', core_date::normalise_timezone(13));
        $this->assertSame('Etc/GMT-14', core_date::normalise_timezone(14));

        $this->assertSame('Asia/Kabul', core_date::normalise_timezone(4.5));
        $this->assertSame('Asia/Kolkata', core_date::normalise_timezone(5.5));
        $this->assertSame('Asia/Rangoon', core_date::normalise_timezone(6.5));
        $this->assertSame('Australia/Darwin', core_date::normalise_timezone('9.5'));

        $this->setTimezone('99', 'Pacific/Auckland');
        $this->assertSame('Pacific/Auckland', core_date::normalise_timezone('Pacific/Auckland'));
        $this->assertSame('Pacific/Auckland', core_date::normalise_timezone('99'));
        $this->assertSame('Pacific/Auckland', core_date::normalise_timezone(99));
        $this->assertSame('GMT', core_date::normalise_timezone('GMT'));
        $this->assertSame('UTC', core_date::normalise_timezone('UTC'));
        $this->assertSame('Pacific/Auckland', core_date::normalise_timezone('xxxxxxxx'));
        $this->assertSame('Europe/Berlin', core_date::normalise_timezone('Central European Time'));
        $this->assertSame('Etc/GMT', core_date::normalise_timezone('0'));
        $this->assertSame('Etc/GMT', core_date::normalise_timezone('0.0'));
        $this->assertSame('Etc/GMT-2', core_date::normalise_timezone(2));
        $this->assertSame('Etc/GMT-2', core_date::normalise_timezone('2.0'));
        $this->assertSame('Etc/GMT+2', core_date::normalise_timezone(-2));
        $this->assertSame('Etc/GMT+2', core_date::normalise_timezone('-2.0'));
        $this->assertSame('Etc/GMT-2', core_date::normalise_timezone('UTC+2'));
        $this->assertSame('Etc/GMT+2', core_date::normalise_timezone('UTC-2'));
        $this->assertSame('Etc/GMT-2', core_date::normalise_timezone('GMT+2'));
        $this->assertSame('Etc/GMT+2', core_date::normalise_timezone('GMT-2'));
        $this->assertSame('Etc/GMT+12', core_date::normalise_timezone(-12));
        $this->assertSame('Pacific/Auckland', core_date::normalise_timezone(-13));
        $this->assertSame('Pacific/Auckland', core_date::normalise_timezone(-14));
        $this->assertSame('Etc/GMT-12', core_date::normalise_timezone(12));
        $this->assertSame('Etc/GMT-13', core_date::normalise_timezone(13));
        $this->assertSame('Etc/GMT-14', core_date::normalise_timezone(14));

        $this->setTimezone('Pacific/Auckland', 'Pacific/Auckland');
        $tz = new DateTimeZone('Pacific/Auckland');
        $this->assertSame('Pacific/Auckland', core_date::normalise_timezone($tz));
    }

    public function test_windows_conversion() {
        $file = __DIR__ . '/fixtures/timezonewindows.xml';

        $contents = file_get_contents($file);
        preg_match_all('/<mapZone other="([^"]+)" territory="001" type="([^"]+)"\/>/', $contents, $matches, PREG_SET_ORDER);

        $this->assertCount(104, $matches); 
        foreach ($matches as $match) {
            $result = core_date::normalise_timezone($match[1]);
            if ($result == $match[2]) {
                $this->assertSame($match[2], $result);
            } else {
                $data = new DateTime('now', new DateTimeZone($match[2]));
                $expectedoffset = $data->getOffset();
                $data = new DateTime('now', new DateTimeZone($result));
                $resultoffset = $data->getOffset();
                $this->assertSame($expectedoffset, $resultoffset, "$match[1] is expected to be converted to $match[2] not $result");
            }
        }
    }

    
    public function test_php_gmt_offsets() {
        $this->resetAfterTest();

        $this->setTimezone('Pacific/Auckland', 'Pacific/Auckland');

        for ($i = -12; $i < 0; $i++) {
            $date = new DateTime('now', new DateTimeZone("Etc/GMT{$i}"));
            $this->assertSame(- $i * 60 * 60, $date->getOffset());
            $date = new DateTime('now', new DateTimeZone(core_date::normalise_timezone("GMT{$i}")));
            $this->assertSame($i * 60 * 60, $date->getOffset());
            $date = new DateTime('now', new DateTimeZone(core_date::normalise_timezone("UTC{$i}")));
            $this->assertSame($i * 60 * 60, $date->getOffset());
        }

        $date = new DateTime('now', new DateTimeZone('Etc/GMT'));
        $this->assertSame(0, $date->getOffset());

        for ($i = 1; $i <= 12; $i++) {
            $date = new DateTime('now', new DateTimeZone("Etc/GMT+{$i}"));
            $this->assertSame(- $i * 60 * 60, $date->getOffset());
            $date = new DateTime('now', new DateTimeZone(core_date::normalise_timezone("GMT+{$i}")));
            $this->assertSame($i * 60 * 60, $date->getOffset());
            $date = new DateTime('now', new DateTimeZone(core_date::normalise_timezone("UTC+{$i}")));
            $this->assertSame($i * 60 * 60, $date->getOffset());
        }
    }

    public function test_get_localised_timezone() {
        $this->resetAfterTest();

        $this->setTimezone('Pacific/Auckland', 'Pacific/Auckland');

        $result = core_date::get_localised_timezone('Pacific/Auckland');
        $this->assertSame('Pacific/Auckland', $result);

        $result = core_date::get_localised_timezone('99');
        $this->assertSame('Server timezone (Pacific/Auckland)', $result);

        $result = core_date::get_localised_timezone(99);
        $this->assertSame('Server timezone (Pacific/Auckland)', $result);

        $result = core_date::get_localised_timezone('Etc/GMT-1');
        $this->assertSame('UTC+1', $result);

        $result = core_date::get_localised_timezone('Etc/GMT+2');
        $this->assertSame('UTC-2', $result);

        $result = core_date::get_localised_timezone('GMT');
        $this->assertSame('UTC', $result);

        $result = core_date::get_localised_timezone('Etc/GMT');
        $this->assertSame('UTC', $result);
    }

    public function test_get_list_of_timezones() {
        $this->resetAfterTest();

        $this->setTimezone('Pacific/Auckland', 'Pacific/Auckland');

        $phpzones = DateTimeZone::listIdentifiers();

        $zones = core_date::get_list_of_timezones();
        $this->assertSame(count($phpzones), count($zones));
        foreach ($zones as $zone => $zonename) {
            $this->assertSame($zone, $zonename);             $this->assertContains($zone, $phpzones);         }

        $this->assertSame($zones, core_date::get_list_of_timezones(null, false));

        $nnzones = core_date::get_list_of_timezones(null, true);
        $last = $nnzones['99'];
        $this->assertSame('Server timezone (Pacific/Auckland)', $last);
        unset($nnzones['99']);
        $this->assertSame($zones, $nnzones);

        $nnzones = core_date::get_list_of_timezones('99', false);
        $last = $nnzones['99'];
        $this->assertSame('Server timezone (Pacific/Auckland)', $last);
        unset($nnzones['99']);
        $this->assertSame($zones, $nnzones);

        $xxzones = core_date::get_list_of_timezones('xx', false);
        $xx = $xxzones['xx'];
        $this->assertSame('Invalid timezone "xx"', $xx);
        unset($xxzones['xx']);
        $this->assertSame($zones, $xxzones);

        $xxzones = core_date::get_list_of_timezones('1', false);
        $xx = $xxzones['1'];
        $this->assertSame('Invalid timezone "UTC+1.0"', $xx);
        unset($xxzones['1']);
        $this->assertSame($zones, $xxzones);

        $xxzones = core_date::get_list_of_timezones('-1.5', false);
        $xx = $xxzones['-1.5'];
        $this->assertSame('Invalid timezone "UTC-1.5"', $xx);
        unset($xxzones['-1.5']);
        $this->assertSame($zones, $xxzones);

    }

    public function test_get_server_timezone() {
        global $CFG;
        $this->resetAfterTest();

        $this->setTimezone('Pacific/Auckland', 'Pacific/Auckland');
        $this->assertSame('Pacific/Auckland', core_date::get_server_timezone());

        $this->setTimezone('Pacific/Auckland', 'Europe/Prague');
        $this->assertSame('Pacific/Auckland', core_date::get_server_timezone());

        $this->setTimezone('99', 'Pacific/Auckland');
        $this->assertSame('Pacific/Auckland', core_date::get_server_timezone());

        $this->setTimezone(99, 'Pacific/Auckland');
        $this->assertSame('Pacific/Auckland', core_date::get_server_timezone());

        $this->setTimezone('Pacific/Auckland', 'Pacific/Auckland');
        unset($CFG->timezone);
        $this->assertSame('Pacific/Auckland', core_date::get_server_timezone());

                $this->setTimezone('xxx/zzzz', 'Europe/Prague');
        $this->assertSame('Europe/Prague', core_date::get_server_timezone());
    }

    public function test_get_server_timezone_object() {
        $this->resetAfterTest();

        $zones = core_date::get_list_of_timezones();
        foreach ($zones as $zone) {
            $this->setTimezone($zone, 'Pacific/Auckland');
            $tz = core_date::get_server_timezone_object();
            $this->assertInstanceOf('DateTimeZone', $tz);
            $this->assertSame($zone, $tz->getName());
        }
    }

    public function test_set_default_server_timezone() {
        global $CFG;
        $this->resetAfterTest();

        $this->setTimezone('Europe/Prague', 'Pacific/Auckland');
        unset($CFG->timezone);
        date_default_timezone_set('UTC');
        core_date::set_default_server_timezone();
        $this->assertSame('Pacific/Auckland', date_default_timezone_get());

        $this->setTimezone('', 'Pacific/Auckland');
        date_default_timezone_set('UTC');
        core_date::set_default_server_timezone();
        $this->assertSame('Pacific/Auckland', date_default_timezone_get());

        $this->setTimezone('99', 'Pacific/Auckland');
        date_default_timezone_set('UTC');
        core_date::set_default_server_timezone();
        $this->assertSame('Pacific/Auckland', date_default_timezone_get());

        $this->setTimezone(99, 'Pacific/Auckland');
        date_default_timezone_set('UTC');
        core_date::set_default_server_timezone();
        $this->assertSame('Pacific/Auckland', date_default_timezone_get());

        $this->setTimezone('Europe/Prague', 'Pacific/Auckland');
        $CFG->timezone = 'UTC';
        core_date::set_default_server_timezone();
        $this->assertSame('UTC', date_default_timezone_get());

        $this->setTimezone('Europe/Prague', 'Pacific/Auckland');
        $CFG->timezone = 'Australia/Perth';
        core_date::set_default_server_timezone();
        $this->assertSame('Australia/Perth', date_default_timezone_get());

        $this->setTimezone('0', 'Pacific/Auckland');
        date_default_timezone_set('UTC');
        core_date::set_default_server_timezone();
        $this->assertSame('Etc/GMT', date_default_timezone_get());

        $this->setTimezone('1', 'Pacific/Auckland');
        date_default_timezone_set('UTC');
        core_date::set_default_server_timezone();
        $this->assertSame('Etc/GMT-1', date_default_timezone_get());

        $this->setTimezone(1, 'Pacific/Auckland');
        date_default_timezone_set('UTC');
        core_date::set_default_server_timezone();
        $this->assertSame('Etc/GMT-1', date_default_timezone_get());

        $this->setTimezone('1.0', 'Pacific/Auckland');
        date_default_timezone_set('UTC');
        core_date::set_default_server_timezone();
        $this->assertSame('Etc/GMT-1', date_default_timezone_get());
    }

    public function legacyUserTimezoneProvider() {
        return [
            ['', 'Australia/Perth'],                        ['-13.0', 'Australia/Perth'],                   ['-12.5', 'Etc/GMT+12'],
            ['-12.0', 'Etc/GMT+12'],
            ['-11.5', 'Etc/GMT+11'],
            ['-11.0', 'Etc/GMT+11'],
            ['-10.5', 'Etc/GMT+10'],
            ['-10.0', 'Etc/GMT+10'],
            ['-9.5', 'Etc/GMT+9'],
            ['-9.0', 'Etc/GMT+9'],
            ['-8.5', 'Etc/GMT+8'],
            ['-8.0', 'Etc/GMT+8'],
            ['-7.5', 'Etc/GMT+7'],
            ['-7.0', 'Etc/GMT+7'],
            ['-6.5', 'Etc/GMT+6'],
            ['-6.0', 'Etc/GMT+6'],
            ['-5.5', 'Etc/GMT+5'],
            ['-5.0', 'Etc/GMT+5'],
            ['-4.5', 'Etc/GMT+4'],
            ['-4.0', 'Etc/GMT+4'],
            ['-3.5', 'Etc/GMT+3'],
            ['-3.0', 'Etc/GMT+3'],
            ['-2.5', 'Etc/GMT+2'],
            ['-2.0', 'Etc/GMT+2'],
            ['-1.5', 'Etc/GMT+1'],
            ['-1.0', 'Etc/GMT+1'],
            ['-0.5', 'Etc/GMT'],
            ['0', 'Etc/GMT'],
            ['0.0', 'Etc/GMT'],
            ['0.5', 'Etc/GMT'],
            ['1.0', 'Etc/GMT-1'],
            ['1.5', 'Etc/GMT-1'],
            ['2.0', 'Etc/GMT-2'],
            ['2.5', 'Etc/GMT-2'],
            ['3.0', 'Etc/GMT-3'],
            ['3.5', 'Etc/GMT-3'],
            ['4.0', 'Etc/GMT-4'],
            ['4.5', 'Asia/Kabul'],
            ['5.0', 'Etc/GMT-5'],
            ['5.5', 'Asia/Kolkata'],
            ['6.0', 'Etc/GMT-6'],
            ['6.5', 'Asia/Rangoon'],
            ['7.0', 'Etc/GMT-7'],
            ['7.5', 'Etc/GMT-7'],
            ['8.0', 'Etc/GMT-8'],
            ['8.5', 'Etc/GMT-8'],
            ['9.0', 'Etc/GMT-9'],
            ['9.5', 'Australia/Darwin'],
            ['10.0', 'Etc/GMT-10'],
            ['10.5', 'Etc/GMT-10'],
            ['11.0', 'Etc/GMT-11'],
            ['11.5', 'Etc/GMT-11'],
            ['12.0', 'Etc/GMT-12'],
            ['12.5', 'Etc/GMT-12'],
            ['13.0', 'Etc/GMT-13'],
        ];
    }

    
    public function test_get_legacy_user_timezone($tz, $expected) {
        $this->setTimezone('Australia/Perth', 'Australia/Perth');
        $this->assertEquals($expected, core_date::get_user_timezone($tz));
    }

    public function test_get_user_timezone() {
        global $CFG, $USER;
        $this->resetAfterTest();

        
        $this->setTimezone('Europe/Prague', 'Pacific/Auckland');
        $USER->timezone = 'Pacific/Auckland';
        $CFG->forcetimezone = '99';
        $this->assertSame('Pacific/Auckland', core_date::get_user_timezone(null));
        $this->assertSame('Pacific/Auckland', core_date::get_user_timezone());

        $this->setTimezone('Europe/Prague', 'Pacific/Auckland');
        $USER->timezone = 'Pacific/Auckland';
        $CFG->forcetimezone = 'Europe/Berlin';
        $this->assertSame('Europe/Berlin', core_date::get_user_timezone(null));
        $this->assertSame('Europe/Berlin', core_date::get_user_timezone());

        $this->setTimezone('Europe/Prague', 'Pacific/Auckland');
        $USER->timezone = 'Pacific/Auckland';
        $CFG->forcetimezone = 'xxx/yyy';
        $this->assertSame('Europe/Prague', core_date::get_user_timezone(null));
        $this->assertSame('Europe/Prague', core_date::get_user_timezone());

        $this->setTimezone('Europe/Prague', 'Pacific/Auckland');
        $USER->timezone = 'abc/def';
        $CFG->forcetimezone = '99';
        $this->assertSame('Europe/Prague', core_date::get_user_timezone(null));
        $this->assertSame('Europe/Prague', core_date::get_user_timezone());

        $this->setTimezone('xxx/yyy', 'Europe/London');
        $USER->timezone = 'abc/def';
        $CFG->forcetimezone = 'Europe/Berlin';
        $this->assertSame('Europe/Berlin', core_date::get_user_timezone(null));
        $this->assertSame('Europe/Berlin', core_date::get_user_timezone());

        $this->setTimezone('xxx/yyy', 'Europe/London');
        $USER->timezone = 'abc/def';
        $CFG->forcetimezone = 99;
        $this->assertSame('Europe/London', core_date::get_user_timezone(null));
        $this->assertSame('Europe/London', core_date::get_user_timezone());

                $admin = get_admin();

        $this->setTimezone('Europe/London');
        $USER->timezone = 'Pacific/Auckland';
        $CFG->forcetimezone = '99';
        $admin->timezone = 'Australia/Perth';
        $this->assertSame('Australia/Perth', core_date::get_user_timezone($admin));

        $this->setTimezone('Europe/Prague');
        $USER->timezone = 'Pacific/Auckland';
        $CFG->forcetimezone = '99';
        $admin->timezone = '99';
        $this->assertSame('Europe/Prague', core_date::get_user_timezone($admin));

        $this->setTimezone('99', 'Europe/London');
        $USER->timezone = 'Pacific/Auckland';
        $CFG->forcetimezone = '99';
        $admin->timezone = '99';
        $this->assertSame('Europe/London', core_date::get_user_timezone($admin));

        $this->setTimezone('Europe/Prague');
        $USER->timezone = 'Pacific/Auckland';
        $CFG->forcetimezone = '99';
        $admin->timezone = 'xx/zz';
        $this->assertSame('Europe/Prague', core_date::get_user_timezone($admin));

        $this->setTimezone('Europe/Prague');
        $USER->timezone = 'Pacific/Auckland';
        $CFG->forcetimezone = '99';
        unset($admin->timezone);
        $this->assertSame('Europe/Prague', core_date::get_user_timezone($admin));

        $this->setTimezone('Europe/Prague');
        $USER->timezone = 'Pacific/Auckland';
        $CFG->forcetimezone = 'Europe/Berlin';
        $admin->timezone = 'Australia/Perth';
        $this->assertSame('Europe/Berlin', core_date::get_user_timezone($admin));

        
        $this->setTimezone('Europe/Prague');
        $CFG->forcetimezone = '99';

        $USER->timezone = 'Pacific/Auckland';
        $this->assertSame('Pacific/Auckland', core_date::get_user_timezone('99'));
        $this->assertSame('Etc/GMT-1', core_date::get_user_timezone('1'));
        $this->assertSame('Etc/GMT+1', core_date::get_user_timezone(-1));
        $this->assertSame('Europe/London', core_date::get_user_timezone('Europe/London'));
        $USER->timezone = '99';
        $this->assertSame('Europe/Prague', core_date::get_user_timezone('99'));
        $this->assertSame('Europe/London', core_date::get_user_timezone('Europe/London'));
        $this->assertSame('Europe/Prague', core_date::get_user_timezone('xxx/zzz'));
        $USER->timezone = 'xxz/zzz';
        $this->assertSame('Europe/Prague', core_date::get_user_timezone('99'));

        $this->setTimezone('99', 'Europe/Prague');
        $CFG->forcetimezone = '99';

        $USER->timezone = 'Pacific/Auckland';
        $this->assertSame('Pacific/Auckland', core_date::get_user_timezone('99'));
        $this->assertSame('Europe/London', core_date::get_user_timezone('Europe/London'));
        $USER->timezone = '99';
        $this->assertSame('Europe/Prague', core_date::get_user_timezone('99'));
        $this->assertSame('Europe/London', core_date::get_user_timezone('Europe/London'));
        $this->assertSame('Europe/Prague', core_date::get_user_timezone('xxx/zzz'));
        $USER->timezone = 99;
        $this->assertSame('Europe/London', core_date::get_user_timezone('Europe/London'));
        $this->assertSame('Europe/Prague', core_date::get_user_timezone('xxx/zzz'));
        $USER->timezone = 'xxz/zzz';
        $this->assertSame('Europe/Prague', core_date::get_user_timezone('99'));

        $this->setTimezone('xxx', 'Europe/Prague');
        $CFG->forcetimezone = '99';
        $USER->timezone = 'xxx';
        $this->assertSame('Europe/Prague', core_date::get_user_timezone('99'));
        $this->assertSame('Europe/Prague', core_date::get_user_timezone(99));
        $this->assertSame('Etc/GMT-1', core_date::get_user_timezone(1));

        $this->setTimezone('Europe/Prague');
        $CFG->forcetimezone = 'Pacific/Auckland';
        $USER->timezone = 'Europe/London';
        $this->assertSame('Pacific/Auckland', core_date::get_user_timezone(99));
        $this->assertSame('Europe/Berlin', core_date::get_user_timezone('Europe/Berlin'));

        
        $this->setTimezone('UTC');
        $USER->timezone = 'Europe/London';
        $CFG->forcetimezone = 99;
        $tz = new DateTimeZone('Pacific/Auckland');
        $this->assertSame('Pacific/Auckland', core_date::get_user_timezone($tz));
    }

    public function test_get_user_timezone_object() {
        global $CFG, $USER;
        $this->resetAfterTest();

        $this->setTimezone('Pacific/Auckland');
        $CFG->forcetimezone = '99';

        $zones = core_date::get_list_of_timezones();
        foreach ($zones as $zone) {
            $USER->timezone = $zone;
            $tz = core_date::get_user_timezone_object();
            $this->assertInstanceOf('DateTimeZone', $tz);
            $this->assertSame($zone, $tz->getName());
        }
    }
}
