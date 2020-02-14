<?php


if (!class_exists('Google_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}


class Google_Cache_Memcache extends Google_Cache_Abstract
{
  private $connection = false;
  private $mc = false;
  private $host;
  private $port;

  
  private $client;

  public function __construct(Google_Client $client)
  {
    if (!function_exists('memcache_connect') && !class_exists("Memcached")) {
      $error = "Memcache functions not available";

      $client->getLogger()->error($error);
      throw new Google_Cache_Exception($error);
    }

    $this->client = $client;

    if ($client->isAppEngine()) {
            $this->mc = new Memcached();
      $this->connection = true;
    } else {
      $this->host = $client->getClassConfig($this, 'host');
      $this->port = $client->getClassConfig($this, 'port');
      if (empty($this->host) || (empty($this->port) && (string) $this->port != "0")) {
        $error = "You need to supply a valid memcache host and port";

        $client->getLogger()->error($error);
        throw new Google_Cache_Exception($error);
      }
    }
  }

  
  public function get($key, $expiration = false)
  {
    $this->connect();
    $ret = false;
    if ($this->mc) {
      $ret = $this->mc->get($key);
    } else {
      $ret = memcache_get($this->connection, $key);
    }
    if ($ret === false) {
      $this->client->getLogger()->debug(
          'Memcache cache miss',
          array('key' => $key)
      );
      return false;
    }
    if (is_numeric($expiration) && (time() - $ret['time'] > $expiration)) {
      $this->client->getLogger()->debug(
          'Memcache cache miss (expired)',
          array('key' => $key, 'var' => $ret)
      );
      $this->delete($key);
      return false;
    }

    $this->client->getLogger()->debug(
        'Memcache cache hit',
        array('key' => $key, 'var' => $ret)
    );

    return $ret['data'];
  }

  
  public function set($key, $value)
  {
    $this->connect();
            $data = array('time' => time(), 'data' => $value);
    $rc = false;
    if ($this->mc) {
      $rc = $this->mc->set($key, $data);
    } else {
      $rc = memcache_set($this->connection, $key, $data, false);
    }
    if ($rc == false) {
      $this->client->getLogger()->error(
          'Memcache cache set failed',
          array('key' => $key, 'var' => $data)
      );

      throw new Google_Cache_Exception("Couldn't store data in cache");
    }

    $this->client->getLogger()->debug(
        'Memcache cache set',
        array('key' => $key, 'var' => $data)
    );
  }

  
  public function delete($key)
  {
    $this->connect();
    if ($this->mc) {
      $this->mc->delete($key, 0);
    } else {
      memcache_delete($this->connection, $key, 0);
    }

    $this->client->getLogger()->debug(
        'Memcache cache delete',
        array('key' => $key)
    );
  }

  
  private function connect()
  {
    if ($this->connection) {
      return;
    }

    if (class_exists("Memcached")) {
      $this->mc = new Memcached();
      $this->mc->addServer($this->host, $this->port);
       $this->connection = true;
    } else {
      $this->connection = memcache_pconnect($this->host, $this->port);
    }

    if (! $this->connection) {
      $error = "Couldn't connect to memcache server";

      $this->client->getLogger()->error($error);
      throw new Google_Cache_Exception($error);
    }
  }
}
