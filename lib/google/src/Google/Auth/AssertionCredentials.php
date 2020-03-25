<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Auth_AssertionCredentials
{
  const MAX_TOKEN_LIFETIME_SECS = 3600;

  public $serviceAccountName;
  public $scopes;
  public $privateKey;
  public $privateKeyPassword;
  public $assertionType;
  public $sub;
  
  public $prn;
  private $useCache;

  
  public function __construct(
      $serviceAccountName,
      $scopes,
      $privateKey,
      $privateKeyPassword = 'notasecret',
      $assertionType = 'http://oauth.net/grant_type/jwt/1.0/bearer',
      $sub = false,
      $useCache = true
  ) {
    $this->serviceAccountName = $serviceAccountName;
    $this->scopes = is_string($scopes) ? $scopes : implode(' ', $scopes);
    $this->privateKey = $privateKey;
    $this->privateKeyPassword = $privateKeyPassword;
    $this->assertionType = $assertionType;
    $this->sub = $sub;
    $this->prn = $sub;
    $this->useCache = $useCache;
  }
  
  
  public function getCacheKey()
  {
    if (!$this->useCache) {
      return false;
    }
    $h = $this->sub;
    $h .= $this->assertionType;
    $h .= $this->privateKey;
    $h .= $this->scopes;
    $h .= $this->serviceAccountName;
    return md5($h);
  }

  public function generateAssertion()
  {
    $now = time();

    $jwtParams = array(
          'aud' => Google_Auth_OAuth2::OAUTH2_TOKEN_URI,
          'scope' => $this->scopes,
          'iat' => $now,
          'exp' => $now + self::MAX_TOKEN_LIFETIME_SECS,
          'iss' => $this->serviceAccountName,
    );

    if ($this->sub !== false) {
      $jwtParams['sub'] = $this->sub;
    } else if ($this->prn !== false) {
      $jwtParams['prn'] = $this->prn;
    }

    return $this->makeSignedJwt($jwtParams);
  }

  
  private function makeSignedJwt($payload)
  {
    $header = array('typ' => 'JWT', 'alg' => 'RS256');

    $payload = json_encode($payload);
            $payload = str_replace('\/', '/', $payload);

    $segments = array(
      Google_Utils::urlSafeB64Encode(json_encode($header)),
      Google_Utils::urlSafeB64Encode($payload)
    );

    $signingInput = implode('.', $segments);
    $signer = new Google_Signer_P12($this->privateKey, $this->privateKeyPassword);
    $signature = $signer->sign($signingInput);
    $segments[] = Google_Utils::urlSafeB64Encode($signature);

    return implode(".", $segments);
  }
}
