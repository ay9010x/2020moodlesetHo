<?php



use google\appengine\api\app_identity\AppIdentityService;

if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Auth_AppIdentity extends Google_Auth_Abstract
{
  const CACHE_PREFIX = "Google_Auth_AppIdentity::";
  private $client;
  private $token = false;
  private $tokenScopes = false;

  public function __construct(Google_Client $client, $config = null)
  {
    $this->client = $client;
  }

  
  public function authenticateForScope($scopes)
  {
    if ($this->token && $this->tokenScopes == $scopes) {
      return $this->token;
    }

    $cacheKey = self::CACHE_PREFIX;
    if (is_string($scopes)) {
      $cacheKey .= $scopes;
    } else if (is_array($scopes)) {
      $cacheKey .= implode(":", $scopes);
    }

    $this->token = $this->client->getCache()->get($cacheKey);
    if (!$this->token) {
      $this->retrieveToken($scopes, $cacheKey);
    } else if ($this->token['expiration_time'] < time()) {
      $this->client->getCache()->delete($cacheKey);
      $this->retrieveToken($scopes, $cacheKey);
    }

    $this->tokenScopes = $scopes;
    return $this->token;
  }

  
  private function retrieveToken($scopes, $cacheKey)
  {
    $this->token = AppIdentityService::getAccessToken($scopes);
    if ($this->token) {
      $this->client->getCache()->set(
          $cacheKey,
          $this->token
      );
    }
  }

  
  public function authenticatedRequest(Google_Http_Request $request)
  {
    $request = $this->sign($request);
    return $this->client->getIo()->makeRequest($request);
  }

  public function sign(Google_Http_Request $request)
  {
    if (!$this->token) {
            return $request;
    }

    $this->client->getLogger()->debug('App Identity authentication');

        $request->setRequestHeaders(
        array('Authorization' => 'Bearer ' . $this->token['access_token'])
    );

    return $request;
  }
}
