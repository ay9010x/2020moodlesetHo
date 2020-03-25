<?php


if (!defined('ADODB_DIR')) die();

include_once(ADODB_DIR."/drivers/adodb-postgres8.inc.php");

class ADODB_postgres9 extends ADODB_postgres8
{
	var $databaseType = 'postgres9';
}

class ADORecordSet_postgres9 extends ADORecordSet_postgres8
{
	var $databaseType = "postgres9";
}

class ADORecordSet_assoc_postgres9 extends ADORecordSet_assoc_postgres8
{
	var $databaseType = "postgres9";
}
