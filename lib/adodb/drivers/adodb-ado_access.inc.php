<?php


if (!defined('ADODB_DIR')) die();

if (!defined('_ADODB_ADO_LAYER')) {
	if (PHP_VERSION >= 5) include(ADODB_DIR."/drivers/adodb-ado5.inc.php");
	else include(ADODB_DIR."/drivers/adodb-ado.inc.php");
}

class  ADODB_ado_access extends ADODB_ado {
	var $databaseType = 'ado_access';
	var $hasTop = 'top';			var $fmtDate = "#Y-m-d#";
	var $fmtTimeStamp = "#Y-m-d h:i:sA#";	var $sysDate = "FORMAT(NOW,'yyyy-mm-dd')";
	var $sysTimeStamp = 'NOW';
	var $upperCase = 'ucase';

	

}


class  ADORecordSet_ado_access extends ADORecordSet_ado {

	var $databaseType = "ado_access";

	function __construct($id,$mode=false)
	{
		return parent::__construct($id,$mode);
	}
}
