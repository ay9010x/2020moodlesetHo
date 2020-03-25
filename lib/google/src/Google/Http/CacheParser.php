<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Http_CacheParser
{
  public static $CACHEABLE_HTTP_METHODS = array('GET', 'HEAD');
  public static $CACHEABLE_STATUS_CODES = array('200', '203', '300', '301');

  
  public static function isRequestCacheable(Google_Http_Request $resp)
  {
    $method = $resp->getRequestMethod();
    if (! in_array($method, self::$CACHEABLE_HTTP_METHODS)) {
      return false;
    }

                    if ($resp->getRequestHeader("authorization")) {
      return false;
    }

    return true;
  }

  
  public static function isResponseCacheable(Google_Http_Request $resp)
  {
            if (false == self::isRequestCacheable($resp)) {
      return false;
    }

    $code = $resp->getResponseHttpCode();
    if (! in_array($code, self::$CACHEABLE_STATUS_CODES)) {
      return false;
    }

            $etag = $resp->getResponseHeader("etag");
    if (self::isExpired($resp) && $etag == false) {
      return false;
    }

            $cacheControl = $resp->getParsedCacheControl();
    if (isset($cacheControl['no-store'])) {
      return false;
    }

            $pragma = $resp->getResponseHeader('pragma');
    if ($pragma == 'no-cache' || strpos($pragma, 'no-cache') !== false) {
      return false;
    }

                    $vary = $resp->getResponseHeader('vary');
    if ($vary) {
      return false;
    }

    return true;
  }

  
  public static function isExpired(Google_Http_Request $resp)
  {
            $parsedExpires = false;
    $responseHeaders = $resp->getResponseHeaders();

    if (isset($responseHeaders['expires'])) {
      $rawExpires = $responseHeaders['expires'];
            if (empty($rawExpires) || (is_numeric($rawExpires) && $rawExpires <= 0)) {
        return true;
      }

            $parsedExpires = strtotime($rawExpires);
      if (false == $parsedExpires || $parsedExpires <= 0) {
        return true;
      }
    }

        $freshnessLifetime = false;
    $cacheControl = $resp->getParsedCacheControl();
    if (isset($cacheControl['max-age'])) {
      $freshnessLifetime = $cacheControl['max-age'];
    }

    $rawDate = $resp->getResponseHeader('date');
    $parsedDate = strtotime($rawDate);

    if (empty($rawDate) || false == $parsedDate) {
                        throw new Google_Exception("All cacheable requests must have creation dates.");
    }

    if (false == $freshnessLifetime && isset($responseHeaders['expires'])) {
      $freshnessLifetime = $parsedExpires - $parsedDate;
    }

    if (false == $freshnessLifetime) {
      return true;
    }

        $age = max(0, time() - $parsedDate);
    if (isset($responseHeaders['age'])) {
      $age = max($age, strtotime($responseHeaders['age']));
    }

    return $freshnessLifetime <= $age;
  }

  
  public static function mustRevalidate(Google_Http_Request $response)
  {
                return self::isExpired($response);
  }
}
