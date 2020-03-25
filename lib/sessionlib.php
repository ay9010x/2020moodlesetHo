<?php



defined('MOODLE_INTERNAL') || die();



function sesskey() {
        if (empty($_SESSION['USER']->sesskey)) {
        if (!isset($_SESSION['USER'])) {
                                                return false;
        }
        $_SESSION['USER']->sesskey = random_string(10);
    }

    return $_SESSION['USER']->sesskey;
}



function confirm_sesskey($sesskey=NULL) {
    global $USER;

    if (!empty($USER->ignoresesskey)) {
        return true;
    }

    if (empty($sesskey)) {
        $sesskey = required_param('sesskey', PARAM_RAW);      }

    return (sesskey() === $sesskey);
}


function require_sesskey() {
    if (!confirm_sesskey()) {
        print_error('invalidsesskey');
    }
}


function is_moodle_cookie_secure() {
    global $CFG;

    if (!isset($CFG->cookiesecure)) {
        return false;
    }
    if (!empty($CFG->loginhttps)) {
        return false;
    }
    if (!is_https() and empty($CFG->sslproxy)) {
        return false;
    }
    return !empty($CFG->cookiesecure);
}


function set_moodle_cookie($username) {
    global $CFG;

    if (NO_MOODLE_COOKIES) {
        return;
    }

    if (empty($CFG->rememberusername)) {
                $username = '';
    }

    if ($username === 'guest') {
                return;
    }

    $cookiename = 'MOODLEID1_'.$CFG->sessioncookie;

    $cookiesecure = is_moodle_cookie_secure();

        setcookie($cookiename, '', time() - HOURSECS, $CFG->sessioncookiepath, $CFG->sessioncookiedomain, $cookiesecure, $CFG->cookiehttponly);

    if ($username !== '') {
                setcookie($cookiename, rc4encrypt($username), time() + (DAYSECS * 60), $CFG->sessioncookiepath, $CFG->sessioncookiedomain, $cookiesecure, $CFG->cookiehttponly);
    }
}


function get_moodle_cookie() {
    global $CFG;

    if (NO_MOODLE_COOKIES) {
        return '';
    }

    if (empty($CFG->rememberusername)) {
        return '';
    }

    $cookiename = 'MOODLEID1_'.$CFG->sessioncookie;

    if (empty($_COOKIE[$cookiename])) {
        return '';
    } else {
        $username = rc4decrypt($_COOKIE[$cookiename]);
        if ($username === 'guest' or $username === 'nobody') {
                        $username = '';
        }
        return $username;
    }
}


function cron_setup_user($user = NULL, $course = NULL) {
    global $CFG, $SITE, $PAGE;

    if (!CLI_SCRIPT) {
        throw new coding_exception('Function cron_setup_user() cannot be used in normal requests!');
    }

    static $cronuser    = NULL;
    static $cronsession = NULL;

    if ($user === 'reset') {
        $cronuser = null;
        $cronsession = null;
        \core\session\manager::init_empty_session();
        return;
    }

    if (empty($cronuser)) {
                $cronuser = get_admin();
        $cronuser->timezone = $CFG->timezone;
        $cronuser->lang     = '';
        $cronuser->theme    = '';
        unset($cronuser->description);

        $cronsession = new stdClass();
    }

    if (!$user) {
                \core\session\manager::init_empty_session();
        \core\session\manager::set_user($cronuser);
        $GLOBALS['SESSION'] = $cronsession;

    } else {
                if ($GLOBALS['USER']->id != $user->id) {
            \core\session\manager::init_empty_session();
            \core\session\manager::set_user($user);
        }
    }

            $PAGE = new moodle_page();
    if ($course) {
        $PAGE->set_course($course);
    } else {
        $PAGE->set_course($SITE);
    }

    
}
