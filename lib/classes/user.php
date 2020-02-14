<?php



defined('MOODLE_INTERNAL') || die();


class core_user {
    
    const NOREPLY_USER = -10;

    
    const SUPPORT_USER = -20;

    
    public static $noreplyuser = false;

    
    public static $supportuser = false;

    
    protected static $propertiescache = null;

    
    public static function get_user($userid, $fields = '*', $strictness = IGNORE_MISSING) {
        global $DB;

                switch ($userid) {
            case self::NOREPLY_USER:
                return self::get_noreply_user();
                break;
            case self::SUPPORT_USER:
                return self::get_support_user();
                break;
            default:
                return $DB->get_record('user', array('id' => $userid), $fields, $strictness);
        }
    }


    
    public static function get_user_by_username($username, $fields = '*', $mnethostid = null, $strictness = IGNORE_MISSING) {
        global $DB, $CFG;

                if (empty($mnethostid)) {
                        $mnethostid = $CFG->mnet_localhost_id;
        }

        return $DB->get_record('user', array('username' => $username, 'mnethostid' => $mnethostid), $fields, $strictness);
    }

    
    protected static function get_dummy_user_record() {
        global $CFG;

        $dummyuser = new stdClass();
        $dummyuser->id = self::NOREPLY_USER;
        $dummyuser->email = $CFG->noreplyaddress;
        $dummyuser->firstname = get_string('noreplyname');
        $dummyuser->username = 'noreply';
        $dummyuser->lastname = '';
        $dummyuser->confirmed = 1;
        $dummyuser->suspended = 0;
        $dummyuser->deleted = 0;
        $dummyuser->picture = 0;
        $dummyuser->auth = 'manual';
        $dummyuser->firstnamephonetic = '';
        $dummyuser->lastnamephonetic = '';
        $dummyuser->middlename = '';
        $dummyuser->alternatename = '';
        $dummyuser->imagealt = '';
        return $dummyuser;
    }

    
    public static function get_noreply_user() {
        global $CFG;

        if (!empty(self::$noreplyuser)) {
            return self::$noreplyuser;
        }

                if (!empty($CFG->noreplyuserid)) {
            self::$noreplyuser = self::get_user($CFG->noreplyuserid);
            self::$noreplyuser->emailstop = 1;             return self::$noreplyuser;
        } else {
                        $noreplyuser = self::get_dummy_user_record();
            $noreplyuser->maildisplay = '1';             $noreplyuser->emailstop = 1;
            return $noreplyuser;
        }
    }

    
    public static function get_support_user() {
        global $CFG;

        if (!empty(self::$supportuser)) {
            return self::$supportuser;
        }

                if (!empty($CFG->supportuserid)) {
            self::$supportuser = self::get_user($CFG->supportuserid, '*', MUST_EXIST);
        } else if (empty(self::$supportuser) && !empty($CFG->supportemail)) {
                        $supportuser = self::get_dummy_user_record();
            $supportuser->id = self::SUPPORT_USER;
            $supportuser->email = $CFG->supportemail;
            if ($CFG->supportname) {
                $supportuser->firstname = $CFG->supportname;
            }
            $supportuser->username = 'support';
            $supportuser->maildisplay = '1';                         $supportuser->emailstop = 0;
            return $supportuser;
        }

                if (empty(self::$supportuser)) {
            self::$supportuser = get_admin();
        }

                self::$supportuser->emailstop = 0;
        return self::$supportuser;
    }

    
    public static function reset_internal_users() {
        if (PHPUNIT_TEST) {
            self::$noreplyuser = false;
            self::$supportuser = false;
        } else {
            debugging('reset_internal_users() should not be used outside phpunit.', DEBUG_DEVELOPER);
        }
    }

    
    public static function is_real_user($userid, $checkdb = false) {
        global $DB;

        if ($userid < 0) {
            return false;
        }
        if ($checkdb) {
            return $DB->record_exists('user', array('id' => $userid));
        } else {
            return true;
        }
    }

    
    public static function require_active_user($user, $checksuspended = false, $checknologin = false) {

        if (!self::is_real_user($user->id)) {
            throw new moodle_exception('invaliduser', 'error');
        }

        if ($user->deleted) {
            throw new moodle_exception('userdeleted');
        }

        if (empty($user->confirmed)) {
            throw new moodle_exception('usernotconfirmed', 'moodle', '', $user->username);
        }

        if (isguestuser($user)) {
            throw new moodle_exception('guestsarenotallowed', 'error');
        }

        if ($checksuspended and $user->suspended) {
            throw new moodle_exception('suspended', 'auth');
        }

        if ($checknologin and $user->auth == 'nologin') {
            throw new moodle_exception('suspended', 'auth');
        }
    }

    
    protected static function fill_properties_cache() {
        global $CFG;
        if (self::$propertiescache !== null) {
            return;
        }

                        $fields = array();
        $fields['id'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED);
        $fields['auth'] = array('type' => PARAM_AUTH, 'null' => NULL_NOT_ALLOWED);
        $fields['confirmed'] = array('type' => PARAM_BOOL, 'null' => NULL_NOT_ALLOWED);
        $fields['policyagreed'] = array('type' => PARAM_BOOL, 'null' => NULL_NOT_ALLOWED);
        $fields['deleted'] = array('type' => PARAM_BOOL, 'null' => NULL_NOT_ALLOWED);
        $fields['suspended'] = array('type' => PARAM_BOOL, 'null' => NULL_NOT_ALLOWED);
        $fields['mnethostid'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED);
        $fields['username'] = array('type' => PARAM_USERNAME, 'null' => NULL_NOT_ALLOWED);
        $fields['password'] = array('type' => PARAM_RAW, 'null' => NULL_NOT_ALLOWED);
        $fields['idnumber'] = array('type' => PARAM_RAW, 'null' => NULL_NOT_ALLOWED);
        $fields['firstname'] = array('type' => PARAM_NOTAGS, 'null' => NULL_NOT_ALLOWED);
        $fields['lastname'] = array('type' => PARAM_NOTAGS, 'null' => NULL_NOT_ALLOWED);
        $fields['surname'] = array('type' => PARAM_NOTAGS, 'null' => NULL_NOT_ALLOWED);
        $fields['email'] = array('type' => PARAM_RAW_TRIMMED, 'null' => NULL_NOT_ALLOWED);
        $fields['emailstop'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED);
        $fields['icq'] = array('type' => PARAM_NOTAGS, 'null' => NULL_NOT_ALLOWED);
        $fields['skype'] = array('type' => PARAM_NOTAGS, 'null' => NULL_ALLOWED);
        $fields['aim'] = array('type' => PARAM_NOTAGS, 'null' => NULL_NOT_ALLOWED);
        $fields['yahoo'] = array('type' => PARAM_NOTAGS, 'null' => NULL_NOT_ALLOWED);
        $fields['msn'] = array('type' => PARAM_NOTAGS, 'null' => NULL_NOT_ALLOWED);
        $fields['phone1'] = array('type' => PARAM_NOTAGS, 'null' => NULL_NOT_ALLOWED);
        $fields['phone2'] = array('type' => PARAM_NOTAGS, 'null' => NULL_NOT_ALLOWED);
        $fields['institution'] = array('type' => PARAM_TEXT, 'null' => NULL_NOT_ALLOWED);
        $fields['department'] = array('type' => PARAM_TEXT, 'null' => NULL_NOT_ALLOWED);
        $fields['address'] = array('type' => PARAM_TEXT, 'null' => NULL_NOT_ALLOWED);
        $fields['city'] = array('type' => PARAM_TEXT, 'null' => NULL_NOT_ALLOWED, 'default' => $CFG->defaultcity);
        $fields['country'] = array('type' => PARAM_ALPHA, 'null' => NULL_NOT_ALLOWED, 'default' => $CFG->country,
                'choices' => array_merge(array('' => ''), get_string_manager()->get_list_of_countries(true, true)));
        $fields['lang'] = array('type' => PARAM_LANG, 'null' => NULL_NOT_ALLOWED, 'default' => $CFG->lang,
                'choices' => array_merge(array('' => ''), get_string_manager()->get_list_of_translations(false)));
        $fields['calendartype'] = array('type' => PARAM_NOTAGS, 'null' => NULL_NOT_ALLOWED, 'default' => $CFG->calendartype,
                'choices' => array_merge(array('' => ''), \core_calendar\type_factory::get_list_of_calendar_types()));
        $fields['theme'] = array('type' => PARAM_THEME, 'null' => NULL_NOT_ALLOWED,
                'default' => theme_config::DEFAULT_THEME, 'choices' => array_merge(array('' => ''), get_list_of_themes()));
        $fields['timezone'] = array('type' => PARAM_TIMEZONE, 'null' => NULL_NOT_ALLOWED,
                'default' => core_date::get_server_timezone());         $fields['firstaccess'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED);
        $fields['lastaccess'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED);
        $fields['lastlogin'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED);
        $fields['currentlogin'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED);
        $fields['lastip'] = array('type' => PARAM_NOTAGS, 'null' => NULL_NOT_ALLOWED);
        $fields['secret'] = array('type' => PARAM_RAW, 'null' => NULL_NOT_ALLOWED);
        $fields['picture'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED);
        $fields['url'] = array('type' => PARAM_URL, 'null' => NULL_NOT_ALLOWED);
        $fields['description'] = array('type' => PARAM_RAW, 'null' => NULL_ALLOWED);
        $fields['descriptionformat'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED);
        $fields['mailformat'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED,
                'default' => $CFG->defaultpreference_mailformat);
        $fields['maildigest'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED,
                'default' => $CFG->defaultpreference_maildigest);
        $fields['maildisplay'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED,
                'default' => $CFG->defaultpreference_maildisplay);
        $fields['autosubscribe'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED,
                'default' => $CFG->defaultpreference_autosubscribe);
        $fields['trackforums'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED,
                'default' => $CFG->defaultpreference_trackforums);
        $fields['timecreated'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED);
        $fields['timemodified'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED);
        $fields['trustbitmask'] = array('type' => PARAM_INT, 'null' => NULL_NOT_ALLOWED);
        $fields['imagealt'] = array('type' => PARAM_TEXT, 'null' => NULL_ALLOWED);
        $fields['lastnamephonetic'] = array('type' => PARAM_NOTAGS, 'null' => NULL_ALLOWED);
        $fields['firstnamephonetic'] = array('type' => PARAM_NOTAGS, 'null' => NULL_ALLOWED);
        $fields['middlename'] = array('type' => PARAM_NOTAGS, 'null' => NULL_ALLOWED);
        $fields['alternatename'] = array('type' => PARAM_NOTAGS, 'null' => NULL_ALLOWED);

        self::$propertiescache = $fields;
    }

    
    public static function get_property_definition($property) {

        self::fill_properties_cache();

        if (!array_key_exists($property, self::$propertiescache)) {
            throw new coding_exception('Invalid property requested.');
        }

        return self::$propertiescache[$property];
    }

    
    public static function validate($data) {
                self::fill_properties_cache();

        foreach ($data as $property => $value) {
            try {
                if (isset(self::$propertiescache[$property])) {
                    validate_param($value, self::$propertiescache[$property]['type'], self::$propertiescache[$property]['null']);
                }
                                if (!empty(self::$propertiescache[$property]['choices']) &&
                        !isset(self::$propertiescache[$property]['choices'][$value])) {
                    throw new invalid_parameter_exception($value);
                }
            } catch (invalid_parameter_exception $e) {
                $errors[$property] = $e->getMessage();
            }
        }

        return empty($errors) ? true : $errors;
    }

    
    public static function reset_caches() {
        self::$propertiescache = null;
    }

    
    public static function clean_data($user) {
        if (empty($user)) {
            return $user;
        }

        foreach ($user as $field => $value) {
                        try {
                $user->$field = core_user::clean_field($value, $field);
            } catch (coding_exception $e) {
                debugging("The property '$field' could not be cleaned.", DEBUG_DEVELOPER);
            }
        }

        return $user;
    }

    
    public static function clean_field($data, $field) {
        if (empty($data) || empty($field)) {
            return $data;
        }

        try {
            $type = core_user::get_property_type($field);

            if (isset(self::$propertiescache[$field]['choices'])) {
                if (!array_key_exists($data, self::$propertiescache[$field]['choices'])) {
                    if (isset(self::$propertiescache[$field]['default'])) {
                        $data = self::$propertiescache[$field]['default'];
                    } else {
                        $data = '';
                    }
                } else {
                    return $data;
                }
            } else {
                $data = clean_param($data, $type);
            }
        } catch (coding_exception $e) {
            debugging("The property '$field' could not be cleaned.", DEBUG_DEVELOPER);
        }

        return $data;
    }

    
    public static function get_property_type($property) {

        self::fill_properties_cache();

        if (!array_key_exists($property, self::$propertiescache)) {
            throw new coding_exception('Invalid property requested: ' . $property);
        }

        return self::$propertiescache[$property]['type'];
    }

    
    public static function get_property_null($property) {

        self::fill_properties_cache();

        if (!array_key_exists($property, self::$propertiescache)) {
            throw new coding_exception('Invalid property requested: ' . $property);
        }

        return self::$propertiescache[$property]['null'];
    }

    
    public static function get_property_choices($property) {

        self::fill_properties_cache();

        if (!array_key_exists($property, self::$propertiescache) && !array_key_exists('choices',
                self::$propertiescache[$property])) {

            throw new coding_exception('Invalid property requested, or the property does not has a list of choices.');
        }

        return self::$propertiescache[$property]['choices'];
    }

    
    public static function get_property_default($property) {

        self::fill_properties_cache();

        if (!array_key_exists($property, self::$propertiescache) || !isset(self::$propertiescache[$property]['default'])) {
            throw new coding_exception('Invalid property requested, or the property does not has a default value.');
        }

        return self::$propertiescache[$property]['default'];
    }
}
