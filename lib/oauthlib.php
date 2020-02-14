<?php


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');



class oauth_helper {
    
    protected $consumer_key;
    
    protected $consumer_secret;
    
    protected $api_root;
    
    protected $request_token_api;
    
    protected $authorize_url;
    protected $http_method;
    
    protected $access_token_api;
    
    protected $http;
    
    protected $http_options;

    
    function __construct($args) {
        if (!empty($args['api_root'])) {
            $this->api_root = $args['api_root'];
        } else {
            $this->api_root = '';
        }
        $this->consumer_key = $args['oauth_consumer_key'];
        $this->consumer_secret = $args['oauth_consumer_secret'];

        if (empty($args['request_token_api'])) {
            $this->request_token_api = $this->api_root . '/request_token';
        } else {
            $this->request_token_api = $args['request_token_api'];
        }

        if (empty($args['authorize_url'])) {
            $this->authorize_url = $this->api_root . '/authorize';
        } else {
            $this->authorize_url = $args['authorize_url'];
        }

        if (empty($args['access_token_api'])) {
            $this->access_token_api = $this->api_root . '/access_token';
        } else {
            $this->access_token_api = $args['access_token_api'];
        }

        if (!empty($args['oauth_callback'])) {
            $this->oauth_callback = new moodle_url($args['oauth_callback']);
        }
        if (!empty($args['access_token'])) {
            $this->access_token = $args['access_token'];
        }
        if (!empty($args['access_token_secret'])) {
            $this->access_token_secret = $args['access_token_secret'];
        }
        $this->http = new curl(array('debug'=>false));
        $this->http_options = array();
    }

    
    function get_signable_parameters($params){
        $sorted = $params;
        ksort($sorted);

        $total = array();
        foreach ($sorted as $k => $v) {
            if ($k == 'oauth_signature') {
                continue;
            }

            $total[] = rawurlencode($k) . '=' . rawurlencode($v);
        }
        return implode('&', $total);
    }

    
    public function sign($http_method, $url, $params, $secret) {
        $sig = array(
            strtoupper($http_method),
            preg_replace('/%7E/', '~', rawurlencode($url)),
            rawurlencode($this->get_signable_parameters($params)),
        );

        $base_string = implode('&', $sig);
        $sig = base64_encode(hash_hmac('sha1', $base_string, $secret, true));
        return $sig;
    }

    
    public function prepare_oauth_parameters($url, $params, $http_method = 'POST') {
        if (is_array($params)) {
            $oauth_params = $params;
        } else {
            $oauth_params = array();
        }
        $oauth_params['oauth_version']	    = '1.0';
        $oauth_params['oauth_nonce']	    = $this->get_nonce();
        $oauth_params['oauth_timestamp']    = $this->get_timestamp();
        $oauth_params['oauth_consumer_key'] = $this->consumer_key;
        if (!empty($this->oauth_callback)) {
            $oauth_params['oauth_callback'] = $this->oauth_callback->out(false);
        }
        $oauth_params['oauth_signature_method']	= 'HMAC-SHA1';
        $oauth_params['oauth_signature']	= $this->sign($http_method, $url, $oauth_params, $this->sign_secret);
        return $oauth_params;
    }

    public function setup_oauth_http_header($params) {

        $total = array();
        ksort($params);
        foreach ($params as $k => $v) {
            $total[] = rawurlencode($k) . '="' . rawurlencode($v).'"';
        }
        $str = implode(', ', $total);
        $str = 'Authorization: OAuth '.$str;
        $this->http->setHeader('Expect:');
        $this->http->setHeader($str);
    }

    
    public function setup_oauth_http_options($options) {
        $this->http_options = $options;
    }

    
    public function request_token() {
        $this->sign_secret = $this->consumer_secret.'&';
        $params = $this->prepare_oauth_parameters($this->request_token_api, array(), 'GET');
        $content = $this->http->get($this->request_token_api, $params, $this->http_options);
                                $result = $this->parse_result($content);
        if (empty($result['oauth_token'])) {
            throw new moodle_exception('Error while requesting an oauth token');
        }
                if (!empty($this->oauth_callback)) {
                        $result['authorize_url'] = $this->authorize_url . '?oauth_token='.$result['oauth_token'].'&oauth_callback='.rawurlencode($this->oauth_callback->out(false));
        } else {
                        $result['authorize_url'] = $this->authorize_url . '?oauth_token='.$result['oauth_token'];
        }
        return $result;
    }

    
    public function set_access_token($token, $secret) {
        $this->access_token = $token;
        $this->access_token_secret = $secret;
    }

    
    public function get_access_token($token, $secret, $verifier='') {
        $this->sign_secret = $this->consumer_secret.'&'.$secret;
        $params = $this->prepare_oauth_parameters($this->access_token_api, array('oauth_token'=>$token, 'oauth_verifier'=>$verifier), 'POST');
        $this->setup_oauth_http_header($params);
                unset($params['oauth_callback']);
        $content = $this->http->post($this->access_token_api, $params, $this->http_options);
        $keys = $this->parse_result($content);
        $this->set_access_token($keys['oauth_token'], $keys['oauth_token_secret']);
        return $keys;
    }

    
    public function request($method, $url, $params=array(), $token='', $secret='') {
        if (empty($token)) {
            $token = $this->access_token;
        }
        if (empty($secret)) {
            $secret = $this->access_token_secret;
        }
                $this->sign_secret = $this->consumer_secret.'&'.$secret;
        if (strtolower($method) === 'post' && !empty($params)) {
            $oauth_params = $this->prepare_oauth_parameters($url, array('oauth_token'=>$token) + $params, $method);
        } else {
            $oauth_params = $this->prepare_oauth_parameters($url, array('oauth_token'=>$token), $method);
        }
        $this->setup_oauth_http_header($oauth_params);
        $content = call_user_func_array(array($this->http, strtolower($method)), array($url, $params, $this->http_options));
                $this->http->resetHeader();
                return $content;
    }

    
    public function get($url, $params=array(), $token='', $secret='') {
        return $this->request('GET', $url, $params, $token, $secret);
    }

    
    public function post($url, $params=array(), $token='', $secret='') {
        return $this->request('POST', $url, $params, $token, $secret);
    }

    
    public function parse_result($str) {
        if (empty($str)) {
            throw new moodle_exception('error');
        }
        $parts = explode('&', $str);
        $result = array();
        foreach ($parts as $part){
            list($k, $v) = explode('=', $part, 2);
            $result[urldecode($k)] = urldecode($v);
        }
        if (empty($result)) {
            throw new moodle_exception('error');
        }
        return $result;
    }

    
    function set_nonce($str) {
        $this->nonce = $str;
    }
    
