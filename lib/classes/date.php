<?php




class core_date {
    
    protected static $goodzones = null;

    
    protected static $bczones = null;

    
    protected static $badzones = null;

    
    protected static $defaultphptimezone = null;

    
    public static function get_list_of_timezones($currentvalue = null, $include99 = false) {
        self::init_zones();

                $timezones = array();
        foreach (self::$goodzones as $tzkey => $ignored) {
            $timezones[$tzkey] = self::get_localised_timezone($tzkey);
        }
        core_collator::asort($timezones);

                if ($include99 or $currentvalue == 99) {
            $timezones['99'] = self::get_localised_timezone('99');
        }

        if (!isset($currentvalue) or isset($timezones[$currentvalue])) {
            return $timezones;
        }

        if (is_numeric($currentvalue)) {
                        if ($currentvalue == 0) {
                $a = 'UTC';
            } else {
                $modifier = ($currentvalue > 0) ? '+' : '';
                $a = 'UTC' . $modifier . number_format($currentvalue, 1);
            }
            $timezones[$currentvalue] = get_string('timezoneinvalid', 'core_admin', $a);
        } else {
                        $timezones[$currentvalue] = get_string('timezoneinvalid', 'core_admin', $currentvalue);
        }

        return $timezones;
    }

    
    public static function get_localised_timezone($tz) {
        if ($tz == 99) {
            $tz = self::get_server_timezone();
            $tz = self::get_localised_timezone($tz);
            return get_string('timezoneserver', 'core_admin', $tz);
        }

        if (get_string_manager()->string_exists(strtolower($tz), 'core_timezones')) {
            $tz = get_string(strtolower($tz), 'core_timezones');
        } else if ($tz === 'GMT' or $tz === 'Etc/GMT' or $tz === 'Etc/UTC') {
            $tz = 'UTC';
        } else if (preg_match('|^Etc/GMT([+-])([0-9]+)$|', $tz, $matches)) {
            $sign = $matches[1] === '+' ? '-' : '+';
            $tz = 'UTC' . $sign . $matches[2];
        }

        return $tz;
    }

    
    public static function normalise_timezone($tz) {
        global $CFG;

        if ($tz instanceof DateTimeZone) {
            return $tz->getName();
        }

        self::init_zones();
        $tz = (string)$tz;

        if (isset(self::$goodzones[$tz]) or isset(self::$bczones[$tz])) {
            return $tz;
        }

        $fixed = false;
        if (isset(self::$badzones[$tz])) {
                        $tz = self::$badzones[$tz];
            $fixed = true;
        } else if (is_numeric($tz)) {
                        $roundedtz = (string)(int)$tz;
            if (isset(self::$badzones[$roundedtz])) {
                $tz = self::$badzones[$roundedtz];
                $fixed = true;
            }
        }

        if ($fixed and isset(self::$goodzones[$tz]) or isset(self::$bczones[$tz])) {
            return $tz;
        }

                if (isset($CFG->timezone) and !is_numeric($CFG->timezone)) {
            $result = @timezone_open($CFG->timezone);             if ($result !== false) {
                return $result->getName();
            }
        }

                return self::get_default_php_timezone();
    }

    
    public static function get_server_timezone() {
        global $CFG;

        if (!isset($CFG->timezone) or $CFG->timezone == 99 or $CFG->timezone === '') {
            return self::get_default_php_timezone();
        }

        return self::normalise_timezone($CFG->timezone);
    }

    
    public static function get_server_timezone_object() {
        $tz = self::get_server_timezone();
        return new DateTimeZone($tz);
    }

    
    public static function set_default_server_timezone() {
        global $CFG;

        if (!isset($CFG->timezone) or $CFG->timezone == 99 or $CFG->timezone === '') {
            date_default_timezone_set(self::get_default_php_timezone());
            return;
        }

        $current = date_default_timezone_get();
        if ($current === $CFG->timezone) {
                        return;
        }

        if (!isset(self::$goodzones)) {
                                    $result = @timezone_open($CFG->timezone);             if ($result !== false) {
                date_default_timezone_set($result->getName());
                return;
            }
        }

                date_default_timezone_set(self::get_server_timezone());
    }

    
    public static function get_user_timezone($userorforcedtz = null) {
        global $USER, $CFG;

        if ($userorforcedtz instanceof DateTimeZone) {
            return $userorforcedtz->getName();
        }

        if (isset($userorforcedtz) and !is_object($userorforcedtz) and $userorforcedtz != 99) {
                        return self::normalise_timezone($userorforcedtz);
        }

        if (isset($CFG->forcetimezone) and $CFG->forcetimezone != 99) {
                        return self::normalise_timezone($CFG->forcetimezone);
        }

        if ($userorforcedtz === null) {
            $tz = isset($USER->timezone) ? $USER->timezone : 99;

        } else if (is_object($userorforcedtz)) {
            $tz = isset($userorforcedtz->timezone) ? $userorforcedtz->timezone : 99;

        } else {
            if ($userorforcedtz == 99) {
                $tz = isset($USER->timezone) ? $USER->timezone : 99;
            } else {
                $tz = $userorforcedtz;
            }
        }

        if ($tz == 99) {
            return self::get_server_timezone();
        }

        return self::normalise_timezone($tz);
    }

    
    public static function get_user_timezone_object($userorforcedtz = null) {
        $tz = self::get_user_timezone($userorforcedtz);
        return new DateTimeZone($tz);
    }

    
    public static function get_default_php_timezone() {
        if (!isset(self::$defaultphptimezone)) {
                        self::store_default_php_timezone();
        }

        return self::$defaultphptimezone;
    }

    
    public static function store_default_php_timezone() {
        if ((defined('PHPUNIT_TEST') and PHPUNIT_TEST)
            or defined('BEHAT_SITE_RUNNING') or defined('BEHAT_TEST') or defined('BEHAT_UTIL')) {
                        self::$defaultphptimezone = 'Australia/Perth';
            return;
        }
        if (!isset(self::$defaultphptimezone)) {
            self::$defaultphptimezone = date_default_timezone_get();
        }
    }

    
    public static function phpunit_override_default_php_timezone($tz) {
        if (!defined('PHPUNIT_TEST')) {
            throw new coding_exception('core_date::phpunit_override_default_php_timezone() must be used only from unit tests');
        }
        $result = timezone_open($tz);         if ($result !== false) {
            self::$defaultphptimezone = $tz;
        } else {
            self::$defaultphptimezone = 'Australia/Perth';
        }
    }

    
    public static function phpunit_reset() {
        global $CFG;
        if (!defined('PHPUNIT_TEST')) {
            throw new coding_exception('core_date::phpunit_reset() must be used only from unit tests');
        }
        self::store_default_php_timezone();
        date_default_timezone_set($CFG->timezone);
    }

    
    protected static function init_zones() {
        if (isset(self::$goodzones)) {
            return;
        }

        $zones = DateTimeZone::listIdentifiers();
        self::$goodzones = array_fill_keys($zones, true);

        $zones = DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC);
        self::$bczones = array();
        foreach ($zones as $zone) {
            if (isset(self::$goodzones[$zone])) {
                continue;
            }
            self::$bczones[$zone] = true;
        }

