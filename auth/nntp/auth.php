<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');


class auth_plugin_nntp extends auth_plugin_base {

    
    public function __construct() {
        $this->authtype = 'nntp';
        $this->config = get_config('auth/nntp');
    }

    
    public function auth_plugin_nntp() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    
    function user_login ($username, $password) {
        if (! function_exists('imap_open')) {
            print_error('auth_nntpnotinstalled','auth_nntp');
            exit;
        }

        global $CFG;

                $hosts = explode(';', $this->config->host);
        foreach ($hosts as $host) {
            $host = '{' . trim($host) . ':' . $this->config->port . '/nntp}';

            error_reporting(0);
            $connection = imap_open($host, $username, $password, OP_HALFOPEN);
            error_reporting($CFG->debug);

            if ($connection) {
                imap_close($connection);
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

    
    function config_form($config, $err, $user_fields) {
        include "config.html";
    }

    
    function process_config($config) {
                if (!isset ($config->host)) {
            $config->host = '127.0.0.1';
        }
        if (!isset ($config->port)) {
            $config->port = '119';
        }
        if (!isset($config->changepasswordurl)) {
            $config->changepasswordurl = '';
        }

                set_config('host', $config->host, 'auth/nntp');
        set_config('port', $config->port, 'auth/nntp');
        set_config('changepasswordurl', $config->changepasswordurl, 'auth/nntp');

        return true;
    }

}


