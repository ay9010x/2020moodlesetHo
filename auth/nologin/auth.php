<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');


class auth_plugin_nologin extends auth_plugin_base {


    
    public function __construct() {
        $this->authtype = 'nologin';
    }

    
    public function auth_plugin_nologin() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    
    function user_login($username, $password) {
        return false;
    }

    
    function user_update_password($user, $newpassword) {
        return false;
    }

    function prevent_local_passwords() {
                return false;
    }

    
    function is_internal() {
                return true;
    }

    
    function can_change_password() {
        return false;
    }

    
    function can_reset_password() {
        return false;
    }

    
    function can_be_manually_set() {
        return true;
    }
}


