<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');


class auth_plugin_pam extends auth_plugin_base {

    
    var $lasterror;

    
    public function __construct() {
        $this->authtype = 'pam';
        $this->config = get_config('auth/pam');
        $this->errormessage = '';
    }

    
    public function auth_plugin_pam() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    
    function user_login ($username, $password) {
                $errormessage = str_repeat(' ', 2048);

                
                        if (pam_auth($username, $password)) {
            return true;
        }
        else {
            $this->lasterror = $errormessage;
            return false;
        }
    }

    function prevent_local_passwords() {
        return true;
    }

    
    function is_internal() {
        return false;
    }

    
    function can_change_password() {
        return false;
    }

    
    function config_form($config, $err, $user_fields) {
        include "config.html";
    }

    
    function process_config($config) {
        return true;
    }

}


