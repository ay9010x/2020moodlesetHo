<?php


if (!defined('ADODB_DIR')) die();

include_once(ADODB_DIR."/drivers/adodb-postgres7.inc.php");

class ADODB_postgres8 extends ADODB_postgres7
{
	var $databaseType = 'postgres8';


	
	function _insertid($table, $column)
	{
		return empty($table) || empty($column)
			? $this->GetOne("SELECT lastval()")
			: $this->GetOne("SELECT currval(pg_get_serial_sequence('$table', '$column'))");
	}
}

class ADORecordSet_postgres8 extends ADORecordSet_postgres7
{
	var $databaseType = "postgres8";
}

class ADORecordSet_assoc_postgres8 extends ADORecordSet_assoc_postgres7
{
	var $databaseType = "postgres8";
}
