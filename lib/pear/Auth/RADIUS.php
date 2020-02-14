<?php



require_once('PEAR.php');





class Auth_RADIUS extends PEAR {

    
    var $_servers  = array();

    
    var $_configfile = null;

    
    var $res = null;

    
    var $username = null;

    
    var $password = null;

    
    var $attributes = array();

    
    var $rawAttributes = array();

    
    var $rawVendorAttributes = array();

    
    var $useStandardAttributes = true;

    
    public function __construct()
    {
        $this->loadExtension('radius');
    }

    
    public function loadExtension($ext) {
        if (extension_loaded($ext)) {
            return true;
        }
                if (
            function_exists('dl') === false ||
            ini_get('enable_dl') != 1 ||
            ini_get('safe_mode') == 1
        ) {
            return false;
        }
        if (OS_WINDOWS) {
            $suffix = '.dll';
        } elseif (PHP_OS == 'HP-UX') {
            $suffix = '.sl';
        } elseif (PHP_OS == 'AIX') {
            $suffix = '.a';
        } elseif (PHP_OS == 'OSX') {
            $suffix = '.bundle';
        } else {
            $suffix = '.so';
        }
        return @dl('php_'.$ext.$suffix) || @dl($ext.$suffix);
    }

    
    public function addServer($servername = 'localhost', $port = 0, $sharedSecret = 'testing123', $timeout = 3, $maxtries = 3)
    {
        $this->_servers[] = array($servername, $port, $sharedSecret, $timeout, $maxtries);
    }

    
    public function getError()
    {
        return radius_strerror($this->res);
    }

    
    public function setConfigfile($file)
    {
        $this->_configfile = $file;
    }

    
    public function putAttribute($attrib, $value, $type = null)
    {
        if ($type == null) {
            $type = gettype($value);
        }

        switch ($type) {
            case 'integer':
            case 'double':
                return radius_put_int($this->res, $attrib, $value);

            case 'addr':
                return radius_put_addr($this->res, $attrib, $value);

            case 'string':
            default:
                return radius_put_attr($this->res, $attrib, $value);
        }

    }

    
    public function putVendorAttribute($vendor, $attrib, $value, $type = null)
    {

        if ($type == null) {
            $type = gettype($value);
        }

        switch ($type) {
            case 'integer':
            case 'double':
                return radius_put_vendor_int($this->res, $vendor, $attrib, $value);

            case 'addr':
                return radius_put_vendor_addr($this->res, $vendor,$attrib, $value);

            case 'string':
            default:
                return radius_put_vendor_attr($this->res, $vendor, $attrib, $value);
        }

    }

    
    public function dumpAttributes()
    {
        foreach ($this->attributes as $name => $data) {
            echo "$name:$data<br>\n";
        }
    }

    
    public function open()
    {
    }

    
    public function createRequest()
    {
    }

    
    public function putStandardAttributes()
    {
        if (!$this->useStandardAttributes) {
            return;
        }

        if (isset($_SERVER)) {
            $var = $_SERVER;
        } else {
            $var = $GLOBALS['HTTP_SERVER_VARS'];
        }

        $this->putAttribute(RADIUS_NAS_IDENTIFIER, isset($var['HTTP_HOST']) ? $var['HTTP_HOST'] : 'localhost');
        $this->putAttribute(RADIUS_NAS_PORT_TYPE, RADIUS_VIRTUAL);
        $this->putAttribute(RADIUS_SERVICE_TYPE, RADIUS_FRAMED);
        $this->putAttribute(RADIUS_FRAMED_PROTOCOL, RADIUS_PPP);
        $this->putAttribute(RADIUS_CALLING_STATION_ID, isset($var['REMOTE_HOST']) ? $var['REMOTE_HOST'] : '127.0.0.1');
    }

    
    public function putAuthAttributes()
    {
        if (isset($this->username)) {
            $this->putAttribute(RADIUS_USER_NAME, $this->username);
        }
    }

    
    public function putServer($servername, $port = 0, $sharedsecret = 'testing123', $timeout = 3, $maxtries = 3)
    {
        if (!radius_add_server($this->res, $servername, $port, $sharedsecret, $timeout, $maxtries)) {
            return false;
        }
        return true;
    }

    
    public function putConfigfile($file)
    {
        if (!radius_config($this->res, $file)) {
            return false;
        }
        return true;
    }

    
    public function start()
    {
        if (!$this->open()) {
            return false;
        }

        foreach ($this->_servers as $s) {
                        if (!$this->putServer($s[0], $s[1], $s[2], $s[3], $s[4])) {
                return false;
            }
        }

        if (!empty($this->_configfile)) {
            if (!$this->putConfigfile($this->_configfile)) {
                return false;
            }
        }

        $this->createRequest();
        $this->putStandardAttributes();
        $this->putAuthAttributes();
        return true;
    }

    
    public function send()
    {
        $req = radius_send_request($this->res);
        if (!$req) {
            throw new Auth_RADIUS_Exception('Error sending request: ' . $this->getError());
        }

        switch($req) {
            case RADIUS_ACCESS_ACCEPT:
                if (is_subclass_of($this, 'auth_radius_acct')) {
                    throw new Auth_RADIUS_Exception('RADIUS_ACCESS_ACCEPT is unexpected for accounting');
                }
                return true;

            case RADIUS_ACCESS_REJECT:
                return false;

            case RADIUS_ACCOUNTING_RESPONSE:
                if (is_subclass_of($this, 'auth_radius_pap')) {
                    throw new Auth_RADIUS_Exception('RADIUS_ACCOUNTING_RESPONSE is unexpected for authentication');
                }
                return true;

            default:
                throw new Auth_RADIUS_Exception("Unexpected return value: $req");
        }

    }

    
    public function getAttributes()
    {

        while ($attrib = radius_get_attr($this->res)) {

            if (!is_array($attrib)) {
                return false;
            }

            $attr = $attrib['attr'];
            $data = $attrib['data'];

            $this->rawAttributes[$attr] = $data;

            switch ($attr) {
                case RADIUS_FRAMED_IP_ADDRESS:
                    $this->attributes['framed_ip'] = radius_cvt_addr($data);
                    break;

                case RADIUS_FRAMED_IP_NETMASK:
                    $this->attributes['framed_mask'] = radius_cvt_addr($data);
                    break;

                case RADIUS_FRAMED_MTU:
                    $this->attributes['framed_mtu'] = radius_cvt_int($data);
                    break;

                case RADIUS_FRAMED_COMPRESSION:
                    $this->attributes['framed_compression'] = radius_cvt_int($data);
                    break;

                case RADIUS_SESSION_TIMEOUT:
                    $this->attributes['session_timeout'] = radius_cvt_int($data);
                    break;

                case RADIUS_IDLE_TIMEOUT:
                    $this->attributes['idle_timeout'] = radius_cvt_int($data);
                    break;

                case RADIUS_SERVICE_TYPE:
                    $this->attributes['service_type'] = radius_cvt_int($data);
                    break;

                case RADIUS_CLASS:
                    $this->attributes['class'] = radius_cvt_string($data);
                    break;

                case RADIUS_FRAMED_PROTOCOL:
                    $this->attributes['framed_protocol'] = radius_cvt_int($data);
                    break;

                case RADIUS_FRAMED_ROUTING:
                    $this->attributes['framed_routing'] = radius_cvt_int($data);
                    break;

                case RADIUS_FILTER_ID:
                    $this->attributes['filter_id'] = radius_cvt_string($data);
                    break;

                case RADIUS_REPLY_MESSAGE:
                    $this->attributes['reply_message'] = radius_cvt_string($data);
                    break;

                case RADIUS_VENDOR_SPECIFIC:
                    $attribv = radius_get_vendor_attr($data);
                    if (!is_array($attribv)) {
                        return false;
                    }

                    $vendor = $attribv['vendor'];
                    $attrv = $attribv['attr'];
                    $datav = $attribv['data'];

                    $this->rawVendorAttributes[$vendor][$attrv] = $datav;

                    if ($vendor == RADIUS_VENDOR_MICROSOFT) {

                        switch ($attrv) {
                            case RADIUS_MICROSOFT_MS_CHAP2_SUCCESS:
                                $this->attributes['ms_chap2_success'] = radius_cvt_string($datav);
                                break;

                            case RADIUS_MICROSOFT_MS_CHAP_ERROR:
                                $this->attributes['ms_chap_error'] = radius_cvt_string(substr($datav,1));
                                break;

                            case RADIUS_MICROSOFT_MS_CHAP_DOMAIN:
                                $this->attributes['ms_chap_domain'] = radius_cvt_string($datav);
                                break;

                            case RADIUS_MICROSOFT_MS_MPPE_ENCRYPTION_POLICY:
                                $this->attributes['ms_mppe_encryption_policy'] = radius_cvt_int($datav);
                                break;

                            case RADIUS_MICROSOFT_MS_MPPE_ENCRYPTION_TYPES:
                                $this->attributes['ms_mppe_encryption_types'] = radius_cvt_int($datav);
                                break;

                            case RADIUS_MICROSOFT_MS_CHAP_MPPE_KEYS:
                                $demangled = radius_demangle($this->res, $datav);
                                $this->attributes['ms_chap_mppe_lm_key'] = substr($demangled, 0, 8);
                                $this->attributes['ms_chap_mppe_nt_key'] = substr($demangled, 8, RADIUS_MPPE_KEY_LEN);
                                break;

                            case RADIUS_MICROSOFT_MS_MPPE_SEND_KEY:
                                $this->attributes['ms_chap_mppe_send_key'] = radius_demangle_mppe_key($this->res, $datav);
                                break;

                            case RADIUS_MICROSOFT_MS_MPPE_RECV_KEY:
                                $this->attributes['ms_chap_mppe_recv_key'] = radius_demangle_mppe_key($this->res, $datav);
                                break;

                            case RADIUS_MICROSOFT_MS_PRIMARY_DNS_SERVER:
                                $this->attributes['ms_primary_dns_server'] = radius_cvt_string($datav);
                                break;
                        }
                    }
                    break;

            }
        }

        return true;
    }

    
    public function close()
    {
        if ($this->res != null) {
            radius_close($this->res);
            $this->res = null;
        }
        $this->username = str_repeat("\0", strlen($this->username));
        $this->password = str_repeat("\0", strlen($this->password));
    }

}


