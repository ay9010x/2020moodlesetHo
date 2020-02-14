<?php


 if (!defined('ADODB_DIR')) die();

class ADODB_sybase extends ADOConnection {
	var $databaseType = "sybase";
	var $dataProvider = 'sybase';
	var $replaceQuote = "''"; 	var $fmtDate = "'Y-m-d'";
	var $fmtTimeStamp = "'Y-m-d H:i:s'";
	var $hasInsertID = true;
	var $hasAffectedRows = true;
  	var $metaTablesSQL="select name from sysobjects where type='U' or type='V'";
		var $metaColumnsSQL = "SELECT c.column_name, c.column_type, c.width FROM syscolumn c, systable t WHERE t.table_name='%s' AND c.table_id=t.table_id AND t.table_type='BASE'";
	
	var $concat_operator = '+';
	var $arrayClass = 'ADORecordSet_array_sybase';
	var $sysDate = 'GetDate()';
	var $leftOuter = '*=';
	var $rightOuter = '=*';

	var $port;

	function __construct()
	{
	}

		function _insertid()
	{
		return $this->GetOne('select @@identity');
	}
	  	function _affectedrows()
	{
		return $this->GetOne('select @@rowcount');
	}


	function BeginTrans()
	{

		if ($this->transOff) return true;
		$this->transCnt += 1;

		$this->Execute('BEGIN TRAN');
		return true;
	}

	function CommitTrans($ok=true)
	{
		if ($this->transOff) return true;

		if (!$ok) return $this->RollbackTrans();

		$this->transCnt -= 1;
		$this->Execute('COMMIT TRAN');
		return true;
	}

	function RollbackTrans()
	{
		if ($this->transOff) return true;
		$this->transCnt -= 1;
		$this->Execute('ROLLBACK TRAN');
		return true;
	}

		function RowLock($tables,$where,$col='top 1 null as ignore')
	{
		if (!$this->_hastrans) $this->BeginTrans();
		$tables = str_replace(',',' HOLDLOCK,',$tables);
		return $this->GetOne("select $col from $tables HOLDLOCK where $where");

	}

	function SelectDB($dbName)
	{
		$this->database = $dbName;
		$this->databaseName = $dbName; 		if ($this->_connectionID) {
			return @sybase_select_db($dbName);
		}
		else return false;
	}

	


	function ErrorMsg()
	{
		if ($this->_logsql) return $this->_errorMsg;
		if (function_exists('sybase_get_last_message'))
			$this->_errorMsg = sybase_get_last_message();
		else
			$this->_errorMsg = isset($php_errormsg) ? $php_errormsg : 'SYBASE error messages not supported on this platform';
		return $this->_errorMsg;
	}

		function _connect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
		if (!function_exists('sybase_connect')) return null;

				if ($this->port) {
			$argHostname .= ':' . $this->port;
		}

		if ($this->charSet) {
			$this->_connectionID = sybase_connect($argHostname,$argUsername,$argPassword, $this->charSet);
		} else {
			$this->_connectionID = sybase_connect($argHostname,$argUsername,$argPassword);
		}

