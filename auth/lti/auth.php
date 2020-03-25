<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');


class auth_plugin_lti extends auth_plugin_base {

    
    public function __construct() {
        $this->authtype = 'lti';
    }

    
    public function user_login($username, $password) {
        return false;
    }
}
