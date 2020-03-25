<?php




if (!defined('ADODB_ERROR_HANDLER_TYPE')) define('ADODB_ERROR_HANDLER_TYPE',E_USER_ERROR);
define('ADODB_ERROR_HANDLER','adodb_throw');

class ADODB_Exception extends Exception {
var $dbms;
var $fn;
var $sql = '';
var $params = '';
var $host = '';
var $database = '';

	function __construct($dbms, $fn, $errno, $errmsg, $p1, $p2, $thisConnection)
	{
		switch($fn) {
		case 'EXECUTE':
			$this->sql = is_array($p1) ? $p1[0] : $p1;
			$this->params = $p2;
			$s = "$dbms error: [$errno: $errmsg] in $fn(\"$this->sql\")\n";
			break;

		case 'PCONNECT':
		case 'CONNECT':
			$user = $thisConnection->user;
			$s = "$dbms error: [$errno: $errmsg] in $fn($p1, '$user', '****', $p2)\n";
			break;
		default:
			$s = "$dbms error: [$errno: $errmsg] in $fn($p1, $p2)\n";
			break;
		}

		$this->dbms = $dbms;
		if ($thisConnection) {
			$this->host = $thisConnection->host;
			$this->database = $thisConnection->database;
		}
		$this->fn = $fn;
		$this->msg = $errmsg;

		if (!is_numeric($errno)) $errno = -1;
		parent::__construct($s,$errno);
	}
}



function adodb_throw($dbms, $fn, $errno, $errmsg, $p1, $p2, $thisConnection)
{
global $ADODB_EXCEPTION;

	if (error_reporting() == 0) return; 	if (is_string($ADODB_EXCEPTION)) $errfn = $ADODB_EXCEPTION;
	else $errfn = 'ADODB_EXCEPTION';
	throw new $errfn($dbms, $fn, $errno, $errmsg, $p1, $p2, $thisConnection);
}
