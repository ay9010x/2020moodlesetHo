<?php


 

define('ADODB_PEAR',dirname(__FILE__));
include_once "PEAR.php";
include_once ADODB_PEAR."/adodb-errorpear.inc.php";
include_once ADODB_PEAR."/adodb.inc.php";

if (!defined('DB_OK')) {
define("DB_OK",	1);
define("DB_ERROR",-1);



define('DB_FETCHMODE_DEFAULT', 0);



define('DB_FETCHMODE_ORDERED', 1);



define('DB_FETCHMODE_ASSOC', 2);



define('DB_GETMODE_ORDERED', DB_FETCHMODE_ORDERED);
define('DB_GETMODE_ASSOC',   DB_FETCHMODE_ASSOC);



define('DB_TABLEINFO_ORDER', 1);
define('DB_TABLEINFO_ORDERTABLE', 2);
define('DB_TABLEINFO_FULL', 3);
}



class DB
{
	

	function factory($type)
	{
		include_once(ADODB_DIR."/drivers/adodb-$type.inc.php");
		$obj = NewADOConnection($type);
		if (!is_object($obj)) $obj = new PEAR_Error('Unknown Database Driver: '.$dsninfo['phptype'],-1);
		return $obj;
	}

	
	function connect($dsn, $options = false)
	{
		if (is_array($dsn)) {
			$dsninfo = $dsn;
		} else {
			$dsninfo = DB::parseDSN($dsn);
		}
		switch ($dsninfo["phptype"]) {
			case 'pgsql': 	$type = 'postgres7'; break;
			case 'ifx':		$type = 'informix9'; break;
			default: 		$type = $dsninfo["phptype"]; break;
		}

		if (is_array($options) && isset($options["debug"]) &&
			$options["debug"] >= 2) {
						 @include_once("adodb-$type.inc.php");
		} else {
			 @include_once("adodb-$type.inc.php");
		}

		@$obj = NewADOConnection($type);
		if (!is_object($obj)) {
			$obj = new PEAR_Error('Unknown Database Driver: '.$dsninfo['phptype'],-1);
			return $obj;
		}
		if (is_array($options)) {
			foreach($options as $k => $v) {
				switch(strtolower($k)) {
				case 'persist':
				case 'persistent': 	$persist = $v; break;
								case 'dialect': 	$obj->dialect = $v; break;
				case 'charset':		$obj->charset = $v; break;
				case 'buffers':		$obj->buffers = $v; break;
								case 'charpage':	$obj->charPage = $v; break;
								case 'clientflags': $obj->clientFlags = $v; break;
				}
			}
		} else {
		   	$persist = false;
		}

		if (isset($dsninfo['socket'])) $dsninfo['hostspec'] .= ':'.$dsninfo['socket'];
		else if (isset($dsninfo['port'])) $dsninfo['hostspec'] .= ':'.$dsninfo['port'];

		if($persist) $ok = $obj->PConnect($dsninfo['hostspec'], $dsninfo['username'],$dsninfo['password'],$dsninfo['database']);
		else  $ok = $obj->Connect($dsninfo['hostspec'], $dsninfo['username'],$dsninfo['password'],$dsninfo['database']);

		if (!$ok) $obj = ADODB_PEAR_Error();
		return $obj;
	}

	
	function apiVersion()
	{
		return 2;
	}

	
	function isError($value)
	{
		if (!is_object($value)) return false;
		$class = strtolower(get_class($value));
		return $class == 'pear_error' || is_subclass_of($value, 'pear_error') ||
				$class == 'db_error' || is_subclass_of($value, 'db_error');
	}


	
	function isWarning($value)
	{
		return false;
		
	}

	
	function parseDSN($dsn)
	{
		if (is_array($dsn)) {
			return $dsn;
		}

		$parsed = array(
			'phptype'  => false,
			'dbsyntax' => false,
			'protocol' => false,
			'hostspec' => false,
			'database' => false,
			'username' => false,
			'password' => false
		);

				if (($pos = strpos($dsn, '://')) !== false) {
			$str = substr($dsn, 0, $pos);
			$dsn = substr($dsn, $pos + 3);
		} else {
			$str = $dsn;
			$dsn = NULL;
		}

						if (preg_match('|^(.+?)\((.*?)\)$|', $str, $arr)) {
			$parsed['phptype'] = $arr[1];
			$parsed['dbsyntax'] = (empty($arr[2])) ? $arr[1] : $arr[2];
		} else {
			$parsed['phptype'] = $str;
			$parsed['dbsyntax'] = $str;
		}

		if (empty($dsn)) {
			return $parsed;
		}

						if (($at = strpos($dsn,'@')) !== false) {
			$str = substr($dsn, 0, $at);
			$dsn = substr($dsn, $at + 1);
			if (($pos = strpos($str, ':')) !== false) {
				$parsed['username'] = urldecode(substr($str, 0, $pos));
				$parsed['password'] = urldecode(substr($str, $pos + 1));
			} else {
				$parsed['username'] = urldecode($str);
			}
		}

						if (($pos=strpos($dsn, '/')) !== false) {
			$str = substr($dsn, 0, $pos);
			$dsn = substr($dsn, $pos + 1);
		} else {
			$str = $dsn;
			$dsn = NULL;
		}

						if (($pos=strpos($str, '+')) !== false) {
			$parsed['protocol'] = substr($str, 0, $pos);
			$parsed['hostspec'] = urldecode(substr($str, $pos + 1));
		} else {
			$parsed['hostspec'] = urldecode($str);
		}

						if (!empty($dsn)) {
			$parsed['database'] = $dsn;
		}

		return $parsed;
	}

	
	function assertExtension($name)
	{
		if (!extension_loaded($name)) {
			$dlext = (strncmp(PHP_OS,'WIN',3) === 0) ? '.dll' : '.so';
			@dl($name . $dlext);
		}
		if (!extension_loaded($name)) {
			return false;
		}
		return true;
	}
}
