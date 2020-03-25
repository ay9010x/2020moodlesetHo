<?php



namespace core\session;

defined('MOODLE_INTERNAL') || die();


class manager {
    
    protected static $handler;

    
    protected static $sessionactive = null;

    
    public static function start() {
        global $CFG, $DB;

        if (isset(self::$sessionactive)) {
            debugging('Session was already started!', DEBUG_DEVELOPER);
            return;
        }

        self::load_handler();

                        if (empty($DB) or empty($CFG->version) or !defined('NO_MOODLE_COOKIES') or NO_MOODLE_COOKIES or CLI_SCRIPT) {
            self::$sessionactive = false;
            self::init_empty_session();
            return;
        }

        try {
            self::$handler->init();
            self::prepare_cookies();
            $isnewsession = empty($_COOKIE[session_name()]);

            if (!self::$handler->start()) {
                                throw new \core\session\exception(get_string('servererror'));
            }

            self::initialise_user_session($isnewsession);
            self::check_security();

                                                                        $GLOBALS['USER'] = $_SESSION['USER'];
            $_SESSION['USER'] =& $GLOBALS['USER'];
            $GLOBALS['SESSION'] = $_SESSION['SESSION'];
            $_SESSION['SESSION'] =& $GLOBALS['SESSION'];

        } catch (\Exception $ex) {
            self::init_empty_session();
            self::$sessionactive = false;
            throw $ex;
        }

        self::$sessionactive = true;
    }

    
    public static function get_performance_info() {
        if (!session_id()) {
            return array();
        }

        self::load_handler();
        $size = display_size(strlen(session_encode()));
        $handler = get_class(self::$handler);

        $info = array();
        $info['size'] = $size;
        $info['html'] = "<span class=\"sessionsize\">Session ($handler): $size</span> ";
        $info['txt'] = "Session ($handler): $size ";

        return $info;
    }

    
    protected static function load_handler() {
        global $CFG, $DB;

        if (self::$handler) {
            return;
        }

                if (PHPUNIT_TEST) {
            $class = '\core\session\file';

        } else if (!empty($CFG->session_handler_class)) {
            $class = $CFG->session_handler_class;

        } else if (!empty($CFG->dbsessions) and $DB->session_lock_supported()) {
            $class = '\core\session\database';

        } else {
            $class = '\core\session\file';
        }
        self::$handler = new $class();
    }

    
    public static function init_empty_session() {
        global $CFG;

        if (isset($GLOBALS['SESSION']->notifications)) {
                        $notifications = $GLOBALS['SESSION']->notifications;
        }
        $GLOBALS['SESSION'] = new \stdClass();

        $GLOBALS['USER'] = new \stdClass();
        $GLOBALS['USER']->id = 0;

        if (!empty($notifications)) {
                        $GLOBALS['SESSION']->notifications = $notifications;
        }
        if (isset($CFG->mnet_localhost_id)) {
            $GLOBALS['USER']->mnethostid = $CFG->mnet_localhost_id;
        } else {
                        $GLOBALS['USER']->mnethostid = 1;
        }

                $_SESSION = array();
        $_SESSION['USER'] =& $GLOBALS['USER'];
        $_SESSION['SESSION'] =& $GLOBALS['SESSION'];
    }

    
    protected static function prepare_cookies() {
        global $CFG;

        $cookiesecure = is_moodle_cookie_secure();

        if (!isset($CFG->cookiehttponly)) {
            $CFG->cookiehttponly = 0;
        }

                if (!isset($CFG->sessioncookie)) {
            $CFG->sessioncookie = '';
        }
        $sessionname = 'MoodleSession'.$CFG->sessioncookie;

                if (!isset($CFG->sessioncookiedomain)) {
            $CFG->sessioncookiedomain = '';
        } else if ($CFG->sessioncookiedomain !== '') {
            $host = parse_url($CFG->wwwroot, PHP_URL_HOST);
            if ($CFG->sessioncookiedomain !== $host) {
                if (substr($CFG->sessioncookiedomain, 0, 1) === '.') {
                    if (!preg_match('|^.*'.preg_quote($CFG->sessioncookiedomain, '|').'$|', $host)) {
                                                $CFG->sessioncookiedomain = '';
                    }
                } else {
                    if (!preg_match('|^.*\.'.preg_quote($CFG->sessioncookiedomain, '|').'$|', $host)) {
                                                $CFG->sessioncookiedomain = '';
                    }
                }
            }
        }

                if (!isset($CFG->sessioncookiepath)) {
            $CFG->sessioncookiepath = '';
        }
        if ($CFG->sessioncookiepath !== '/') {
            $path = parse_url($CFG->wwwroot, PHP_URL_PATH).'/';
            if ($CFG->sessioncookiepath === '') {
                $CFG->sessioncookiepath = $path;
            } else {
                if (strpos($path, $CFG->sessioncookiepath) !== 0 or substr($CFG->sessioncookiepath, -1) !== '/') {
                    $CFG->sessioncookiepath = $path;
                }
            }
        }

                        unset($GLOBALS[$sessionname]);
        unset($_GET[$sessionname]);
        unset($_POST[$sessionname]);
        unset($_REQUEST[$sessionname]);

                if (!empty($_COOKIE[$sessionname]) && $_COOKIE[$sessionname] == "deleted") {
            unset($_COOKIE[$sessionname]);
        }

                session_name($sessionname);
        session_set_cookie_params(0, $CFG->sessioncookiepath, $CFG->sessioncookiedomain, $cookiesecure, $CFG->cookiehttponly);
        ini_set('session.use_trans_sid', '0');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.hash_function', '0');                ini_set('session.use_strict_mode', '0');              ini_set('session.serialize_handler', 'php');  
                ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 1000);
        ini_set('session.gc_maxlifetime', 60*60*24*4);
    }

    
    protected static function initialise_user_session($newsid) {
        global $CFG, $DB;

        $sid = session_id();
        if (!$sid) {
                        error_log('Missing session ID, session not started!');
            self::init_empty_session();
            return;
        }

        if (!$record = $DB->get_record('sessions', array('sid'=>$sid), 'id, sid, state, userid, lastip, timecreated, timemodified')) {
            if (!$newsid) {
                if (!empty($_SESSION['USER']->id)) {
                                        error_log("Cannot find session record $sid for user ".$_SESSION['USER']->id.", creating new session.");
                }
                                session_regenerate_id(true);
            }
            $_SESSION = array();
        }
        unset($sid);

        if (isset($_SESSION['USER']->id)) {
            if (!empty($_SESSION['USER']->realuser)) {
                $userid = $_SESSION['USER']->realuser;
            } else {
                $userid = $_SESSION['USER']->id;
            }

                        $maxlifetime = $CFG->sessiontimeout;
            $timeout = false;
            if (isguestuser($userid) or empty($userid)) {
                                $timeout = false;

            } else if ($record->timemodified < time() - $maxlifetime) {
                $timeout = true;
                $authsequence = get_enabled_auth_plugins();                 foreach ($authsequence as $authname) {
                    $authplugin = get_auth_plugin($authname);
                    if ($authplugin->ignore_timeout_hook($_SESSION['USER'], $record->sid, $record->timecreated, $record->timemodified)) {
                        $timeout = false;
                        break;
                    }
                }
            }

            if ($timeout) {
                session_regenerate_id(true);
                $_SESSION = array();
                $DB->delete_records('sessions', array('id'=>$record->id));

            } else {
                
                $update = new \stdClass();
                $updated = false;

                if ($record->userid != $userid) {
                    $update->userid = $record->userid = $userid;
                    $updated = true;
                }

                $ip = getremoteaddr();
                if ($record->lastip != $ip) {
                    $update->lastip = $record->lastip = $ip;
                    $updated = true;
                }

                $updatefreq = empty($CFG->session_update_timemodified_frequency) ? 20 : $CFG->session_update_timemodified_frequency;

                if ($record->timemodified == $record->timecreated) {
                                        $update->timemodified = $record->timemodified = time();
                    $updated = true;

                } else if ($record->timemodified < time() - $updatefreq) {
                                        $update->timemodified = $record->timemodified = time();
                    $updated = true;
                }

                if ($updated) {
                    $update->id = $record->id;
                    $DB->update_record('sessions', $update);
                }

                return;
            }
        } else {
            if ($record) {
                                session_regenerate_id(true);
                $_SESSION = array();
                $DB->delete_records('sessions', array('id'=>$record->id));
            }
        }
        unset($record);

        $timedout = false;
        if (!isset($_SESSION['SESSION'])) {
            $_SESSION['SESSION'] = new \stdClass();
            if (!$newsid) {
                $timedout = true;
            }
        }

        $user = null;

        if (!empty($CFG->opentogoogle)) {
            if (\core_useragent::is_web_crawler()) {
                $user = guest_user();
            }
            $referer = get_local_referer(false);
            if (!empty($CFG->guestloginbutton) and !$user and !empty($referer)) {
                                if (strpos($referer, 'google') !== false ) {
                    $user = guest_user();
                } else if (strpos($referer, 'altavista') !== false ) {
                    $user = guest_user();
                }
            }
        }

                if ($user) {
            self::set_user($user);
            self::add_session_record($user->id);
        } else {
            self::init_empty_session();
            self::add_session_record(0);
        }

        if ($timedout) {
            $_SESSION['SESSION']->has_timed_out = true;
        }
    }

    
    protected static function add_session_record($userid) {
        global $DB;
        $record = new \stdClass();
        $record->state       = 0;
        $record->sid         = session_id();
        $record->sessdata    = null;
        $record->userid      = $userid;
        $record->timecreated = $record->timemodified = time();
        $record->firstip     = $record->lastip = getremoteaddr();

        $record->id = $DB->insert_record('sessions', $record);

        return $record;
    }

    
    protected static function check_security() {
        global $CFG;

        if (!empty($_SESSION['USER']->id) and !empty($CFG->tracksessionip)) {
                        $remoteaddr = getremoteaddr();

            if (empty($_SESSION['USER']->sessionip)) {
                $_SESSION['USER']->sessionip = $remoteaddr;
            }

            if ($_SESSION['USER']->sessionip != $remoteaddr) {
                                self::terminate_current();
                throw new exception('sessionipnomatch2', 'error');
            }
        }
    }

    
    public static function login_user(\stdClass $user) {
        global $DB;

                
        $sid = session_id();
        session_regenerate_id(true);
        $DB->delete_records('sessions', array('sid'=>$sid));
        self::add_session_record($user->id);

                enrol_check_plugins($user);

                self::set_user($user);
    }

    
    public static function terminate_current() {
        global $DB;

        if (!self::$sessionactive) {
            self::init_empty_session();
            self::$sessionactive = false;
            return;
        }

        try {
            $DB->delete_records('external_tokens', array('sid'=>session_id(), 'tokentype'=>EXTERNAL_TOKEN_EMBEDDED));
        } catch (\Exception $ignored) {
                    }

                $file = null;
        $line = null;
        if (headers_sent($file, $line)) {
            error_log('Cannot terminate session properly - headers were already sent in file: '.$file.' on line '.$line);
        }

                $sid = session_id();
        session_regenerate_id(true);
        $DB->delete_records('sessions', array('sid'=>$sid));
        self::init_empty_session();
        self::add_session_record($_SESSION['USER']->id);         session_write_close();
        self::$sessionactive = false;
    }

    
    public static function write_close() {
        if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
                                    if (self::$sessionactive && session_id()) {
                                                session_write_close();
            } else {
                                                @session_abort();
            }
        } else {
                                    if (self::$sessionactive || session_id()) {
                session_write_close();
            }
        }
        self::$sessionactive = false;
    }

    
    public static function session_exists($sid) {
        global $DB, $CFG;

        if (empty($CFG->version)) {
                        return false;
        }

                if (!$record = $DB->get_record('sessions', array('sid' => $sid), 'id, userid, timemodified')) {
            return false;
        }

        if (empty($record->userid) or isguestuser($record->userid)) {
                    } else if ($record->timemodified < time() - $CFG->sessiontimeout) {
            return false;
        }

                self::load_handler();
        return self::$handler->session_exists($sid);
    }

    
    public static function touch_session($sid) {
        global $DB;

        
        $sql = "UPDATE {sessions} SET timemodified = :now WHERE sid = :sid";
        $DB->execute($sql, array('now'=>time(), 'sid'=>$sid));
    }

    
    public static function kill_all_sessions() {
        global $DB;

        self::terminate_current();

        self::load_handler();
        self::$handler->kill_all_sessions();

        try {
            $DB->delete_records('sessions');
        } catch (\dml_exception $ignored) {
                    }
    }

    
    public static function kill_session($sid) {
        global $DB;

        self::load_handler();

        if ($sid === session_id()) {
            self::write_close();
        }

        self::$handler->kill_session($sid);

        $DB->delete_records('sessions', array('sid'=>$sid));
    }

    
    public static function kill_user_sessions($userid, $keepsid = null) {
        global $DB;

        $sessions = $DB->get_records('sessions', array('userid'=>$userid), 'id DESC', 'id, sid');
        foreach ($sessions as $session) {
            if ($keepsid and $keepsid === $session->sid) {
                continue;
            }
            self::kill_session($session->sid);
        }
    }

    
    public static function apply_concurrent_login_limit($userid, $sid = null) {
        global $CFG, $DB;

                
        if (isguestuser($userid) or empty($userid)) {
                        return;
        }

        if (empty($CFG->limitconcurrentlogins) or $CFG->limitconcurrentlogins < 0) {
            return;
        }

        $count = $DB->count_records('sessions', array('userid' => $userid));

        if ($count <= $CFG->limitconcurrentlogins) {
            return;
        }

        $i = 0;
        $select = "userid = :userid";
        $params = array('userid' => $userid);
        if ($sid) {
            if ($DB->record_exists('sessions', array('sid' => $sid, 'userid' => $userid))) {
                $select .= " AND sid <> :sid";
                $params['sid'] = $sid;
                $i = 1;
            }
        }

        $sessions = $DB->get_records_select('sessions', $select, $params, 'timecreated DESC', 'id, sid');
        foreach ($sessions as $session) {
            $i++;
            if ($i <= $CFG->limitconcurrentlogins) {
                continue;
            }
            self::kill_session($session->sid);
        }
    }

    
    public static function set_user(\stdClass $user) {
        $GLOBALS['USER'] = $user;
        unset($GLOBALS['USER']->description);         unset($GLOBALS['USER']->password);            if (isset($GLOBALS['USER']->lang)) {
                        $GLOBALS['USER']->lang = clean_param($GLOBALS['USER']->lang, PARAM_LANG);
        }

                $_SESSION['USER'] =& $GLOBALS['USER'];

                sesskey();
    }

    
    public static function gc() {
        global $CFG, $DB;

                \core_php_time_limit::raise();

        $maxlifetime = $CFG->sessiontimeout;

        try {
                        $rs = $DB->get_recordset_select('sessions', "userid IN (SELECT id FROM {user} WHERE deleted <> 0 OR suspended <> 0)", array(), 'id DESC', 'id, sid');
            foreach ($rs as $session) {
                self::kill_session($session->sid);
            }
            $rs->close();

                        $auth_sequence = get_enabled_auth_plugins(true);
            $auth_sequence = array_flip($auth_sequence);
            unset($auth_sequence['nologin']);             $auth_sequence = array_flip($auth_sequence);

            list($notplugins, $params) = $DB->get_in_or_equal($auth_sequence, SQL_PARAMS_QM, '', false);
            $rs = $DB->get_recordset_select('sessions', "userid IN (SELECT id FROM {user} WHERE auth $notplugins)", $params, 'id DESC', 'id, sid');
            foreach ($rs as $session) {
                self::kill_session($session->sid);
            }
            $rs->close();

                        $sql = "SELECT u.*, s.sid, s.timecreated AS s_timecreated, s.timemodified AS s_timemodified
                      FROM {user} u
                      JOIN {sessions} s ON s.userid = u.id
                     WHERE s.timemodified < :purgebefore AND u.id <> :guestid";
            $params = array('purgebefore' => (time() - $maxlifetime), 'guestid'=>$CFG->siteguest);

            $authplugins = array();
            foreach ($auth_sequence as $authname) {
                $authplugins[$authname] = get_auth_plugin($authname);
            }
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $user) {
                foreach ($authplugins as $authplugin) {
                    
                    if ($authplugin->ignore_timeout_hook($user, $user->sid, $user->s_timecreated, $user->s_timemodified)) {
                        continue 2;
                    }
                }
                self::kill_session($user->sid);
            }
            $rs->close();

                        $params = array('purgebefore' => (time() - ($maxlifetime * 5)), 'guestid'=>$CFG->siteguest);
            $rs = $DB->get_recordset_select('sessions', 'userid = :guestid AND timemodified < :purgebefore', $params, 'id DESC', 'id, sid');
            foreach ($rs as $session) {
                self::kill_session($session->sid);
            }
            $rs->close();

                        $params = array('purgebefore' => (time() - $maxlifetime));
            $rs = $DB->get_recordset_select('sessions', 'userid = 0 AND timemodified < :purgebefore', $params, 'id DESC', 'id, sid');
            foreach ($rs as $session) {
                self::kill_session($session->sid);
            }
            $rs->close();

                        $params = array('purgebefore' => (time() - 60*3));
            $rs = $DB->get_recordset_select('sessions', 'userid = 0 AND timemodified = timecreated AND timemodified < :purgebefore', $params, 'id ASC', 'id, sid');
            foreach ($rs as $session) {
                self::kill_session($session->sid);
            }
            $rs->close();

        } catch (\Exception $ex) {
            debugging('Error gc-ing sessions: '.$ex->getMessage(), DEBUG_NORMAL, $ex->getTrace());
        }
    }

    
    public static function is_loggedinas() {
        return !empty($GLOBALS['USER']->realuser);
    }

    
    public static function get_realuser() {
        if (self::is_loggedinas()) {
            return $_SESSION['REALUSER'];
        } else {
            return $GLOBALS['USER'];
        }
    }

    
    public static function loginas($userid, \context $context) {
        global $USER;

        if (self::is_loggedinas()) {
            return;
        }

                $_SESSION = array();
        $_SESSION['REALSESSION'] = clone($GLOBALS['SESSION']);
        $GLOBALS['SESSION'] = new \stdClass();
        $_SESSION['SESSION'] =& $GLOBALS['SESSION'];

                $_SESSION['REALUSER'] = clone($GLOBALS['USER']);
        $user = get_complete_user_data('id', $userid);
        $user->realuser       = $_SESSION['REALUSER']->id;
        $user->loginascontext = $context;

                enrol_check_plugins($user);

                $event = \core\event\user_loggedinas::create(
            array(
                'objectid' => $USER->id,
                'context' => $context,
                'relateduserid' => $userid,
                'other' => array(
                    'originalusername' => fullname($USER, true),
                    'loggedinasusername' => fullname($user, true)
                )
            )
        );
                \core\session\manager::set_user($user);
        $event->trigger();
    }

    
    public static function keepalive($identifier = 'sessionerroruser', $component = 'error', $frequency = null) {
        global $CFG, $PAGE;

        if ($frequency) {
            if ($frequency > $CFG->sessiontimeout) {
                                throw new \coding_exception('Keepalive frequency is longer than the session lifespan.');
            }
        } else {
                        $frequency = $CFG->sessiontimeout / 3;
        }

                $sessionkeepaliveurl = new \moodle_url('/lib/sessionkeepalive_ajax.php');
        $PAGE->requires->string_for_js($identifier, $component);
        $PAGE->requires->yui_module('moodle-core-checknet', 'M.core.checknet.init', array(array(
                        'frequency' => $frequency * 1000,
            'message' => array($identifier, $component),
            'uri' => $sessionkeepaliveurl->out(),
        )));
    }

}