		if ($this->_connectionID === false) return false;
		if ($argDatabasename) return $this->SelectDB($argDatabasename);
		return true;
	}

		function _pconnect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
		if (!function_exists('sybase_connect')) return null;

				if ($this->port) {
			$argHostname .= ':' . $this->port;
		}

		if ($this->charSet) {
			$this->_connectionID = sybase_pconnect($argHostname,$argUsername,$argPassword, $this->charSet);
		} else {
			$this->_connectionID = sybase_pconnect($argHostname,$argUsername,$argPassword);
		}

		if ($this->_connectionID === false) return false;
		if ($argDatabasename) return $this->SelectDB($argDatabasename);
		return true;
	}

		function _query($sql,$inputarr=false)
	{
	global $ADODB_COUNTRECS;

		if ($ADODB_COUNTRECS == false && ADODB_PHPVER >= 0x4300)
			return sybase_unbuffered_query($sql,$this->_connectionID);
		else
			return sybase_query($sql,$this->_connectionID);
	}

		function SelectLimit($sql,$nrows=-1,$offset=-1,$inputarr=false,$secs2cache=0)
	{
		if ($secs2cache > 0) {			$rs = ADOConnection::SelectLimit($sql,$nrows,$offset,$inputarr,$secs2cache);
			return $rs;
		}

		$nrows = (integer) $nrows;
		$offset = (integer) $offset;

		$cnt = ($nrows >= 0) ? $nrows : 999999999;
		if ($offset > 0 && $cnt) $cnt += $offset;

		$this->Execute("set rowcount $cnt");
		$rs = ADOConnection::SelectLimit($sql,$nrows,$offset,$inputarr,0);
		$this->Execute("set rowcount 0");

		return $rs;
	}

		function _close()
	{
		return @sybase_close($this->_connectionID);
	}

	static function UnixDate($v)
	{
		return ADORecordSet_array_sybase::UnixDate($v);
	}

	static function UnixTimeStamp($v)
	{
		return ADORecordSet_array_sybase::UnixTimeStamp($v);
	}



					function SQLDate($fmt, $col=false)
	{
		if (!$col) $col = $this->sysTimeStamp;
		$s = '';

		$len = strlen($fmt);
		for ($i=0; $i < $len; $i++) {
			if ($s) $s .= '+';
			$ch = $fmt[$i];
			switch($ch) {
			case 'Y':
			case 'y':
				$s .= "datename(yy,$col)";
				break;
			case 'M':
				$s .= "convert(char(3),$col,0)";
				break;
			case 'm':
				$s .= "str_replace(str(month($col),2),' ','0')";
				break;
			case 'Q':
			case 'q':
				$s .= "datename(qq,$col)";
				break;
			case 'D':
			case 'd':
				$s .= "str_replace(str(datepart(dd,$col),2),' ','0')";
				break;
			case 'h':
				$s .= "substring(convert(char(14),$col,0),13,2)";
				break;

			case 'H':
				$s .= "str_replace(str(datepart(hh,$col),2),' ','0')";
				break;

			case 'i':
				$s .= "str_replace(str(datepart(mi,$col),2),' ','0')";
				break;
			case 's':
				$s .= "str_replace(str(datepart(ss,$col),2),' ','0')";
				break;
			case 'a':
			case 'A':
				$s .= "substring(convert(char(19),$col,0),18,2)";
				break;

			default:
				if ($ch == '\\') {
					$i++;
					$ch = substr($fmt,$i,1);
				}
				$s .= $this->qstr($ch);
				break;
			}
		}
		return $s;
	}

				function MetaPrimaryKeys($table, $owner = false)
	{
		$sql = "SELECT c.column_name " .
			   "FROM syscolumn c, systable t " .
			   "WHERE t.table_name='$table' AND c.table_id=t.table_id " .
			   "AND t.table_type='BASE' " .
			   "AND c.pkey = 'Y' " .
			   "ORDER BY c.column_id";

		$a = $this->GetCol($sql);
		if ($a && sizeof($a)>0) return $a;
		return false;
	}
}


global $ADODB_sybase_mths;
$ADODB_sybase_mths = array(
	'JAN'=>1,'FEB'=>2,'MAR'=>3,'APR'=>4,'MAY'=>5,'JUN'=>6,
	'JUL'=>7,'AUG'=>8,'SEP'=>9,'OCT'=>10,'NOV'=>11,'DEC'=>12);

class ADORecordset_sybase extends ADORecordSet {

	var $databaseType = "sybase";
	var $canSeek = true;
		var  $_mths = array('JAN'=>1,'FEB'=>2,'MAR'=>3,'APR'=>4,'MAY'=>5,'JUN'=>6,'JUL'=>7,'AUG'=>8,'SEP'=>9,'OCT'=>10,'NOV'=>11,'DEC'=>12);

