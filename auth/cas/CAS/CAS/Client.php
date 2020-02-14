<?php





class CAS_Client
{

                

    
    private function _htmlFilterOutput($str)
    {
        $str = str_replace('__CAS_VERSION__', $this->getServerVersion(), $str);
        $str = str_replace('__PHPCAS_VERSION__', phpCAS::getVersion(), $str);
        $str = str_replace('__SERVER_BASE_URL__', $this->_getServerBaseURL(), $str);
        echo $str;
    }

    
    private $_output_header = '';

    
    public function printHTMLHeader($title)
    {
        $this->_htmlFilterOutput(
            str_replace(
                '__TITLE__', $title,
                (empty($this->_output_header)
                ? '<html><head><title>__TITLE__</title></head><body><h1>__TITLE__</h1>'
                : $this->_output_header)
            )
        );
    }

    
    private $_output_footer = '';

    
    public function printHTMLFooter()
    {
        $lang = $this->getLangObj();
        $this->_htmlFilterOutput(
            empty($this->_output_footer)?
            (phpcas::getVerbose())?
                '<hr><address>phpCAS __PHPCAS_VERSION__ '
                .$lang->getUsingServer()
                .' <a href="__SERVER_BASE_URL__">__SERVER_BASE_URL__</a> (CAS __CAS_VERSION__)</a></address></body></html>'
                :'</body></html>'
            :$this->_output_footer
        );
    }

    
    public function setHTMLHeader($header)
    {
    	    	if (gettype($header) != 'string')
        	throw new CAS_TypeMismatchException($header, '$header', 'string');

        $this->_output_header = $header;
    }

    
    public function setHTMLFooter($footer)
    {
    	    	if (gettype($footer) != 'string')
        	throw new CAS_TypeMismatchException($footer, '$footer', 'string');

        $this->_output_footer = $footer;
    }


    


                
    
    private $_lang = PHPCAS_LANG_DEFAULT;

    
    public function setLang($lang)
    {
    	    	if (gettype($lang) != 'string')
        	throw new CAS_TypeMismatchException($lang, '$lang', 'string');

        phpCAS::traceBegin();
        $obj = new $lang();
        if (!($obj instanceof CAS_Languages_LanguageInterface)) {
            throw new CAS_InvalidArgumentException(
                '$className must implement the CAS_Languages_LanguageInterface'
            );
        }
        $this->_lang = $lang;
        phpCAS::traceEnd();
    }
    
    public function getLangObj()
    {
        $classname = $this->_lang;
        return new $classname();
    }

    
                

    
    private $_server = array(
        'version' => -1,
        'hostname' => 'none',
        'port' => -1,
        'uri' => 'none');

    
    public function getServerVersion()
    {
        return $this->_server['version'];
    }

    
    private function _getServerHostname()
    {
        return $this->_server['hostname'];
    }

    
    private function _getServerPort()
    {
        return $this->_server['port'];
    }

    
    private function _getServerURI()
    {
        return $this->_server['uri'];
    }

    
    private function _getServerBaseURL()
    {
                if ( empty($this->_server['base_url']) ) {
            $this->_server['base_url'] = 'https://' . $this->_getServerHostname();
            if ($this->_getServerPort()!=443) {
                $this->_server['base_url'] .= ':'
                .$this->_getServerPort();
            }
            $this->_server['base_url'] .= $this->_getServerURI();
        }
        return $this->_server['base_url'];
    }

    
    public function getServerLoginURL($gateway=false,$renew=false)
    {
        phpCAS::traceBegin();
                if ( empty($this->_server['login_url']) ) {
            $this->_server['login_url'] = $this->_buildQueryUrl($this->_getServerBaseURL().'login','service='.urlencode($this->getURL()));
        }
        $url = $this->_server['login_url'];
        if ($renew) {
                                    $url = $this->_buildQueryUrl($url, 'renew=true');
        } elseif ($gateway) {
                                    $url = $this->_buildQueryUrl($url, 'gateway=true');
        }
        phpCAS::traceEnd($url);
        return $url;
    }

    
    public function setServerLoginURL($url)
    {
    	    	if (gettype($url) != 'string')
        	throw new CAS_TypeMismatchException($url, '$url', 'string');

        return $this->_server['login_url'] = $url;
    }


    
    public function setServerServiceValidateURL($url)
    {
    	    	if (gettype($url) != 'string')
        	throw new CAS_TypeMismatchException($url, '$url', 'string');

        return $this->_server['service_validate_url'] = $url;
    }


    
    public function setServerProxyValidateURL($url)
    {
    	    	if (gettype($url) != 'string')
        	throw new CAS_TypeMismatchException($url, '$url', 'string');

        return $this->_server['proxy_validate_url'] = $url;
    }


    
    public function setServerSamlValidateURL($url)
    {
    	    	if (gettype($url) != 'string')
        	throw new CAS_TypeMismatchException($url, '$url', 'string');

        return $this->_server['saml_validate_url'] = $url;
    }


    
    public function getServerServiceValidateURL()
    {
        phpCAS::traceBegin();
                if ( empty($this->_server['service_validate_url']) ) {
            switch ($this->getServerVersion()) {
            case CAS_VERSION_1_0:
                $this->_server['service_validate_url'] = $this->_getServerBaseURL()
                .'validate';
                break;
            case CAS_VERSION_2_0:
                $this->_server['service_validate_url'] = $this->_getServerBaseURL()
                .'serviceValidate';
                break;
            case CAS_VERSION_3_0:
                $this->_server['service_validate_url'] = $this->_getServerBaseURL()
                .'p3/serviceValidate';
                break;
            }
        }
        $url = $this->_buildQueryUrl(
            $this->_server['service_validate_url'],
            'service='.urlencode($this->getURL())
        );
        phpCAS::traceEnd($url);
        return $url;
    }
    
    public function getServerSamlValidateURL()
    {
        phpCAS::traceBegin();
                if ( empty($this->_server['saml_validate_url']) ) {
            switch ($this->getServerVersion()) {
            case SAML_VERSION_1_1:
                $this->_server['saml_validate_url'] = $this->_getServerBaseURL().'samlValidate';
                break;
            }
        }

        $url = $this->_buildQueryUrl(
            $this->_server['saml_validate_url'],
            'TARGET='.urlencode($this->getURL())
        );
        phpCAS::traceEnd($url);
        return $url;
    }

    
    public function getServerProxyValidateURL()
    {
        phpCAS::traceBegin();
                if ( empty($this->_server['proxy_validate_url']) ) {
            switch ($this->getServerVersion()) {
            case CAS_VERSION_1_0:
                $this->_server['proxy_validate_url'] = '';
                break;
            case CAS_VERSION_2_0:
                $this->_server['proxy_validate_url'] = $this->_getServerBaseURL().'proxyValidate';
                break;
            case CAS_VERSION_3_0:
                $this->_server['proxy_validate_url'] = $this->_getServerBaseURL().'p3/proxyValidate';
                break;
            }
        }
        $url = $this->_buildQueryUrl(
            $this->_server['proxy_validate_url'],
            'service='.urlencode($this->getURL())
        );
        phpCAS::traceEnd($url);
        return $url;
    }


    
    public function getServerProxyURL()
    {
                if ( empty($this->_server['proxy_url']) ) {
            switch ($this->getServerVersion()) {
            case CAS_VERSION_1_0:
                $this->_server['proxy_url'] = '';
                break;
            case CAS_VERSION_2_0:
            case CAS_VERSION_3_0:
                $this->_server['proxy_url'] = $this->_getServerBaseURL().'proxy';
                break;
            }
        }
        return $this->_server['proxy_url'];
    }

    
    public function getServerLogoutURL()
    {
                if ( empty($this->_server['logout_url']) ) {
            $this->_server['logout_url'] = $this->_getServerBaseURL().'logout';
        }
        return $this->_server['logout_url'];
    }

    
    public function setServerLogoutURL($url)
    {
    	    	if (gettype($url) != 'string')
        	throw new CAS_TypeMismatchException($url, '$url', 'string');

        return $this->_server['logout_url'] = $url;
    }

    
    private $_curl_options = array();

    
    public function setExtraCurlOption($key, $value)
    {
        $this->_curl_options[$key] = $value;
    }

    

            
    

    
    private $_requestImplementation = 'CAS_Request_CurlRequest';

    
    public function setRequestImplementation ($className)
    {
        $obj = new $className;
        if (!($obj instanceof CAS_Request_RequestInterface)) {
            throw new CAS_InvalidArgumentException(
                '$className must implement the CAS_Request_RequestInterface'
            );
        }
        $this->_requestImplementation = $className;
    }

    
    private $_clearTicketsFromUrl = true;

    
    public function setNoClearTicketsFromUrl ()
    {
        $this->_clearTicketsFromUrl = false;
    }

    
    private $_postAuthenticateCallbackFunction = null;

    
    private $_postAuthenticateCallbackArgs = array();

    
    public function setPostAuthenticateCallback ($function, array $additionalArgs = array())
    {
        $this->_postAuthenticateCallbackFunction = $function;
        $this->_postAuthenticateCallbackArgs = $additionalArgs;
    }

    
    private $_signoutCallbackFunction = null;

    
    private $_signoutCallbackArgs = array();

    
    public function setSingleSignoutCallback ($function, array $additionalArgs = array())
    {
        $this->_signoutCallbackFunction = $function;
        $this->_signoutCallbackArgs = $additionalArgs;
    }

            
    
