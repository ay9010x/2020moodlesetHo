<?php




if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}

class Google_IO_Curl extends Google_IO_Abstract
{
    const NO_QUIRK_VERSION = 0x071E00;

  private $options = array();

  
  private $disableProxyWorkaround;

  public function __construct(Google_Client $client)
  {
    if (!extension_loaded('curl')) {
      $error = 'The cURL IO handler requires the cURL extension to be enabled';
      $client->getLogger()->critical($error);
      throw new Google_IO_Exception($error);
    }

    parent::__construct($client);

    $this->disableProxyWorkaround = $this->client->getClassConfig(
        'Google_IO_Curl',
        'disable_proxy_workaround'
    );
  }

  
  public function executeRequest(Google_Http_Request $request)
  {
    $curl = curl_init();

    if ($request->getPostBody()) {
      curl_setopt($curl, CURLOPT_POSTFIELDS, $request->getPostBody());
    }

    $requestHeaders = $request->getRequestHeaders();
    if ($requestHeaders && is_array($requestHeaders)) {
      $curlHeaders = array();
      foreach ($requestHeaders as $k => $v) {
        $curlHeaders[] = "$k: $v";
      }
      curl_setopt($curl, CURLOPT_HTTPHEADER, $curlHeaders);
    }
    curl_setopt($curl, CURLOPT_URL, $request->getUrl());

    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request->getRequestMethod());
    curl_setopt($curl, CURLOPT_USERAGENT, $request->getUserAgent());

    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

            
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, true);

    if ($request->canGzip()) {
      curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
    }
    
    $options = $this->client->getClassConfig('Google_IO_Curl', 'options');
    if (is_array($options)) {
      $this->setOptions($options);
    }
    
    foreach ($this->options as $key => $var) {
      curl_setopt($curl, $key, $var);
    }

    if (!isset($this->options[CURLOPT_CAINFO])) {
      curl_setopt($curl, CURLOPT_CAINFO, dirname(__FILE__) . '/cacerts.pem');
    }

    $this->client->getLogger()->debug(
        'cURL request',
        array(
            'url' => $request->getUrl(),
            'method' => $request->getRequestMethod(),
            'headers' => $requestHeaders,
            'body' => $request->getPostBody()
        )
    );

    $response = curl_exec($curl);
    if ($response === false) {
      $error = curl_error($curl);
      $code = curl_errno($curl);
      $map = $this->client->getClassConfig('Google_IO_Exception', 'retry_map');

      $this->client->getLogger()->error('cURL ' . $error);
      throw new Google_IO_Exception($error, $code, null, $map);
    }
    $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

    list($responseHeaders, $responseBody) = $this->parseHttpResponse($response, $headerSize);
    $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $this->client->getLogger()->debug(
        'cURL response',
        array(
            'code' => $responseCode,
            'headers' => $responseHeaders,
            'body' => $responseBody,
        )
    );

    return array($responseBody, $responseHeaders, $responseCode);
  }

  
  public function setOptions($options)
  {
    $this->options = $options + $this->options;
  }

  
  public function setTimeout($timeout)
  {
                    $this->options[CURLOPT_CONNECTTIMEOUT] = $timeout;
    $this->options[CURLOPT_TIMEOUT] = $timeout;
  }

  
  public function getTimeout()
  {
    return $this->options[CURLOPT_TIMEOUT];
  }

  
  protected function needsQuirk()
  {
    if ($this->disableProxyWorkaround) {
      return false;
    }

    $ver = curl_version();
    $versionNum = $ver['version_number'];
    return $versionNum < Google_IO_Curl::NO_QUIRK_VERSION;
  }
}
