<?php




defined('MOODLE_INTERNAL') || die();


define('AUTH_OK',     0);


define('AUTH_FAIL',   1);


define('AUTH_DENIED', 2);


define('AUTH_ERROR',  4);


define('AUTH_CONFIRM_FAIL', 0);
define('AUTH_CONFIRM_OK', 1);
define('AUTH_CONFIRM_ALREADY', 2);
define('AUTH_CONFIRM_ERROR', 3);

define('AUTH_REMOVEUSER_KEEP', 0);
define('AUTH_REMOVEUSER_SUSPEND', 1);
define('AUTH_REMOVEUSER_FULLDELETE', 2);


define('AUTH_LOGIN_OK', 0);


define('AUTH_LOGIN_NOUSER', 1);


define('AUTH_LOGIN_SUSPENDED', 2);


define('AUTH_LOGIN_FAILED', 3);


define('AUTH_LOGIN_LOCKOUT', 4);


define('AUTH_LOGIN_UNAUTHORISED', 5);


class auth_plugin_base {

    
    var $config;

    
    var $authtype;
    
    var $userfields = array(
        'firstname',
        'lastname',
        'email',
        'city',
        'country',
        'lang',
        'description',
        'url',
        'idnumber',
        'institution',
        'department',
        'phone1',
        'phone2',
        'address',
        'firstnamephonetic',
        'lastnamephonetic',
        'middlename',
        'alternatename'
    );

    
    var $customfields = null;

    
    function user_login($username, $password) {
        print_error('mustbeoveride', 'debug', '', 'user_login()' );
    }

    
    function can_change_password() {
                return false;
    }

    
    function change_password_url() {
                return null;
    }

    
    function can_edit_profile() {
                return true;
    }

    
    function edit_profile_url() {
                return null;
    }

    
    function is_internal() {
                return true;
    }

    
    public function is_configured() {
        return false;
    }

    
    function prevent_local_passwords() {
        return !$this->is_internal();
    }

    
    function is_synchronised_with_external() {
        return !$this->is_internal();
    }

    
    function user_update_password($user, $newpassword) {
                return true;
    }

    
    function user_update($olduser, $newuser) {
                return true;
    }

    
    function user_delete($olduser) {
                return;
    }

    
    function can_reset_password() {
                return false;
    }

    
    function can_signup() {
                return false;
    }

    
    function user_signup($user, $notify=true) {
                print_error('mustbeoveride', 'debug', '', 'user_signup()' );
    }

    
    function signup_form() {
        global $CFG;

        require_once($CFG->dirroot.'/login/signup_form.php');
        return new login_signup_form(null, null, 'post', '', array('autocomplete'=>'on'));
    }

    
    function can_confirm() {
                return false;
    }

    
    function user_confirm($username, $confirmsecret) {
                print_error('mustbeoveride', 'debug', '', 'user_confirm()' );
    }

    
    function user_exists($username) {
                return false;
    }

    
    function password_expire($username) {
        return 0;
    }
    
    function sync_roles($user) {
            }

    
    function get_userinfo($username) {
                return array();
    }

    
    function config_form($config, $err, $user_fields) {
            }

    
     function validate_form($form, &$err) {
            }

    
    function process_config($config) {
                return true;
    }

    
    function loginpage_hook() {
        global $frm;          global $user; 
            }

    
    function pre_loginpage_hook() {
                            }

    
    function user_authenticated_hook(&$user, $username, $password) {
            }

    
    function prelogout_hook() {
        global $USER; 
            }

    
    function logoutpage_hook() {
        global $USER;             global $redirect; 
            }

    
    function ignore_timeout_hook($user, $sid, $timecreated, $timemodified) {
        return false;
    }

    
    function get_title() {
        return get_string('pluginname', "auth_{$this->authtype}");
    }

    
    function get_description() {
        $authdescription = get_string("auth_{$this->authtype}description", "auth_{$this->authtype}");
        return $authdescription;
    }

    
    function is_captcha_enabled() {
        return false;
    }

    
    function can_be_manually_set() {
                return false;
    }

    
    function loginpage_idp_list($wantsurl) {
        return array();
    }

    
    public function get_custom_user_profile_fields() {
        global $DB;
                if (!is_null($this->customfields)) {
            return $this->customfields;
        }

        $this->customfields = array();
        if ($proffields = $DB->get_records('user_info_field')) {
            foreach ($proffields as $proffield) {
                $this->customfields[] = 'profile_field_'.$proffield->shortname;
            }
        }
        unset($proffields);

        return $this->customfields;
    }

    
    public function postlogout_hook($user) {
    }
}


