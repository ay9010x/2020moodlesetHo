<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Http_REST
{
  
  public static function execute(Google_Client $client, Google_Http_Request $req)
  {
    $runner = new Google_Task_Runner(
        $client,
        sprintf('%s %s', $req->getRequestMethod(), $req->getUrl()),
        array(get_class(), 'doExecute'),
        array($client, $req)
    );

    return $runner->run();
  }

  
  public static function doExecute(Google_Client $client, Google_Http_Request $req)
  {
    $httpRequest = $client->getIo()->makeRequest($req);
    $httpRequest->setExpectedClass($req->getExpectedClass());
    return self::decodeHttpResponse($httpRequest, $client);
  }

  
  public static function decodeHttpResponse($response, Google_Client $client = null)
  {
    $code = $response->getResponseHttpCode();
    $body = $response->getResponseBody();
    $decoded = null;

    if ((intVal($code)) >= 300) {
      $decoded = json_decode($body, true);
      $err = 'Error calling ' . $response->getRequestMethod() . ' ' . $response->getUrl();
      if (isset($decoded['error']) &&
          isset($decoded['error']['message'])  &&
          isset($decoded['error']['code'])) {
                        $err .= ": ({$decoded['error']['code']}) {$decoded['error']['message']}";
      } else {
        $err .= ": ($code) $body";
      }

      $errors = null;
            if (isset($decoded['error']) && isset($decoded['error']['errors'])) {
        $errors = $decoded['error']['errors'];
      }

      $map = null;
      if ($client) {
        $client->getLogger()->error(
            $err,
            array('code' => $code, 'errors' => $errors)
        );

        $map = $client->getClassConfig(
            'Google_Service_Exception',
            'retry_map'
        );
      }
      throw new Google_Service_Exception($err, $code, null, $errors, $map);
    }

        if ($code != '204') {
      if ($response->getExpectedRaw()) {
        return $body;
      }
      
      $decoded = json_decode($body, true);
      if ($decoded === null || $decoded === "") {
        $error = "Invalid json in service response: $body";
        if ($client) {
          $client->getLogger()->error($error);
        }
        throw new Google_Service_Exception($error);
      }

      if ($response->getExpectedClass()) {
        $class = $response->getExpectedClass();
        $decoded = new $class($decoded);
      }
    }
    return $decoded;
  }

  
  public static function createRequestUri($servicePath, $restPath, $params)
  {
    $requestUrl = $servicePath . $restPath;
    $uriTemplateVars = array();
    $queryVars = array();
    foreach ($params as $paramName => $paramSpec) {
      if ($paramSpec['type'] == 'boolean') {
        $paramSpec['value'] = ($paramSpec['value']) ? 'true' : 'false';
      }
      if ($paramSpec['location'] == 'path') {
        $uriTemplateVars[$paramName] = $paramSpec['value'];
      } else if ($paramSpec['location'] == 'query') {
        if (isset($paramSpec['repeated']) && is_array($paramSpec['value'])) {
          foreach ($paramSpec['value'] as $value) {
            $queryVars[] = $paramName . '=' . rawurlencode(rawurldecode($value));
          }
        } else {
          $queryVars[] = $paramName . '=' . rawurlencode(rawurldecode($paramSpec['value']));
        }
      }
    }

    if (count($uriTemplateVars)) {
      $uriTemplateParser = new Google_Utils_URITemplate();
      $requestUrl = $uriTemplateParser->parse($requestUrl, $uriTemplateVars);
    }

    if (count($queryVars)) {
      $requestUrl .= '?' . implode($queryVars, '&');
    }

    return $requestUrl;
  }
}
