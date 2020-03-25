<?php


if (!defined('ADODB_DIR')) die();

class ADODB_oracle extends ADOConnection {
	var $databaseType = "oracle";
	var $replaceQuote = "''"; 	var $concat_operator='||';
	var $_curs;
	var $_initdate = true; 	var $metaTablesSQL = 'select table_name from cat';
	var $metaColumnsSQL = "select cname,coltype,width from col where tname='%s' order by colno";
	var $sysDate = "TO_DATE(TO_CHAR(SYSDATE,'YYYY-MM-DD'),'YYYY-MM-DD')";
	var $sysTimeStamp = 'SYSDATE';
	var $connectSID = true;

	function __construct()
	{
	}

		function DBDate($d, $isfld = false)
	{
		if (is_string($d)) $d = ADORecordSet::UnixDate($d);
		if (is_object($d)) $ds = $d->format($this->fmtDate);
		else $ds = adodb_date($this->fmtDate,$d);
		return 'TO_DATE('.$ds.",'YYYY-MM-DD')";
	}

		function DBTimeStamp($ts, $isfld = false)
	{

		if (is_string($ts)) $ts = ADORecordSet::UnixTimeStamp($ts);
		if (is_object($ts)) $ds = $ts->format($this->fmtDate);
		else $ds = adodb_date($this->fmtTimeStamp,$ts);
		return 'TO_DATE('.$ds.",'RRRR-MM-DD, HH:MI:SS AM')";
	}


	function BindDate($d)
	{
		$d = ADOConnection::DBDate($d);
		if (strncmp($d,"'",1)) return $d;

		return substr($d,1,strlen($d)-2);
	}

	function BindTimeStamp($d)
	{
		$d = ADOConnection::DBTimeStamp($d);
		if (strncmp($d,"'",1)) return $d;

		return substr($d,1,strlen($d)-2);
	}



	function BeginTrans()
	{
		 $this->autoCommit = false;
		 ora_commitoff($this->_connectionID);
		 return true;
	}


	function CommitTrans($ok=true)
	{
		   if (!$ok) return $this->RollbackTrans();
		   $ret = ora_commit($this->_connectionID);
		   ora_commiton($this->_connectionID);
		   return $ret;
	}


	function RollbackTrans()
	{
		$ret = ora_rollback($this->_connectionID);
		ora_commiton($this->_connectionID);
		return $ret;
	}


	
	function ErrorMsg()
 	{
        if ($this->_errorMsg !== false) return $this->_errorMsg;

        if (is_resource($this->_curs)) $this->_errorMsg = @ora_error($this->_curs);
 		if (empty($this->_errorMsg)) $this->_errorMsg = @ora_error($this->_connectionID);
		return $this->_errorMsg;
	}


	function ErrorNo()
	{
		if ($this->_errorCode !== false) return $this->_errorCode;

		if (is_resource($this->_curs)) $this->_errorCode = @ora_errorcode($this->_curs);
		if (empty($this->_errorCode)) $this->_errorCode = @ora_errorcode($this->_connectionID);
        return $this->_errorCode;
	}



				function _connect($argHostname, $argUsername, $argPassword, $argDatabasename, $mode=0)
		{
			if (!function_exists('ora_plogon')) return null;

                        $this->_errorMsg = false;
		    $this->_errorCode = false;

                        
			if($argHostname) { 				if (empty($argDatabasename)) $argDatabasename = $argHostname;
				else {
					if(strpos($argHostname,":")) {
						$argHostinfo=explode(":",$argHostname);
						$argHostname=$argHostinfo[0];
						$argHostport=$argHostinfo[1];
					} else {
						$argHostport="1521";
					}


					if ($this->connectSID) {
						$argDatabasename="(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=".$argHostname
						.")(PORT=$argHostport))(CONNECT_DATA=(SID=$argDatabasename)))";
					} else
						$argDatabasename="(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=".$argHostname
						.")(PORT=$argHostport))(CONNECT_DATA=(SERVICE_NAME=$argDatabasename)))";
				}

			}

			if ($argDatabasename) $argUsername .= "@$argDatabasename";

					if ($mode == 1)
				$this->_connectionID = ora_plogon($argUsername,$argPassword);
			else
				$this->_connectionID = ora_logon($argUsername,$argPassword);
			if ($this->_connectionID === false) return false;
			if ($this->autoCommit) ora_commiton($this->_connectionID);
			if ($this->_initdate) {
				$rs = $this->_query("ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD'");
				if ($rs) ora_close($rs);
			}

			return true;
		}