class Auth_RADIUS_PAP extends Auth_RADIUS
{

    
    public function __construct($username = null, $password = null)
    {
        parent::__construct();
        $this->username = $username;
        $this->password = $password;
    }

    
    function open()
    {
        $this->res = radius_auth_open();
        if (!$this->res) {
            return false;
        }
        return true;
    }

    
    function createRequest()
    {
        if (!radius_create_request($this->res, RADIUS_ACCESS_REQUEST)) {
            return false;
        }
        return true;
    }

    
    function putAuthAttributes()
    {
        if (isset($this->username)) {
            $this->putAttribute(RADIUS_USER_NAME, $this->username);
        }
        if (isset($this->password)) {
            $this->putAttribute(RADIUS_USER_PASSWORD, $this->password);
        }
    }

}


class Auth_RADIUS_CHAP_MD5 extends Auth_RADIUS_PAP
{
    
    var $challenge = null;

    
    var $response = null;

    
    var $chapid = 1;

    
    function __construct($username = null, $challenge = null, $chapid = 1)
    {
        parent::__construct();
        $this->username = $username;
        $this->challenge = $challenge;
        $this->chapid = $chapid;
    }

    
    function putAuthAttributes()
    {
        if (isset($this->username)) {
            $this->putAttribute(RADIUS_USER_NAME, $this->username);
        }
        if (isset($this->response)) {
            $response = pack('C', $this->chapid) . $this->response;
            $this->putAttribute(RADIUS_CHAP_PASSWORD, $response);
        }
        if (isset($this->challenge)) {
            $this->putAttribute(RADIUS_CHAP_CHALLENGE, $this->challenge);
        }
    }

    
    public function close()
    {
        parent::close();
        $this->challenge =  str_repeat("\0", strlen($this->challenge));
        $this->response =  str_repeat("\0", strlen($this->response));
    }

}