    public function ensureIsProxy()
    {
        if (!$this->isProxy()) {
            throw new CAS_OutOfSequenceBeforeProxyException();
        }
    }

    
    public function markAuthenticationCall ($auth)
    {
                $dbg = debug_backtrace();
        $this->_authentication_caller = array (
            'file' => $dbg[1]['file'],
            'line' => $dbg[1]['line'],
            'method' => $dbg[1]['class'] . '::' . $dbg[1]['function'],
            'result' => (boolean)$auth
        );
    }
    private $_authentication_caller;

    
    public function wasAuthenticationCalled ()
    {
        return !empty($this->_authentication_caller);
    }

    
    private function _ensureAuthenticationCalled()
    {
        if (!$this->wasAuthenticationCalled()) {
            throw new CAS_OutOfSequenceBeforeAuthenticationCallException();
        }
    }

    
    public function wasAuthenticationCallSuccessful ()
    {
        $this->_ensureAuthenticationCalled();
        return $this->_authentication_caller['result'];
    }


    
    public function ensureAuthenticationCallSuccessful()
    {
        $this->_ensureAuthenticationCalled();
        if (!$this->_authentication_caller['result']) {
            throw new CAS_OutOfSequenceException(
                'authentication was checked (by '
                . $this->getAuthenticationCallerMethod()
                . '() at ' . $this->getAuthenticationCallerFile()
                . ':' . $this->getAuthenticationCallerLine()
                . ') but the method returned false'
            );
        }
    }

    
    public function getAuthenticationCallerFile ()
    {
        $this->_ensureAuthenticationCalled();
        return $this->_authentication_caller['file'];
    }

    
    public function getAuthenticationCallerLine ()
    {
        $this->_ensureAuthenticationCalled();
        return $this->_authentication_caller['line'];
    }

    
    public function getAuthenticationCallerMethod ()
    {
        $this->_ensureAuthenticationCalled();
        return $this->_authentication_caller['method'];
    }

    

                

    
    public function __construct(
        $server_version,
        $proxy,
        $server_hostname,
        $server_port,
        $server_uri,
        $changeSessionID = true
    ) {
		        if (gettype($server_version) != 'string')
        	throw new CAS_TypeMismatchException($server_version, '$server_version', 'string');
        if (gettype($proxy) != 'boolean')
        	throw new CAS_TypeMismatchException($proxy, '$proxy', 'boolean');
        if (gettype($server_hostname) != 'string')
        	throw new CAS_TypeMismatchException($server_hostname, '$server_hostname', 'string');
        if (gettype($server_port) != 'integer')
        	throw new CAS_TypeMismatchException($server_port, '$server_port', 'integer');
        if (gettype($server_uri) != 'string')
        	throw new CAS_TypeMismatchException($server_uri, '$server_uri', 'string');
        if (gettype($changeSessionID) != 'boolean')
        	throw new CAS_TypeMismatchException($changeSessionID, '$changeSessionID', 'boolean');

        phpCAS::traceBegin();
                        $this->_setChangeSessionID($changeSessionID);

                if (session_id()=="" && !$this->_isLogoutRequest()) {
            session_start();
            phpCAS :: trace("Starting a new session " . session_id());
        }

                $this->_proxy = $proxy;

                if ($this->isProxy()) {
            if (!isset($_SESSION['phpCAS'])) {
                $_SESSION['phpCAS'] = array();
            }
            if (!isset($_SESSION['phpCAS']['service_cookies'])) {
                $_SESSION['phpCAS']['service_cookies'] = array();
            }
            $this->_serviceCookieJar = new CAS_CookieJar(
                $_SESSION['phpCAS']['service_cookies']
            );
        }

                switch ($server_version) {
        case CAS_VERSION_1_0:
            if ( $this->isProxy() ) {
                phpCAS::error(
                    'CAS proxies are not supported in CAS '.$server_version
                );
            }
            break;
        case CAS_VERSION_2_0:
        case CAS_VERSION_3_0:
            break;
        case SAML_VERSION_1_1:
            break;
        default:
            phpCAS::error(
                'this version of CAS (`'.$server_version
                .'\') is not supported by phpCAS '.phpCAS::getVersion()
            );
        }
        $this->_server['version'] = $server_version;

                if ( empty($server_hostname)
            || !preg_match('/[\.\d\-abcdefghijklmnopqrstuvwxyz]*/', $server_hostname)
        ) {
            phpCAS::error('bad CAS server hostname (`'.$server_hostname.'\')');
        }
        $this->_server['hostname'] = $server_hostname;

                if ( $server_port == 0
            || !is_int($server_port)
        ) {
            phpCAS::error('bad CAS server port (`'.$server_hostname.'\')');
        }
        $this->_server['port'] = $server_port;

                if ( !preg_match('/[\.\d\-_abcdefghijklmnopqrstuvwxyz\/]*/', $server_uri) ) {
            phpCAS::error('bad CAS server URI (`'.$server_uri.'\')');
        }
                if(strstr($server_uri, '?') === false) $server_uri .= '/';
        $server_uri = preg_replace('/\/\//', '/', '/'.$server_uri);
        $this->_server['uri'] = $server_uri;

                if ( $this->isProxy() ) {
            $this->_setCallbackMode(!empty($_GET['pgtIou'])&&!empty($_GET['pgtId']));
        }

        if ( $this->_isCallbackMode() ) {
                        if ( !$this->_isHttps() ) {
                phpCAS::error(
                    'CAS proxies must be secured to use phpCAS; PGT\'s will not be received from the CAS server'
                );
            }
        } else {
                                    $ticket = (isset($_GET['ticket']) ? $_GET['ticket'] : null);
            if (preg_match('/^[SP]T-/', $ticket) ) {
                phpCAS::trace('Ticket \''.$ticket.'\' found');
                $this->setTicket($ticket);
                unset($_GET['ticket']);
            } else if ( !empty($ticket) ) {
                                phpCAS::error(
                    'ill-formed ticket found in the URL (ticket=`'
                    .htmlentities($ticket).'\')'
                );
            }

        }
        phpCAS::traceEnd();
    }

    

                    
    


    
    private $_change_session_id = true;

    
    private function _setChangeSessionID($allowed)
    {
        $this->_change_session_id = $allowed;
    }

    
    public function getChangeSessionID()
    {
        return $this->_change_session_id;
    }

    

                    
    

    
    private $_user = '';

    
    private function _setUser($user)
    {
        $this->_user = $user;
    }

    
    public function getUser()
    {
    	    	$this->ensureAuthenticationCallSuccessful();

    	return $this->_getUser();
    }

    
    private function _getUser()
    {
    	        if ( empty($this->_user) ) {
            phpCAS::error(
                'this method should be used only after '.__CLASS__
                .'::forceAuthentication() or '.__CLASS__.'::isAuthenticated()'
            );
        }
        return $this->_user;
    }

    
    private $_attributes = array();

    
    public function setAttributes($attributes)
    {
        $this->_attributes = $attributes;
    }

    
    public function getAttributes()
    {
    	    	$this->ensureAuthenticationCallSuccessful();
    	        if ( empty($this->_user) ) {
                        phpCAS::error(
                'this method should be used only after '.__CLASS__
                .'::forceAuthentication() or '.__CLASS__.'::isAuthenticated()'
            );
        }
        return $this->_attributes;
    }

    
    public function hasAttributes()
    {
    	    	$this->ensureAuthenticationCallSuccessful();

        return !empty($this->_attributes);
    }
    
