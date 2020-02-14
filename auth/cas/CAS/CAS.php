<?php




if (php_sapi_name() != 'cli') {
    if (!isset($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];
    }
}

if (!defined('E_USER_DEPRECATED')) {
    define('E_USER_DEPRECATED', E_USER_NOTICE);
}





define('PHPCAS_VERSION', '1.3.4');




define("CAS_VERSION_1_0", '1.0');

define("CAS_VERSION_2_0", '2.0');

define("CAS_VERSION_3_0", '3.0');



define("SAML_VERSION_1_1", 'S1');


define("SAML_XML_HEADER", '<?xml version="1.0" encoding="UTF-8"?>');


define("SAML_SOAP_ENV", '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Header/>');


define("SAML_SOAP_BODY", '<SOAP-ENV:Body>');


define("SAMLP_REQUEST", '<samlp:Request xmlns:samlp="urn:oasis:names:tc:SAML:1.0:protocol"  MajorVersion="1" MinorVersion="1" RequestID="_192.168.16.51.1024506224022" IssueInstant="2002-06-19T17:03:44.022Z">');
define("SAMLP_REQUEST_CLOSE", '</samlp:Request>');


define("SAML_ASSERTION_ARTIFACT", '<samlp:AssertionArtifact>');


define("SAML_ASSERTION_ARTIFACT_CLOSE", '</samlp:AssertionArtifact>');


define("SAML_SOAP_BODY_CLOSE", '</SOAP-ENV:Body>');


define("SAML_SOAP_ENV_CLOSE", '</SOAP-ENV:Envelope>');


define("SAML_ATTRIBUTES", 'SAMLATTRIBS');


define("DEFAULT_ERROR", 'Internal script failure');




define("CAS_PGT_STORAGE_FILE_DEFAULT_PATH", session_save_path());




define("PHPCAS_SERVICE_OK", 0);

define("PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE", 1);

define("PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE", 2);

define("PHPCAS_SERVICE_PT_FAILURE", 3);

define("PHPCAS_SERVICE_NOT_AVAILABLE", 4);


define("PHPCAS_PROXIED_SERVICE_HTTP_GET", 'CAS_ProxiedService_Http_Get');

define("PHPCAS_PROXIED_SERVICE_HTTP_POST", 'CAS_ProxiedService_Http_Post');

define("PHPCAS_PROXIED_SERVICE_IMAP", 'CAS_ProxiedService_Imap');





define("PHPCAS_LANG_ENGLISH", 'CAS_Languages_English');
define("PHPCAS_LANG_FRENCH", 'CAS_Languages_French');
define("PHPCAS_LANG_GREEK", 'CAS_Languages_Greek');
define("PHPCAS_LANG_GERMAN", 'CAS_Languages_German');
define("PHPCAS_LANG_JAPANESE", 'CAS_Languages_Japanese');
define("PHPCAS_LANG_SPANISH", 'CAS_Languages_Spanish');
define("PHPCAS_LANG_CATALAN", 'CAS_Languages_Catalan');






define("PHPCAS_LANG_DEFAULT", PHPCAS_LANG_ENGLISH);





function gettmpdir() {
if (!empty($_ENV['TMP'])) { return realpath($_ENV['TMP']); }
if (!empty($_ENV['TMPDIR'])) { return realpath( $_ENV['TMPDIR']); }
if (!empty($_ENV['TEMP'])) { return realpath( $_ENV['TEMP']); }
return "/tmp";
}
define('DEFAULT_DEBUG_DIR', gettmpdir()."/");



require_once dirname(__FILE__) . '/CAS/Autoload.php';



