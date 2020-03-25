<?php

 
if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Verifier_Pem extends Google_Verifier_Abstract
{
  private $publicKey;

  
  public function __construct($pem)
  {
    if (!function_exists('openssl_x509_read')) {
      throw new Google_Exception('Google API PHP client needs the openssl PHP extension');
    }
    $this->publicKey = openssl_x509_read($pem);
    if (!$this->publicKey) {
      throw new Google_Auth_Exception("Unable to parse PEM: $pem");
    }
  }

  public function __destruct()
  {
    if ($this->publicKey) {
      openssl_x509_free($this->publicKey);
    }
  }

  
  public function verify($data, $signature)
  {
    $hash = defined("OPENSSL_ALGO_SHA256") ? OPENSSL_ALGO_SHA256 : "sha256";
    $status = openssl_verify($data, $signature, $this->publicKey, $hash);
    if ($status === -1) {
      throw new Google_Auth_Exception('Signature verification error: ' . openssl_error_string());
    }
    return $status === 1;
  }
}
