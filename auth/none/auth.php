<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');


class auth_plugin_none extends auth_plugin_base {

    
    public function __construct() {
        $this->authtype = 'none';
        $this->config = get_config('auth/none');
    }

    
    public function auth_plugin_none() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    
    function user_login ($username, $password) {
        global $CFG, $DB;
        if ($user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id))) {
            return validate_internal_user_password($user, $password);
        }
        return true;
    }

    
    function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
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
        include "config.html";
    }

    
    function process_config($config) {
        return true;
    }

}


