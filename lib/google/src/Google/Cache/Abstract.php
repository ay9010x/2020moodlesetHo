<?php



abstract class Google_Cache_Abstract
{
  
  abstract public function __construct(Google_Client $client);

  
  abstract public function get($key, $expiration = false);

  
  abstract public function set($key, $value);

  
  abstract public function delete($key);
}