class Auth_RADIUS_MSCHAPv1 extends Auth_RADIUS_CHAP_MD5
{
    
    var $lmResponse = null;

    
    var $flags = 1;

    
    function putAuthAttributes()
    {
        if (isset($this->username)) {
            $this->putAttribute(RADIUS_USER_NAME, $this->username);
        }
        if (isset($this->response) || isset($this->lmResponse)) {
            $lmResp = isset($this->lmResponse) ? $this->lmResponse : str_repeat ("\0", 24);
            $ntResp = isset($this->response)   ? $this->response :   str_repeat ("\0", 24);
            $resp = pack('CC', $this->chapid, $this->flags) . $lmResp . $ntResp;
            $this->putVendorAttribute(RADIUS_VENDOR_MICROSOFT, RADIUS_MICROSOFT_MS_CHAP_RESPONSE, $resp);
        }
        if (isset($this->challenge)) {
            $this->putVendorAttribute(RADIUS_VENDOR_MICROSOFT, RADIUS_MICROSOFT_MS_CHAP_CHALLENGE, $this->challenge);
        }
    }
}


class Auth_RADIUS_MSCHAPv2 extends Auth_RADIUS_MSCHAPv1
{
    
    var $challenge = null;

    
    var $peerChallenge = null;

    
    function putAuthAttributes()
    {
        if (isset($this->username)) {
            $this->putAttribute(RADIUS_USER_NAME, $this->username);
        }
        if (isset($this->response) && isset($this->peerChallenge)) {
                        $resp = pack('CCa16a8a24',$this->chapid , 1, $this->peerChallenge, str_repeat("\0", 8), $this->response);
            $this->putVendorAttribute(RADIUS_VENDOR_MICROSOFT, RADIUS_MICROSOFT_MS_CHAP2_RESPONSE, $resp);
        }
        if (isset($this->challenge)) {
            $this->putVendorAttribute(RADIUS_VENDOR_MICROSOFT, RADIUS_MICROSOFT_MS_CHAP_CHALLENGE, $this->challenge);
        }
    }

    
    function close()
    {
        parent::close();
        $this->peerChallenge = str_repeat("\0", strlen($this->peerChallenge));
    }
}


