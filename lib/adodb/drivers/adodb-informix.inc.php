<?php


if (!defined('ADODB_DIR')) die();

include_once(ADODB_DIR.'/drivers/adodb-informix72.inc.php');

class ADODB_informix extends ADODB_informix72 {
	var $databaseType = "informix";
	var $hasTop = 'FIRST';
	var $ansiOuter = true;

	function IfNull( $field, $ifNull )
	{
		return " NVL($field, $ifNull) "; 	}
}

class ADORecordset_informix extends ADORecordset_informix72 {
	var $databaseType = "informix";

	function __construct($id,$mode=false)
	{
		parent::__construct($id,$mode);
	}
}