    public function hasAttribute($key)
    {
    	    	$this->ensureAuthenticationCallSuccessful();

        return $this->_hasAttribute($key);
    }

    
    private function _hasAttribute($key)
    {
        return (is_array($this->_attributes)
            && array_key_exists($key, $this->_attributes));
    }

    
    public function getAttribute($key)
    {
    	    	$this->ensureAuthenticationCallSuccessful();

        if ($this->_hasAttribute($key)) {
            return $this->_attributes[$key];
        }
    }

    
    public function renewAuthentication()
    {
        phpCAS::traceBegin();
                if (isset( $_SESSION['phpCAS']['auth_checked'])) {
            unset($_SESSION['phpCAS']['auth_checked']);
        }
        if ( $this->isAuthenticated(true) ) {
            phpCAS::trace('user already authenticated');
            $res = true;
        } else {
            $this->redirectToCas(false, true);
                        $res = false;
        }
        phpCAS::traceEnd();
        return $res;
    }

    
    public function forceAuthentication()
    {
        phpCAS::traceBegin();

        if ( $this->isAuthenticated() ) {
                        phpCAS::trace('no need to authenticate');
            $res = true;
        } else {
                        if (isset($_SESSION['phpCAS']['auth_checked'])) {
                unset($_SESSION['phpCAS']['auth_checked']);
            }
            $this->redirectToCas(false);
                        $res = false;
        }
        phpCAS::traceEnd($res);
        return $res;
    }

    
    private $_cache_times_for_auth_recheck = 0;

    
    public function setCacheTimesForAuthRecheck($n)
    {
    	if (gettype($n) != 'integer')
        	throw new CAS_TypeMismatchException($n, '$n', 'string');

        $this->_cache_times_for_auth_recheck = $n;
    }

    
    public function checkAuthentication()
    {
        phpCAS::traceBegin();
        $res = false;
        if ( $this->isAuthenticated() ) {
            phpCAS::trace('user is authenticated');
            
            unset($_SESSION['phpCAS']['auth_checked']);
            $res = true;
        } else if (isset($_SESSION['phpCAS']['auth_checked'])) {
                                    unset($_SESSION['phpCAS']['auth_checked']);
            $res = false;
        } else {
                        if (!isset($_SESSION['phpCAS']['unauth_count'])) {
                $_SESSION['phpCAS']['unauth_count'] = -2;             }

            if (($_SESSION['phpCAS']['unauth_count'] != -2
                && $this->_cache_times_for_auth_recheck == -1)
                || ($_SESSION['phpCAS']['unauth_count'] >= 0
                && $_SESSION['phpCAS']['unauth_count'] < $this->_cache_times_for_auth_recheck)
            ) {
                $res = false;

                if ($this->_cache_times_for_auth_recheck != -1) {
                    $_SESSION['phpCAS']['unauth_count']++;
                    phpCAS::trace(
                        'user is not authenticated (cached for '
                        .$_SESSION['phpCAS']['unauth_count'].' times of '
                        .$this->_cache_times_for_auth_recheck.')'
                    );
                } else {
                    phpCAS::trace(
                        'user is not authenticated (cached for until login pressed)'
                    );
                }
            } else {
                $_SESSION['phpCAS']['unauth_count'] = 0;
                $_SESSION['phpCAS']['auth_checked'] = true;
                phpCAS::trace('user is not authenticated (cache reset)');
                $this->redirectToCas(true);
                                $res = false;
            }
        }
        phpCAS::traceEnd($res);
        return $res;
    }

    
    public function isAuthenticated($renew=false)
    {
        phpCAS::traceBegin();
        $res = false;
        $validate_url = '';
        if ( $this->_wasPreviouslyAuthenticated() ) {
            if ($this->hasTicket()) {
                                phpCAS::trace(
                    'ticket was present and will be discarded, use renewAuthenticate()'
                );
                if ($this->_clearTicketsFromUrl) {
                    phpCAS::trace("Prepare redirect to : ".$this->getURL());
                    session_write_close();
                    header('Location: '.$this->getURL());
                    flush();
                    phpCAS::traceExit();
                    throw new CAS_GracefullTerminationException();
                } else {
                    phpCAS::trace(
                        'Already authenticated, but skipping ticket clearing since setNoClearTicketsFromUrl() was used.'
                    );
                    $res = true;
                }
            } else {
                                                phpCAS::trace(
                    'user was already authenticated, no need to look for tickets'
                );
                $res = true;
            }

                                    $this->markAuthenticationCall($res);
        } else {
            if ($this->hasTicket()) {
                switch ($this->getServerVersion()) {
                case CAS_VERSION_1_0:
                                        phpCAS::trace(
                        'CAS 1.0 ticket `'.$this->getTicket().'\' is present'
                    );
                    $this->validateCAS10(
                        $validate_url, $text_response, $tree_response, $renew
                    );                     phpCAS::trace(
                        'CAS 1.0 ticket `'.$this->getTicket().'\' was validated'
                    );
                    $_SESSION['phpCAS']['user'] = $this->_getUser();
                    $res = true;
                    $logoutTicket = $this->getTicket();
                    break;
                case CAS_VERSION_2_0:
                case CAS_VERSION_3_0:
                                        phpCAS::trace(
                        'CAS '.$this->getServerVersion().' ticket `'.$this->getTicket().'\' is present'
                    );
                    $this->validateCAS20(
                        $validate_url, $text_response, $tree_response, $renew
                    );                     phpCAS::trace(
                        'CAS '.$this->getServerVersion().' ticket `'.$this->getTicket().'\' was validated'
                    );
                    if ( $this->isProxy() ) {
                        $this->_validatePGT(
                            $validate_url, $text_response, $tree_response
                        );                         phpCAS::trace('PGT `'.$this->_getPGT().'\' was validated');
                        $_SESSION['phpCAS']['pgt'] = $this->_getPGT();
                    }
                    $_SESSION['phpCAS']['user'] = $this->_getUser();
                    if (!empty($this->_attributes)) {
                        $_SESSION['phpCAS']['attributes'] = $this->_attributes;
                    }
                    $proxies = $this->getProxies();
                    if (!empty($proxies)) {
                        $_SESSION['phpCAS']['proxies'] = $this->getProxies();
                    }
                    $res = true;
                    $logoutTicket = $this->getTicket();
                    break;
                case SAML_VERSION_1_1:
                                        phpCAS::trace(
                        'SAML 1.1 ticket `'.$this->getTicket().'\' is present'
                    );
                    $this->validateSA(
                        $validate_url, $text_response, $tree_response, $renew
                    );                     phpCAS::trace(
                        'SAML 1.1 ticket `'.$this->getTicket().'\' was validated'
                    );
                    $_SESSION['phpCAS']['user'] = $this->_getUser();
                    $_SESSION['phpCAS']['attributes'] = $this->_attributes;
                    $res = true;
                    $logoutTicket = $this->getTicket();
                    break;
                default:
                    phpCAS::trace('Protocoll error');
                    break;
                }
            } else {
                                phpCAS::trace('no ticket found');
            }

                                    $this->markAuthenticationCall($res);

            if ($res) {
                                if ($this->_postAuthenticateCallbackFunction) {
                    $args = $this->_postAuthenticateCallbackArgs;
                    array_unshift($args, $logoutTicket);
                    call_user_func_array(
                        $this->_postAuthenticateCallbackFunction, $args
                    );
                }

                                                                                                                if ($this->_clearTicketsFromUrl) {
                    phpCAS::trace("Prepare redirect to : ".$this->getURL());
                    session_write_close();
                    header('Location: '.$this->getURL());
                    flush();
                    phpCAS::traceExit();
                    throw new CAS_GracefullTerminationException();
                }
            }
        }
        phpCAS::traceEnd($res);
        return $res;
    }

    
    public function isSessionAuthenticated ()
    {
        return !empty($_SESSION['phpCAS']['user']);
    }

    
    private function _wasPreviouslyAuthenticated()
    {
        phpCAS::traceBegin();

        if ( $this->_isCallbackMode() ) {
                        if ($this->_rebroadcast&&!isset($_POST['rebroadcast'])) {
                $this->_rebroadcast(self::PGTIOU);
            }
            $this->_callback();
        }

        $auth = false;

        if ( $this->isProxy() ) {
                        if ( $this->isSessionAuthenticated()
                && !empty($_SESSION['phpCAS']['pgt'])
            ) {
                                $this->_setUser($_SESSION['phpCAS']['user']);
                if (isset($_SESSION['phpCAS']['attributes'])) {
                    $this->setAttributes($_SESSION['phpCAS']['attributes']);
                }
                $this->_setPGT($_SESSION['phpCAS']['pgt']);
                phpCAS::trace(
                    'user = `'.$_SESSION['phpCAS']['user'].'\', PGT = `'
                    .$_SESSION['phpCAS']['pgt'].'\''
                );

                                if (isset($_SESSION['phpCAS']['proxies'])) {
                    $this->_setProxies($_SESSION['phpCAS']['proxies']);
                    phpCAS::trace(
                        'proxies = "'
                        .implode('", "', $_SESSION['phpCAS']['proxies']).'"'
                    );
                }

                $auth = true;
            } elseif ( $this->isSessionAuthenticated()
                && empty($_SESSION['phpCAS']['pgt'])
            ) {
                                phpCAS::trace(
                    'username found (`'.$_SESSION['phpCAS']['user']
                    .'\') but PGT is empty'
                );
                                unset($_SESSION['phpCAS']);
                $this->setTicket('');
            } elseif ( !$this->isSessionAuthenticated()
                && !empty($_SESSION['phpCAS']['pgt'])
            ) {
                                phpCAS::trace(
                    'PGT found (`'.$_SESSION['phpCAS']['pgt']
                    .'\') but username is empty'
                );
                                unset($_SESSION['phpCAS']);
                $this->setTicket('');
            } else {
                phpCAS::trace('neither user nor PGT found');
            }
        } else {
                        if ( $this->isSessionAuthenticated() ) {
                                $this->_setUser($_SESSION['phpCAS']['user']);
                if (isset($_SESSION['phpCAS']['attributes'])) {
                    $this->setAttributes($_SESSION['phpCAS']['attributes']);
                }
                phpCAS::trace('user = `'.$_SESSION['phpCAS']['user'].'\'');

                                if (isset($_SESSION['phpCAS']['proxies'])) {
                    $this->_setProxies($_SESSION['phpCAS']['proxies']);
                    phpCAS::trace(
                        'proxies = "'
                        .implode('", "', $_SESSION['phpCAS']['proxies']).'"'
                    );
                }

                $auth = true;
            } else {
                phpCAS::trace('no user found');
            }
        }

        phpCAS::traceEnd($auth);
        return $auth;
    }

    
    public function redirectToCas($gateway=false,$renew=false)
    {
        phpCAS::traceBegin();
        $cas_url = $this->getServerLoginURL($gateway, $renew);
        session_write_close();
        if (php_sapi_name() === 'cli') {
            @header('Location: '.$cas_url);
        } else {
            header('Location: '.$cas_url);
        }
        phpCAS::trace("Redirect to : ".$cas_url);
        $lang = $this->getLangObj();
        $this->printHTMLHeader($lang->getAuthenticationWanted());
        printf('<p>'. $lang->getShouldHaveBeenRedirected(). '</p>', $cas_url);
        $this->printHTMLFooter();
        phpCAS::traceExit();
        throw new CAS_GracefullTerminationException();
    }


    
    public function logout($params)
    {
        phpCAS::traceBegin();
        $cas_url = $this->getServerLogoutURL();
        $paramSeparator = '?';
        if (isset($params['url'])) {
            $cas_url = $cas_url . $paramSeparator . "url="
                . urlencode($params['url']);
            $paramSeparator = '&';
        }
        if (isset($params['service'])) {
            $cas_url = $cas_url . $paramSeparator . "service="
                . urlencode($params['service']);
        }
        header('Location: '.$cas_url);
        phpCAS::trace("Prepare redirect to : ".$cas_url);

        session_unset();
        session_destroy();
        $lang = $this->getLangObj();
        $this->printHTMLHeader($lang->getLogout());
        printf('<p>'.$lang->getShouldHaveBeenRedirected(). '</p>', $cas_url);
        $this->printHTMLFooter();
        phpCAS::traceExit();
        throw new CAS_GracefullTerminationException();
    }

    
    private function _isLogoutRequest()
    {
        return !empty($_POST['logoutRequest']);
    }

    
    public function handleLogoutRequests($check_client=true, $allowed_clients=false)
    {
        phpCAS::traceBegin();
        if (!$this->_isLogoutRequest()) {
            phpCAS::trace("Not a logout request");
            phpCAS::traceEnd();
            return;
        }
        if (!$this->getChangeSessionID()
            && is_null($this->_signoutCallbackFunction)
        ) {
            phpCAS::trace(
                "phpCAS can't handle logout requests if it is not allowed to change session_id."
            );
        }
        phpCAS::trace("Logout requested");
        $decoded_logout_rq = urldecode($_POST['logoutRequest']);
        phpCAS::trace("SAML REQUEST: ".$decoded_logout_rq);
        $allowed = false;
        if ($check_client) {
            if (!$allowed_clients) {
                $allowed_clients = array( $this->_getServerHostname() );
            }
            $client_ip = $_SERVER['REMOTE_ADDR'];
            $client = gethostbyaddr($client_ip);
            phpCAS::trace("Client: ".$client."/".$client_ip);
            foreach ($allowed_clients as $allowed_client) {
                if (($client == $allowed_client)
                    || ($client_ip == $allowed_client)
                ) {
                    phpCAS::trace(
                        "Allowed client '".$allowed_client
                        ."' matches, logout request is allowed"
                    );
                    $allowed = true;
                    break;
                } else {
                    phpCAS::trace(
                        "Allowed client '".$allowed_client."' does not match"
                    );
                }
            }
        } else {
            phpCAS::trace("No access control set");
            $allowed = true;
        }
                if ($allowed) {
            phpCAS::trace("Logout command allowed");
                        if ($this->_rebroadcast && !isset($_POST['rebroadcast'])) {
                $this->_rebroadcast(self::LOGOUT);
            }
                        preg_match(
                "|<samlp:SessionIndex>(.*)</samlp:SessionIndex>|",
                $decoded_logout_rq, $tick, PREG_OFFSET_CAPTURE, 3
            );
            $wrappedSamlSessionIndex = preg_replace(
                '|<samlp:SessionIndex>|', '', $tick[0][0]
            );
            $ticket2logout = preg_replace(
                '|</samlp:SessionIndex>|', '', $wrappedSamlSessionIndex
            );
            phpCAS::trace("Ticket to logout: ".$ticket2logout);

                        if ($this->_signoutCallbackFunction) {
                $args = $this->_signoutCallbackArgs;
                array_unshift($args, $ticket2logout);
                call_user_func_array($this->_signoutCallbackFunction, $args);
            }

                                    if ($this->getChangeSessionID()) {
                $session_id = preg_replace('/[^a-zA-Z0-9\-]/', '', $ticket2logout);
                phpCAS::trace("Session id: ".$session_id);

                                if (session_id() !== "") {
                    session_unset();
                    session_destroy();
                }
                                session_id($session_id);
                $_COOKIE[session_name()]=$session_id;
                $_GET[session_name()]=$session_id;

                                session_start();
                session_unset();
                session_destroy();
                phpCAS::trace("Session ". $session_id . " destroyed");
            }
        } else {
            phpCAS::error("Unauthorized logout request from client '".$client."'");
            phpCAS::trace("Unauthorized logout request from client '".$client."'");
        }
        flush();
        phpCAS::traceExit();
        throw new CAS_GracefullTerminationException();

    }

    

                    
                

    
    private $_ticket = '';

    
    public  function getTicket()
    {
        return $this->_ticket;
    }

    
    public function setTicket($st)
    {
        $this->_ticket = $st;
    }

    
    public function hasTicket()
    {
        return !empty($this->_ticket);
    }

    

                

    
    private $_cas_server_ca_cert = null;


    