class Auth_RADIUS_Acct extends Auth_RADIUS
{
    
    var $authentic = null;

    
    var $status_type = null;

    
    var $session_time = null;

    
    var $session_id = null;

    
    function __construct()
    {
        parent::__construct();

        if (isset($_SERVER)) {
            $var = $_SERVER;
        } else {
            $var = $GLOBALS['HTTP_SERVER_VARS'];
        }

        $this->session_id = sprintf("%s:%d-%s", isset($var['REMOTE_ADDR']) ? $var['REMOTE_ADDR'] : '127.0.0.1' , getmypid(), get_current_user());
    }

    
    function open()
    {
        $this->res = radius_acct_open();
        if (!$this->res) {
            return false;
        }
        return true;
    }

    
    function createRequest()
    {
        if (!radius_create_request($this->res, RADIUS_ACCOUNTING_REQUEST)) {
            return false;
        }
        return true;
    }

    
    function putAuthAttributes()
    {
        $this->putAttribute(RADIUS_ACCT_SESSION_ID, $this->session_id);
        $this->putAttribute(RADIUS_ACCT_STATUS_TYPE, $this->status_type);
        if (isset($this->session_time) && $this->status_type == RADIUS_STOP) {
            $this->putAttribute(RADIUS_ACCT_SESSION_TIME, $this->session_time);
        }
        if (isset($this->authentic)) {
            $this->putAttribute(RADIUS_ACCT_AUTHENTIC, $this->authentic);
        }

    }

}


class Auth_RADIUS_Acct_Start extends Auth_RADIUS_Acct
{
    
    var $status_type = RADIUS_START;
}


class Auth_RADIUS_Acct_Stop extends Auth_RADIUS_Acct
{
    
    var $status_type = RADIUS_STOP;
}

if (!defined('RADIUS_UPDATE')) {
    define('RADIUS_UPDATE', 3);
}


class Auth_RADIUS_Acct_Update extends Auth_RADIUS_Acct
{
    
    var $status_type = RADIUS_UPDATE;
}

class Auth_RADIUS_Exception extends Exception {}