        self::$badzones = array(
                        'Dateline Standard Time' => 'Etc/GMT+12',
            'Hawaiian Standard Time' => 'Pacific/Honolulu',
            'Alaskan Standard Time' => 'America/Anchorage',
            'Pacific Standard Time (Mexico)' => 'America/Santa_Isabel',
            'Pacific Standard Time' => 'America/Los_Angeles',
            'US Mountain Standard Time' => 'America/Phoenix',
            'Mountain Standard Time (Mexico)' => 'America/Chihuahua',
            'Mountain Standard Time' => 'America/Denver',
            'Central America Standard Time' => 'America/Guatemala',
            'Central Standard Time' => 'America/Chicago',
            'Central Standard Time (Mexico)' => 'America/Mexico_City',
            'Canada Central Standard Time' => 'America/Regina',
            'SA Pacific Standard Time' => 'America/Bogota',
            'Eastern Standard Time' => 'America/New_York',
            'US Eastern Standard Time' => 'America/Indianapolis',
            'Venezuela Standard Time' => 'America/Caracas',
            'Paraguay Standard Time' => 'America/Asuncion',
            'Atlantic Standard Time' => 'America/Halifax',
            'Central Brazilian Standard Time' => 'America/Cuiaba',
            'SA Western Standard Time' => 'America/La_Paz',
            'Pacific SA Standard Time' => 'America/Santiago',
            'Newfoundland Standard Time' => 'America/St_Johns',
            'E. South America Standard Time' => 'America/Sao_Paulo',
            'Argentina Standard Time' => 'America/Buenos_Aires',
            'SA Eastern Standard Time' => 'America/Cayenne',
            'Greenland Standard Time' => 'America/Godthab',
            'Montevideo Standard Time' => 'America/Montevideo',
            'Bahia Standard Time' => 'America/Bahia',
            'Azores Standard Time' => 'Atlantic/Azores',
            'Cape Verde Standard Time' => 'Atlantic/Cape_Verde',
            'Morocco Standard Time' => 'Africa/Casablanca',
            'GMT Standard Time' => 'Europe/London',
            'Greenwich Standard Time' => 'Atlantic/Reykjavik',
            'W. Europe Standard Time' => 'Europe/Berlin',
            'Central Europe Standard Time' => 'Europe/Budapest',
            'Romance Standard Time' => 'Europe/Paris',
            'Central European Standard Time' => 'Europe/Warsaw',
            'W. Central Africa Standard Time' => 'Africa/Lagos',
            'Namibia Standard Time' => 'Africa/Windhoek',
            'Jordan Standard Time' => 'Asia/Amman',
            'GTB Standard Time' => 'Europe/Bucharest',
            'Middle East Standard Time' => 'Asia/Beirut',
            'Egypt Standard Time' => 'Africa/Cairo',
            'Syria Standard Time' => 'Asia/Damascus',
            'South Africa Standard Time' => 'Africa/Johannesburg',
            'FLE Standard Time' => 'Europe/Kiev',
            'Turkey Standard Time' => 'Europe/Istanbul',
            'Israel Standard Time' => 'Asia/Jerusalem',
            'Kaliningrad Standard Time' => 'Europe/Kaliningrad',
            'Libya Standard Time' => 'Africa/Tripoli',
            'Arabic Standard Time' => 'Asia/Baghdad',
            'Arab Standard Time' => 'Asia/Riyadh',
            'Belarus Standard Time' => 'Europe/Minsk',
            'Russian Standard Time' => 'Europe/Moscow',
            'E. Africa Standard Time' => 'Africa/Nairobi',
            'Iran Standard Time' => 'Asia/Tehran',
            'Arabian Standard Time' => 'Asia/Dubai',
            'Azerbaijan Standard Time' => 'Asia/Baku',
            'Russia Time Zone 3' => 'Europe/Samara',
            'Mauritius Standard Time' => 'Indian/Mauritius',
            'Georgian Standard Time' => 'Asia/Tbilisi',
            'Caucasus Standard Time' => 'Asia/Yerevan',
            'Afghanistan Standard Time' => 'Asia/Kabul',
            'West Asia Standard Time' => 'Asia/Tashkent',
            'Ekaterinburg Standard Time' => 'Asia/Yekaterinburg',
            'Pakistan Standard Time' => 'Asia/Karachi',
            'India Standard Time' => 'Asia/Kolkata',             'Sri Lanka Standard Time' => 'Asia/Colombo',
            'Nepal Standard Time' => 'Asia/Katmandu',
            'Central Asia Standard Time' => 'Asia/Almaty',
            'Bangladesh Standard Time' => 'Asia/Dhaka',
            'N. Central Asia Standard Time' => 'Asia/Novosibirsk',
            'Myanmar Standard Time' => 'Asia/Rangoon',
            'SE Asia Standard Time' => 'Asia/Bangkok',
            'North Asia Standard Time' => 'Asia/Krasnoyarsk',
            'China Standard Time' => 'Asia/Shanghai',
            'North Asia East Standard Time' => 'Asia/Irkutsk',
            'Singapore Standard Time' => 'Asia/Singapore',
            'W. Australia Standard Time' => 'Australia/Perth',
            'Taipei Standard Time' => 'Asia/Taipei',
            'Ulaanbaatar Standard Time' => 'Asia/Ulaanbaatar',
            'Tokyo Standard Time' => 'Asia/Tokyo',
            'Korea Standard Time' => 'Asia/Seoul',
            'Yakutsk Standard Time' => 'Asia/Yakutsk',
            'Cen. Australia Standard Time' => 'Australia/Adelaide',
            'AUS Central Standard Time' => 'Australia/Darwin',
            'E. Australia Standard Time' => 'Australia/Brisbane',
            'AUS Eastern Standard Time' => 'Australia/Sydney',
            'West Pacific Standard Time' => 'Pacific/Port_Moresby',
            'Tasmania Standard Time' => 'Australia/Hobart',
            'Magadan Standard Time' => 'Asia/Magadan',
            'Vladivostok Standard Time' => 'Asia/Vladivostok',
            'Russia Time Zone 10' => 'Asia/Srednekolymsk',
            'Central Pacific Standard Time' => 'Pacific/Guadalcanal',
            'Russia Time Zone 11' => 'Asia/Kamchatka',
            'New Zealand Standard Time' => 'Pacific/Auckland',
            'Fiji Standard Time' => 'Pacific/Fiji',
            'Tonga Standard Time' => 'Pacific/Tongatapu',
            'Samoa Standard Time' => 'Pacific/Apia',
            'Line Islands Standard Time' => 'Pacific/Kiritimati',

                        'CET' => 'Europe/Berlin',
            'Central European Time' => 'Europe/Berlin',
            'CST' => 'America/Chicago',
            'Central Time' => 'America/Chicago',
            'CST6CDT' => 'America/Chicago',
            'CDT' => 'America/Chicago',
            'China Time' => 'Asia/Shanghai',
            'EDT' => 'America/New_York',
            'EST' => 'America/New_York',
            'EST5EDT' => 'America/New_York',
            'Eastern Time' => 'America/New_York',
            'IST' => 'Asia/Kolkata',
            'India Time' => 'Asia/Kolkata',
            'JST' => 'Asia/Tokyo',
            'Japan Time' => 'Asia/Tokyo',
            'Japan Standard Time' => 'Asia/Tokyo',
            'MDT' => 'America/Denver',
            'MST' => 'America/Denver',
            'MST7MDT' => 'America/Denver',
            'PDT' => 'America/Los_Angeles',
            'PST' => 'America/Los_Angeles',
            'Pacific Time' => 'America/Los_Angeles',
            'PST8PDT' => 'America/Los_Angeles',
            'HST' => 'Pacific/Honolulu',
            'WET' => 'Europe/London',
            'EET' => 'Europe/Kiev',
            'FET' => 'Europe/Minsk',

                        'UTC-01' => 'Etc/GMT+1',
            'UTC-02' => 'Etc/GMT+2',
            'UTC-03' => 'Etc/GMT+3',
            'UTC-04' => 'Etc/GMT+4',
            'UTC-05' => 'Etc/GMT+5',
            'UTC-06' => 'Etc/GMT+6',
            'UTC-07' => 'Etc/GMT+7',
            'UTC-08' => 'Etc/GMT+8',
            'UTC-09' => 'Etc/GMT+9',

                        'Etc/GMT+0' => 'Etc/GMT',
            'Etc/GMT-0' => 'Etc/GMT',
            'Etc/GMT0' => 'Etc/GMT',

                        'Asia/Calcutta' => 'Asia/Kolkata',
        );

