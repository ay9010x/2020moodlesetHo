<?php


if (!defined('ADODB_DIR')) die();

include_once(ADODB_DIR.'/drivers/adodb-oci8.inc.php');

class ADODB_oci805 extends ADODB_oci8 {
	var $databaseType = "oci805";
	var $connectSID = true;

	function SelectLimit($sql,$nrows=-1,$offset=-1, $inputarr=false,$secs2cache=0)
	{
				if (strpos($sql,'/*+') !== false)
			$sql = str_replace('/*+ ','/*+FIRST_ROWS ',$sql);
		else
			$sql = preg_replace('/^[ \t\n]*select/i','SELECT /*+FIRST_ROWS*/',$sql);

		

		return ADOConnection::SelectLimit($sql,$nrows,$offset,$inputarr,$secs2cache);
	}
}

class ADORecordset_oci805 extends ADORecordset_oci8 {
	var $databaseType = "oci805";
	function __construct($id,$mode=false)
	{
		parent::__construct($id,$mode);
	}
}
