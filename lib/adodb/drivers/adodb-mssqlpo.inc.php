<?php


if (!defined('ADODB_DIR')) die();




include_once(ADODB_DIR.'/drivers/adodb-mssql.inc.php');

class ADODB_mssqlpo extends ADODB_mssql {
	var $databaseType = "mssqlpo";
	var $concat_operator = '||';

	function PrepareSP($sql, $param = true)
	{
		if (!$this->_has_mssql_init) {
			ADOConnection::outp( "PrepareSP: mssql_init only available since PHP 4.1.0");
			return $sql;
		}
		if (is_string($sql)) $sql = str_replace('||','+',$sql);
		$stmt = mssql_init($sql,$this->_connectionID);
		if (!$stmt)  return $sql;
		return array($sql,$stmt);
	}

	function _query($sql,$inputarr=false)
	{
		if (is_string($sql)) $sql = str_replace('||','+',$sql);
		return ADODB_mssql::_query($sql,$inputarr);
	}
}

class ADORecordset_mssqlpo extends ADORecordset_mssql {
	var $databaseType = "mssqlpo";
	function __construct($id,$mode=false)
	{
		parent::__construct($id,$mode);
	}
}
