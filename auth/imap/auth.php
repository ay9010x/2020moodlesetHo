<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');


class auth_plugin_imap extends auth_plugin_base {

    
    public function __construct() {
        $this->authtype = 'imap';
        $this->config = get_config('auth/imap');
    }

    
    public function auth_plugin_imap() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    
    function user_login ($username, $password) {
        if (! function_exists('imap_open')) {
            print_error('auth_imapnotinstalled','mnet');
            return false;
        }

        global $CFG;
        $hosts = explode(';', $this->config->host);   
        foreach ($hosts as $host) {                             $host = trim($host);

            switch ($this->config->type) {
                case 'imapssl':
                    $host = '{'.$host.":{$this->config->port}/imap/ssl}";
                break;

                case 'imapcert':
                    $host = '{'.$host.":{$this->config->port}/imap/ssl/novalidate-cert}";
                break;

                case 'imaptls':
                    $host = '{'.$host.":{$this->config->port}/imap/tls}";
                break;

                case 'imapnosslcert':
                    $host = '{'.$host.":{$this->config->port}/imap/novalidate-cert}";
                break;

                default:
                    $host = '{'.$host.":{$this->config->port}/imap}";
            }

            error_reporting(0);
            $connection = imap_open($host, $username, $password, OP_HALFOPEN);
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
            $config->type = 'imap';
        }
        if (!isset ($config->port)) {
            $config->port = '143';
        }
        if (!isset($config->changepasswordurl)) {
            $config->changepasswordurl = '';
        }

                set_config('host', $config->host, 'auth/imap');
        set_config('type', $config->type, 'auth/imap');
        set_config('port', $config->port, 'auth/imap');
        set_config('changepasswordurl', $config->changepasswordurl, 'auth/imap');

        return true;
    }

}