class phpCAS
{

    
    private static $_PHPCAS_CLIENT;

    
    private static $_PHPCAS_INIT_CALL;

    
    private static $_PHPCAS_DEBUG;

    
    private static $_PHPCAS_VERBOSE = false;


            
    

    
    public static function client($server_version, $server_hostname,
        $server_port, $server_uri, $changeSessionID = true
    ) {
        phpCAS :: traceBegin();
        if (is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error(self::$_PHPCAS_INIT_CALL['method'] . '() has already been called (at ' . self::$_PHPCAS_INIT_CALL['file'] . ':' . self::$_PHPCAS_INIT_CALL['line'] . ')');
        }

                $dbg = debug_backtrace();
        self::$_PHPCAS_INIT_CALL = array (
            'done' => true,
            'file' => $dbg[0]['file'],
            'line' => $dbg[0]['line'],
            'method' => __CLASS__ . '::' . __FUNCTION__
        );

                try {
            self::$_PHPCAS_CLIENT = new CAS_Client(
                $server_version, false, $server_hostname, $server_port, $server_uri,
                $changeSessionID
            );
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }
        phpCAS :: traceEnd();
    }

    
    public static function proxy($server_version, $server_hostname,
        $server_port, $server_uri, $changeSessionID = true
    ) {
        phpCAS :: traceBegin();
        if (is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error(self::$_PHPCAS_INIT_CALL['method'] . '() has already been called (at ' . self::$_PHPCAS_INIT_CALL['file'] . ':' . self::$_PHPCAS_INIT_CALL['line'] . ')');
        }

                $dbg = debug_backtrace();
        self::$_PHPCAS_INIT_CALL = array (
            'done' => true,
            'file' => $dbg[0]['file'],
            'line' => $dbg[0]['line'],
            'method' => __CLASS__ . '::' . __FUNCTION__
        );

                try {
            self::$_PHPCAS_CLIENT = new CAS_Client(
                $server_version, true, $server_hostname, $server_port, $server_uri,
                $changeSessionID
            );
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }
        phpCAS :: traceEnd();
    }

    
    public static function isInitialized ()
    {
        return (is_object(self::$_PHPCAS_CLIENT));
    }

    
            
    

    
    public static function setDebug($filename = '')
    {
        if ($filename != false && gettype($filename) != 'string') {
            phpCAS :: error('type mismatched for parameter $dbg (should be false or the name of the log file)');
        }
        if ($filename === false) {
            self::$_PHPCAS_DEBUG['filename'] = false;

        } else {
            if (empty ($filename)) {
                if (preg_match('/^Win.*/', getenv('OS'))) {
                    if (isset ($_ENV['TMP'])) {
                        $debugDir = $_ENV['TMP'] . '/';
                    } else {
                        $debugDir = '';
                    }
                } else {
                    $debugDir = DEFAULT_DEBUG_DIR;
                }
                $filename = $debugDir . 'phpCAS.log';
            }

            if (empty (self::$_PHPCAS_DEBUG['unique_id'])) {
                self::$_PHPCAS_DEBUG['unique_id'] = substr(strtoupper(md5(uniqid(''))), 0, 4);
            }

            self::$_PHPCAS_DEBUG['filename'] = $filename;
            self::$_PHPCAS_DEBUG['indent'] = 0;

            phpCAS :: trace('START ('.date("Y-m-d H:i:s").') phpCAS-' . PHPCAS_VERSION . ' ******************');
        }
    }

    
    public static function setVerbose($verbose)
    {
        if ($verbose === true) {
            self::$_PHPCAS_VERBOSE = true;
        } else {
            self::$_PHPCAS_VERBOSE = false;
        }
    }


    
    public static function getVerbose()
    {
        return self::$_PHPCAS_VERBOSE;
    }

    
    public static function log($str)
    {
        $indent_str = ".";


        if (!empty(self::$_PHPCAS_DEBUG['filename'])) {
                                    if (!file_exists(self::$_PHPCAS_DEBUG['filename'])) {
                touch(self::$_PHPCAS_DEBUG['filename']);
                                @chmod(self::$_PHPCAS_DEBUG['filename'], 0600);
            }
            for ($i = 0; $i < self::$_PHPCAS_DEBUG['indent']; $i++) {

                $indent_str .= '|    ';
            }
                                    $str2 = str_replace("\n", "\n" . self::$_PHPCAS_DEBUG['unique_id'] . ' ' . $indent_str, $str);
            error_log(self::$_PHPCAS_DEBUG['unique_id'] . ' ' . $indent_str . $str2 . "\n", 3, self::$_PHPCAS_DEBUG['filename']);
        }

    }

    
    public static function error($msg)
    {
        phpCAS :: traceBegin();
        $dbg = debug_backtrace();
        $function = '?';
        $file = '?';
        $line = '?';
        if (is_array($dbg)) {
            for ($i = 1; $i < sizeof($dbg); $i++) {
                if (is_array($dbg[$i]) && isset($dbg[$i]['class']) ) {
                    if ($dbg[$i]['class'] == __CLASS__) {
                        $function = $dbg[$i]['function'];
                        $file = $dbg[$i]['file'];
                        $line = $dbg[$i]['line'];
                    }
                }
            }
        }
        if (self::$_PHPCAS_VERBOSE) {
            echo "<br />\n<b>phpCAS error</b>: <font color=\"FF0000\"><b>" . __CLASS__ . "::" . $function . '(): ' . htmlentities($msg) . "</b></font> in <b>" . $file . "</b> on line <b>" . $line . "</b><br />\n";
        } else {
            echo "<br />\n<b>Error</b>: <font color=\"FF0000\"><b>". DEFAULT_ERROR ."</b><br />\n";
        }
        phpCAS :: trace($msg . ' in ' . $file . 'on line ' . $line );
        phpCAS :: traceEnd();

        throw new CAS_GracefullTerminationException(__CLASS__ . "::" . $function . '(): ' . $msg);
    }

    
    public static function trace($str)
    {
        $dbg = debug_backtrace();
        phpCAS :: log($str . ' [' . basename($dbg[0]['file']) . ':' . $dbg[0]['line'] . ']');
    }

    
    public static function traceBegin()
    {
        $dbg = debug_backtrace();
        $str = '=> ';
        if (!empty ($dbg[1]['class'])) {
            $str .= $dbg[1]['class'] . '::';
        }
        $str .= $dbg[1]['function'] . '(';
        if (is_array($dbg[1]['args'])) {
            foreach ($dbg[1]['args'] as $index => $arg) {
                if ($index != 0) {
                    $str .= ', ';
                }
                if (is_object($arg)) {
                    $str .= get_class($arg);
                } else {
                    $str .= str_replace(array("\r\n", "\n", "\r"), "", var_export($arg, true));
                }
            }
        }
        if (isset($dbg[1]['file'])) {
            $file = basename($dbg[1]['file']);
        } else {
            $file = 'unknown_file';
        }
        if (isset($dbg[1]['line'])) {
            $line = $dbg[1]['line'];
        } else {
            $line = 'unknown_line';
        }
        $str .= ') [' . $file . ':' . $line . ']';
        phpCAS :: log($str);
        if (!isset(self::$_PHPCAS_DEBUG['indent'])) {
            self::$_PHPCAS_DEBUG['indent'] = 0;
        } else {
            self::$_PHPCAS_DEBUG['indent']++;
        }
    }

    
    public static function traceEnd($res = '')
    {
        if (empty(self::$_PHPCAS_DEBUG['indent'])) {
            self::$_PHPCAS_DEBUG['indent'] = 0;
        } else {
            self::$_PHPCAS_DEBUG['indent']--;
        }
        $dbg = debug_backtrace();
        $str = '';
        if (is_object($res)) {
            $str .= '<= ' . get_class($res);
        } else {
            $str .= '<= ' . str_replace(array("\r\n", "\n", "\r"), "", var_export($res, true));
        }

        phpCAS :: log($str);
    }

    
    public static function traceExit()
    {
        phpCAS :: log('exit()');
        while (self::$_PHPCAS_DEBUG['indent'] > 0) {
            phpCAS :: log('-');
            self::$_PHPCAS_DEBUG['indent']--;
        }
    }

    
                

    
    public static function setLang($lang)
    {
        phpCAS::_validateClientExists();

        try {
            self::$_PHPCAS_CLIENT->setLang($lang);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }
    }

    
                

    
    public static function getVersion()
    {
        return PHPCAS_VERSION;
    }

    
                

    
    public static function setHTMLHeader($header)
    {
        phpCAS::_validateClientExists();

        try {
            self::$_PHPCAS_CLIENT->setHTMLHeader($header);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }
    }

    
    public static function setHTMLFooter($footer)
    {
        phpCAS::_validateClientExists();

        try {
            self::$_PHPCAS_CLIENT->setHTMLFooter($footer);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }
    }

    
                

    
    public static function setPGTStorage($storage)
    {
        phpCAS :: traceBegin();
        phpCAS::_validateProxyExists();

        try {
            self::$_PHPCAS_CLIENT->setPGTStorage($storage);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }
        phpCAS :: traceEnd();
    }

    
    public static function setPGTStorageDb($dsn_or_pdo, $username='',
        $password='', $table='', $driver_options=null
    ) {
        phpCAS :: traceBegin();
        phpCAS::_validateProxyExists();

        try {
            self::$_PHPCAS_CLIENT->setPGTStorageDb($dsn_or_pdo, $username, $password, $table, $driver_options);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }
        phpCAS :: traceEnd();
    }

    
    public static function setPGTStorageFile($path = '')
    {
        phpCAS :: traceBegin();
        phpCAS::_validateProxyExists();

        try {
            self::$_PHPCAS_CLIENT->setPGTStorageFile($path);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }
        phpCAS :: traceEnd();
    }
    
                

    
    public static function getProxiedService ($type)
    {
        phpCAS :: traceBegin();
        phpCAS::_validateProxyExists();

        try {
            $res = self::$_PHPCAS_CLIENT->getProxiedService($type);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }

        phpCAS :: traceEnd();
        return $res;
    }

    
    public static function initializeProxiedService (CAS_ProxiedService $proxiedService)
    {
        phpCAS::_validateProxyExists();

        try {
            self::$_PHPCAS_CLIENT->initializeProxiedService($proxiedService);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }
    }

    
    public static function serviceWeb($url, & $err_code, & $output)
    {
        phpCAS :: traceBegin();
        phpCAS::_validateProxyExists();

        try {
            $res = self::$_PHPCAS_CLIENT->serviceWeb($url, $err_code, $output);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }

        phpCAS :: traceEnd($res);
        return $res;
    }

    
    public static function serviceMail($url, $service, $flags, & $err_code, & $err_msg, & $pt)
    {
        phpCAS :: traceBegin();
        phpCAS::_validateProxyExists();

        try {
            $res = self::$_PHPCAS_CLIENT->serviceMail($url, $service, $flags, $err_code, $err_msg, $pt);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }

        phpCAS :: traceEnd($res);
        return $res;
    }

    
                

    
    public static function setCacheTimesForAuthRecheck($n)
    {
        phpCAS::_validateClientExists();

        try {
            self::$_PHPCAS_CLIENT->setCacheTimesForAuthRecheck($n);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }
    }

    
    public static function setPostAuthenticateCallback ($function, array $additionalArgs = array())
    {
        phpCAS::_validateClientExists();

        self::$_PHPCAS_CLIENT->setPostAuthenticateCallback($function, $additionalArgs);
    }

    
    public static function setSingleSignoutCallback ($function, array $additionalArgs = array())
    {
        phpCAS::_validateClientExists();

        self::$_PHPCAS_CLIENT->setSingleSignoutCallback($function, $additionalArgs);
    }

    
    public static function checkAuthentication()
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

