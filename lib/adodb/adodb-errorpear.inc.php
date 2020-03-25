<?php

include_once('PEAR.php');

if (!defined('ADODB_ERROR_HANDLER')) define('ADODB_ERROR_HANDLER','ADODB_Error_PEAR');




if (!defined('ADODB_PEAR_ERROR_CLASS')) define('ADODB_PEAR_ERROR_CLASS','PEAR_Error');


global $ADODB_Last_PEAR_Error; $ADODB_Last_PEAR_Error = false;

  
function ADODB_Error_PEAR($dbms, $fn, $errno, $errmsg, $p1=false, $p2=false)
{
global $ADODB_Last_PEAR_Error;

	if (error_reporting() == 0) return; 	switch($fn) {
	case 'EXECUTE':
		$sql = $p1;
		$inputparams = $p2;

		$s = "$dbms error: [$errno: $errmsg] in $fn(\"$sql\")";
		break;

	case 'PCONNECT':
	case 'CONNECT':
		$host = $p1;
		$database = $p2;

		$s = "$dbms error: [$errno: $errmsg] in $fn('$host', ?, ?, '$database')";
		break;

	default:
		$s = "$dbms error: [$errno: $errmsg] in $fn($p1, $p2)";
		break;
	}

	$class = ADODB_PEAR_ERROR_CLASS;
	$ADODB_Last_PEAR_Error = new $class($s, $errno,
		$GLOBALS['_PEAR_default_error_mode'],
		$GLOBALS['_PEAR_default_error_options'],
		$errmsg);

	}


function ADODB_PEAR_Error()
{
global $ADODB_Last_PEAR_Error;

	return $ADODB_Last_PEAR_Error;
}
