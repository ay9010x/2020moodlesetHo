<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/autoload.php';
}


class Google_Client
{
  const LIBVER = "1.1.5";
  const USER_AGENT_SUFFIX = "google-api-php-client/";
  
  private $auth;

  
  private $io;

  
  private $cache;

  
  private $config;

  
  private $logger;

  
  private $deferExecution = false;

  
    protected $requestedScopes = array();

    protected $services = array();

    private $authenticated = false;

  
  public function __construct($config = null)
  {
    if (is_string($config) && strlen($config)) {
      $config = new Google_Config($config);
    } else if ( !($config instanceof Google_Config)) {
      $config = new Google_Config();

      if ($this->isAppEngine()) {
                $config->setCacheClass('Google_Cache_Memcache');
      }

      if (version_compare(phpversion(), "5.3.4", "<=") || $this->isAppEngine()) {
                $config->setClassConfig('Google_Http_Request', 'disable_gzip', true);
      }
    }

    if ($config->getIoClass() == Google_Config::USE_AUTO_IO_SELECTION) {
      if (function_exists('curl_version') && function_exists('curl_exec')
          && !$this->isAppEngine()) {
        $config->setIoClass("Google_IO_Curl");
      } else {
        $config->setIoClass("Google_IO_Stream");
      }
    }

    $this->config = $config;
  }

  
  public function getLibraryVersion()
  {
    return self::LIBVER;
  }

  
  public function authenticate($code, $crossClient = false)
  {
    $this->authenticated = true;
    return $this->getAuth()->authenticate($code, $crossClient);
  }
  
  
  public function loadServiceAccountJson($jsonLocation, $scopes)
  {
    $data = json_decode(file_get_contents($jsonLocation));
    if (isset($data->type) && $data->type == 'service_account') {
            $cred = new Google_Auth_AssertionCredentials(
          $data->client_email,
          $scopes,
          $data->private_key
      );
      return $cred;
    } else {
      throw new Google_Exception("Invalid service account JSON file.");
    }
  }

  
  public function setAuthConfig($json)
  {
    $data = json_decode($json);
    $key = isset($data->installed) ? 'installed' : 'web';
    if (!isset($data->$key)) {
      throw new Google_Exception("Invalid client secret JSON file.");
    }
    $this->setClientId($data->$key->client_id);
    $this->setClientSecret($data->$key->client_secret);
    if (isset($data->$key->redirect_uris)) {
      $this->setRedirectUri($data->$key->redirect_uris[0]);
    }
  }

  
  public function setAuthConfigFile($file)
  {
    $this->setAuthConfig(file_get_contents($file));
  }

  
  public function prepareScopes()
  {
    if (empty($this->requestedScopes)) {
      throw new Google_Auth_Exception("No scopes specified");
    }
    $scopes = implode(' ', $this->requestedScopes);
    return $scopes;
  }

  
  public function setAccessToken($accessToken)
  {
    if ($accessToken == 'null') {
      $accessToken = null;
    }
    $this->getAuth()->setAccessToken($accessToken);
  }



  
  public function setAuth(Google_Auth_Abstract $auth)
  {
    $this->config->setAuthClass(get_class($auth));
    $this->auth = $auth;
  }

  
  public function setIo(Google_IO_Abstract $io)
  {
    $this->config->setIoClass(get_class($io));
    $this->io = $io;
  }

  
  public function setCache(Google_Cache_Abstract $cache)
  {
    $this->config->setCacheClass(get_class($cache));
    $this->cache = $cache;
  }

  
  public function setLogger(Google_Logger_Abstract $logger)
  {
    $this->config->setLoggerClass(get_class($logger));
    $this->logger = $logger;
  }

  
  public function createAuthUrl()
  {
    $scopes = $this->prepareScopes();
    return $this->getAuth()->createAuthUrl($scopes);
  }

  
  public function getAccessToken()
  {
    $token = $this->getAuth()->getAccessToken();
                return (null == $token || 'null' == $token || '[]' == $token) ? null : $token;
  }

  
  public function getRefreshToken()
  {
    return $this->getAuth()->getRefreshToken();
  }

  
  public function isAccessTokenExpired()
  {
    return $this->getAuth()->isAccessTokenExpired();
  }

  
  public function setState($state)
  {
    $this->getAuth()->setState($state);
  }

  
  public function setAccessType($accessType)
  {
    $this->config->setAccessType($accessType);
  }

  
  public function setApprovalPrompt($approvalPrompt)
  {
    $this->config->setApprovalPrompt($approvalPrompt);
  }

  
  public function setLoginHint($loginHint)
  {
      $this->config->setLoginHint($loginHint);
  }

  
  public function setApplicationName($applicationName)
  {
    $this->config->setApplicationName($applicationName);
  }

  
  public function setClientId($clientId)
  {
    $this->config->setClientId($clientId);
  }

  
  public function setClientSecret($clientSecret)
  {
    $this->config->setClientSecret($clientSecret);
  }

  
  public function setRedirectUri($redirectUri)
  {
    $this->config->setRedirectUri($redirectUri);
  }

  
  public function setRequestVisibleActions($requestVisibleActions)
  {
    if (is_array($requestVisibleActions)) {
      $requestVisibleActions = join(" ", $requestVisibleActions);
    }
    $this->config->setRequestVisibleActions($requestVisibleActions);
  }

  
  public function setDeveloperKey($developerKey)
  {
    $this->config->setDeveloperKey($developerKey);
  }

  
  public function setHostedDomain($hd)
  {
    $this->config->setHostedDomain($hd);
  }

  
  public function setPrompt($prompt)
  {
    $this->config->setPrompt($prompt);
  }

  
  public function setOpenidRealm($realm)
  {
    $this->config->setOpenidRealm($realm);
  }

  
  public function setIncludeGrantedScopes($include)
  {
    $this->config->setIncludeGrantedScopes($include);
  }

  
  public function refreshToken($refreshToken)
  {
    $this->getAuth()->refreshToken($refreshToken);
  }

  
  public function revokeToken($token = null)
  {
    return $this->getAuth()->revokeToken($token);
  }

  
  public function verifyIdToken($token = null)
  {
    return $this->getAuth()->verifyIdToken($token);
  }

  
  public function verifySignedJwt($id_token, $cert_location, $audience, $issuer, $max_expiry = null)
  {
    $auth = new Google_Auth_OAuth2($this);
    $certs = $auth->retrieveCertsFromLocation($cert_location);
    return $auth->verifySignedJwtWithCerts($id_token, $certs, $audience, $issuer, $max_expiry);
  }

  
  public function setAssertionCredentials(Google_Auth_AssertionCredentials $creds)
  {
    $this->getAuth()->setAssertionCredentials($creds);
  }

  
  public function setScopes($scopes)
  {
    $this->requestedScopes = array();
    $this->addScope($scopes);
  }

  
  public function addScope($scope_or_scopes)
  {
    if (is_string($scope_or_scopes) && !in_array($scope_or_scopes, $this->requestedScopes)) {
      $this->requestedScopes[] = $scope_or_scopes;
    } else if (is_array($scope_or_scopes)) {
      foreach ($scope_or_scopes as $scope) {
        $this->addScope($scope);
      }
    }
  }

  
  public function getScopes()
  {
     return $this->requestedScopes;
  }

  
  public function setUseBatch($useBatch)
  {
        $this->setDefer($useBatch);
  }

  
  public function setDefer($defer)
  {
    $this->deferExecution = $defer;
  }

  
  public function execute($request)
  {
    if ($request instanceof Google_Http_Request) {
      $request->setUserAgent(
          $this->getApplicationName()
          . " " . self::USER_AGENT_SUFFIX
          . $this->getLibraryVersion()
      );
      if (!$this->getClassConfig("Google_Http_Request", "disable_gzip")) {
        $request->enableGzip();
      }
      $request->maybeMoveParametersToBody();
      return Google_Http_REST::execute($this, $request);
    } else if ($request instanceof Google_Http_Batch) {
      return $request->execute();
    } else {
      throw new Google_Exception("Do not know how to execute this type of object.");
    }
  }

  
  public function shouldDefer()
  {
    return $this->deferExecution;
  }

  
  public function getAuth()
  {
    if (!isset($this->auth)) {
      $class = $this->config->getAuthClass();
      $this->auth = new $class($this);
    }
    return $this->auth;
  }

  
  public function getIo()
  {
    if (!isset($this->io)) {
      $class = $this->config->getIoClass();
      $this->io = new $class($this);
    }
    return $this->io;
  }

  
  public function getCache()
  {
    if (!isset($this->cache)) {
      $class = $this->config->getCacheClass();
      $this->cache = new $class($this);
    }
    return $this->cache;
  }

  
  public function getLogger()
  {
    if (!isset($this->logger)) {
      $class = $this->config->getLoggerClass();
      $this->logger = new $class($this);
    }
    return $this->logger;
  }

  
  public function getClassConfig($class, $key = null)
  {
    if (!is_string($class)) {
      $class = get_class($class);
    }
    return $this->config->getClassConfig($class, $key);
  }

  
  public function setClassConfig($class, $config, $value = null)
  {
    if (!is_string($class)) {
      $class = get_class($class);
    }
    $this->config->setClassConfig($class, $config, $value);

  }

  
  public function getBasePath()
  {
    return $this->config->getBasePath();
  }

  
  public function getApplicationName()
  {
    return $this->config->getApplicationName();
  }

  
  public function isAppEngine()
  {
    return (isset($_SERVER['SERVER_SOFTWARE']) &&
        strpos($_SERVER['SERVER_SOFTWARE'], 'Google App Engine') !== false);
  }
}
