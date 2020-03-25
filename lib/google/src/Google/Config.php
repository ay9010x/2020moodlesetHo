<?php



class Google_Config
{
  const GZIP_DISABLED = true;
  const GZIP_ENABLED = false;
  const GZIP_UPLOADS_ENABLED = true;
  const GZIP_UPLOADS_DISABLED = false;
  const USE_AUTO_IO_SELECTION = "auto";
  const TASK_RETRY_NEVER = 0;
  const TASK_RETRY_ONCE = 1;
  const TASK_RETRY_ALWAYS = -1;
  protected $configuration;

  
  public function __construct($ini_file_location = null)
  {
    $this->configuration = array(
            'application_name' => '',

            'auth_class'    => 'Google_Auth_OAuth2',
      'io_class'      => self::USE_AUTO_IO_SELECTION,
      'cache_class'   => 'Google_Cache_File',
      'logger_class'  => 'Google_Logger_Null',

                  'base_path' => 'https://www.googleapis.com',

            'classes' => array(
        'Google_IO_Abstract' => array(
          'request_timeout_seconds' => 100,
        ),
        'Google_IO_Curl' => array(
          'disable_proxy_workaround' => false,
          'options' => null,
        ),
        'Google_Logger_Abstract' => array(
          'level' => 'debug',
          'log_format' => "[%datetime%] %level%: %message% %context%\n",
          'date_format' => 'd/M/Y:H:i:s O',
          'allow_newlines' => true
        ),
        'Google_Logger_File' => array(
          'file' => 'php://stdout',
          'mode' => 0640,
          'lock' => false,
        ),
        'Google_Http_Request' => array(
                    'disable_gzip' => self::GZIP_ENABLED,

                                                  'enable_gzip_for_uploads' => self::GZIP_UPLOADS_DISABLED,
        ),
                        'Google_Auth_OAuth2' => array(
                              'client_id' => '',
          'client_secret' => '',
          'redirect_uri' => '',

                              'developer_key' => '',

                    'hd' => '',
          'prompt' => '',
          'openid.realm' => '',
          'include_granted_scopes' => '',
          'login_hint' => '',
          'request_visible_actions' => '',
          'access_type' => 'online',
          'approval_prompt' => 'auto',
          'federated_signon_certs_url' =>
              'https://www.googleapis.com/oauth2/v1/certs',
        ),
        'Google_Task_Runner' => array(
                    'initial_delay' => 1,
          'max_delay' => 60,
                    'factor' => 2,
                                        'jitter' => .5,
                    'retries' => 0
        ),
        'Google_Service_Exception' => array(
          'retry_map' => array(
            '500' => self::TASK_RETRY_ALWAYS,
            '503' => self::TASK_RETRY_ALWAYS,
            'rateLimitExceeded' => self::TASK_RETRY_ALWAYS,
            'userRateLimitExceeded' => self::TASK_RETRY_ALWAYS
          )
        ),
        'Google_IO_Exception' => array(
          'retry_map' => !extension_loaded('curl') ? array() : array(
            CURLE_COULDNT_RESOLVE_HOST => self::TASK_RETRY_ALWAYS,
            CURLE_COULDNT_CONNECT => self::TASK_RETRY_ALWAYS,
            CURLE_OPERATION_TIMEOUTED => self::TASK_RETRY_ALWAYS,
            CURLE_SSL_CONNECT_ERROR => self::TASK_RETRY_ALWAYS,
            CURLE_GOT_NOTHING => self::TASK_RETRY_ALWAYS
          )
        ),
                'Google_Cache_File' => array(
          'directory' => sys_get_temp_dir() . '/Google_Client'
        )
      ),
    );
    if ($ini_file_location) {
      $ini = parse_ini_file($ini_file_location, true);
      if (is_array($ini) && count($ini)) {
        $merged_configuration = $ini + $this->configuration;
        if (isset($ini['classes']) && isset($this->configuration['classes'])) {
          $merged_configuration['classes'] = $ini['classes'] + $this->configuration['classes'];
        }
        $this->configuration = $merged_configuration;
      }
    }
  }

  
  public function setClassConfig($class, $config, $value = null)
  {
    if (!is_array($config)) {
      if (!isset($this->configuration['classes'][$class])) {
        $this->configuration['classes'][$class] = array();
      }
      $this->configuration['classes'][$class][$config] = $value;
    } else {
      $this->configuration['classes'][$class] = $config;
    }
  }

