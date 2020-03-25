<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Service_Resource
{
    private $stackParameters = array(
      'alt' => array('type' => 'string', 'location' => 'query'),
      'fields' => array('type' => 'string', 'location' => 'query'),
      'trace' => array('type' => 'string', 'location' => 'query'),
      'userIp' => array('type' => 'string', 'location' => 'query'),
      'quotaUser' => array('type' => 'string', 'location' => 'query'),
      'data' => array('type' => 'string', 'location' => 'body'),
      'mimeType' => array('type' => 'string', 'location' => 'header'),
      'uploadType' => array('type' => 'string', 'location' => 'query'),
      'mediaUpload' => array('type' => 'complex', 'location' => 'query'),
      'prettyPrint' => array('type' => 'string', 'location' => 'query'),
  );

  
  private $rootUrl;

  
  private $client;

  
  private $serviceName;

  
  private $servicePath;

  
  private $resourceName;

  
  private $methods;

  public function __construct($service, $serviceName, $resourceName, $resource)
  {
    $this->rootUrl = $service->rootUrl;
    $this->client = $service->getClient();
    $this->servicePath = $service->servicePath;
    $this->serviceName = $serviceName;
    $this->resourceName = $resourceName;
    $this->methods = is_array($resource) && isset($resource['methods']) ?
        $resource['methods'] :
        array($resourceName => $resource);
  }

  
  public function call($name, $arguments, $expected_class = null)
  {
    if (! isset($this->methods[$name])) {
      $this->client->getLogger()->error(
          'Service method unknown',
          array(
              'service' => $this->serviceName,
              'resource' => $this->resourceName,
              'method' => $name
          )
      );

      throw new Google_Exception(
          "Unknown function: " .
          "{$this->serviceName}->{$this->resourceName}->{$name}()"
      );
    }
    $method = $this->methods[$name];
    $parameters = $arguments[0];

            $postBody = null;
    if (isset($parameters['postBody'])) {
      if ($parameters['postBody'] instanceof Google_Model) {
                                $parameters['postBody'] = $parameters['postBody']->toSimpleObject();
      } else if (is_object($parameters['postBody'])) {
                        $parameters['postBody'] =
            $this->convertToArrayAndStripNulls($parameters['postBody']);
      }
      $postBody = json_encode($parameters['postBody']);
      if ($postBody === false && $parameters['postBody'] !== false) {
        throw new Google_Exception("JSON encoding failed. Ensure all strings in the request are UTF-8 encoded.");
      }
      unset($parameters['postBody']);
    }

            if (isset($parameters['optParams'])) {
      $optParams = $parameters['optParams'];
      unset($parameters['optParams']);
      $parameters = array_merge($parameters, $optParams);
    }

    if (!isset($method['parameters'])) {
      $method['parameters'] = array();
    }

    $method['parameters'] = array_merge(
        $this->stackParameters,
        $method['parameters']
    );
    foreach ($parameters as $key => $val) {
      if ($key != 'postBody' && ! isset($method['parameters'][$key])) {
        $this->client->getLogger()->error(
            'Service parameter unknown',
            array(
                'service' => $this->serviceName,
                'resource' => $this->resourceName,
                'method' => $name,
                'parameter' => $key
            )
        );
        throw new Google_Exception("($name) unknown parameter: '$key'");
      }
    }

    foreach ($method['parameters'] as $paramName => $paramSpec) {
      if (isset($paramSpec['required']) &&
          $paramSpec['required'] &&
          ! isset($parameters[$paramName])
      ) {
        $this->client->getLogger()->error(
            'Service parameter missing',
            array(
                'service' => $this->serviceName,
                'resource' => $this->resourceName,
                'method' => $name,
                'parameter' => $paramName
            )
        );
        throw new Google_Exception("($name) missing required param: '$paramName'");
      }
      if (isset($parameters[$paramName])) {
        $value = $parameters[$paramName];
        $parameters[$paramName] = $paramSpec;
        $parameters[$paramName]['value'] = $value;
        unset($parameters[$paramName]['required']);
      } else {
                unset($parameters[$paramName]);
      }
    }

    $this->client->getLogger()->info(
        'Service Call',
        array(
            'service' => $this->serviceName,
            'resource' => $this->resourceName,
            'method' => $name,
            'arguments' => $parameters,
        )
    );

    $url = Google_Http_REST::createRequestUri(
        $this->servicePath,
        $method['path'],
        $parameters
    );
    $httpRequest = new Google_Http_Request(
        $url,
        $method['httpMethod'],
        null,
        $postBody
    );

    if ($this->rootUrl) {
      $httpRequest->setBaseComponent($this->rootUrl);
    } else {
      $httpRequest->setBaseComponent($this->client->getBasePath());
    }

    if ($postBody) {
      $contentTypeHeader = array();
      $contentTypeHeader['content-type'] = 'application/json; charset=UTF-8';
      $httpRequest->setRequestHeaders($contentTypeHeader);
      $httpRequest->setPostBody($postBody);
    }

    $httpRequest = $this->client->getAuth()->sign($httpRequest);
    $httpRequest->setExpectedClass($expected_class);

    if (isset($parameters['data']) &&
        ($parameters['uploadType']['value'] == 'media' || $parameters['uploadType']['value'] == 'multipart')) {
            $mfu = new Google_Http_MediaFileUpload(
          $this->client,
          $httpRequest,
          isset($parameters['mimeType']) ? $parameters['mimeType']['value'] : 'application/octet-stream',
          $parameters['data']['value']
      );
    }

    if (isset($parameters['alt']) && $parameters['alt']['value'] == 'media') {
      $httpRequest->enableExpectedRaw();
    }

    if ($this->client->shouldDefer()) {
            return $httpRequest;
    }

    return $this->client->execute($httpRequest);
  }

  protected function convertToArrayAndStripNulls($o)
  {
    $o = (array) $o;
    foreach ($o as $k => $v) {
      if ($v === null) {
        unset($o[$k]);
      } elseif (is_object($v) || is_array($v)) {
        $o[$k] = $this->convertToArrayAndStripNulls($o[$k]);
      }
    }
    return $o;
  }
}
