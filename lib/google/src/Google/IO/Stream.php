<?php




if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}

class Google_IO_Stream extends Google_IO_Abstract
{
  const TIMEOUT = "timeout";
  const ZLIB = "compress.zlib://";
  private $options = array();
  private $trappedErrorNumber;
  private $trappedErrorString;

  private static $DEFAULT_HTTP_CONTEXT = array(
    "follow_location" => 0,
    "ignore_errors" => 1,
  );

  private static $DEFAULT_SSL_CONTEXT = array(
    "verify_peer" => true,
  );

  public function __construct(Google_Client $client)
  {
    if (!ini_get('allow_url_fopen')) {
      $error = 'The stream IO handler requires the allow_url_fopen runtime ' .
               'configuration to be enabled';
      $client->getLogger()->critical($error);
      throw new Google_IO_Exception($error);
    }

    parent::__construct($client);
  }

  
  public function executeRequest(Google_Http_Request $request)
  {
    $default_options = stream_context_get_options(stream_context_get_default());

    $requestHttpContext = array_key_exists('http', $default_options) ?
        $default_options['http'] : array();

    if ($request->getPostBody()) {
      $requestHttpContext["content"] = $request->getPostBody();
    }

    $requestHeaders = $request->getRequestHeaders();
    if ($requestHeaders && is_array($requestHeaders)) {
      $headers = "";
      foreach ($requestHeaders as $k => $v) {
        $headers .= "$k: $v\r\n";
      }
      $requestHttpContext["header"] = $headers;
    }

    $requestHttpContext["method"] = $request->getRequestMethod();
    $requestHttpContext["user_agent"] = $request->getUserAgent();

    $requestSslContext = array_key_exists('ssl', $default_options) ?
        $default_options['ssl'] : array();

    if (!$this->client->isAppEngine() && !array_key_exists("cafile", $requestSslContext)) {
      $requestSslContext["cafile"] = dirname(__FILE__) . '/cacerts.pem';
    }

    $options = array(
        "http" => array_merge(
            self::$DEFAULT_HTTP_CONTEXT,
            $requestHttpContext
        ),
        "ssl" => array_merge(
            self::$DEFAULT_SSL_CONTEXT,
            $requestSslContext
        )
    );

    $context = stream_context_create($options);

    $url = $request->getUrl();

    if ($request->canGzip()) {
      $url = self::ZLIB . $url;
    }

    $this->client->getLogger()->debug(
        'Stream request',
        array(
            'url' => $url,
            'method' => $request->getRequestMethod(),
            'headers' => $requestHeaders,
            'body' => $request->getPostBody()
        )
    );

            $this->trappedErrorNumber = null;
    $this->trappedErrorString = null;

        set_error_handler(array($this, 'trapError'));
    $fh = fopen($url, 'r', false, $context);
    restore_error_handler();
    
    if ($this->trappedErrorNumber) {
      $error = sprintf(
          "HTTP Error: Unable to connect: '%s'",
          $this->trappedErrorString
      );

      $this->client->getLogger()->error('Stream ' . $error);
      throw new Google_IO_Exception($error, $this->trappedErrorNumber);
    }

    $response_data = false;
    $respHttpCode = self::UNKNOWN_CODE;
    if ($fh) {
      if (isset($this->options[self::TIMEOUT])) {
        stream_set_timeout($fh, $this->options[self::TIMEOUT]);
      }

      $response_data = stream_get_contents($fh);
      fclose($fh);

      $respHttpCode = $this->getHttpResponseCode($http_response_header);
    }

    if (false === $response_data) {
      $error = sprintf(
          "HTTP Error: Unable to connect: '%s'",
          $respHttpCode
      );

      $this->client->getLogger()->error('Stream ' . $error);
      throw new Google_IO_Exception($error, $respHttpCode);
    }

    $responseHeaders = $this->getHttpResponseHeaders($http_response_header);

    $this->client->getLogger()->debug(
        'Stream response',
        array(
            'code' => $respHttpCode,
            'headers' => $responseHeaders,
            'body' => $response_data,
        )
    );

    return array($response_data, $responseHeaders, $respHttpCode);
  }

  
  public function setOptions($options)
  {
    $this->options = $options + $this->options;
  }

  
  public function trapError($errno, $errstr)
  {
    $this->trappedErrorNumber = $errno;
    $this->trappedErrorString = $errstr;
  }

  
  public function setTimeout($timeout)
  {
    $this->options[self::TIMEOUT] = $timeout;
  }

  
  public function getTimeout()
  {
    return $this->options[self::TIMEOUT];
  }

  
  protected function needsQuirk()
  {
    return false;
  }

  protected function getHttpResponseCode($response_headers)
  {
    $header_count = count($response_headers);

    for ($i = 0; $i < $header_count; $i++) {
      $header = $response_headers[$i];
      if (strncasecmp("HTTP", $header, strlen("HTTP")) == 0) {
        $response = explode(' ', $header);
        return $response[1];
      }
    }
    return self::UNKNOWN_CODE;
  }
}
