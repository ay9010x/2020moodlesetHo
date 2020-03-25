<?php



if (!defined('ADODB_ERROR_HANDLER_TYPE')) define('ADODB_ERROR_HANDLER_TYPE',E_USER_ERROR);

if (!defined('ADODB_ERROR_HANDLER')) define('ADODB_ERROR_HANDLER','ADODB_Error_Handler');


function ADODB_Error_Handler($dbms, $fn, $errno, $errmsg, $p1, $p2, &$thisConnection)
{
	if (error_reporting() == 0) return; 	switch($fn) {
	case 'EXECUTE':
		$sql = $p1;
		$inputparams = $p2;

		$s = "$dbms error: [$errno: $errmsg] in $fn(\"$sql\")\n";
		break;

	case 'PCONNECT':
	case 'CONNECT':
		$host = $p1;
		$database = $p2;

		$s = "$dbms error: [$errno: $errmsg] in $fn($host, '****', '****', $database)\n";
		break;
	default:
		$s = "$dbms error: [$errno: $errmsg] in $fn($p1, $p2)\n";
		break;
	}
	
	if (defined('ADODB_ERROR_LOG_TYPE')) {
		$t = date('Y-m-d H:i:s');
		if (defined('ADODB_ERROR_LOG_DEST'))
			error_log("($t) $s", ADODB_ERROR_LOG_TYPE, ADODB_ERROR_LOG_DEST);
		else
			error_log("($t) $s", ADODB_ERROR_LOG_TYPE);
	}


		trigger_error($s,ADODB_ERROR_HANDLER_TYPE);
}