    function set_timestamp($time) {
        $this->timestamp = $time;
    }
    
    function get_timestamp() {
        if (!empty($this->timestamp)) {
            $timestamp = $this->timestamp;
            unset($this->timestamp);
            return $timestamp;
        }
        return time();
    }
    
    function get_nonce() {
        if (!empty($this->nonce)) {
            $nonce = $this->nonce;
            unset($this->nonce);
            return $nonce;
        }
        $mt = microtime();
        $rand = mt_rand();

        return md5($mt . $rand);
    }
}


abstract class oauth2_client extends curl {
    
    private $clientid = '';
    
    private $clientsecret = '';
    
    private $returnurl = null;
    
    private $scope = '';
    
    private $accesstoken = null;

    
    abstract protected function auth_url();

    
    abstract protected function token_url();

    
    public function __construct($clientid, $clientsecret, moodle_url $returnurl, $scope) {
        parent::__construct();
        $this->clientid = $clientid;
        $this->clientsecret = $clientsecret;
        $this->returnurl = $returnurl;
        $this->scope = $scope;
        $this->accesstoken = $this->get_stored_token();
    }

    
    public function is_logged_in() {
                if (isset($this->accesstoken->expires) && time() >= $this->accesstoken->expires) {
            $this->log_out();
            return false;
        }

                if (isset($this->accesstoken->token)) {
            return true;
        }

                        $code = optional_param('oauth2code', null, PARAM_RAW);
        if ($code && $this->upgrade_token($code)) {
            return true;
        }

        return false;
    }

    
    public static function callback_url() {
        global $CFG;

        return new moodle_url('/admin/oauth2callback.php');
    }

    
    public function get_login_url() {

        $callbackurl = self::callback_url();
        $url = new moodle_url($this->auth_url(),
                        array('client_id' => $this->clientid,
                              'response_type' => 'code',
                              'redirect_uri' => $callbackurl->out(false),
                              'state' => $this->returnurl->out_as_local_url(false),
                              'scope' => $this->scope,
                          ));

        return $url;
    }

    
    public function upgrade_token($code) {
        $callbackurl = self::callback_url();
        $params = array('client_id' => $this->clientid,
            'client_secret' => $this->clientsecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $callbackurl->out(false),
        );

                if ($this->use_http_get()) {
            $response = $this->get($this->token_url(), $params);
        } else {
            $response = $this->post($this->token_url(), $params);
        }

        if (!$this->info['http_code'] === 200) {
            throw new moodle_exception('Could not upgrade oauth token');
        }

        $r = json_decode($response);

        if (!isset($r->access_token)) {
            return false;
        }

                $accesstoken = new stdClass;
        $accesstoken->token = $r->access_token;
        if (isset($r->expires_in)) {
                        $accesstoken->expires = (time() + ($r->expires_in - 10));
        }
        $this->store_token($accesstoken);

        return true;
    }

    
    public function log_out() {
        $this->store_token(null);
    }

    
    protected function request($url, $options = array()) {
        $murl = new moodle_url($url);

        if ($this->accesstoken) {
            if ($this->use_http_get()) {
                                $murl->param('access_token', $this->accesstoken->token);
            } else {
                $this->setHeader('Authorization: Bearer '.$this->accesstoken->token);
            }
        }

        return parent::request($murl->out(false), $options);
    }

    
    protected function multi($requests, $options = array()) {
        if ($this->accesstoken) {
            $this->setHeader('Authorization: Bearer '.$this->accesstoken->token);
        }
        return parent::multi($requests, $options);
    }

    
    protected function get_tokenname() {
                return get_class($this).'-'.md5($this->scope);
    }

    
    protected function store_token($token) {
        global $SESSION;

        $this->accesstoken = $token;
        $name = $this->get_tokenname();

        if ($token !== null) {
            $SESSION->{$name} = $token;
        } else {
            unset($SESSION->{$name});
        }
    }

    
    protected function get_stored_token() {
        global $SESSION;

        $name = $this->get_tokenname();

        if (isset($SESSION->{$name})) {
            return $SESSION->{$name};
        }

        return null;
    }

    
    public function get_accesstoken() {
        return $this->accesstoken;
    }

    
    public function get_clientid() {
        return $this->clientid;
    }

    
    public function get_clientsecret() {
        return $this->clientsecret;
    }

    
    protected function use_http_get() {
        return false;
    }
}
