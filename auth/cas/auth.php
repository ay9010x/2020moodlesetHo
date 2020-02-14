<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/auth/ldap/auth.php');
require_once($CFG->dirroot.'/auth/cas/CAS/CAS.php');


class auth_plugin_cas extends auth_plugin_ldap {

    
    public function __construct() {
        $this->authtype = 'cas';
        $this->roleauth = 'auth_cas';
        $this->errorlogtag = '[AUTH CAS] ';
        $this->init_plugin($this->authtype);
    }

    
    public function auth_plugin_cas() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    function prevent_local_passwords() {
        return true;
    }

    
    function user_login ($username, $password) {
        $this->connectCAS();
        return phpCAS::isAuthenticated() && (trim(core_text::strtolower(phpCAS::getUser())) == $username);
    }

    
    function is_internal() {
        return false;
    }

    
    function can_change_password() {
        return false;
    }

    
    function loginpage_hook() {
        global $frm;
        global $CFG;
        global $SESSION, $OUTPUT, $PAGE;

        $site = get_site();
        $CASform = get_string('CASform', 'auth_cas');
        $username = optional_param('username', '', PARAM_RAW);
        $courseid = optional_param('courseid', 0, PARAM_INT);

        if (!empty($username)) {
            if (isset($SESSION->wantsurl) && (strstr($SESSION->wantsurl, 'ticket') ||
                                              strstr($SESSION->wantsurl, 'NOCAS'))) {
                unset($SESSION->wantsurl);
            }
            return;
        }

                if (empty($this->config->hostname)) {
            return;
        }

                if ($this->config->multiauth) {

                        if (!empty($SESSION->loginerrormsg)) {
                return;
            }

            $authCAS = optional_param('authCAS', '', PARAM_RAW);
            if ($authCAS == 'NOCAS') {
                return;
            }
                                    if ($authCAS != 'CAS' && !isset($_GET['pgtIou'])) {
                $PAGE->set_url('/login/index.php');
                $PAGE->navbar->add($CASform);
                $PAGE->set_title("$site->fullname: $CASform");
                $PAGE->set_heading($site->fullname);
                echo $OUTPUT->header();
                include($CFG->dirroot.'/auth/cas/cas_form.html');
                echo $OUTPUT->footer();
                exit();
            }
        }

                $this->connectCAS();

        if (phpCAS::checkAuthentication()) {
            $frm = new stdClass();
            $frm->username = phpCAS::getUser();
            $frm->password = 'passwdCas';

                        if ($this->config->multiauth && !empty($courseid)) {
                redirect(new moodle_url('/course/view.php', array('id'=>$courseid)));
            }

            return;
        }

        if (isset($_GET['loginguest']) && ($_GET['loginguest'] == true)) {
            $frm = new stdClass();
            $frm->username = 'guest';
            $frm->password = 'guest';
            return;
        }

                if (!phpCAS::isAuthenticated()) {
            phpCAS::setLang($this->config->language);
            phpCAS::forceAuthentication();
        }
    }


    
    function connectCAS() {
        global $CFG;
        static $connected = false;

        if (!$connected) {
                        if ($this->config->proxycas) {
                phpCAS::proxy($this->config->casversion, $this->config->hostname, (int) $this->config->port, $this->config->baseuri, false);
            } else {
                phpCAS::client($this->config->casversion, $this->config->hostname, (int) $this->config->port, $this->config->baseuri, false);
            }
                        if (!empty($this->config->curl_ssl_version)) {
                phpCAS::setExtraCurlOption(CURLOPT_SSLVERSION, $this->config->curl_ssl_version);
            }

            $connected = true;
        }

                if (!empty($CFG->proxyhost) && !is_proxybypass(phpCAS::getServerLoginURL())) {
            phpCAS::setExtraCurlOption(CURLOPT_PROXY, $CFG->proxyhost);
            if (!empty($CFG->proxyport)) {
                phpCAS::setExtraCurlOption(CURLOPT_PROXYPORT, $CFG->proxyport);
            }
            if (!empty($CFG->proxytype)) {
                                if ($CFG->proxytype == 'SOCKS5') {
                    phpCAS::setExtraCurlOption(CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                }
            }
            if (!empty($CFG->proxyuser) and !empty($CFG->proxypassword)) {
                phpCAS::setExtraCurlOption(CURLOPT_PROXYUSERPWD, $CFG->proxyuser.':'.$CFG->proxypassword);
                if (defined('CURLOPT_PROXYAUTH')) {
                                        phpCAS::setExtraCurlOption(CURLOPT_PROXYAUTH, CURLAUTH_BASIC | CURLAUTH_NTLM);
                }
            }
        }

        if ($this->config->certificate_check && $this->config->certificate_path){
            phpCAS::setCasServerCACert($this->config->certificate_path);
        } else {
                        phpCAS::setNoCasServerValidation();
        }
    }

    
    function config_form($config, $err, $user_fields) {
        global $CFG, $OUTPUT;

        if (!function_exists('ldap_connect')) {             echo $OUTPUT->notification(get_string('auth_ldap_noextension', 'auth_ldap'));

                                                if (!defined('LDAP_DEREF_NEVER')) {
                define ('LDAP_DEREF_NEVER', 0);
            }
            if (!defined('LDAP_DEREF_ALWAYS')) {
                define ('LDAP_DEREF_ALWAYS', 3);
            }
        }

        include($CFG->dirroot.'/auth/cas/config.html');
    }

    
    function validate_form($form, &$err) {
        $certificate_path = trim($form->certificate_path);
        if ($form->certificate_check && empty($certificate_path)) {
            $err['certificate_path'] = get_string('auth_cas_certificate_path_empty', 'auth_cas');
        }
    }

    
    function change_password_url() {
        return null;
    }

    
    function process_config($config) {

                if (!isset($config->hostname)) {
            $config->hostname = '';
        }
        if (!isset($config->port)) {
            $config->port = '';
        }
        if (!isset($config->casversion)) {
            $config->casversion = '';
        }
        if (!isset($config->baseuri)) {
            $config->baseuri = '';
        }
        if (!isset($config->language)) {
            $config->language = '';
        }
        if (!isset($config->proxycas)) {
            $config->proxycas = '';
        }
        if (!isset($config->logoutcas)) {
            $config->logoutcas = '';
        }
        if (!isset($config->multiauth)) {
            $config->multiauth = '';
        }
        if (!isset($config->certificate_check)) {
            $config->certificate_check = '';
        }
        if (!isset($config->certificate_path)) {
            $config->certificate_path = '';
        }
        if (!isset($config->curl_ssl_version)) {
            $config->curl_ssl_version = '';
        }
        if (!isset($config->logout_return_url)) {
            $config->logout_return_url = '';
        }

                if (!isset($config->host_url)) {
            $config->host_url = '';
        }
        if (!isset($config->start_tls)) {
             $config->start_tls = false;
        }
        if (empty($config->ldapencoding)) {
            $config->ldapencoding = 'utf-8';
        }
        if (!isset($config->pagesize)) {
            $config->pagesize = LDAP_DEFAULT_PAGESIZE;
        }
        if (!isset($config->contexts)) {
            $config->contexts = '';
        }
        if (!isset($config->user_type)) {
            $config->user_type = 'default';
        }
        if (!isset($config->user_attribute)) {
            $config->user_attribute = '';
        }
        if (!isset($config->search_sub)) {
            $config->search_sub = '';
        }
        if (!isset($config->opt_deref)) {
            $config->opt_deref = LDAP_DEREF_NEVER;
        }
        if (!isset($config->bind_dn)) {
            $config->bind_dn = '';
        }
        if (!isset($config->bind_pw)) {
            $config->bind_pw = '';
        }
        if (!isset($config->ldap_version)) {
            $config->ldap_version = '3';
        }
        if (!isset($config->objectclass)) {
            $config->objectclass = '';
        }
        if (!isset($config->memberattribute)) {
            $config->memberattribute = '';
        }

        if (!isset($config->memberattribute_isdn)) {
            $config->memberattribute_isdn = '';
        }
        if (!isset($config->attrcreators)) {
            $config->attrcreators = '';
        }
        if (!isset($config->groupecreators)) {
            $config->groupecreators = '';
        }
        if (!isset($config->removeuser)) {
            $config->removeuser = AUTH_REMOVEUSER_KEEP;
        }

                set_config('hostname', trim($config->hostname), $this->pluginconfig);
        set_config('port', trim($config->port), $this->pluginconfig);
        set_config('casversion', $config->casversion, $this->pluginconfig);
        set_config('baseuri', trim($config->baseuri), $this->pluginconfig);
        set_config('language', $config->language, $this->pluginconfig);
        set_config('proxycas', $config->proxycas, $this->pluginconfig);
        set_config('logoutcas', $config->logoutcas, $this->pluginconfig);
        set_config('multiauth', $config->multiauth, $this->pluginconfig);
        set_config('certificate_check', $config->certificate_check, $this->pluginconfig);
        set_config('certificate_path', $config->certificate_path, $this->pluginconfig);
        set_config('curl_ssl_version', $config->curl_ssl_version, $this->pluginconfig);
        set_config('logout_return_url', $config->logout_return_url, $this->pluginconfig);

                set_config('host_url', trim($config->host_url), $this->pluginconfig);
        set_config('start_tls', $config->start_tls, $this->pluginconfig);
        set_config('ldapencoding', trim($config->ldapencoding), $this->pluginconfig);
        set_config('pagesize', (int)trim($config->pagesize), $this->pluginconfig);
        set_config('contexts', trim($config->contexts), $this->pluginconfig);
        set_config('user_type', core_text::strtolower(trim($config->user_type)), $this->pluginconfig);
        set_config('user_attribute', core_text::strtolower(trim($config->user_attribute)), $this->pluginconfig);
        set_config('search_sub', $config->search_sub, $this->pluginconfig);
        set_config('opt_deref', $config->opt_deref, $this->pluginconfig);
        set_config('bind_dn', trim($config->bind_dn), $this->pluginconfig);
        set_config('bind_pw', $config->bind_pw, $this->pluginconfig);
        set_config('ldap_version', $config->ldap_version, $this->pluginconfig);
        set_config('objectclass', trim($config->objectclass), $this->pluginconfig);
        set_config('memberattribute', core_text::strtolower(trim($config->memberattribute)), $this->pluginconfig);
        set_config('memberattribute_isdn', $config->memberattribute_isdn, $this->pluginconfig);
        set_config('attrcreators', trim($config->attrcreators), $this->pluginconfig);
        set_config('groupecreators', trim($config->groupecreators), $this->pluginconfig);
        set_config('removeuser', $config->removeuser, $this->pluginconfig);

        return true;
    }

    
    function iscreator($username) {
        if (empty($this->config->host_url) or (empty($this->config->attrcreators) && empty($this->config->groupecreators)) or empty($this->config->memberattribute)) {
            return false;
        }

        $extusername = core_text::convert($username, 'utf-8', $this->config->ldapencoding);

                if (!empty($this->config->groupecreators)) {
            $ldapconnection = $this->ldap_connect();
            if ($this->config->memberattribute_isdn) {
                if(!($userid = $this->ldap_find_userdn($ldapconnection, $extusername))) {
                    return false;
                }
            } else {
                $userid = $extusername;
            }

            $group_dns = explode(';', $this->config->groupecreators);
            if (ldap_isgroupmember($ldapconnection, $userid, $group_dns, $this->config->memberattribute)) {
                return true;
            }
        }

                if (!empty($this->config->attrcreators)) {
            $attrs = explode(';', $this->config->attrcreators);
            $filter = '(& ('.$this->config->user_attribute."=$username)(|";
            foreach ($attrs as $attr){
                if(strpos($attr, '=')) {
                    $filter .= "($attr)";
                } else {
                    $filter .= '('.$this->config->memberattribute."=$attr)";
                }
            }
            $filter .= '))';

                        $result = $this->ldap_get_userlist($filter);
            if (count($result) != 0) {
                return true;
            }
        }

        return false;
    }

    
    function get_userinfo($username) {
        if (empty($this->config->host_url)) {
            return array();
        }
        return parent::get_userinfo($username);
    }

    
    function sync_users($do_updates=true) {
        if (empty($this->config->host_url)) {
            error_log('[AUTH CAS] '.get_string('noldapserver', 'auth_cas'));
            return;
        }
        parent::sync_users($do_updates);
    }

    
    function logoutpage_hook() {
        global $USER, $redirect;

                if ($USER->auth === $this->authtype) {
                        if (isset($this->config->logout_return_url) && !empty($this->config->logout_return_url)) {
                                $redirect = $this->config->logout_return_url;
            }
        }
    }

    
    public function postlogout_hook($user) {
        global $CFG;
                if (!empty($this->config->logoutcas) && $user->auth == $this->authtype) {
            $backurl = !empty($this->config->logout_return_url) ? $this->config->logout_return_url : $CFG->wwwroot;
            $this->connectCAS();
            phpCAS::logoutWithRedirectService($backurl);
        }
    }
}