                for ($i = -12; $i <= 14; $i++) {
            $off = abs($i);
            if ($i < 0) {
                $mapto = 'Etc/GMT+' . $off;
                $utc = 'UTC-' . $off;
                $gmt = 'GMT-' . $off;
            } else if ($i > 0) {
                $mapto = 'Etc/GMT-' . $off;
                $utc = 'UTC+' . $off;
                $gmt = 'GMT+' . $off;
            } else {
                $mapto = 'Etc/GMT';
                $utc = 'UTC';
                $gmt = 'GMT';
            }
            if (isset(self::$bczones[$mapto])) {
                self::$badzones[$i . ''] = $mapto;
                self::$badzones[$i . '.0'] = $mapto;
                self::$badzones[$utc] = $mapto;
                self::$badzones[$gmt] = $mapto;
            }
        }

                self::$badzones['4.5'] = 'Asia/Kabul';
        self::$badzones['5.5'] = 'Asia/Kolkata';
        self::$badzones['6.5'] = 'Asia/Rangoon';
        self::$badzones['9.5'] = 'Australia/Darwin';

                foreach (self::$bczones as $zone => $unused) {
            if (isset(self::$badzones[$zone])) {
                unset(self::$badzones[$zone]);
            }
        }
        foreach (self::$goodzones as $zone => $unused) {
            if (isset(self::$badzones[$zone])) {
                unset(self::$badzones[$zone]);
            }
        }
    }
}
