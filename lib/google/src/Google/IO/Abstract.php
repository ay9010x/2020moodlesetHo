<?php




if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}

abstract class Google_IO_Abstract
{
  const UNKNOWN_CODE = 0;
  const FORM_URLENCODED = 'application/x-www-form-urlencoded';
  private static $CONNECTION_ESTABLISHED_HEADERS = array(
    "HTTP/1.0 200 Connection established\r\n\r\n",
    "HTTP/1.1 200 Connection established\r\n\r\n",
  );
  private static $ENTITY_HTTP_METHODS = array("POST" => null, "PUT" => null);
  private static $HOP_BY_HOP = array(
    'connection' => true,
    'keep-alive' => true,
    'proxy-authenticate' => true,
    'proxy-authorization' => true,
    'te' => true,
    'trailers' => true,
    'transfer-encoding' => true,
    'upgrade' => true
  );


  
  protected $client;

  public function __construct(Google_Client $client)
  {
    $this->client = $client;
    $timeout = $client->getClassConfig('Google_IO_Abstract', 'request_timeout_seconds');
    if ($timeout > 0) {
      $this->setTimeout($timeout);
    }
  }

  
  abstract public function executeRequest(Google_Http_Request $request);

  
  abstract public function setOptions($options);

  
  abstract public function setTimeout($timeout);

  
  abstract public function getTimeout();

  
  abstract protected function needsQuirk();

  
  public function setCachedRequest(Google_Http_Request $request)
  {
        if (Google_Http_CacheParser::isResponseCacheable($request)) {
      $this->client->getCache()->set($request->getCacheKey(), $request);
      return true;
    }

    return false;
  }

  
  public function makeRequest(Google_Http_Request $request)
  {
        $cached = $this->getCachedRequest($request);
    if ($cached !== false && $cached instanceof Google_Http_Request) {
      if (!$this->checkMustRevalidateCachedRequest($cached, $request)) {
        return $cached;
      }
    }

    if (array_key_exists($request->getRequestMethod(), self::$ENTITY_HTTP_METHODS)) {
      $request = $this->processEntityRequest($request);
    }

    list($responseData, $responseHeaders, $respHttpCode) = $this->executeRequest($request);

    if ($respHttpCode == 304 && $cached) {
            $this->updateCachedRequest($cached, $responseHeaders);
      return $cached;
    }

    if (!isset($responseHeaders['Date']) && !isset($responseHeaders['date'])) {
      $responseHeaders['date'] = date("r");
    }

    $request->setResponseHttpCode($respHttpCode);
    $request->setResponseHeaders($responseHeaders);
    $request->setResponseBody($responseData);
            $this->setCachedRequest($request);
    return $request;
  }

  
  public function getCachedRequest(Google_Http_Request $request)
  {
    if (false === Google_Http_CacheParser::isRequestCacheable($request)) {
      return false;
    }

    return $this->client->getCache()->get($request->getCacheKey());
  }

  
  public function processEntityRequest(Google_Http_Request $request)
  {
    $postBody = $request->getPostBody();
    $contentType = $request->getRequestHeader("content-type");

        if (false == $contentType) {
      $contentType = self::FORM_URLENCODED;
      $request->setRequestHeaders(array('content-type' => $contentType));
    }

        if ($contentType == self::FORM_URLENCODED && is_array($postBody)) {
      $postBody = http_build_query($postBody, '', '&');
      $request->setPostBody($postBody);
    }

        if (!$postBody || is_string($postBody)) {
      $postsLength = strlen($postBody);
      $request->setRequestHeaders(array('content-length' => $postsLength));
    }

    return $request;
  }

  
  protected function checkMustRevalidateCachedRequest($cached, $request)
  {
    if (Google_Http_CacheParser::mustRevalidate($cached)) {
      $addHeaders = array();
      if ($cached->getResponseHeader('etag')) {
                        $addHeaders['If-None-Match'] = $cached->getResponseHeader('etag');
      } elseif ($cached->getResponseHeader('date')) {
        $addHeaders['If-Modified-Since'] = $cached->getResponseHeader('date');
      }

      $request->setRequestHeaders($addHeaders);
      return true;
    } else {
      return false;
    }
  }

  
  protected function updateCachedRequest($cached, $responseHeaders)
  {
    $hopByHop = self::$HOP_BY_HOP;
    if (!empty($responseHeaders['connection'])) {
      $connectionHeaders = array_map(
          'strtolower',
          array_filter(
              array_map('trim', explode(',', $responseHeaders['connection']))
          )
      );
      $hopByHop += array_fill_keys($connectionHeaders, true);
    }

    $endToEnd = array_diff_key($responseHeaders, $hopByHop);
    $cached->setResponseHeaders($endToEnd);
  }

  
  public function parseHttpResponse($respData, $headerSize)
  {
        foreach (self::$CONNECTION_ESTABLISHED_HEADERS as $established_header) {
      if (stripos($respData, $established_header) !== false) {
                $respData = str_ireplace($established_header, '', $respData);
                                if (!$this->needsQuirk()) {
          $headerSize -= strlen($established_header);
        }
        break;
      }
    }

    if ($headerSize) {
      $responseBody = substr($respData, $headerSize);
      $responseHeaders = substr($respData, 0, $headerSize);
    } else {
      $responseSegments = explode("\r\n\r\n", $respData, 2);
      $responseHeaders = $responseSegments[0];
      $responseBody = isset($responseSegments[1]) ? $responseSegments[1] :
                                                    null;
    }

    $responseHeaders = $this->getHttpResponseHeaders($responseHeaders);
    return array($responseHeaders, $responseBody);
  }

  
  public function getHttpResponseHeaders($rawHeaders)
  {
    if (is_array($rawHeaders)) {
      return $this->parseArrayHeaders($rawHeaders);
    } else {
      return $this->parseStringHeaders($rawHeaders);
    }
  }

  private function parseStringHeaders($rawHeaders)
  {
    $headers = array();
    $responseHeaderLines = explode("\r\n", $rawHeaders);
    foreach ($responseHeaderLines as $headerLine) {
      if ($headerLine && strpos($headerLine, ':') !== false) {
        list($header, $value) = explode(': ', $headerLine, 2);
        $header = strtolower($header);
        if (isset($headers[$header])) {
          $headers[$header] .= "\n" . $value;
        } else {
          $headers[$header] = $value;
        }
      }
    }
    return $headers;
  }

  private function parseArrayHeaders($rawHeaders)
  {
    $header_count = count($rawHeaders);
    $headers = array();

    for ($i = 0; $i < $header_count; $i++) {
      $header = $rawHeaders[$i];
            $header_parts = explode(': ', $header, 2);
      if (count($header_parts) == 2) {
        $headers[strtolower($header_parts[0])] = $header_parts[1];
      }
    }

    return $headers;
  }
}