				function _pconnect($argHostname, $argUsername, $argPassword, $argDatabasename)
		{
			return $this->_connect($argHostname, $argUsername, $argPassword, $argDatabasename, 1);
		}


				function _query($sql,$inputarr=false)
		{
                        $this->_errorMsg = false;
		    $this->_errorCode = false;

			$curs = ora_open($this->_connectionID);

		 	if ($curs === false) return false;
			$this->_curs = $curs;
			if (!ora_parse($curs,$sql)) return false;
			if (ora_exec($curs)) return $curs;
                                    $this->_errorCode = @ora_errorcode($curs);
            $this->_errorMsg = @ora_error($curs);
            		 	@ora_close($curs);
			return false;
		}


				function _close()
		{
			return @ora_logoff($this->_connectionID);
		}



}




class ADORecordset_oracle extends ADORecordSet {

	var $databaseType = "oracle";
	var $bind = false;

	function __construct($queryID,$mode=false)
	{

		if ($mode === false) {
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;
		}
		$this->fetchMode = $mode;

		$this->_queryID = $queryID;

		$this->_inited = true;
		$this->fields = array();
		if ($queryID) {
			$this->_currentRow = 0;
			$this->EOF = !$this->_fetch();
			@$this->_initrs();
		} else {
			$this->_numOfRows = 0;
			$this->_numOfFields = 0;
			$this->EOF = true;
		}

		return $this->_queryID;
	}



	   

	   function FetchField($fieldOffset = -1)
	   {
			$fld = new ADOFieldObject;
			$fld->name = ora_columnname($this->_queryID, $fieldOffset);
			$fld->type = ora_columntype($this->_queryID, $fieldOffset);
			$fld->max_length = ora_columnsize($this->_queryID, $fieldOffset);
			return $fld;
	   }

	
	function Fields($colname)
	{
		if (!$this->bind) {
			$this->bind = array();
			for ($i=0; $i < $this->_numOfFields; $i++) {
				$o = $this->FetchField($i);
				$this->bind[strtoupper($o->name)] = $i;
			}
		}

		 return $this->fields[$this->bind[strtoupper($colname)]];
	}

   function _initrs()
   {
		   $this->_numOfRows = -1;
		   $this->_numOfFields = @ora_numcols($this->_queryID);
   }


   function _seek($row)
   {
		   return false;
   }

   function _fetch($ignore_fields=false) {
		if ($this->fetchMode & ADODB_FETCH_ASSOC)
			return @ora_fetch_into($this->_queryID,$this->fields,ORA_FETCHINTO_NULLS|ORA_FETCHINTO_ASSOC);
   		else
			return @ora_fetch_into($this->_queryID,$this->fields,ORA_FETCHINTO_NULLS);
   }

   

   function _close()
{
		   return @ora_close($this->_queryID);
   }

	function MetaType($t, $len = -1, $fieldobj = false)
	{
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
		}

		switch (strtoupper($t)) {
		case 'VARCHAR':
		case 'VARCHAR2':
		case 'CHAR':
		case 'VARBINARY':
		case 'BINARY':
				if ($len <= $this->blobSize) return 'C';
		case 'LONG':
		case 'LONG VARCHAR':
		case 'CLOB':
		return 'X';
		case 'LONG RAW':
		case 'LONG VARBINARY':
		case 'BLOB':
				return 'B';

		case 'DATE': return 'D';

		
		case 'BIT': return 'L';
		case 'INT':
		case 'SMALLINT':
		case 'INTEGER': return 'I';
		default: return 'N';
		}
	}
}
