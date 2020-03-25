<?php


if (!defined('ADODB_DIR')) die();

if (! defined("_ADODB_CSV_LAYER")) {
 define("_ADODB_CSV_LAYER", 1 );

include_once(ADODB_DIR.'/adodb-csvlib.inc.php');

class ADODB_csv extends ADOConnection {
	var $databaseType = 'csv';
	var $databaseProvider = 'csv';
	var $hasInsertID = true;
	var $hasAffectedRows = true;
	var $fmtTimeStamp = "'Y-m-d H:i:s'";
	var $_affectedrows=0;
	var $_insertid=0;
	var $_url;
	var $replaceQuote = "''"; 	var $hasTransactions = false;
	var $_errorNo = false;

	function __construct()
	{
	}

	function _insertid()
	{
			return $this->_insertid;
	}

	function _affectedrows()
	{
			return $this->_affectedrows;
	}

  	function MetaDatabases()
	{
		return false;
	}


		function _connect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
		if (strtolower(substr($argHostname,0,7)) !== 'http://') return false;
		$this->_url = $argHostname;
		return true;
	}

		function _pconnect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
		if (strtolower(substr($argHostname,0,7)) !== 'http://') return false;
		$this->_url = $argHostname;
		return true;
	}

 	function MetaColumns($table, $normalize=true)
	{
		return false;
	}


		function SelectLimit($sql, $nrows = -1, $offset = -1, $inputarr = false, $secs2cache = 0)
	{
	global $ADODB_FETCH_MODE;

		$url = $this->_url.'?sql='.urlencode($sql)."&nrows=$nrows&fetch=".
			(($this->fetchMode !== false)?$this->fetchMode : $ADODB_FETCH_MODE).
			"&offset=$offset";
		$err = false;
		$rs = csv2rs($url,$err,false);

		if ($this->debug) print "$url<br><i>$err</i><br>";

		$at = strpos($err,'::::');
		if ($at === false) {
			$this->_errorMsg = $err;
			$this->_errorNo = (integer)$err;
		} else {
			$this->_errorMsg = substr($err,$at+4,1024);
			$this->_errorNo = -9999;
		}
		if ($this->_errorNo)
			if ($fn = $this->raiseErrorFn) {
				$fn($this->databaseType,'EXECUTE',$this->ErrorNo(),$this->ErrorMsg(),$sql,'');
			}

		if (is_object($rs)) {

			$rs->databaseType='csv';
			$rs->fetchMode = ($this->fetchMode !== false) ?  $this->fetchMode : $ADODB_FETCH_MODE;
			$rs->connection = $this;
		}
		return $rs;
	}

		function _Execute($sql,$inputarr=false)
	{
	global $ADODB_FETCH_MODE;

		if (!$this->_bindInputArray && $inputarr) {
			$sqlarr = explode('?',$sql);
			$sql = '';
			$i = 0;
			foreach($inputarr as $v) {

				$sql .= $sqlarr[$i];
				if (gettype($v) == 'string')
					$sql .= $this->qstr($v);
				else if ($v === null)
					$sql .= 'NULL';
				else
					$sql .= $v;
				$i += 1;

			}
			$sql .= $sqlarr[$i];
			if ($i+1 != sizeof($sqlarr))
				print "Input Array does not match ?: ".htmlspecialchars($sql);
			$inputarr = false;
		}

		$url =  $this->_url.'?sql='.urlencode($sql)."&fetch=".
			(($this->fetchMode !== false)?$this->fetchMode : $ADODB_FETCH_MODE);
		$err = false;


		$rs = csv2rs($url,$err,false);
		if ($this->debug) print urldecode($url)."<br><i>$err</i><br>";
		$at = strpos($err,'::::');
		if ($at === false) {
			$this->_errorMsg = $err;
			$this->_errorNo = (integer)$err;
		} else {
			$this->_errorMsg = substr($err,$at+4,1024);
			$this->_errorNo = -9999;
		}

		if ($this->_errorNo)
			if ($fn = $this->raiseErrorFn) {
				$fn($this->databaseType,'EXECUTE',$this->ErrorNo(),$this->ErrorMsg(),$sql,$inputarr);
			}
		if (is_object($rs)) {
			$rs->fetchMode = ($this->fetchMode !== false) ?  $this->fetchMode : $ADODB_FETCH_MODE;

			$this->_affectedrows = $rs->affectedrows;
			$this->_insertid = $rs->insertid;
			$rs->databaseType='csv';
			$rs->connection = $this;
		}
		return $rs;
	}

	
	function ErrorMsg()
	{
			return $this->_errorMsg;
	}

	
	function ErrorNo()
	{
		return $this->_errorNo;
	}

		function _close()
	{
		return true;
	}
} 
class ADORecordset_csv extends ADORecordset {
	function __construct($id,$mode=false)
	{
		parent::__construct($id,$mode);
	}

	function _close()
	{
		return true;
	}
}

} 