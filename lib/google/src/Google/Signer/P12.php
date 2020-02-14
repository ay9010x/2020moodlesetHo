<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Signer_P12 extends Google_Signer_Abstract
{
    private $privateKey;

    public function __construct($p12, $password)
  {
    if (!function_exists('openssl_x509_read')) {
      throw new Google_Exception(
          'The Google PHP API library needs the openssl PHP extension'
      );
    }

                    if (!$password && strpos($p12, "-----BEGIN RSA PRIVATE KEY-----") !== false) {
      $this->privateKey = openssl_pkey_get_private($p12);
    } elseif ($password === 'notasecret' && strpos($p12, "-----BEGIN PRIVATE KEY-----") !== false) {
      $this->privateKey = openssl_pkey_get_private($p12);
    } else {
            $certs = array();
      if (!openssl_pkcs12_read($p12, $certs, $password)) {
        throw new Google_Auth_Exception(
            "Unable to parse the p12 file.  " .
            "Is this a .p12 file?  Is the password correct?  OpenSSL error: " .
            openssl_error_string()
        );
      }
                  if (!array_key_exists("pkey", $certs) || !$certs["pkey"]) {
        throw new Google_Auth_Exception("No private key found in p12 file.");
      }
      $this->privateKey = openssl_pkey_get_private($certs['pkey']);
    }

    if (!$this->privateKey) {
      throw new Google_Auth_Exception("Unable to load private key");
    }
  }

  public function __destruct()
  {
    if ($this->privateKey) {
      openssl_pkey_free($this->privateKey);
    }
  }

  public function sign($data)
  {
    if (version_compare(PHP_VERSION, '5.3.0') < 0) {
      throw new Google_Auth_Exception(
          "PHP 5.3.0 or higher is required to use service accounts."
      );
    }
    $hash = defined("OPENSSL_ALGO_SHA256") ? OPENSSL_ALGO_SHA256 : "sha256";
    if (!openssl_sign($data, $signature, $this->privateKey, $hash)) {
      throw new Google_Auth_Exception("Unable to sign data");
    }
    return $signature;
  }
}
