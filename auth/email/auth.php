<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');


class auth_plugin_email extends auth_plugin_base {

    
    public function __construct() {
        $this->authtype = 'email';
        $this->config = get_config('auth/email');
    }

    
    public function auth_plugin_email() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    
    function user_login ($username, $password) {
        global $CFG, $DB;
        if ($user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id))) {
            return validate_internal_user_password($user, $password);
        }
        return false;
    }

    
    function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
                                return update_internal_user_password($user, $newpassword);
    }

    function can_signup() {
        return true;
    }

    
    function user_signup($user, $notify=true) {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/lib.php');
        require_once($CFG->dirroot.'/user/lib.php');

        $plainpassword = $user->password;
        $user->password = hash_internal_user_password($user->password);
        if (empty($user->calendartype)) {
            $user->calendartype = $CFG->calendartype;
        }

        $user->id = user_create_user($user, false, false);

        user_add_password_history($user->id, $plainpassword);

                profile_save_data($user);

                \core\event\user_created::create_from_userid($user->id)->trigger();

        if (! send_confirmation_email($user)) {
            print_error('auth_emailnoemail','auth_email');
        }

        if ($notify) {
            global $CFG, $PAGE, $OUTPUT;
            $emailconfirm = get_string('emailconfirm');
            $PAGE->navbar->add($emailconfirm);
            $PAGE->set_title($emailconfirm);
            $PAGE->set_heading($PAGE->course->fullname);
            echo $OUTPUT->header();
            notice(get_string('emailconfirmsent', '', $user->email), "$CFG->wwwroot/index.php");
        } else {
            return true;
        }
    }

    
    function can_confirm() {
        return true;
    }

    
    function user_confirm($username, $confirmsecret) {
        global $DB;
        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->auth != $this->authtype) {
                return AUTH_CONFIRM_ERROR;

            } else if ($user->secret == $confirmsecret && $user->confirmed) {
                return AUTH_CONFIRM_ALREADY;

            } else if ($user->secret == $confirmsecret) {                   $DB->set_field("user", "confirmed", 1, array("id"=>$user->id));
                return AUTH_CONFIRM_OK;
            }
        } else {
            return AUTH_CONFIRM_ERROR;
        }
    }

    function prevent_local_passwords() {
        return false;
    }

    
    function is_internal() {
        return true;
    }

    
    function can_change_password() {
        return true;
    }

    
    function change_password_url() {
        return null;     }

    
    function can_reset_password() {
        return true;
    }

    
    function can_be_manually_set() {
        return true;
    }

    
    function config_form($config, $err, $user_fields) {
        include "config.html";
    }

    
    function process_config($config) {
                if (!isset($config->recaptcha)) {
            $config->recaptcha = false;
        }

                set_config('recaptcha', $config->recaptcha, 'auth/email');
        return true;
    }

    
    function is_captcha_enabled() {
        return get_config("auth/{$this->authtype}", 'recaptcha');
    }

}


