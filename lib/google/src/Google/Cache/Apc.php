<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Cache_Apc extends Google_Cache_Abstract
{
  
  private $client;

  public function __construct(Google_Client $client)
  {
    if (! function_exists('apc_add') ) {
      $error = "Apc functions not available";

      $client->getLogger()->error($error);
      throw new Google_Cache_Exception($error);
    }

    $this->client = $client;
  }

   
  public function get($key, $expiration = false)
  {
    $ret = apc_fetch($key);
    if ($ret === false) {
      $this->client->getLogger()->debug(
          'APC cache miss',
          array('key' => $key)
      );
      return false;
    }
    if (is_numeric($expiration) && (time() - $ret['time'] > $expiration)) {
      $this->client->getLogger()->debug(
          'APC cache miss (expired)',
          array('key' => $key, 'var' => $ret)
      );
      $this->delete($key);
      return false;
    }

    $this->client->getLogger()->debug(
        'APC cache hit',
        array('key' => $key, 'var' => $ret)
    );

    return $ret['data'];
  }

  
  public function set($key, $value)
  {
    $var = array('time' => time(), 'data' => $value);
    $rc = apc_store($key, $var);

    if ($rc == false) {
      $this->client->getLogger()->error(
          'APC cache set failed',
          array('key' => $key, 'var' => $var)
      );
      throw new Google_Cache_Exception("Couldn't store data");
    }

    $this->client->getLogger()->debug(
        'APC cache set',
        array('key' => $key, 'var' => $var)
    );
  }

  
  public function delete($key)
  {
    $this->client->getLogger()->debug(
        'APC cache delete',
        array('key' => $key)
    );
    apc_delete($key);
  }
}