    private $_cas_server_cn_validate = true;

    
    private $_no_cas_server_validation = false;


    
    public function setCasServerCACert($cert, $validate_cn)
    {
    	    	if (gettype($cert) != 'string')
        	throw new CAS_TypeMismatchException($cert, '$cert', 'string');
        if (gettype($validate_cn) != 'boolean')
        	throw new CAS_TypeMismatchException($validate_cn, '$validate_cn', 'boolean');

        $this->_cas_server_ca_cert = $cert;
        $this->_cas_server_cn_validate = $validate_cn;
    }

    
    public function setNoCasServerValidation()
    {
        $this->_no_cas_server_validation = true;
    }

    
    public function validateCAS10(&$validate_url,&$text_response,&$tree_response,$renew=false)
    {
        phpCAS::traceBegin();
        $result = false;
                $validate_url = $this->getServerServiceValidateURL()
            .'&ticket='.urlencode($this->getTicket());

        if ( $renew ) {
                        $validate_url .= '&renew=true';
        }

                if ( !$this->_readURL($validate_url, $headers, $text_response, $err_msg) ) {
            phpCAS::trace(
                'could not open URL \''.$validate_url.'\' to validate ('.$err_msg.')'
            );
            throw new CAS_AuthenticationException(
                $this, 'CAS 1.0 ticket not validated', $validate_url,
                true
            );
            $result = false;
        }

        if (preg_match('/^no\n/', $text_response)) {
            phpCAS::trace('Ticket has not been validated');
            throw new CAS_AuthenticationException(
                $this, 'ST not validated', $validate_url, false,
                false, $text_response
            );
            $result = false;
        } else if (!preg_match('/^yes\n/', $text_response)) {
            phpCAS::trace('ill-formed response');
            throw new CAS_AuthenticationException(
                $this, 'Ticket not validated', $validate_url,
                false, true, $text_response
            );
            $result = false;
        }
                $arr = preg_split('/\n/', $text_response);
        $this->_setUser(trim($arr[1]));
        $result = true;

        if ($result) {
            $this->_renameSession($this->getTicket());
        }
                phpCAS::traceEnd(true);
        return true;
    }

    


                

    
    public function validateSA(&$validate_url,&$text_response,&$tree_response,$renew=false)
    {
        phpCAS::traceBegin();
        $result = false;
                $validate_url = $this->getServerSamlValidateURL();

        if ( $renew ) {
                        $validate_url .= '&renew=true';
        }

                if ( !$this->_readURL($validate_url, $headers, $text_response, $err_msg) ) {
            phpCAS::trace(
                'could not open URL \''.$validate_url.'\' to validate ('.$err_msg.')'
            );
            throw new CAS_AuthenticationException(
                $this, 'SA not validated', $validate_url, true
            );
        }

        phpCAS::trace('server version: '.$this->getServerVersion());

                switch ($this->getServerVersion()) {
        case SAML_VERSION_1_1:
                        $dom = new DOMDocument();
                        $dom->preserveWhiteSpace = false;
                        if (!($dom->loadXML($text_response))) {
                phpCAS::trace('dom->loadXML() failed');
                throw new CAS_AuthenticationException(
                    $this, 'SA not validated', $validate_url,
                    false, true,
                    $text_response
                );
                $result = false;
            }
                        if (!($tree_response = $dom->documentElement)) {
                phpCAS::trace('documentElement() failed');
                throw new CAS_AuthenticationException(
                    $this, 'SA not validated', $validate_url,
                    false, true,
                    $text_response
                );
                $result = false;
            } else if ( $tree_response->localName != 'Envelope' ) {
                                phpCAS::trace(
                    'bad XML root node (should be `Envelope\' instead of `'
                    .$tree_response->localName.'\''
                );
                throw new CAS_AuthenticationException(
                    $this, 'SA not validated', $validate_url,
                    false, true,
                    $text_response
                );
                $result = false;
            } else if ($tree_response->getElementsByTagName("NameIdentifier")->length != 0) {
                                $success_elements = $tree_response->getElementsByTagName("NameIdentifier");
                phpCAS::trace('NameIdentifier found');
                $user = trim($success_elements->item(0)->nodeValue);
                phpCAS::trace('user = `'.$user.'`');
                $this->_setUser($user);
                $this->_setSessionAttributes($text_response);
                $result = true;
            } else {
                phpCAS::trace('no <NameIdentifier> tag found in SAML payload');
                throw new CAS_AuthenticationException(
                    $this, 'SA not validated', $validate_url,
                    false, true,
                    $text_response
                );
                $result = false;
            }
        }
        if ($result) {
            $this->_renameSession($this->getTicket());
        }
                phpCAS::traceEnd($result);
        return $result;
    }

    
    private function _setSessionAttributes($text_response)
    {
        phpCAS::traceBegin();

        $result = false;

        $attr_array = array();

                $dom = new DOMDocument();
                $dom->preserveWhiteSpace = false;
        if (($dom->loadXML($text_response))) {
            $xPath = new DOMXpath($dom);
            $xPath->registerNamespace('samlp', 'urn:oasis:names:tc:SAML:1.0:protocol');
            $xPath->registerNamespace('saml', 'urn:oasis:names:tc:SAML:1.0:assertion');
            $nodelist = $xPath->query("//saml:Attribute");

            if ($nodelist) {
                foreach ($nodelist as $node) {
                    $xres = $xPath->query("saml:AttributeValue", $node);
                    $name = $node->getAttribute("AttributeName");
                    $value_array = array();
                    foreach ($xres as $node2) {
                        $value_array[] = $node2->nodeValue;
                    }
                    $attr_array[$name] = $value_array;
                }
                                foreach ($attr_array as $attr_key => $attr_value) {
                    if (count($attr_value) > 1) {
                        $this->_attributes[$attr_key] = $attr_value;
                        phpCAS::trace("* " . $attr_key . "=" . print_r($attr_value, true));
                    } else {
                        $this->_attributes[$attr_key] = $attr_value[0];
                        phpCAS::trace("* " . $attr_key . "=" . $attr_value[0]);
                    }
                }
                $result = true;
            } else {
                phpCAS::trace("SAML Attributes are empty");
                $result = false;
            }
        }
        phpCAS::traceEnd($result);
        return $result;
    }

    

                    
                

    
    private $_proxy;

    
    private $_serviceCookieJar;

    
    public function isProxy()
    {
        return $this->_proxy;
    }


    
                

    
    private $_pgt = '';

    
    private function _getPGT()
    {
        return $this->_pgt;
    }

    
    private function _setPGT($pgt)
    {
        $this->_pgt = $pgt;
    }

    
    private function _hasPGT()
    {
        return !empty($this->_pgt);
    }

    

                
    

    
    private $_callback_mode = false;

    
    private function _setCallbackMode($callback_mode)
    {
        $this->_callback_mode = $callback_mode;
    }

    
    private function _isCallbackMode()
    {
        return $this->_callback_mode;
    }

    
    private $_callback_url = '';

    
    private function _getCallbackURL()
    {
                if ( empty($this->_callback_url) ) {
            $final_uri = '';
                        $final_uri = 'https://';
            $final_uri .= $this->_getClientUrl();
            $request_uri = $_SERVER['REQUEST_URI'];
            $request_uri = preg_replace('/\?.*$/', '', $request_uri);
            $final_uri .= $request_uri;
            $this->_callback_url = $final_uri;
        }
        return $this->_callback_url;
    }

    
    public function setCallbackURL($url)
    {
    	        $this->ensureIsProxy();
    	    	if (gettype($url) != 'string')
        	throw new CAS_TypeMismatchException($url, '$url', 'string');

        return $this->_callback_url = $url;
    }

    
    private function _callback()
    {
        phpCAS::traceBegin();
        if (preg_match('/PGTIOU-[\.\-\w]/', $_GET['pgtIou'])) {
            if (preg_match('/[PT]GT-[\.\-\w]/', $_GET['pgtId'])) {
                $this->printHTMLHeader('phpCAS callback');
                $pgt_iou = $_GET['pgtIou'];
                $pgt = $_GET['pgtId'];
                phpCAS::trace('Storing PGT `'.$pgt.'\' (id=`'.$pgt_iou.'\')');
                echo '<p>Storing PGT `'.$pgt.'\' (id=`'.$pgt_iou.'\').</p>';
                $this->_storePGT($pgt, $pgt_iou);
                $this->printHTMLFooter();
                phpCAS::traceExit("Successfull Callback");
            } else {
                phpCAS::error('PGT format invalid' . $_GET['pgtId']);
                phpCAS::traceExit('PGT format invalid' . $_GET['pgtId']);
            }
        } else {
            phpCAS::error('PGTiou format invalid' . $_GET['pgtIou']);
            phpCAS::traceExit('PGTiou format invalid' . $_GET['pgtIou']);
        }

                                flush();
        throw new CAS_GracefullTerminationException();
    }


    

                

    
    private $_pgt_storage = null;

    
    private function _initPGTStorage()
    {
                if ( !is_object($this->_pgt_storage) ) {
            $this->setPGTStorageFile();
        }

                $this->_pgt_storage->init();
    }

    
    private function _storePGT($pgt,$pgt_iou)
    {
                $this->_initPGTStorage();
                $this->_pgt_storage->write($pgt, $pgt_iou);
    }

    
    private function _loadPGT($pgt_iou)
    {
                $this->_initPGTStorage();
                return $this->_pgt_storage->read($pgt_iou);
    }

    
    public function setPGTStorage($storage)
    {
    	        $this->ensureIsProxy();

                if ( is_object($this->_pgt_storage) ) {
            phpCAS::error('PGT storage already defined');
        }

                if ( !($storage instanceof CAS_PGTStorage_AbstractStorage) )
            throw new CAS_TypeMismatchException($storage, '$storage', 'CAS_PGTStorage_AbstractStorage object');

                $this->_pgt_storage = $storage;
    }

    
    public function setPGTStorageDb(
        $dsn_or_pdo, $username='', $password='', $table='', $driver_options=null
    ) {
    	        $this->ensureIsProxy();

    	    	if ((is_object($dsn_or_pdo) && !($dsn_or_pdo instanceof PDO)) || gettype($dsn_or_pdo) != 'string')
			throw new CAS_TypeMismatchException($dsn_or_pdo, '$dsn_or_pdo', 'string or PDO object');
    	if (gettype($username) != 'string')
        	throw new CAS_TypeMismatchException($username, '$username', 'string');
        if (gettype($password) != 'string')
        	throw new CAS_TypeMismatchException($password, '$password', 'string');
        if (gettype($table) != 'string')
        	throw new CAS_TypeMismatchException($table, '$password', 'string');

                $this->setPGTStorage(
            new CAS_PGTStorage_Db(
                $this, $dsn_or_pdo, $username, $password, $table, $driver_options
            )
        );
    }

    
    public function setPGTStorageFile($path='')
    {
    	        $this->ensureIsProxy();

    	    	if (gettype($path) != 'string')
        	throw new CAS_TypeMismatchException($path, '$path', 'string');

                $this->setPGTStorage(new CAS_PGTStorage_File($this, $path));
    }


                
    private function _validatePGT(&$validate_url,$text_response,$tree_response)
    {
        phpCAS::traceBegin();
        if ( $tree_response->getElementsByTagName("proxyGrantingTicket")->length == 0) {
            phpCAS::trace('<proxyGrantingTicket> not found');
                        throw new CAS_AuthenticationException(
                $this, 'Ticket validated but no PGT Iou transmitted',
                $validate_url, false, false,
                $text_response
            );
        } else {
                        $pgt_iou = trim(
                $tree_response->getElementsByTagName("proxyGrantingTicket")->item(0)->nodeValue
            );
            if (preg_match('/PGTIOU-[\.\-\w]/', $pgt_iou)) {
                $pgt = $this->_loadPGT($pgt_iou);
                if ( $pgt == false ) {
                    phpCAS::trace('could not load PGT');
                    throw new CAS_AuthenticationException(
                        $this,
                        'PGT Iou was transmitted but PGT could not be retrieved',
                        $validate_url, false,
                        false, $text_response
                    );
                }
                $this->_setPGT($pgt);
            } else {
                phpCAS::trace('PGTiou format error');
                throw new CAS_AuthenticationException(
                    $this, 'PGT Iou was transmitted but has wrong format',
                    $validate_url, false, false,
                    $text_response
                );
            }
        }
        phpCAS::traceEnd(true);
        return true;
    }

            
    