  public function getClassConfig($class, $key = null)
  {
    if (!isset($this->configuration['classes'][$class])) {
      return null;
    }
    if ($key === null) {
      return $this->configuration['classes'][$class];
    } else {
      return $this->configuration['classes'][$class][$key];
    }
  }

  
  public function getCacheClass()
  {
    return $this->configuration['cache_class'];
  }

  
  public function getLoggerClass()
  {
    return $this->configuration['logger_class'];
  }

  
  public function getAuthClass()
  {
    return $this->configuration['auth_class'];
  }

  
  public function setAuthClass($class)
  {
    $prev = $this->configuration['auth_class'];
    if (!isset($this->configuration['classes'][$class]) &&
        isset($this->configuration['classes'][$prev])) {
      $this->configuration['classes'][$class] =
          $this->configuration['classes'][$prev];
    }
    $this->configuration['auth_class'] = $class;
  }

  
  public function setIoClass($class)
  {
    $prev = $this->configuration['io_class'];
    if (!isset($this->configuration['classes'][$class]) &&
        isset($this->configuration['classes'][$prev])) {
      $this->configuration['classes'][$class] =
          $this->configuration['classes'][$prev];
    }
    $this->configuration['io_class'] = $class;
  }

  
  public function setCacheClass($class)
  {
    $prev = $this->configuration['cache_class'];
    if (!isset($this->configuration['classes'][$class]) &&
        isset($this->configuration['classes'][$prev])) {
      $this->configuration['classes'][$class] =
          $this->configuration['classes'][$prev];
    }
    $this->configuration['cache_class'] = $class;
  }

  
  public function setLoggerClass($class)
  {
    $prev = $this->configuration['logger_class'];
    if (!isset($this->configuration['classes'][$class]) &&
        isset($this->configuration['classes'][$prev])) {
      $this->configuration['classes'][$class] =
          $this->configuration['classes'][$prev];
    }
    $this->configuration['logger_class'] = $class;
  }

  
  public function getIoClass()
  {
    return $this->configuration['io_class'];
  }

  
  public function setApplicationName($name)
  {
    $this->configuration['application_name'] = $name;
  }

  
  public function getApplicationName()
  {
    return $this->configuration['application_name'];
  }

  
  public function setClientId($clientId)
  {
    $this->setAuthConfig('client_id', $clientId);
  }

  
  public function setClientSecret($secret)
  {
    $this->setAuthConfig('client_secret', $secret);
  }

  
  public function setRedirectUri($uri)
  {
    $this->setAuthConfig('redirect_uri', $uri);
  }

  
  public function setRequestVisibleActions($rva)
  {
    $this->setAuthConfig('request_visible_actions', $rva);
  }

  
  public function setAccessType($access)
  {
    $this->setAuthConfig('access_type', $access);
  }

  
  public function setApprovalPrompt($approval)
  {
    $this->setAuthConfig('approval_prompt', $approval);
  }

  
  public function setLoginHint($hint)
  {
    $this->setAuthConfig('login_hint', $hint);
  }

  
  public function setDeveloperKey($key)
  {
    $this->setAuthConfig('developer_key', $key);
  }

  
  public function setHostedDomain($hd)
  {
    $this->setAuthConfig('hd', $hd);
  }

  
  public function setPrompt($prompt)
  {
    $this->setAuthConfig('prompt', $prompt);
  }

  
  public function setOpenidRealm($realm)
  {
    $this->setAuthConfig('openid.realm', $realm);
  }

  
  public function setIncludeGrantedScopes($include)
  {
    $this->setAuthConfig(
        'include_granted_scopes',
        $include ? "true" : "false"
    );
  }

  
  public function getBasePath()
  {
    return $this->configuration['base_path'];
  }

  
  private function setAuthConfig($key, $value)
  {
    if (!isset($this->configuration['classes'][$this->getAuthClass()])) {
      $this->configuration['classes'][$this->getAuthClass()] = array();
    }
    $this->configuration['classes'][$this->getAuthClass()][$key] = $value;
  }
}
