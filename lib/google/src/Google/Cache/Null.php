<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Cache_Null extends Google_Cache_Abstract
{
  public function __construct(Google_Client $client)
  {

  }

   
  public function get($key, $expiration = false)
  {
    return false;
  }

  
  public function set($key, $value)
  {
      }

  
  public function delete($key)
  {
      }
}