    public function retrievePT($target_service,&$err_code,&$err_msg)
    {
    	    	if (gettype($target_service) != 'string')
        	throw new CAS_TypeMismatchException($target_service, '$target_service', 'string');

        phpCAS::traceBegin();

                                        $err_msg = '';

                $cas_url = $this->getServerProxyURL().'?targetService='
            .urlencode($target_service).'&pgt='.$this->_getPGT();

                if ( !$this->_readURL($cas_url, $headers, $cas_response, $err_msg) ) {
            phpCAS::trace(
                'could not open URL \''.$cas_url.'\' to validate ('.$err_msg.')'
            );
            $err_code = PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE;
            $err_msg = 'could not retrieve PT (no response from the CAS server)';
            phpCAS::traceEnd(false);
            return false;
        }

        $bad_response = false;

        if ( !$bad_response ) {
                        $dom = new DOMDocument();
                        $dom->preserveWhiteSpace = false;
                        if ( !($dom->loadXML($cas_response))) {
                phpCAS::trace('dom->loadXML() failed');
                                $bad_response = true;
            }
        }

        if ( !$bad_response ) {
                        if ( !($root = $dom->documentElement) ) {
                phpCAS::trace('documentElement failed');
                                $bad_response = true;
            }
        }

        if ( !$bad_response ) {
                        if ( $root->localName != 'serviceResponse' ) {
                phpCAS::trace('localName failed');
                                $bad_response = true;
            }
        }

        if ( !$bad_response ) {
                        if ( $root->getElementsByTagName("proxySuccess")->length != 0) {
                $proxy_success_list = $root->getElementsByTagName("proxySuccess");

                                if ( $proxy_success_list->item(0)->getElementsByTagName("proxyTicket")->length != 0) {
                    $err_code = PHPCAS_SERVICE_OK;
                    $err_msg = '';
                    $pt = trim(
                        $proxy_success_list->item(0)->getElementsByTagName("proxyTicket")->item(0)->nodeValue
                    );
                    phpCAS::trace('original PT: '.trim($pt));
                    phpCAS::traceEnd($pt);
                    return $pt;
                } else {
                    phpCAS::trace('<proxySuccess> was found, but not <proxyTicket>');
                }
            } else if ($root->getElementsByTagName("proxyFailure")->length != 0) {
                                $proxy_failure_list = $root->getElementsByTagName("proxyFailure");

                                $err_code = PHPCAS_SERVICE_PT_FAILURE;
                $err_msg = 'PT retrieving failed (code=`'
                .$proxy_failure_list->item(0)->getAttribute('code')
                .'\', message=`'
                .trim($proxy_failure_list->item(0)->nodeValue)
                .'\')';
                phpCAS::traceEnd(false);
                return false;
            } else {
                phpCAS::trace('neither <proxySuccess> nor <proxyFailure> found');
            }
        }

                        $err_code = PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE;
        $err_msg = 'Invalid response from the CAS server (response=`'
            .$cas_response.'\')';

        phpCAS::traceEnd(false);
        return false;
    }

    

            
    

    
    private function _readURL($url, &$headers, &$body, &$err_msg)
    {
        phpCAS::traceBegin();
        $className = $this->_requestImplementation;
        $request = new $className();

        if (count($this->_curl_options)) {
            $request->setCurlOptions($this->_curl_options);
        }

        $request->setUrl($url);

        if (empty($this->_cas_server_ca_cert) && !$this->_no_cas_server_validation) {
            phpCAS::error(
                'one of the methods phpCAS::setCasServerCACert() or phpCAS::setNoCasServerValidation() must be called.'
            );
        }
        if ($this->_cas_server_ca_cert != '') {
            $request->setSslCaCert(
                $this->_cas_server_ca_cert, $this->_cas_server_cn_validate
            );
        }

                if ($this->getServerVersion() == SAML_VERSION_1_1) {
            $request->addHeader("soapaction: http://www.oasis-open.org/committees/security");
            $request->addHeader("cache-control: no-cache");
            $request->addHeader("pragma: no-cache");
            $request->addHeader("accept: text/xml");
            $request->addHeader("connection: keep-alive");
            $request->addHeader("content-type: text/xml");
            $request->makePost();
            $request->setPostBody($this->_buildSAMLPayload());
        }

        if ($request->send()) {
            $headers = $request->getResponseHeaders();
            $body = $request->getResponseBody();
            $err_msg = '';
            phpCAS::traceEnd(true);
            return true;
        } else {
            $headers = '';
            $body = '';
            $err_msg = $request->getErrorMessage();
            phpCAS::traceEnd(false);
            return false;
        }
    }

    
    private function _buildSAMLPayload()
    {
        phpCAS::traceBegin();

                $sa = urlencode($this->getTicket());

        $body = SAML_SOAP_ENV.SAML_SOAP_BODY.SAMLP_REQUEST
            .SAML_ASSERTION_ARTIFACT.$sa.SAML_ASSERTION_ARTIFACT_CLOSE
            .SAMLP_REQUEST_CLOSE.SAML_SOAP_BODY_CLOSE.SAML_SOAP_ENV_CLOSE;

        phpCAS::traceEnd($body);
        return ($body);
    }

    

            
    


    
    public function getProxiedService ($type)
    {
    	        $this->ensureIsProxy();
    	$this->ensureAuthenticationCallSuccessful();

    	    	if (gettype($type) != 'string')
        	throw new CAS_TypeMismatchException($type, '$type', 'string');

        switch ($type) {
        case PHPCAS_PROXIED_SERVICE_HTTP_GET:
        case PHPCAS_PROXIED_SERVICE_HTTP_POST:
            $requestClass = $this->_requestImplementation;
            $request = new $requestClass();
            if (count($this->_curl_options)) {
                $request->setCurlOptions($this->_curl_options);
            }
            $proxiedService = new $type($request, $this->_serviceCookieJar);
            if ($proxiedService instanceof CAS_ProxiedService_Testable) {
                $proxiedService->setCasClient($this);
            }
            return $proxiedService;
        case PHPCAS_PROXIED_SERVICE_IMAP;
            $proxiedService = new CAS_ProxiedService_Imap($this->_getUser());
            if ($proxiedService instanceof CAS_ProxiedService_Testable) {
                $proxiedService->setCasClient($this);
            }
            return $proxiedService;
        default:
            throw new CAS_InvalidArgumentException(
                "Unknown proxied-service type, $type."
            );
        }
    }

    
    public function initializeProxiedService (CAS_ProxiedService $proxiedService)
    {
    	        $this->ensureIsProxy();
    	$this->ensureAuthenticationCallSuccessful();

        $url = $proxiedService->getServiceUrl();
        if (!is_string($url)) {
            throw new CAS_ProxiedService_Exception(
                "Proxied Service ".get_class($proxiedService)
                ."->getServiceUrl() should have returned a string, returned a "
                .gettype($url)." instead."
            );
        }
        $pt = $this->retrievePT($url, $err_code, $err_msg);
        if (!$pt) {
            throw new CAS_ProxyTicketException($err_msg, $err_code);
        }
        $proxiedService->setProxyTicket($pt);
    }

    
    public function serviceWeb($url,&$err_code,&$output)
    {
    	        $this->ensureIsProxy();
    	$this->ensureAuthenticationCallSuccessful();

    	    	if (gettype($url) != 'string')
        	throw new CAS_TypeMismatchException($url, '$url', 'string');

        try {
            $service = $this->getProxiedService(PHPCAS_PROXIED_SERVICE_HTTP_GET);
            $service->setUrl($url);
            $service->send();
            $output = $service->getResponseBody();
            $err_code = PHPCAS_SERVICE_OK;
            return true;
        } catch (CAS_ProxyTicketException $e) {
            $err_code = $e->getCode();
            $output = $e->getMessage();
            return false;
        } catch (CAS_ProxiedService_Exception $e) {
            $lang = $this->getLangObj();
            $output = sprintf(
                $lang->getServiceUnavailable(), $url, $e->getMessage()
            );
            $err_code = PHPCAS_SERVICE_NOT_AVAILABLE;
            return false;
        }
    }

    
    public function serviceMail($url,$serviceUrl,$flags,&$err_code,&$err_msg,&$pt)
    {
    	        $this->ensureIsProxy();
    	$this->ensureAuthenticationCallSuccessful();

    	    	if (gettype($url) != 'string')
        	throw new CAS_TypeMismatchException($url, '$url', 'string');
        if (gettype($serviceUrl) != 'string')
        	throw new CAS_TypeMismatchException($serviceUrl, '$serviceUrl', 'string');
        if (gettype($flags) != 'integer')
        	throw new CAS_TypeMismatchException($flags, '$flags', 'string');

        try {
            $service = $this->getProxiedService(PHPCAS_PROXIED_SERVICE_IMAP);
            $service->setServiceUrl($serviceUrl);
            $service->setMailbox($url);
            $service->setOptions($flags);

            $stream = $service->open();
            $err_code = PHPCAS_SERVICE_OK;
            $pt = $service->getImapProxyTicket();
            return $stream;
        } catch (CAS_ProxyTicketException $e) {
            $err_msg = $e->getMessage();
            $err_code = $e->getCode();
            $pt = false;
            return false;
        } catch (CAS_ProxiedService_Exception $e) {
            $lang = $this->getLangObj();
            $err_msg = sprintf(
                $lang->getServiceUnavailable(),
                $url,
                $e->getMessage()
            );
            $err_code = PHPCAS_SERVICE_NOT_AVAILABLE;
            $pt = false;
            return false;
        }
    }

    

                    
                

    
    private $_proxies = array();

    
    public function getProxies()
    {
        return $this->_proxies;
    }

    
    private function _setProxies($proxies)
    {
        $this->_proxies = $proxies;
        if (!empty($proxies)) {
                                                                                                $this->setNoClearTicketsFromUrl();
        }
    }

    
    private $_allowed_proxy_chains;

    
    public function getAllowedProxyChains ()
    {
        if (empty($this->_allowed_proxy_chains)) {
            $this->_allowed_proxy_chains = new CAS_ProxyChain_AllowedList();
        }
        return $this->_allowed_proxy_chains;
    }

    
                

    
    public function validateCAS20(&$validate_url,&$text_response,&$tree_response, $renew=false)
    {
        phpCAS::traceBegin();
        phpCAS::trace($text_response);
        $result = false;
                if ($this->getAllowedProxyChains()->isProxyingAllowed()) {
            $validate_url = $this->getServerProxyValidateURL().'&ticket='
                .urlencode($this->getTicket());
        } else {
            $validate_url = $this->getServerServiceValidateURL().'&ticket='
                .urlencode($this->getTicket());
        }

        if ( $this->isProxy() ) {
                        $validate_url .= '&pgtUrl='.urlencode($this->_getCallbackURL());
        }

        if ( $renew ) {
                        $validate_url .= '&renew=true';
        }

                if ( !$this->_readURL($validate_url, $headers, $text_response, $err_msg) ) {
            phpCAS::trace(
                'could not open URL \''.$validate_url.'\' to validate ('.$err_msg.')'
            );
            throw new CAS_AuthenticationException(
                $this, 'Ticket not validated', $validate_url,
                true
            );
            $result = false;
        }

                $dom = new DOMDocument();
                $dom->preserveWhiteSpace = false;
                $dom->encoding = "utf-8";
                if ( !($dom->loadXML($text_response))) {
                        throw new CAS_AuthenticationException(
                $this, 'Ticket not validated', $validate_url,
                false, true, $text_response
            );
            $result = false;
        } else if ( !($tree_response = $dom->documentElement) ) {
                                    throw new CAS_AuthenticationException(
                $this, 'Ticket not validated', $validate_url,
                false, true, $text_response
            );
            $result = false;
        } else if ($tree_response->localName != 'serviceResponse') {
                                    throw new CAS_AuthenticationException(
                $this, 'Ticket not validated', $validate_url,
                false, true, $text_response
            );
            $result = false;
        } else if ($tree_response->getElementsByTagName("authenticationSuccess")->length != 0) {
                        $success_elements = $tree_response
                ->getElementsByTagName("authenticationSuccess");
            if ( $success_elements->item(0)->getElementsByTagName("user")->length == 0) {
                                throw new CAS_AuthenticationException(
                    $this, 'Ticket not validated', $validate_url,
                    false, true, $text_response
                );
                $result = false;
            } else {
                $this->_setUser(
                    trim(
                        $success_elements->item(0)->getElementsByTagName("user")->item(0)->nodeValue
                    )
                );
                $this->_readExtraAttributesCas20($success_elements);
                                $proxyList = array();
                if ( sizeof($arr = $success_elements->item(0)->getElementsByTagName("proxy")) > 0) {
                    foreach ($arr as $proxyElem) {
                        phpCAS::trace("Found Proxy: ".$proxyElem->nodeValue);
                        $proxyList[] = trim($proxyElem->nodeValue);
                    }
                    $this->_setProxies($proxyList);
                    phpCAS::trace("Storing Proxy List");
                }
                                if (!$this->getAllowedProxyChains()->isProxyListAllowed($proxyList)) {
                    throw new CAS_AuthenticationException(
                        $this, 'Proxy not allowed', $validate_url,
                        false, true,
                        $text_response
                    );
                    $result = false;
                } else {
                    $result = true;
                }
            }
        } else if ( $tree_response->getElementsByTagName("authenticationFailure")->length != 0) {
                        $auth_fail_list = $tree_response
                ->getElementsByTagName("authenticationFailure");
            throw new CAS_AuthenticationException(
                $this, 'Ticket not validated', $validate_url,
                false, false,
                $text_response,
                $auth_fail_list->item(0)->getAttribute('code'),
                trim($auth_fail_list->item(0)->nodeValue)
            );
            $result = false;
        } else {
            throw new CAS_AuthenticationException(
                $this, 'Ticket not validated', $validate_url,
                false, true,
                $text_response
            );
            $result = false;
        }
        if ($result) {
            $this->_renameSession($this->getTicket());
        }
        
        phpCAS::traceEnd($result);
        return $result;
    }


    
    private function _readExtraAttributesCas20($success_elements)
    {
        phpCAS::traceBegin();

        $extra_attributes = array();

                                                                                                                                        if ( $success_elements->item(0)->getElementsByTagName("attributes")->length != 0) {
            $attr_nodes = $success_elements->item(0)
                ->getElementsByTagName("attributes");
            phpCas :: trace("Found nested jasig style attributes");
            if ($attr_nodes->item(0)->hasChildNodes()) {
                                foreach ($attr_nodes->item(0)->childNodes as $attr_child) {
                    phpCas :: trace(
                        "Attribute [".$attr_child->localName."] = "
                        .$attr_child->nodeValue
                    );
                    $this->_addAttributeToArray(
                        $extra_attributes, $attr_child->localName,
                        $attr_child->nodeValue
                    );
                }
            }
        } else {
                                                                                                                                                                                                            phpCas :: trace("Testing for rubycas style attributes");
            $childnodes = $success_elements->item(0)->childNodes;
            foreach ($childnodes as $attr_node) {
                switch ($attr_node->localName) {
                case 'user':
                case 'proxies':
                case 'proxyGrantingTicket':
                    continue;
                default:
                    if (strlen(trim($attr_node->nodeValue))) {
                        phpCas :: trace(
                            "Attribute [".$attr_node->localName."] = ".$attr_node->nodeValue
                        );
                        $this->_addAttributeToArray(
                            $extra_attributes, $attr_node->localName,
                            $attr_node->nodeValue
                        );
                    }
                }
            }
        }

                                                                                                                                                                        if (!count($extra_attributes)
            && $success_elements->item(0)->getElementsByTagName("attribute")->length != 0
        ) {
            $attr_nodes = $success_elements->item(0)
                ->getElementsByTagName("attribute");
            $firstAttr = $attr_nodes->item(0);
            if (!$firstAttr->hasChildNodes()
                && $firstAttr->hasAttribute('name')
                && $firstAttr->hasAttribute('value')
            ) {
                phpCas :: trace("Found Name-Value style attributes");
                                foreach ($attr_nodes as $attr_node) {
                    if ($attr_node->hasAttribute('name')
                        && $attr_node->hasAttribute('value')
                    ) {
                        phpCas :: trace(
                            "Attribute [".$attr_node->getAttribute('name')
                            ."] = ".$attr_node->getAttribute('value')
                        );
                        $this->_addAttributeToArray(
                            $extra_attributes, $attr_node->getAttribute('name'),
                            $attr_node->getAttribute('value')
                        );
                    }
                }
            }
        }

        $this->setAttributes($extra_attributes);
        phpCAS::traceEnd();
        return true;
    }

    
    private function _addAttributeToArray(array &$attributeArray, $name, $value)
    {
                if (isset($attributeArray[$name])) {
                        if (!is_array($attributeArray[$name])) {
                $existingValue = $attributeArray[$name];
                $attributeArray[$name] = array($existingValue);
            }

            $attributeArray[$name][] = trim($value);
        } else {
            $attributeArray[$name] = trim($value);
        }
    }

    

                    
    

                
    private $_url = '';


    
    public function setURL($url)
    {
    	    	if (gettype($url) != 'string')
        	throw new CAS_TypeMismatchException($url, '$url', 'string');

        $this->_url = $url;
    }

    
    public function getURL()
    {
        phpCAS::traceBegin();
                if ( empty($this->_url) ) {
            $final_uri = '';
                        $final_uri = ($this->_isHttps()) ? 'https' : 'http';
            $final_uri .= '://';

            $final_uri .= $this->_getClientUrl();
            $request_uri	= explode('?', $_SERVER['REQUEST_URI'], 2);
            $final_uri		.= $request_uri[0];

            if (isset($request_uri[1]) && $request_uri[1]) {
                $query_string= $this->_removeParameterFromQueryString('ticket', $request_uri[1]);

                                                if ($query_string !== '') {
                    $final_uri	.= "?$query_string";
                }
            }

            phpCAS::trace("Final URI: $final_uri");
            $this->setURL($final_uri);
        }
        phpCAS::traceEnd($this->_url);
        return $this->_url;
    }


    
    private function _getClientUrl()
    {
        $server_url = '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
                        $hosts = explode(',', $_SERVER['HTTP_X_FORWARDED_HOST']);
                        return $hosts[0];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_SERVER'])) {
            $server_url = $_SERVER['HTTP_X_FORWARDED_SERVER'];
        } else {
            if (empty($_SERVER['SERVER_NAME'])) {
                $server_url = $_SERVER['HTTP_HOST'];
            } else {
                $server_url = $_SERVER['SERVER_NAME'];
            }
        }
        if (!strpos($server_url, ':')) {
            if (empty($_SERVER['HTTP_X_FORWARDED_PORT'])) {
                $server_port = $_SERVER['SERVER_PORT'];
            } else {
                $ports = explode(',', $_SERVER['HTTP_X_FORWARDED_PORT']);
                $server_port = $ports[0];
            }

            if ( ($this->_isHttps() && $server_port!=443)
                || (!$this->_isHttps() && $server_port!=80)
            ) {
                $server_url .= ':';
                $server_url .= $server_port;
            }
        }
        return $server_url;
    }

    
    private function _isHttps()
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            return ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        }
        if ( isset($_SERVER['HTTPS'])
            && !empty($_SERVER['HTTPS'])
            && strcasecmp($_SERVER['HTTPS'], 'off') !== 0
        ) {
            return true;
        } else {
            return false;
        }
    }

    
    private function _removeParameterFromQueryString($parameterName, $queryString)
    {
        $parameterName	= preg_quote($parameterName);
        return preg_replace(
            "/&$parameterName(=[^&]*)?|^$parameterName(=[^&]*)?&?/",
            '', $queryString
        );
    }

    
    private function _buildQueryUrl($url, $query)
    {
        $url .= (strstr($url, '?') === false) ? '?' : '&';
        $url .= $query;
        return $url;
    }

    
    private function _renameSession($ticket)
    {
        phpCAS::traceBegin();
        if ($this->getChangeSessionID()) {
            if (!empty($this->_user)) {
                $old_session = $_SESSION;
                phpCAS :: trace("Killing session: ". session_id());
                session_destroy();
                                $session_id = preg_replace('/[^a-zA-Z0-9\-]/', '', $ticket);
                phpCAS :: trace("Starting session: ". $session_id);
                session_id($session_id);
                session_start();
                phpCAS :: trace("Restoring old session vars");
                $_SESSION = $old_session;
            } else {
                phpCAS :: trace (
                    'Session should only be renamed after successfull authentication'
                );
            }
        } else {
            phpCAS :: trace(
                "Skipping session rename since phpCAS is not handling the session."
            );
        }
        phpCAS::traceEnd();
    }


                
    private function _authError(
        $failure,
        $cas_url,
        $no_response,
        $bad_response='',
        $cas_response='',
        $err_code='',
        $err_msg=''
    ) {
        phpCAS::traceBegin();
        $lang = $this->getLangObj();
        $this->printHTMLHeader($lang->getAuthenticationFailed());
        printf(
            $lang->getYouWereNotAuthenticated(), htmlentities($this->getURL()),
            isset($_SERVER['SERVER_ADMIN']) ? $_SERVER['SERVER_ADMIN']:''
        );
        phpCAS::trace('CAS URL: '.$cas_url);
        phpCAS::trace('Authentication failure: '.$failure);
        if ( $no_response ) {
            phpCAS::trace('Reason: no response from the CAS server');
        } else {
            if ( $bad_response ) {
                phpCAS::trace('Reason: bad response from the CAS server');
            } else {
                switch ($this->getServerVersion()) {
                case CAS_VERSION_1_0:
                    phpCAS::trace('Reason: CAS error');
                    break;
                case CAS_VERSION_2_0:
                case CAS_VERSION_3_0:
                    if ( empty($err_code) ) {
                        phpCAS::trace('Reason: no CAS error');
                    } else {
                        phpCAS::trace(
                            'Reason: ['.$err_code.'] CAS error: '.$err_msg
                        );
                    }
                    break;
                }
            }
            phpCAS::trace('CAS response: '.$cas_response);
        }
        $this->printHTMLFooter();
        phpCAS::traceExit();
        throw new CAS_GracefullTerminationException();
    }

            
    
    private $_rebroadcast = false;
    private $_rebroadcast_nodes = array();

    
    const HOSTNAME = 0;
    const IP = 1;

    
    private function _getNodeType($nodeURL)
    {
        phpCAS::traceBegin();
        if (preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/", $nodeURL)) {
            phpCAS::traceEnd(self::IP);
            return self::IP;
        } else {
            phpCAS::traceEnd(self::HOSTNAME);
            return self::HOSTNAME;
        }
    }

    
    public function addRebroadcastNode($rebroadcastNodeUrl)
    {
    	    	if ( !(bool)preg_match("/^(http|https):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i", $rebroadcastNodeUrl))
        	throw new CAS_TypeMismatchException($rebroadcastNodeUrl, '$rebroadcastNodeUrl', 'url');

                $this->_rebroadcast = true;
        $this->_rebroadcast_nodes[] = $rebroadcastNodeUrl;
    }

    
    private $_rebroadcast_headers = array();

    
    public function addRebroadcastHeader($header)
    {
    	if (gettype($header) != 'string')
        	throw new CAS_TypeMismatchException($header, '$header', 'string');

        $this->_rebroadcast_headers[] = $header;
    }

    
    const LOGOUT = 0;
    const PGTIOU = 1;

    
    private function _rebroadcast($type)
    {
        phpCAS::traceBegin();

        $rebroadcast_curl_options = array(
        CURLOPT_FAILONERROR => 1,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_CONNECTTIMEOUT => 1,
        CURLOPT_TIMEOUT => 4);

                if (!empty($_SERVER['SERVER_ADDR'])) {
            $ip = $_SERVER['SERVER_ADDR'];
        } else if (!empty($_SERVER['LOCAL_ADDR'])) {
                        $ip = $_SERVER['LOCAL_ADDR'];
        }
                if (!empty($ip)) {
            $dns = gethostbyaddr($ip);
        }
        $multiClassName = 'CAS_Request_CurlMultiRequest';
        $multiRequest = new $multiClassName();

        for ($i = 0; $i < sizeof($this->_rebroadcast_nodes); $i++) {
            if ((($this->_getNodeType($this->_rebroadcast_nodes[$i]) == self::HOSTNAME) && !empty($dns) && (stripos($this->_rebroadcast_nodes[$i], $dns) === false))
                || (($this->_getNodeType($this->_rebroadcast_nodes[$i]) == self::IP) && !empty($ip) && (stripos($this->_rebroadcast_nodes[$i], $ip) === false))
            ) {
                phpCAS::trace(
                    'Rebroadcast target URL: '.$this->_rebroadcast_nodes[$i]
                    .$_SERVER['REQUEST_URI']
                );
                $className = $this->_requestImplementation;
                $request = new $className();

                $url = $this->_rebroadcast_nodes[$i].$_SERVER['REQUEST_URI'];
                $request->setUrl($url);

                if (count($this->_rebroadcast_headers)) {
                    $request->addHeaders($this->_rebroadcast_headers);
                }

                $request->makePost();
                if ($type == self::LOGOUT) {
                                        $request->setPostBody(
                        'rebroadcast=false&logoutRequest='.$_POST['logoutRequest']
                    );
                } else if ($type == self::PGTIOU) {
                                        $request->setPostBody('rebroadcast=false');
                }

                $request->setCurlOptions($rebroadcast_curl_options);

                $multiRequest->addRequest($request);
            } else {
                phpCAS::trace(
                    'Rebroadcast not sent to self: '
                    .$this->_rebroadcast_nodes[$i].' == '.(!empty($ip)?$ip:'')
                    .'/'.(!empty($dns)?$dns:'')
                );
            }
        }
                if ($multiRequest->getNumRequests() > 0) {
            $multiRequest->send();
        }
        phpCAS::traceEnd();
    }

    
}

?>
