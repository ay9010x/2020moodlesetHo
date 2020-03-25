<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');


class auth_plugin_shibboleth extends auth_plugin_base {

    
    public function __construct() {
        $this->authtype = 'shibboleth';
        $this->config = get_config('auth/shibboleth');
    }

    
    public function auth_plugin_shibboleth() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    
    function user_login($username, $password) {
       global $SESSION;

                if (!empty($_SERVER[$this->config->user_attribute])) {
                        $sessionkey = '';
            if (isset($_SERVER['Shib-Session-ID'])){
                                $sessionkey = $_SERVER['Shib-Session-ID'];
            } else {
                                foreach ($_COOKIE as $name => $value){
                    if (preg_match('/_shibsession_/i', $name)){
                        $sessionkey = $value;
                    }
                }
            }

                        $SESSION->shibboleth_session_id  = $sessionkey;

            return (strtolower($_SERVER[$this->config->user_attribute]) == strtolower($username));
        } else {
                                    return false;
        }
    }



    
    function get_userinfo($username) {
            global $CFG;

                if ( empty($_SERVER[$this->config->user_attribute]) ) {
            print_error( 'shib_not_all_attributes_error', 'auth_shibboleth' , '', "'".$this->config->user_attribute."' ('".$_SERVER[$this->config->user_attribute]."'), '".$this->config->field_map_firstname."' ('".$_SERVER[$this->config->field_map_firstname]."'), '".$this->config->field_map_lastname."' ('".$_SERVER[$this->config->field_map_lastname]."') and '".$this->config->field_map_email."' ('".$_SERVER[$this->config->field_map_email]."')");
        }

        $attrmap = $this->get_attributes();

        $result = array();
        $search_attribs = array();

        foreach ($attrmap as $key=>$value) {
                        if (!isset($_SERVER[$value])){
                $result[$key] = '';
                continue;
            }

                        if ($key == 'username'){
                $result[$key] = strtolower($this->get_first_string($_SERVER[$value]));
            } else {
                $result[$key] = $this->get_first_string($_SERVER[$value]);
            }
        }

                         if (
              $this->config->convert_data
              && $this->config->convert_data != ''
              && is_readable($this->config->convert_data)
            ) {

                                    include($this->config->convert_data);
        }

        return $result;
    }

    
    function get_attributes() {
        $configarray = (array) $this->config;

        $moodleattributes = array();
        $userfields = array_merge($this->userfields, $this->get_custom_user_profile_fields());
        foreach ($userfields as $field) {
            if (isset($configarray["field_map_$field"])) {
                $moodleattributes[$field] = $configarray["field_map_$field"];
            }
        }
        $moodleattributes['username'] = $configarray["user_attribute"];

        return $moodleattributes;
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

     
    function loginpage_hook() {
        global $SESSION, $CFG;

                $CFG->nolastloggedin = true;

        return;
    }

     
    function logoutpage_hook() {
        global $SESSION, $redirect;

                $logouthandlervalid = isset($this->config->logout_handler) && !empty($this->config->logout_handler);
        if (isset($SESSION->shibboleth_session_id) && $logouthandlervalid ) {
                        if (isset($this->config->logout_return_url) && !empty($this->config->logout_return_url)) {
                                $temp_redirect = $this->config->logout_return_url;
            } else {
                                $temp_redirect = $redirect;
            }

                        $redirecturl = new moodle_url($this->config->logout_handler, array('return' => $temp_redirect));
            $redirect = $redirecturl->out();
        }
    }



    
    function config_form($config, $err, $user_fields) {
        include "config.html";
    }

    
    function process_config($config) {
        global $CFG;

                if (!isset($config->auth_instructions) or empty($config->user_attribute)) {
            $config->auth_instructions = get_string('auth_shib_instructions', 'auth_shibboleth', $CFG->wwwroot.'/auth/shibboleth/index.php');
        }
        if (!isset ($config->user_attribute)) {
            $config->user_attribute = '';
        }
        if (!isset ($config->convert_data)) {
            $config->convert_data = '';
        }

        if (!isset($config->changepasswordurl)) {
            $config->changepasswordurl = '';
        }

        if (!isset($config->login_name)) {
            $config->login_name = 'Shibboleth Login';
        }

                if (isset($config->organization_selection) && !empty($config->organization_selection) && isset($config->alt_login) && $config->alt_login == 'on') {
            $idp_list = get_idp_list($config->organization_selection);
            if (count($idp_list) < 1){
                return false;
            }
            $config->organization_selection = '';
            foreach ($idp_list as $idp => $value){
                $config->organization_selection .= $idp.', '.$value[0].', '.$value[1]."\n";
            }
        }


                set_config('user_attribute',    $config->user_attribute,    'auth/shibboleth');

        if (isset($config->organization_selection) && !empty($config->organization_selection)) {
            set_config('organization_selection',    $config->organization_selection,    'auth/shibboleth');
        }
        set_config('logout_handler',    $config->logout_handler,    'auth/shibboleth');
        set_config('logout_return_url',    $config->logout_return_url,    'auth/shibboleth');
        set_config('login_name',    $config->login_name,    'auth/shibboleth');
        set_config('convert_data',      $config->convert_data,      'auth/shibboleth');
        set_config('auth_instructions', $config->auth_instructions, 'auth/shibboleth');
        set_config('changepasswordurl', $config->changepasswordurl, 'auth/shibboleth');

                if (isset($config->alt_login) && $config->alt_login == 'on'){
            set_config('alt_login',    $config->alt_login,    'auth/shibboleth');
            set_config('alternateloginurl', $CFG->wwwroot.'/auth/shibboleth/login.php');
        } else {
                                    if (isset($this->config->alt_login) and $this->config->alt_login == 'on'){
                set_config('alt_login',    'off',    'auth/shibboleth');
                set_config('alternateloginurl', '');
            }
            $config->alt_login = 'off';
        }

                        if (($config->convert_data != '')&&(!file_exists($config->convert_data) || !is_readable($config->convert_data))){
            return false;
        }

                if (isset($config->organization_selection) && empty($config->organization_selection) && isset($config->alt_login) && $config->alt_login == 'on'){
            return false;
        }

        return true;
    }

    
    function get_first_string($string) {
        $list = explode( ';', $string);
        $clean_string = rtrim($list[0]);

        return $clean_string;
    }
}


    
    function set_saml_cookie($selectedIDP) {
        if (isset($_COOKIE['_saml_idp']))
        {
            $IDPArray = generate_cookie_array($_COOKIE['_saml_idp']);
        }
        else
        {
            $IDPArray = array();
        }
        $IDPArray = appendCookieValue($selectedIDP, $IDPArray);
        setcookie ('_saml_idp', generate_cookie_value($IDPArray), time() + (100*24*3600));
    }

     
    function print_idp_list(){
        $config = get_config('auth/shibboleth');

        $IdPs = get_idp_list($config->organization_selection);
        if (isset($_COOKIE['_saml_idp'])){
            $idp_cookie = generate_cookie_array($_COOKIE['_saml_idp']);
            do {
                $selectedIdP = array_pop($idp_cookie);
            } while (!isset($IdPs[$selectedIdP]) && count($idp_cookie) > 0);

        } else {
            $selectedIdP = '-';
        }

        foreach($IdPs as $IdP => $data){
            if ($IdP == $selectedIdP){
                echo '<option value="'.$IdP.'" selected="selected">'.$data[0].'</option>';
            } else {
                echo '<option value="'.$IdP.'">'.$data[0].'</option>';
            }
        }
    }


     

    function get_idp_list($organization_selection) {
        $idp_list = array();

        $idp_raw_list = explode("\n",  $organization_selection);

        foreach ($idp_raw_list as $idp_line){
            $idp_data = explode(',', $idp_line);
            if (isset($idp_data[2]))
            {
                $idp_list[trim($idp_data[0])] = array(trim($idp_data[1]),trim($idp_data[2]));
            }
            elseif(isset($idp_data[1]))
            {
                $idp_list[trim($idp_data[0])] = array(trim($idp_data[1]));
            }
        }

        return $idp_list;
    }

    
    function generate_cookie_array($value) {

                $CookieArray = explode(' ', $value);
        $CookieArray = array_map('base64_decode', $CookieArray);

        return $CookieArray;
    }

    
    function generate_cookie_value($CookieArray) {

                $CookieArray = array_map('base64_encode', $CookieArray);
        $value = implode(' ', $CookieArray);
        return $value;
    }

    
    function appendCookieValue($value, $CookieArray) {

        array_push($CookieArray, $value);
        $CookieArray = array_reverse($CookieArray);
        $CookieArray = array_unique($CookieArray);
        $CookieArray = array_reverse($CookieArray);

        return $CookieArray;
    }