function login_is_lockedout($user) {
    global $CFG;

    if ($user->mnethostid != $CFG->mnet_localhost_id) {
        return false;
    }
    if (isguestuser($user)) {
        return false;
    }

    if (empty($CFG->lockoutthreshold)) {
                return false;
    }

    if (get_user_preferences('login_lockout_ignored', 0, $user)) {
                return false;
    }

    $locked = get_user_preferences('login_lockout', 0, $user);
    if (!$locked) {
        return false;
    }

    if (empty($CFG->lockoutduration)) {
                return true;
    }

    if (time() - $locked < $CFG->lockoutduration) {
        return true;
    }

    login_unlock_account($user);

    return false;
}


function login_attempt_valid($user) {
    global $CFG;

    
    if ($user->mnethostid != $CFG->mnet_localhost_id) {
        return;
    }
    if (isguestuser($user)) {
        return;
    }

        login_unlock_account($user);
}


function login_attempt_failed($user) {
    global $CFG;

    if ($user->mnethostid != $CFG->mnet_localhost_id) {
        return;
    }
    if (isguestuser($user)) {
        return;
    }

    $count = get_user_preferences('login_failed_count', 0, $user);
    $last = get_user_preferences('login_failed_last', 0, $user);
    $sincescuccess = get_user_preferences('login_failed_count_since_success', $count, $user);
    $sincescuccess = $sincescuccess + 1;
    set_user_preference('login_failed_count_since_success', $sincescuccess, $user);

    if (empty($CFG->lockoutthreshold)) {
                        login_unlock_account($user);
        return;
    }

    if (!empty($CFG->lockoutwindow) and time() - $last > $CFG->lockoutwindow) {
        $count = 0;
    }

    $count = $count+1;

    set_user_preference('login_failed_count', $count, $user);
    set_user_preference('login_failed_last', time(), $user);

    if ($count >= $CFG->lockoutthreshold) {
        login_lock_account($user);
    }
}


function login_lock_account($user) {
    global $CFG;

    if ($user->mnethostid != $CFG->mnet_localhost_id) {
        return;
    }
    if (isguestuser($user)) {
        return;
    }

    if (get_user_preferences('login_lockout_ignored', 0, $user)) {
                return;
    }

    $alreadylockedout = get_user_preferences('login_lockout', 0, $user);

    set_user_preference('login_lockout', time(), $user);

    if ($alreadylockedout == 0) {
        $secret = random_string(15);
        set_user_preference('login_lockout_secret', $secret, $user);

        $oldforcelang = force_current_language($user->lang);

        $site = get_site();
        $supportuser = core_user::get_support_user();

        $data = new stdClass();
        $data->firstname = $user->firstname;
        $data->lastname  = $user->lastname;
        $data->username  = $user->username;
        $data->sitename  = format_string($site->fullname);
        $data->link      = $CFG->wwwroot.'/login/unlock_account.php?u='.$user->id.'&s='.$secret;
        $data->admin     = generate_email_signoff();

        $message = get_string('lockoutemailbody', 'admin', $data);
        $subject = get_string('lockoutemailsubject', 'admin', format_string($site->fullname));

        if ($message) {
                        email_to_user($user, $supportuser, $subject, $message);
        }

        force_current_language($oldforcelang);
    }
}


function login_unlock_account($user) {
    unset_user_preference('login_lockout', $user);
    unset_user_preference('login_failed_count', $user);
    unset_user_preference('login_failed_last', $user);

    }
