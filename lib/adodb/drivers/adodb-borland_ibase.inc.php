<?php


if (!defined('ADODB_DIR')) die();

include_once(ADODB_DIR."/drivers/adodb-ibase.inc.php");

class ADODB_borland_ibase extends ADODB_ibase {
	var $databaseType = "borland_ibase";

	function BeginTrans()
	{
		if ($this->transOff) return true;
		$this->transCnt += 1;
		$this->autoCommit = false;
	 	$this->_transactionID = ibase_trans($this->ibasetrans, $this->_connectionID);
		return $this->_transactionID;
	}

	function ServerInfo()
	{
		$arr['dialect'] = $this->dialect;
		switch($arr['dialect']) {
		case '':
		case '1': $s = 'Interbase 6.5, Dialect 1'; break;
		case '2': $s = 'Interbase 6.5, Dialect 2'; break;
		default:
		case '3': $s = 'Interbase 6.5, Dialect 3'; break;
		}
		$arr['version'] = '6.5';
		$arr['description'] = $s;
		return $arr;
	}

						function SelectLimit($sql,$nrows=-1,$offset=-1,$inputarr=false,$secs2cache=0)
	{
		if ($nrows > 0) {
			if ($offset <= 0) $str = " ROWS $nrows ";
			else {
				$a = $offset+1;
				$b = $offset+$nrows;
				$str = " ROWS $a TO $b";
			}
		} else {
						$a = $offset + 1;
			$str = " ROWS $a TO 999999999"; 		}
		$sql .= $str;

		return ($secs2cache) ?
				$this->CacheExecute($secs2cache,$sql,$inputarr)
			:
				$this->Execute($sql,$inputarr);
	}

};


class  ADORecordSet_borland_ibase extends ADORecordSet_ibase {

	var $databaseType = "borland_ibase";

	function __construct($id,$mode=false)
	{
		parent::__construct($id,$mode);
	}
}
