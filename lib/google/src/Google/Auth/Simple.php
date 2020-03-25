<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Auth_Simple extends Google_Auth_Abstract
{
  private $client;

  public function __construct(Google_Client $client, $config = null)
  {
    $this->client = $client;
  }

  
  public function authenticatedRequest(Google_Http_Request $request)
  {
    $request = $this->sign($request);
    return $this->io->makeRequest($request);
  }

  public function sign(Google_Http_Request $request)
  {
    $key = $this->client->getClassConfig($this, 'developer_key');
    if ($key) {
      $this->client->getLogger()->debug(
          'Simple API Access developer key authentication'
      );
      $request->setQueryParam('key', $key);
    }
    return $request;
  }
}
