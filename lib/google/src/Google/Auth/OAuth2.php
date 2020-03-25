<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Auth_OAuth2 extends Google_Auth_Abstract
{
  const OAUTH2_REVOKE_URI = 'https://accounts.google.com/o/oauth2/revoke';
  const OAUTH2_TOKEN_URI = 'https://accounts.google.com/o/oauth2/token';
  const OAUTH2_AUTH_URL = 'https://accounts.google.com/o/oauth2/auth';
  const CLOCK_SKEW_SECS = 300;   const AUTH_TOKEN_LIFETIME_SECS = 300;   const MAX_TOKEN_LIFETIME_SECS = 86400;   const OAUTH2_ISSUER = 'accounts.google.com';
  const OAUTH2_ISSUER_HTTPS = 'https://accounts.google.com';

  
  private $assertionCredentials;

  
  private $state;

  
  private $token = array();

  
  private $client;

  
  public function __construct(Google_Client $client)
  {
    $this->client = $client;
  }

  
  public function authenticatedRequest(Google_Http_Request $request)
  {
    $request = $this->sign($request);
    return $this->client->getIo()->makeRequest($request);
  }

  
  public function authenticate($code, $crossClient = false)
  {
    if (strlen($code) == 0) {
      throw new Google_Auth_Exception("Invalid code");
    }

    $arguments = array(
          'code' => $code,
          'grant_type' => 'authorization_code',
          'client_id' => $this->client->getClassConfig($this, 'client_id'),
          'client_secret' => $this->client->getClassConfig($this, 'client_secret')
    );

    if ($crossClient !== true) {
        $arguments['redirect_uri'] = $this->client->getClassConfig($this, 'redirect_uri');
    }

            $request = new Google_Http_Request(
        self::OAUTH2_TOKEN_URI,
        'POST',
        array(),
        $arguments
    );
    $request->disableGzip();
    $response = $this->client->getIo()->makeRequest($request);

    if ($response->getResponseHttpCode() == 200) {
      $this->setAccessToken($response->getResponseBody());
      $this->token['created'] = time();
      return $this->getAccessToken();
    } else {
      $decodedResponse = json_decode($response->getResponseBody(), true);
      if ($decodedResponse != null && $decodedResponse['error']) {
        $errorText = $decodedResponse['error'];
        if (isset($decodedResponse['error_description'])) {
          $errorText .= ": " . $decodedResponse['error_description'];
        }
      }
      throw new Google_Auth_Exception(
          sprintf(
              "Error fetching OAuth2 access token, message: '%s'",
              $errorText
          ),
          $response->getResponseHttpCode()
      );
    }
  }

  
  public function createAuthUrl($scope)
  {
    $params = array(
        'response_type' => 'code',
        'redirect_uri' => $this->client->getClassConfig($this, 'redirect_uri'),
        'client_id' => $this->client->getClassConfig($this, 'client_id'),
        'scope' => $scope,
        'access_type' => $this->client->getClassConfig($this, 'access_type'),
    );

        if ($this->client->getClassConfig($this, 'prompt')) {
      $params = $this->maybeAddParam($params, 'prompt');
    } else {
      $params = $this->maybeAddParam($params, 'approval_prompt');
    }
    $params = $this->maybeAddParam($params, 'login_hint');
    $params = $this->maybeAddParam($params, 'hd');
    $params = $this->maybeAddParam($params, 'openid.realm');
    $params = $this->maybeAddParam($params, 'include_granted_scopes');

            $rva = $this->client->getClassConfig($this, 'request_visible_actions');
    if (strpos($scope, 'plus.login') && strlen($rva) > 0) {
        $params['request_visible_actions'] = $rva;
    }

    if (isset($this->state)) {
      $params['state'] = $this->state;
    }

    return self::OAUTH2_AUTH_URL . "?" . http_build_query($params, '', '&');
  }

  
  public function setAccessToken($token)
  {
    $token = json_decode($token, true);
    if ($token == null) {
      throw new Google_Auth_Exception('Could not json decode the token');
    }
    if (! isset($token['access_token'])) {
      throw new Google_Auth_Exception("Invalid token format");
    }
    $this->token = $token;
  }

  public function getAccessToken()
  {
    return json_encode($this->token);
  }

  public function getRefreshToken()
  {
    if (array_key_exists('refresh_token', $this->token)) {
      return $this->token['refresh_token'];
    } else {
      return null;
    }
  }

  public function setState($state)
  {
    $this->state = $state;
  }

  public function setAssertionCredentials(Google_Auth_AssertionCredentials $creds)
  {
    $this->assertionCredentials = $creds;
  }

  
  public function sign(Google_Http_Request $request)
  {
        if ($this->client->getClassConfig($this, 'developer_key')) {
      $request->setQueryParam('key', $this->client->getClassConfig($this, 'developer_key'));
    }

        if (null == $this->token && null == $this->assertionCredentials) {
      return $request;
    }

            if ($this->isAccessTokenExpired()) {
      if ($this->assertionCredentials) {
        $this->refreshTokenWithAssertion();
      } else {
        $this->client->getLogger()->debug('OAuth2 access token expired');
        if (! array_key_exists('refresh_token', $this->token)) {
          $error = "The OAuth 2.0 access token has expired,"
                  ." and a refresh token is not available. Refresh tokens"
                  ." are not returned for responses that were auto-approved.";

          $this->client->getLogger()->error($error);
          throw new Google_Auth_Exception($error);
        }
        $this->refreshToken($this->token['refresh_token']);
      }
    }

    $this->client->getLogger()->debug('OAuth2 authentication');

        $request->setRequestHeaders(
        array('Authorization' => 'Bearer ' . $this->token['access_token'])
    );

    return $request;
  }

  
  public function refreshToken($refreshToken)
  {
    $this->refreshTokenRequest(
        array(
          'client_id' => $this->client->getClassConfig($this, 'client_id'),
          'client_secret' => $this->client->getClassConfig($this, 'client_secret'),
          'refresh_token' => $refreshToken,
          'grant_type' => 'refresh_token'
        )
    );
  }

  
  public function refreshTokenWithAssertion($assertionCredentials = null)
  {
    if (!$assertionCredentials) {
      $assertionCredentials = $this->assertionCredentials;
    }

    $cacheKey = $assertionCredentials->getCacheKey();

    if ($cacheKey) {
                        $token = $this->client->getCache()->get($cacheKey);
      if ($token) {
        $this->setAccessToken($token);
      }
      if (!$this->isAccessTokenExpired()) {
        return;
      }
    }

    $this->client->getLogger()->debug('OAuth2 access token expired');
    $this->refreshTokenRequest(
        array(
          'grant_type' => 'assertion',
          'assertion_type' => $assertionCredentials->assertionType,
          'assertion' => $assertionCredentials->generateAssertion(),
        )
    );

    if ($cacheKey) {
            $this->client->getCache()->set(
          $cacheKey,
          $this->getAccessToken()
      );
    }
  }

  private function refreshTokenRequest($params)
  {
    if (isset($params['assertion'])) {
      $this->client->getLogger()->info(
          'OAuth2 access token refresh with Signed JWT assertion grants.'
      );
    } else {
      $this->client->getLogger()->info('OAuth2 access token refresh');
    }

    $http = new Google_Http_Request(
        self::OAUTH2_TOKEN_URI,
        'POST',
        array(),
        $params
    );
    $http->disableGzip();
    $request = $this->client->getIo()->makeRequest($http);

    $code = $request->getResponseHttpCode();
    $body = $request->getResponseBody();
    if (200 == $code) {
      $token = json_decode($body, true);
      if ($token == null) {
        throw new Google_Auth_Exception("Could not json decode the access token");
      }

      if (! isset($token['access_token']) || ! isset($token['expires_in'])) {
        throw new Google_Auth_Exception("Invalid token format");
      }

      if (isset($token['id_token'])) {
        $this->token['id_token'] = $token['id_token'];
      }
      $this->token['access_token'] = $token['access_token'];
      $this->token['expires_in'] = $token['expires_in'];
      $this->token['created'] = time();
    } else {
      throw new Google_Auth_Exception("Error refreshing the OAuth2 token, message: '$body'", $code);
    }
  }

  
  public function revokeToken($token = null)
  {
    if (!$token) {
      if (!$this->token) {
                return false;
      } elseif (array_key_exists('refresh_token', $this->token)) {
        $token = $this->token['refresh_token'];
      } else {
        $token = $this->token['access_token'];
      }
    }
    $request = new Google_Http_Request(
        self::OAUTH2_REVOKE_URI,
        'POST',
        array(),
        "token=$token"
    );
    $request->disableGzip();
    $response = $this->client->getIo()->makeRequest($request);
    $code = $response->getResponseHttpCode();
    if ($code == 200) {
      $this->token = null;
      return true;
    }

    return false;
  }

  
  public function isAccessTokenExpired()
  {
    if (!$this->token || !isset($this->token['created'])) {
      return true;
    }

        $expired = ($this->token['created']
        + ($this->token['expires_in'] - 30)) < time();

    return $expired;
  }

        private function getFederatedSignOnCerts()
  {
    return $this->retrieveCertsFromLocation(
        $this->client->getClassConfig($this, 'federated_signon_certs_url')
    );
  }

  
  public function retrieveCertsFromLocation($url)
  {
        if ("http" != substr($url, 0, 4)) {
      $file = file_get_contents($url);
      if ($file) {
        return json_decode($file, true);
      } else {
        throw new Google_Auth_Exception(
            "Failed to retrieve verification certificates: '" .
            $url . "'."
        );
      }
    }

        $request = $this->client->getIo()->makeRequest(
        new Google_Http_Request(
            $url
        )
    );
    if ($request->getResponseHttpCode() == 200) {
      $certs = json_decode($request->getResponseBody(), true);
      if ($certs) {
        return $certs;
      }
    }
    throw new Google_Auth_Exception(
        "Failed to retrieve verification certificates: '" .
        $request->getResponseBody() . "'.",
        $request->getResponseHttpCode()
    );
  }

  
  public function verifyIdToken($id_token = null, $audience = null)
  {
    if (!$id_token) {
      $id_token = $this->token['id_token'];
    }
    $certs = $this->getFederatedSignonCerts();
    if (!$audience) {
      $audience = $this->client->getClassConfig($this, 'client_id');
    }

    return $this->verifySignedJwtWithCerts(
        $id_token,
        $certs,
        $audience,
        array(self::OAUTH2_ISSUER, self::OAUTH2_ISSUER_HTTPS)
    );
  }

  
  public function verifySignedJwtWithCerts(
      $jwt,
      $certs,
      $required_audience,
      $issuer = null,
      $max_expiry = null
  ) {
    if (!$max_expiry) {
            $max_expiry = self::MAX_TOKEN_LIFETIME_SECS;
    }

    $segments = explode(".", $jwt);
    if (count($segments) != 3) {
      throw new Google_Auth_Exception("Wrong number of segments in token: $jwt");
    }
    $signed = $segments[0] . "." . $segments[1];
    $signature = Google_Utils::urlSafeB64Decode($segments[2]);

        $envelope = json_decode(Google_Utils::urlSafeB64Decode($segments[0]), true);
    if (!$envelope) {
      throw new Google_Auth_Exception("Can't parse token envelope: " . $segments[0]);
    }

        $json_body = Google_Utils::urlSafeB64Decode($segments[1]);
    $payload = json_decode($json_body, true);
    if (!$payload) {
      throw new Google_Auth_Exception("Can't parse token payload: " . $segments[1]);
    }

        $verified = false;
    foreach ($certs as $keyName => $pem) {
      $public_key = new Google_Verifier_Pem($pem);
      if ($public_key->verify($signed, $signature)) {
        $verified = true;
        break;
      }
    }

    if (!$verified) {
      throw new Google_Auth_Exception("Invalid token signature: $jwt");
    }

        $iat = 0;
    if (array_key_exists("iat", $payload)) {
      $iat = $payload["iat"];
    }
    if (!$iat) {
      throw new Google_Auth_Exception("No issue time in token: $json_body");
    }
    $earliest = $iat - self::CLOCK_SKEW_SECS;

        $now = time();
    $exp = 0;
    if (array_key_exists("exp", $payload)) {
      $exp = $payload["exp"];
    }
    if (!$exp) {
      throw new Google_Auth_Exception("No expiration time in token: $json_body");
    }
    if ($exp >= $now + $max_expiry) {
      throw new Google_Auth_Exception(
          sprintf("Expiration time too far in future: %s", $json_body)
      );
    }

    $latest = $exp + self::CLOCK_SKEW_SECS;
    if ($now < $earliest) {
      throw new Google_Auth_Exception(
          sprintf(
              "Token used too early, %s < %s: %s",
              $now,
              $earliest,
              $json_body
          )
      );
    }
    if ($now > $latest) {
      throw new Google_Auth_Exception(
          sprintf(
              "Token used too late, %s > %s: %s",
              $now,
              $latest,
              $json_body
          )
      );
    }

            $iss = $payload['iss'];
    if ($issuer && !in_array($iss, (array) $issuer)) {
      throw new Google_Auth_Exception(
          sprintf(
              "Invalid issuer, %s not in %s: %s",
              $iss,
              "[".implode(",", (array) $issuer)."]",
              $json_body
          )
      );
    }

        $aud = $payload["aud"];
    if ($aud != $required_audience) {
      throw new Google_Auth_Exception(
          sprintf(
              "Wrong recipient, %s != %s:",
              $aud,
              $required_audience,
              $json_body
          )
      );
    }

        return new Google_Auth_LoginTicket($envelope, $payload);
  }

  
  private function maybeAddParam($params, $name)
  {
    $param = $this->client->getClassConfig($this, $name);
    if ($param != '') {
      $params[$name] = $param;
    }
    return $params;
  }
}
