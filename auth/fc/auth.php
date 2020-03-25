<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');

require_once 'fcFPP.php';


class auth_plugin_fc extends auth_plugin_base {

    
    public function __construct() {
        $this->authtype = 'fc';
        $this->config = get_config('auth/fc');
    }

    
    public function auth_plugin_fc() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    
    function user_login ($username, $password) {
        global $CFG;
        $retval = false;

                if (!$username or !$password) {
            return $retval;
        }

        $fpp = new fcFPP($this->config->host, $this->config->fppport);
        if ($fpp->open()) {
            if ($fpp->login($username, $password)) {
                $retval = true;
            }
        }
        $fpp->close();

        return $retval;
    }

    
    function get_userinfo($username) {
        

        $userinfo = array();

        $fpp = new fcFPP($this->config->host, $this->config->fppport);
        if ($fpp->open()) {
            if ($fpp->login($this->config->userid, $this->config->passwd)) {
                $userinfo['firstname']   = $fpp->getUserInfo($username,"1202");
                $userinfo['lastname']    = $fpp->getUserInfo($username,"1204");
                $userinfo['email']       = strtok($fpp->getUserInfo($username,"1252"),',');
                $userinfo['phone1']      = $fpp->getUserInfo($username,"1206");
                $userinfo['phone2']      = $fpp->getUserInfo($username,"1207");
                $userinfo['description'] = $fpp->getResume($username);
            }
        }
        $fpp->close();

        foreach($userinfo as $key => $value) {
            if (!$value) {
                unset($userinfo[$key]);
            }
        }

        return $userinfo;
    }

    
    function iscreator($username) {
        if (! $this->config->creators) {
            return null;
        }

        $fcgroups = array();

        $fpp = new fcFPP($this->config->host, $this->config->fppport);
        if ($fpp->open()) {
            if ($fpp->login($this->config->userid, $this->config->passwd)) {
                $fcgroups = $fpp->getGroups($username);
            }
        }
        $fpp->close();

        if ((! $fcgroups)) {
            return false;
        }

        $creators = explode(";", $this->config->creators);

        foreach($creators as $creator) {
            if (in_array($creator, $fcgroups)) {
                return true;
            }
        }

        return false;
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

    
    function sync_roles($user) {
        $iscreator = $this->iscreator($user->username);
        if ($iscreator === null) {
            return;         }

        if ($roles = get_archetype_roles('coursecreator')) {
            $creatorrole = array_shift($roles);                  $systemcontext = context_system::instance();

            if ($iscreator) {                 role_assign($creatorrole->id, $user->id, $systemcontext->id, 'auth_fc');
            } else {
                                role_unassign($creatorrole->id, $user->id, $systemcontext->id, 'auth_fc');
            }
        }
    }

    
    function config_form($config, $err, $user_fields) {
        include "config.html";
    }

    
    function process_config($config) {
                if (!isset($config->host)) {
            $config->host = "127.0.0.1";
        }
        if (!isset($config->fppport)) {
            $config->fppport = "3333";
        }
        if (!isset($config->userid)) {
            $config->userid = "fcMoodle";
        }
        if (!isset($config->passwd)) {
            $config->passwd = "";
        }
        if (!isset($config->creators)) {
            $config->creators = "";
        }
        if (!isset($config->changepasswordurl)) {
            $config->changepasswordurl = '';
        }

                set_config('host',      $config->host,     'auth/fc');
        set_config('fppport',   $config->fppport,  'auth/fc');
        set_config('userid',    $config->userid,   'auth/fc');
        set_config('passwd',    $config->passwd,   'auth/fc');
        set_config('creators',  $config->creators, 'auth/fc');
        set_config('changepasswordurl', $config->changepasswordurl, 'auth/fc');

        return true;
    }

}


