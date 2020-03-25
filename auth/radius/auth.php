<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');


class auth_plugin_radius extends auth_plugin_base {

    
    public function __construct() {
        $this->authtype = 'radius';
        $this->config = get_config('auth/radius');
    }

    
    public function auth_plugin_radius() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    
    function user_login ($username, $password) {
        require_once 'Auth/RADIUS.php';
        require_once 'Crypt/CHAP.php';

                                                
                                                $type = $this->config->radiustype;
        if (empty($type)) {
            $type = 'PAP';
        }

        $classname = 'Auth_RADIUS_' . $type;
        $rauth = new $classname($username, $password);
        $rauth->addServer($this->config->host, $this->config->nasport, $this->config->secret);

        $rauth->username = $username;

        switch($type) {
        case 'CHAP_MD5':
        case 'MSCHAPv1':
            $classname = $type == 'MSCHAPv1' ? 'Crypt_CHAP_MSv1' : 'Crypt_CHAP_MD5';
            $crpt = new $classname;
            $crpt->password = $password;
            $rauth->challenge = $crpt->challenge;
            $rauth->chapid = $crpt->chapid;
            $rauth->response = $crpt->challengeResponse();
            $rauth->flags = 1;
                                                break;

        case 'MSCHAPv2':
            $crpt = new Crypt_CHAP_MSv2;
            $crpt->username = $username;
            $crpt->password = $password;
            $rauth->challenge = $crpt->authChallenge;
            $rauth->peerChallenge = $crpt->peerChallenge;
            $rauth->chapid = $crpt->chapid;
            $rauth->response = $crpt->challengeResponse();
            break;

        default:
            $rauth->password = $password;
            break;
        }

        if (!$rauth->start()) {
            printf("Radius start: %s<br/>\n", $rauth->getError());
            exit;
        }

        $result = $rauth->send();
        if ($rauth->isError($result)) {
            printf("Radius send failed: %s<br/>\n", $result->getMessage());
            exit;
        } else if ($result === true) {
                        return true;
        } else {
                        return false;
        }

                if (!$rauth->getAttributes()) {
            printf("Radius getAttributes: %s<br/>\n", $rauth->getError());
        } else {
            $rauth->dumpAttributes();
        }

        $rauth->close();
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
        global $OUTPUT;

        include "config.html";
    }

    
    function process_config($config) {
                if (!isset ($config->host)) {
            $config->host = '127.0.0.1';
        }
        if (!isset ($config->nasport)) {
            $config->nasport = '1812';
        }
        if (!isset($config->radiustype)) {
            $config->radiustype = 'PAP';
        }
        if (!isset ($config->secret)) {
            $config->secret = '';
        }
        if (!isset($config->changepasswordurl)) {
            $config->changepasswordurl = '';
        }

                set_config('host',    $config->host,    'auth/radius');
        set_config('nasport', $config->nasport, 'auth/radius');
        set_config('secret',  $config->secret,  'auth/radius');
        set_config('changepasswordurl', $config->changepasswordurl, 'auth/radius');
        set_config('radiustype', $config->radiustype, 'auth/radius');

        return true;
    }

}