	function __construct($id,$mode=false)
	{
		if ($mode === false) {
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;
		}
		if (!$mode) $this->fetchMode = ADODB_FETCH_ASSOC;
		else $this->fetchMode = $mode;
		parent::__construct($id,$mode);
	}

	
	function FetchField($fieldOffset = -1)
	{
		if ($fieldOffset != -1) {
			$o = @sybase_fetch_field($this->_queryID, $fieldOffset);
		}
		else if ($fieldOffset == -1) {	
			$o = @sybase_fetch_field($this->_queryID);
		}
				if ($o && !isset($o->type)) $o->type = ($o->numeric) ? 'float' : 'varchar';
		return $o;
	}

	function _initrs()
	{
	global $ADODB_COUNTRECS;
		$this->_numOfRows = ($ADODB_COUNTRECS)? @sybase_num_rows($this->_queryID):-1;
		$this->_numOfFields = @sybase_num_fields($this->_queryID);
	}

	function _seek($row)
	{
		return @sybase_data_seek($this->_queryID, $row);
	}

	function _fetch($ignore_fields=false)
	{
		if ($this->fetchMode == ADODB_FETCH_NUM) {
			$this->fields = @sybase_fetch_row($this->_queryID);
		} else if ($this->fetchMode == ADODB_FETCH_ASSOC) {
			$this->fields = @sybase_fetch_assoc($this->_queryID);

			if (is_array($this->fields)) {
				$this->fields = $this->GetRowAssoc();
				return true;
			}
			return false;
		}  else {
			$this->fields = @sybase_fetch_array($this->_queryID);
		}
		if ( is_array($this->fields)) {
			return true;
		}

		return false;
	}

	
	function _close() {
		return @sybase_free_result($this->_queryID);
	}

		static function UnixDate($v)
	{
		return ADORecordSet_array_sybase::UnixDate($v);
	}

	static function UnixTimeStamp($v)
	{
		return ADORecordSet_array_sybase::UnixTimeStamp($v);
	}
}

class ADORecordSet_array_sybase extends ADORecordSet_array {
	function __construct($id=-1)
	{
		parent::__construct($id);
	}

			static function UnixDate($v)
	{
	global $ADODB_sybase_mths;

				if (!preg_match( "/([A-Za-z]{3})[-/\. ]+([0-9]{1,2})[-/\. ]+([0-9]{4})/"
			,$v, $rr)) return parent::UnixDate($v);

		if ($rr[3] <= TIMESTAMP_FIRST_YEAR) return 0;

		$themth = substr(strtoupper($rr[1]),0,3);
		$themth = $ADODB_sybase_mths[$themth];
		if ($themth <= 0) return false;
				return  adodb_mktime(0,0,0,$themth,$rr[2],$rr[3]);
	}

	static function UnixTimeStamp($v)
	{
	global $ADODB_sybase_mths;
						if (!preg_match( "/([A-Za-z]{3})[-/\. ]([0-9 ]{1,2})[-/\. ]([0-9]{4}) +([0-9]{1,2}):([0-9]{1,2}) *([apAP]{0,1})/"
			,$v, $rr)) return parent::UnixTimeStamp($v);
		if ($rr[3] <= TIMESTAMP_FIRST_YEAR) return 0;

		$themth = substr(strtoupper($rr[1]),0,3);
		$themth = $ADODB_sybase_mths[$themth];
		if ($themth <= 0) return false;

		switch (strtoupper($rr[6])) {
		case 'P':
			if ($rr[4]<12) $rr[4] += 12;
			break;
		case 'A':
			if ($rr[4]==12) $rr[4] = 0;
			break;
		default:
			break;
		}
				return  adodb_mktime($rr[4],$rr[5],0,$themth,$rr[2],$rr[3]);
	}
}
