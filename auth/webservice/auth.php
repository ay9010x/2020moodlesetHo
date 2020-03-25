<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');


class auth_plugin_webservice extends auth_plugin_base {

    
    public function __construct() {
        $this->authtype = 'webservice';
        $this->config = get_config('auth/webservice');
    }

    
    public function auth_plugin_webservice() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    
    function user_login($username, $password) {
                return false;
    }

    
    function user_login_webservice($username, $password) {
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

    
    function is_internal() {
        return false;
    }

    
    function can_change_password() {
        return false;
    }

    
    function change_password_url() {
        return null;
    }

    
    function can_reset_password() {
        return false;
    }

    
    function config_form($config, $err, $user_fields) {
    }

    
    function process_config($config) {
        return true;
    }

   
    function user_confirm($username, $confirmsecret = null) {
        return AUTH_CONFIRM_ERROR;
    }

}
