<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');


class auth_plugin_pop3 extends auth_plugin_base {

    
    public function __construct() {
        $this->authtype = 'pop3';
        $this->config = get_config('auth/pop3');
    }

    
    public function auth_plugin_pop3() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    
    function user_login($username, $password) {
        if (! function_exists('imap_open')) {
            print_error('auth_pop3notinstalled','auth_pop3');
            exit;
        }

        global $CFG;
        $hosts = explode(';', $this->config->host);           foreach ($hosts as $host) {                             $host = trim($host);

                        if (substr($host, -1) == '/') {
                $host = substr($host, 0, strlen($host) - 1);
            }

            switch ($this->config->type) {
                case 'pop3':
                    $host = '{'.$host.":{$this->config->port}/pop3}{$this->config->mailbox}";
                break;

                case 'pop3notls':
                    $host = '{'.$host.":{$this->config->port}/pop3/notls}{$this->config->mailbox}";
                break;

                case 'pop3cert':
                    $host = '{'.$host.":{$this->config->port}/pop3/ssl/novalidate-cert}{$this->config->mailbox}";
                break;
            }

            error_reporting(0);
            $connection = imap_open($host, $username, $password);
            error_reporting($CFG->debug);

            if ($connection) {
                imap_close($connection);
                return true;
            }
        }
        return false;      }

    function prevent_local_passwords() {
        return true;
    }

    
    function is_internal() {
        return false;
    }

    
    function can_change_password() {
        return !empty($this->config->changepasswordurl);
    }

    
    function change_password_url() {
        if (!empty($this->config->changepasswordurl)) {
            return new moodle_url($this->config->changepasswordurl);
        } else {
            return null;
        }
    }

    
    function config_form($config, $err, $user_fields) {
        global $OUTPUT;

        include "config.html";
    }

    
    function process_config($config) {
                if (!isset ($config->host)) {
            $config->host = '127.0.0.1';
        }
        if (!isset ($config->type)) {
            $config->type = 'pop3notls';
        }
        if (!isset ($config->port)) {
            $config->port = '143';
        }
        if (!isset ($config->mailbox)) {
            $config->mailbox = 'INBOX';
        }
        if (!isset($config->changepasswordurl)) {
            $config->changepasswordurl = '';
        }

                set_config('host',    $config->host,    'auth/pop3');
        set_config('type',    $config->type,    'auth/pop3');
        set_config('port',    $config->port,    'auth/pop3');
        set_config('mailbox', $config->mailbox, 'auth/pop3');
        set_config('changepasswordurl', $config->changepasswordurl, 'auth/pop3');

        return true;
    }

}


