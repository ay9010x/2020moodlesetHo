<?php


if (!defined('ADODB_DIR')) die();

include_once(ADODB_DIR."/drivers/adodb-ibase.inc.php");

class ADODB_firebird extends ADODB_ibase {
	var $databaseType = "firebird";
	var $dialect = 3;

	var $sysTimeStamp = "CURRENT_TIMESTAMP"; 
	function ServerInfo()
	{
		$arr['dialect'] = $this->dialect;
		switch($arr['dialect']) {
		case '':
		case '1': $s = 'Firebird Dialect 1'; break;
		case '2': $s = 'Firebird Dialect 2'; break;
		default:
		case '3': $s = 'Firebird Dialect 3'; break;
		}
		$arr['version'] = ADOConnection::_findvers($s);
		$arr['description'] = $s;
		return $arr;
	}

				function SelectLimit($sql,$nrows=-1,$offset=-1,$inputarr=false, $secs=0)
	{
		$nrows = (integer) $nrows;
		$offset = (integer) $offset;
		$str = 'SELECT ';
		if ($nrows >= 0) $str .= "FIRST $nrows ";
		$str .=($offset>=0) ? "SKIP $offset " : '';

		$sql = preg_replace('/^[ \t]*select/i',$str,$sql);
		if ($secs)
			$rs = $this->CacheExecute($secs,$sql,$inputarr);
		else
			$rs = $this->Execute($sql,$inputarr);

		return $rs;
	}


};


class  ADORecordSet_firebird extends ADORecordSet_ibase {

	var $databaseType = "firebird";

	function __construct($id,$mode=false)
	{
		parent::__construct($id,$mode);
	}
}
