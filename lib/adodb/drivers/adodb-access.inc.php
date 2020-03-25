<?php

if (!defined('_ADODB_ODBC_LAYER')) {
	if (!defined('ADODB_DIR')) die();

	include(ADODB_DIR."/drivers/adodb-odbc.inc.php");
}
 if (!defined('_ADODB_ACCESS')) {
 	define('_ADODB_ACCESS',1);

class  ADODB_access extends ADODB_odbc {
	var $databaseType = 'access';
	var $hasTop = 'top';			var $fmtDate = "#Y-m-d#";
	var $fmtTimeStamp = "#Y-m-d h:i:sA#"; 	var $_bindInputArray = false; 	var $sysDate = "FORMAT(NOW,'yyyy-mm-dd')";
	var $sysTimeStamp = 'NOW';
	var $hasTransactions = false;
	var $upperCase = 'ucase';

	function __construct()
	{
	global $ADODB_EXTENSION;

		$ADODB_EXTENSION = false;
		parent::__construct();
	}

	function Time()
	{
		return time();
	}

	function BeginTrans() { return false;}

	function IfNull( $field, $ifNull )
	{
		return " IIF(IsNull($field), $ifNull, $field) "; 	}

}


class  ADORecordSet_access extends ADORecordSet_odbc {

	var $databaseType = "access";

	function __construct($id,$mode=false)
	{
		return parent::__construct($id,$mode);
	}
}}
