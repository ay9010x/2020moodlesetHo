<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');


class auth_plugin_manual extends auth_plugin_base {

    
    const COMPONENT_NAME = 'auth_manual';
    const LEGACY_COMPONENT_NAME = 'auth/manual';

    
    public function __construct() {
        $this->authtype = 'manual';
        $config = get_config(self::COMPONENT_NAME);
        $legacyconfig = get_config(self::LEGACY_COMPONENT_NAME);
        $this->config = (object)array_merge((array)$legacyconfig, (array)$config);
    }

    
    public function auth_plugin_manual() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    
    function user_login($username, $password) {
        global $CFG, $DB, $USER;
        if (!$user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id))) {
            return false;
        }
        if (!validate_internal_user_password($user, $password)) {
            return false;
        }
        if ($password === 'changeme') {
                                                set_user_preference('auth_forcepasswordchange', true, $user->id);
        }
        return true;
    }

    
    function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        set_user_preference('auth_manual_passwordupdatetime', time(), $user->id);
                                return update_internal_user_password($user, $newpassword);
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
        return null;
    }

    
    function can_reset_password() {
        return true;
    }

    
    function can_be_manually_set() {
        return true;
    }

    
    function config_form($config, $err, $user_fields) {
        include 'config.html';
    }

    
    public function password_expire($username) {
        $result = 0;

        if (!empty($this->config->expirationtime)) {
            $user = core_user::get_user_by_username($username, 'id,timecreated');
            $lastpasswordupdatetime = get_user_preferences('auth_manual_passwordupdatetime', $user->timecreated, $user->id);
            $expiretime = $lastpasswordupdatetime + $this->config->expirationtime * DAYSECS;
            $now = time();
            $result = ($expiretime - $now) / DAYSECS;
            if ($expiretime > $now) {
                $result = ceil($result);
            } else {
                $result = floor($result);
            }
        }

        return $result;
    }

    
    function process_config($config) {
                if (!isset($config->expiration)) {
            $config->expiration = '';
        }
        if (!isset($config->expiration_warning)) {
            $config->expiration_warning = '';
        }
        if (!isset($config->expirationtime)) {
            $config->expirationtime = '';
        }

                set_config('expiration', $config->expiration, self::COMPONENT_NAME);
        set_config('expiration_warning', $config->expiration_warning, self::COMPONENT_NAME);
        set_config('expirationtime', $config->expirationtime, self::COMPONENT_NAME);
        return true;
    }

   
    function user_confirm($username, $confirmsecret = null) {
        global $DB;

        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->confirmed) {
                return AUTH_CONFIRM_ALREADY;
            } else {
                $DB->set_field("user", "confirmed", 1, array("id"=>$user->id));
                return AUTH_CONFIRM_OK;
            }
        } else  {
            return AUTH_CONFIRM_ERROR;
        }
    }

}