        $auth = self::$_PHPCAS_CLIENT->checkAuthentication();

                self::$_PHPCAS_CLIENT->markAuthenticationCall($auth);

        phpCAS :: traceEnd($auth);
        return $auth;
    }

    
    public static function forceAuthentication()
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();
        $auth = self::$_PHPCAS_CLIENT->forceAuthentication();

                self::$_PHPCAS_CLIENT->markAuthenticationCall($auth);

        

        phpCAS :: traceEnd();
        return $auth;
    }

    
    public static function renewAuthentication()
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

        $auth = self::$_PHPCAS_CLIENT->renewAuthentication();

                self::$_PHPCAS_CLIENT->markAuthenticationCall($auth);

                phpCAS :: traceEnd();
    }

    
    public static function isAuthenticated()
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

                $auth = self::$_PHPCAS_CLIENT->isAuthenticated();

                self::$_PHPCAS_CLIENT->markAuthenticationCall($auth);

        phpCAS :: traceEnd($auth);
        return $auth;
    }

    
    public static function isSessionAuthenticated()
    {
        phpCAS::_validateClientExists();

        return (self::$_PHPCAS_CLIENT->isSessionAuthenticated());
    }

    
    public static function getUser()
    {
        phpCAS::_validateClientExists();

        try {
            return self::$_PHPCAS_CLIENT->getUser();
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }
    }

    
    public static function getAttributes()
    {
        phpCAS::_validateClientExists();

        try {
            return self::$_PHPCAS_CLIENT->getAttributes();
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }
    }

    
    public static function hasAttributes()
    {
        phpCAS::_validateClientExists();

        try {
            return self::$_PHPCAS_CLIENT->hasAttributes();
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }
    }

    
    public static function hasAttribute($key)
    {
        phpCAS::_validateClientExists();

        try {
            return self::$_PHPCAS_CLIENT->hasAttribute($key);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }
    }

    
    public static function getAttribute($key)
    {
        phpCAS::_validateClientExists();

        try {
            return self::$_PHPCAS_CLIENT->getAttribute($key);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }
    }

    
    public static function handleLogoutRequests($check_client = true, $allowed_clients = false)
    {
        phpCAS::_validateClientExists();

        return (self::$_PHPCAS_CLIENT->handleLogoutRequests($check_client, $allowed_clients));
    }

    
    public static function getServerLoginURL()
    {
        phpCAS::_validateClientExists();

        return self::$_PHPCAS_CLIENT->getServerLoginURL();
    }

    
    public static function setServerLoginURL($url = '')
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

        try {
            self::$_PHPCAS_CLIENT->setServerLoginURL($url);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }

        phpCAS :: traceEnd();
    }

    
    public static function setServerServiceValidateURL($url = '')
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

        try {
            self::$_PHPCAS_CLIENT->setServerServiceValidateURL($url);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }

        phpCAS :: traceEnd();
    }

    
    public static function setServerProxyValidateURL($url = '')
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

        try {
            self::$_PHPCAS_CLIENT->setServerProxyValidateURL($url);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }

        phpCAS :: traceEnd();
    }

    
    public static function setServerSamlValidateURL($url = '')
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

        try {
            self::$_PHPCAS_CLIENT->setServerSamlValidateURL($url);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }

        phpCAS :: traceEnd();
    }

    
    public static function getServerLogoutURL()
    {
        phpCAS::_validateClientExists();

        return self::$_PHPCAS_CLIENT->getServerLogoutURL();
    }

    
    public static function setServerLogoutURL($url = '')
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

        try {
            self::$_PHPCAS_CLIENT->setServerLogoutURL($url);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }

        phpCAS :: traceEnd();
    }

    
    public static function logout($params = "")
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

        $parsedParams = array ();
        if ($params != "") {
            if (is_string($params)) {
                phpCAS :: error('method `phpCAS::logout($url)\' is now deprecated, use `phpCAS::logoutWithUrl($url)\' instead');
            }
            if (!is_array($params)) {
                phpCAS :: error('type mismatched for parameter $params (should be `array\')');
            }
            foreach ($params as $key => $value) {
                if ($key != "service" && $key != "url") {
                    phpCAS :: error('only `url\' and `service\' parameters are allowed for method `phpCAS::logout($params)\'');
                }
                $parsedParams[$key] = $value;
            }
        }
        self::$_PHPCAS_CLIENT->logout($parsedParams);
                phpCAS :: traceEnd();
    }

    
    public static function logoutWithRedirectService($service)
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

        if (!is_string($service)) {
            phpCAS :: error('type mismatched for parameter $service (should be `string\')');
        }
        self::$_PHPCAS_CLIENT->logout(array ( "service" => $service ));
                phpCAS :: traceEnd();
    }

    
    public static function logoutWithUrl($url)
    {
        trigger_error('Function deprecated for cas servers >= 3.3.5.1', E_USER_DEPRECATED);
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::client() or' . __CLASS__ . '::proxy()');
        }
        if (!is_string($url)) {
            phpCAS :: error('type mismatched for parameter $url (should be `string\')');
        }
        self::$_PHPCAS_CLIENT->logout(array ( "url" => $url ));
                phpCAS :: traceEnd();
    }

    
    public static function logoutWithRedirectServiceAndUrl($service, $url)
    {
        trigger_error('Function deprecated for cas servers >= 3.3.5.1', E_USER_DEPRECATED);
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

        if (!is_string($service)) {
            phpCAS :: error('type mismatched for parameter $service (should be `string\')');
        }
        if (!is_string($url)) {
            phpCAS :: error('type mismatched for parameter $url (should be `string\')');
        }
        self::$_PHPCAS_CLIENT->logout(
            array (
                "service" => $service,
                "url" => $url
            )
        );
                phpCAS :: traceEnd();
    }

    
    public static function setFixedCallbackURL($url = '')
    {
        phpCAS :: traceBegin();
        phpCAS::_validateProxyExists();

        try {
            self::$_PHPCAS_CLIENT->setCallbackURL($url);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }

        phpCAS :: traceEnd();
    }

    
    public static function setFixedServiceURL($url)
    {
        phpCAS :: traceBegin();
        phpCAS::_validateProxyExists();

        try {
            self::$_PHPCAS_CLIENT->setURL($url);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }

        phpCAS :: traceEnd();
    }

    
    public static function getServiceURL()
    {
        phpCAS::_validateProxyExists();
        return (self::$_PHPCAS_CLIENT->getURL());
    }

    
    public static function retrievePT($target_service, & $err_code, & $err_msg)
    {
        phpCAS::_validateProxyExists();

        try {
            return (self::$_PHPCAS_CLIENT->retrievePT($target_service, $err_code, $err_msg));
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }
    }

    
    public static function setCasServerCACert($cert, $validate_cn = true)
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

        try {
            self::$_PHPCAS_CLIENT->setCasServerCACert($cert, $validate_cn);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }

        phpCAS :: traceEnd();
    }

    
    public static function setNoCasServerValidation()
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

        phpCAS :: trace('You have configured no validation of the legitimacy of the cas server. This is not recommended for production use.');
        self::$_PHPCAS_CLIENT->setNoCasServerValidation();
        phpCAS :: traceEnd();
    }


    
    public static function setNoClearTicketsFromUrl()
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

        self::$_PHPCAS_CLIENT->setNoClearTicketsFromUrl();
        phpCAS :: traceEnd();
    }

    

    
    public static function setExtraCurlOption($key, $value)
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

        self::$_PHPCAS_CLIENT->setExtraCurlOption($key, $value);
        phpCAS :: traceEnd();
    }

    
    public static function allowProxyChain(CAS_ProxyChain_Interface $proxy_chain)
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

        if (self::$_PHPCAS_CLIENT->getServerVersion() !== CAS_VERSION_2_0
            && self::$_PHPCAS_CLIENT->getServerVersion() !== CAS_VERSION_3_0
        ) {
            phpCAS :: error('this method can only be used with the cas 2.0/3.0 protocols');
        }
        self::$_PHPCAS_CLIENT->getAllowedProxyChains()->allowProxyChain($proxy_chain);
        phpCAS :: traceEnd();
    }

    
    public static function getProxies ()
    {
        phpCAS::_validateProxyExists();

        return(self::$_PHPCAS_CLIENT->getProxies());
    }

            
    
    public static function addRebroadcastNode($rebroadcastNodeUrl)
    {
        phpCAS::traceBegin();
        phpCAS::log('rebroadcastNodeUrl:'.$rebroadcastNodeUrl);
        phpCAS::_validateClientExists();

        try {
            self::$_PHPCAS_CLIENT->addRebroadcastNode($rebroadcastNodeUrl);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }

        phpCAS::traceEnd();
    }

    
    public static function addRebroadcastHeader($header)
    {
        phpCAS :: traceBegin();
        phpCAS::_validateClientExists();

        try {
            self::$_PHPCAS_CLIENT->addRebroadcastHeader($header);
        } catch (Exception $e) {
            phpCAS :: error(get_class($e) . ': ' . $e->getMessage());
        }

        phpCAS :: traceEnd();
    }

    
    private static function _validateClientExists()
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            throw new CAS_OutOfSequenceBeforeClientException();
        }
    }

    
    private static function _validateProxyExists()
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            throw new CAS_OutOfSequenceBeforeProxyException();
        }
    }
}











































































?>
