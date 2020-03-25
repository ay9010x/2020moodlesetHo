<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Http_Request
{
  const GZIP_UA = " (gzip)";

  private $batchHeaders = array(
    'Content-Type' => 'application/http',
    'Content-Transfer-Encoding' => 'binary',
    'MIME-Version' => '1.0',
  );

  protected $queryParams;
  protected $requestMethod;
  protected $requestHeaders;
  protected $baseComponent = null;
  protected $path;
  protected $postBody;
  protected $userAgent;
  protected $canGzip = null;

  protected $responseHttpCode;
  protected $responseHeaders;
  protected $responseBody;
  
  protected $expectedClass;
  protected $expectedRaw = false;

  public $accessKey;

  public function __construct(
      $url,
      $method = 'GET',
      $headers = array(),
      $postBody = null
  ) {
    $this->setUrl($url);
    $this->setRequestMethod($method);
    $this->setRequestHeaders($headers);
    $this->setPostBody($postBody);
  }

  
  public function getBaseComponent()
  {
    return $this->baseComponent;
  }
  
  
  public function setBaseComponent($baseComponent)
  {
    $this->baseComponent = rtrim($baseComponent, '/');
  }
  
  
  public function enableGzip()
  {
    $this->setRequestHeaders(array("Accept-Encoding" => "gzip"));
    $this->canGzip = true;
    $this->setUserAgent($this->userAgent);
  }
  
  
  public function disableGzip()
  {
    if (
        isset($this->requestHeaders['accept-encoding']) &&
        $this->requestHeaders['accept-encoding'] == "gzip"
    ) {
      unset($this->requestHeaders['accept-encoding']);
    }
    $this->canGzip = false;
    $this->userAgent = str_replace(self::GZIP_UA, "", $this->userAgent);
  }
  
  
  public function canGzip()
  {
    return $this->canGzip;
  }

  
  public function getQueryParams()
  {
    return $this->queryParams;
  }

  
  public function setQueryParam($key, $value)
  {
    $this->queryParams[$key] = $value;
  }

  
  public function getResponseHttpCode()
  {
    return (int) $this->responseHttpCode;
  }

  
  public function setResponseHttpCode($responseHttpCode)
  {
    $this->responseHttpCode = $responseHttpCode;
  }

  
  public function getResponseHeaders()
  {
    return $this->responseHeaders;
  }

  
  public function getResponseBody()
  {
    return $this->responseBody;
  }
  
  
  public function setExpectedClass($class)
  {
    $this->expectedClass = $class;
  }
  
  
  public function getExpectedClass()
  {
    return $this->expectedClass;
  }

  
  public function enableExpectedRaw()
  {
    $this->expectedRaw = true;
  }

  
  public function disableExpectedRaw()
  {
    $this->expectedRaw = false;
  }

  
  public function getExpectedRaw()
  {
    return $this->expectedRaw;
  }

  
  public function setResponseHeaders($headers)
  {
    $headers = Google_Utils::normalize($headers);
    if ($this->responseHeaders) {
      $headers = array_merge($this->responseHeaders, $headers);
    }

    $this->responseHeaders = $headers;
  }

  
  public function getResponseHeader($key)
  {
    return isset($this->responseHeaders[$key])
        ? $this->responseHeaders[$key]
        : false;
  }

  
  public function setResponseBody($responseBody)
  {
    $this->responseBody = $responseBody;
  }

  
  public function getUrl()
  {
    return $this->baseComponent . $this->path .
        (count($this->queryParams) ?
            "?" . $this->buildQuery($this->queryParams) :
            '');
  }

  
  public function getRequestMethod()
  {
    return $this->requestMethod;
  }

  
  public function getRequestHeaders()
  {
    return $this->requestHeaders;
  }

  
  public function getRequestHeader($key)
  {
    return isset($this->requestHeaders[$key])
        ? $this->requestHeaders[$key]
        : false;
  }

  
  public function getPostBody()
  {
    return $this->postBody;
  }

  
  public function setUrl($url)
  {
    if (substr($url, 0, 4) != 'http') {
            if (substr($url, 0, 1) !== '/') {
        $url = '/' . $url;
      }
    }
    $parts = parse_url($url);
    if (isset($parts['host'])) {
      $this->baseComponent = sprintf(
          "%s%s%s",
          isset($parts['scheme']) ? $parts['scheme'] . "://" : '',
          isset($parts['host']) ? $parts['host'] : '',
          isset($parts['port']) ? ":" . $parts['port'] : ''
      );
    }
    $this->path = isset($parts['path']) ? $parts['path'] : '';
    $this->queryParams = array();
    if (isset($parts['query'])) {
      $this->queryParams = $this->parseQuery($parts['query']);
    }
  }

  
  public function setRequestMethod($method)
  {
    $this->requestMethod = strtoupper($method);
  }

  
  public function setRequestHeaders($headers)
  {
    $headers = Google_Utils::normalize($headers);
    if ($this->requestHeaders) {
      $headers = array_merge($this->requestHeaders, $headers);
    }
    $this->requestHeaders = $headers;
  }

  
  public function setPostBody($postBody)
  {
    $this->postBody = $postBody;
  }

  
  public function setUserAgent($userAgent)
  {
    $this->userAgent = $userAgent;
    if ($this->canGzip) {
      $this->userAgent = $userAgent . self::GZIP_UA;
    }
  }

  
  public function getUserAgent()
  {
    return $this->userAgent;
  }

  
  public function getCacheKey()
  {
    $key = $this->getUrl();

    if (isset($this->accessKey)) {
      $key .= $this->accessKey;
    }

    if (isset($this->requestHeaders['authorization'])) {
      $key .= $this->requestHeaders['authorization'];
    }

    return md5($key);
  }

  public function getParsedCacheControl()
  {
    $parsed = array();
    $rawCacheControl = $this->getResponseHeader('cache-control');
    if ($rawCacheControl) {
      $rawCacheControl = str_replace(', ', '&', $rawCacheControl);
      parse_str($rawCacheControl, $parsed);
    }

    return $parsed;
  }

  
  public function toBatchString($id)
  {
    $str = '';
    $path = parse_url($this->getUrl(), PHP_URL_PATH) . "?" .
        http_build_query($this->queryParams);
    $str .= $this->getRequestMethod() . ' ' . $path . " HTTP/1.1\n";

    foreach ($this->getRequestHeaders() as $key => $val) {
      $str .= $key . ': ' . $val . "\n";
    }

    if ($this->getPostBody()) {
      $str .= "\n";
      $str .= $this->getPostBody();
    }
    
    $headers = '';
    foreach ($this->batchHeaders as $key => $val) {
      $headers .= $key . ': ' . $val . "\n";
    }

    $headers .= "Content-ID: $id\n";
    $str = $headers . "\n" . $str;

    return $str;
  }
  
  
  private function parseQuery($string)
  {
    $return = array();
    $parts = explode("&", $string);
    foreach ($parts as $part) {
      list($key, $value) = explode('=', $part, 2);
      $value = urldecode($value);
      if (isset($return[$key])) {
        if (!is_array($return[$key])) {
          $return[$key] = array($return[$key]);
        }
        $return[$key][] = $value;
      } else {
        $return[$key] = $value;
      }
    }
    return $return;
  }
  
  
  private function buildQuery($parts)
  {
    $return = array();
    foreach ($parts as $key => $value) {
      if (is_array($value)) {
        foreach ($value as $v) {
          $return[] = urlencode($key) . "=" . urlencode($v);
        }
      } else {
        $return[] = urlencode($key) . "=" . urlencode($value);
      }
    }
    return implode('&', $return);
  }
  
  
  public function maybeMoveParametersToBody()
  {
    if ($this->getRequestMethod() == "POST" && empty($this->postBody)) {
      $this->setRequestHeaders(
          array(
            "content-type" =>
                "application/x-www-form-urlencoded; charset=UTF-8"
          )
      );
      $this->setPostBody($this->buildQuery($this->queryParams));
      $this->queryParams = array();
    }
  }
}
