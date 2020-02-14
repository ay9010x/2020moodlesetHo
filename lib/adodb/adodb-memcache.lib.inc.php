<?php

if (!defined('ADODB_DIR')) die();

global $ADODB_INCLUDED_MEMCACHE;
$ADODB_INCLUDED_MEMCACHE = 1;

global $ADODB_INCLUDED_CSV;
if (empty($ADODB_INCLUDED_CSV)) include_once(ADODB_DIR.'/adodb-csvlib.inc.php');



	class ADODB_Cache_MemCache {
		var $createdir = false; 
				
		var $hosts;			var $port = 11211;
		var $compress = false; 
		var $_connected = false;
		var $_memcache = false;

		function __construct(&$obj)
		{
			$this->hosts = $obj->memCacheHost;
			$this->port = $obj->memCachePort;
			$this->compress = $obj->memCacheCompress;
		}

				function connect(&$err)
		{
			if (!function_exists('memcache_pconnect')) {
				$err = 'Memcache module PECL extension not found!';
				return false;
			}

			$memcache = new MemCache;

			if (!is_array($this->hosts)) $this->hosts = array($this->hosts);

			$failcnt = 0;
			foreach($this->hosts as $host) {
				if (!@$memcache->addServer($host,$this->port,true)) {
					$failcnt += 1;
				}
			}
			if ($failcnt == sizeof($this->hosts)) {
				$err = 'Can\'t connect to any memcache server';
				return false;
			}
			$this->_connected = true;
			$this->_memcache = $memcache;
			return true;
		}

				function writecache($filename, $contents, $debug, $secs2cache)
		{
			if (!$this->_connected) {
				$err = '';
				if (!$this->connect($err) && $debug) ADOConnection::outp($err);
			}
			if (!$this->_memcache) return false;

			if (!$this->_memcache->set($filename, $contents, $this->compress ? MEMCACHE_COMPRESSED : 0, $secs2cache)) {
				if ($debug) ADOConnection::outp(" Failed to save data at the memcached server!<br>\n");
				return false;
			}

			return true;
		}

				function readcache($filename, &$err, $secs2cache, $rsClass)
		{
			$false = false;
			if (!$this->_connected) $this->connect($err);
			if (!$this->_memcache) return $false;

			$rs = $this->_memcache->get($filename);
			if (!$rs) {
				$err = 'Item with such key doesn\'t exists on the memcached server.';
				return $false;
			}

						$rs = explode("\n", $rs);
            unset($rs[0]);
            $rs = join("\n", $rs);
 			$rs = unserialize($rs);
			if (! is_object($rs)) {
				$err = 'Unable to unserialize $rs';
				return $false;
			}
			if ($rs->timeCreated == 0) return $rs; 
			$tdiff = intval($rs->timeCreated+$secs2cache - time());
			if ($tdiff <= 2) {
				switch($tdiff) {
					case 2:
						if ((rand() & 15) == 0) {
							$err = "Timeout 2";
							return $false;
						}
						break;
					case 1:
						if ((rand() & 3) == 0) {
							$err = "Timeout 1";
							return $false;
						}
						break;
					default:
						$err = "Timeout 0";
						return $false;
				}
			}
			return $rs;
		}

		function flushall($debug=false)
		{
			if (!$this->_connected) {
				$err = '';
				if (!$this->connect($err) && $debug) ADOConnection::outp($err);
			}
			if (!$this->_memcache) return false;

			$del = $this->_memcache->flush();

			if ($debug)
				if (!$del) ADOConnection::outp("flushall: failed!<br>\n");
				else ADOConnection::outp("flushall: succeeded!<br>\n");

			return $del;
		}

		function flushcache($filename, $debug=false)
		{
			if (!$this->_connected) {
  				$err = '';
  				if (!$this->connect($err) && $debug) ADOConnection::outp($err);
			}
			if (!$this->_memcache) return false;

			$del = $this->_memcache->delete($filename);

			if ($debug)
				if (!$del) ADOConnection::outp("flushcache: $key entry doesn't exist on memcached server!<br>\n");
				else ADOConnection::outp("flushcache: $key entry flushed from memcached server!<br>\n");

			return $del;
		}

				function createdir($dir, $hash)
		{
			return true;
		}
	}
