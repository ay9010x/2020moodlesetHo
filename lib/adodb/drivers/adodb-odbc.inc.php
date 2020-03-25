<?php

if (!defined('ADODB_DIR')) die();

  define("_ADODB_ODBC_LAYER", 2 );




class ADODB_odbc extends ADOConnection {
	var $databaseType = "odbc";
	var $fmtDate = "'Y-m-d'";
	var $fmtTimeStamp = "'Y-m-d, h:i:sA'";
	var $replaceQuote = "''"; 	var $dataProvider = "odbc";
	var $hasAffectedRows = true;
	var $binmode = ODBC_BINMODE_RETURN;
	var $useFetchArray = false; 										var $_bindInputArray = false;
	var $curmode = SQL_CUR_USE_DRIVER; 	var $_genSeqSQL = "create table %s (id integer)";
	var $_autocommit = true;
	var $_haserrorfunctions = true;
	var $_has_stupid_odbc_fetch_api_change = true;
	var $_lastAffectedRows = 0;
	var $uCaseTables = true; 
	function __construct()
	{
		$this->_haserrorfunctions = ADODB_PHPVER >= 0x4050;
		$this->_has_stupid_odbc_fetch_api_change = ADODB_PHPVER >= 0x4200;
	}

			function _connect($argDSN, $argUsername, $argPassword, $argDatabasename)
	{
	global $php_errormsg;

		if (!function_exists('odbc_connect')) return null;

		if (!empty($argDatabasename) && stristr($argDSN, 'Database=') === false) {
			$argDSN = trim($argDSN);
			$endDSN = substr($argDSN, strlen($argDSN) - 1);
			if ($endDSN != ';') $argDSN .= ';';
			$argDSN .= 'Database='.$argDatabasename;
		}

		if (isset($php_errormsg)) $php_errormsg = '';
		if ($this->curmode === false) $this->_connectionID = odbc_connect($argDSN,$argUsername,$argPassword);
		else $this->_connectionID = odbc_connect($argDSN,$argUsername,$argPassword,$this->curmode);
		$this->_errorMsg = isset($php_errormsg) ? $php_errormsg : '';
		if (isset($this->connectStmt)) $this->Execute($this->connectStmt);

		return $this->_connectionID != false;
	}

		function _pconnect($argDSN, $argUsername, $argPassword, $argDatabasename)
	{
	global $php_errormsg;

		if (!function_exists('odbc_connect')) return null;

		if (isset($php_errormsg)) $php_errormsg = '';
		$this->_errorMsg = isset($php_errormsg) ? $php_errormsg : '';
		if ($this->debug && $argDatabasename) {
			ADOConnection::outp("For odbc PConnect(), $argDatabasename is not used. Place dsn in 1st parameter.");
		}
			if ($this->curmode === false) $this->_connectionID = odbc_connect($argDSN,$argUsername,$argPassword);
		else $this->_connectionID = odbc_pconnect($argDSN,$argUsername,$argPassword,$this->curmode);

		$this->_errorMsg = isset($php_errormsg) ? $php_errormsg : '';
		if ($this->_connectionID && $this->autoRollback) @odbc_rollback($this->_connectionID);
		if (isset($this->connectStmt)) $this->Execute($this->connectStmt);

		return $this->_connectionID != false;
	}


	function ServerInfo()
	{

		if (!empty($this->host) && ADODB_PHPVER >= 0x4300) {
			$dsn = strtoupper($this->host);
			$first = true;
			$found = false;

			if (!function_exists('odbc_data_source')) return false;

			while(true) {

				$rez = @odbc_data_source($this->_connectionID,
					$first ? SQL_FETCH_FIRST : SQL_FETCH_NEXT);
				$first = false;
				if (!is_array($rez)) break;
				if (strtoupper($rez['server']) == $dsn) {
					$found = true;
					break;
				}
			}
			if (!$found) return ADOConnection::ServerInfo();
			if (!isset($rez['version'])) $rez['version'] = '';
			return $rez;
		} else {
			return ADOConnection::ServerInfo();
		}
	}


	function CreateSequence($seqname='adodbseq',$start=1)
	{
		if (empty($this->_genSeqSQL)) return false;
		$ok = $this->Execute(sprintf($this->_genSeqSQL,$seqname));
		if (!$ok) return false;
		$start -= 1;
		return $this->Execute("insert into $seqname values($start)");
	}

	var $_dropSeqSQL = 'drop table %s';
	function DropSequence($seqname = 'adodbseq')
	{
		if (empty($this->_dropSeqSQL)) return false;
		return $this->Execute(sprintf($this->_dropSeqSQL,$seqname));
	}

	
	function GenID($seq='adodbseq',$start=1)
	{
						$MAXLOOPS = 100;
				while (--$MAXLOOPS>=0) {
			$num = $this->GetOne("select id from $seq");
			if ($num === false) {
				$this->Execute(sprintf($this->_genSeqSQL ,$seq));
				$start -= 1;
				$num = '0';
				$ok = $this->Execute("insert into $seq values($start)");
				if (!$ok) return false;
			}
			$this->Execute("update $seq set id=id+1 where id=$num");

			if ($this->affected_rows() > 0) {
				$num += 1;
				$this->genID = $num;
				return $num;
			} elseif ($this->affected_rows() == 0) {
								$value = $this->GetOne("select id from $seq");
				if ($value == $num + 1) {
					return $value;
				}
			}
		}
		if ($fn = $this->raiseErrorFn) {
			$fn($this->databaseType,'GENID',-32000,"Unable to generate unique id after $MAXLOOPS attempts",$seq,$num);
		}
		return false;
	}


	function ErrorMsg()
	{
		if ($this->_haserrorfunctions) {
			if ($this->_errorMsg !== false) return $this->_errorMsg;
			if (empty($this->_connectionID)) return @odbc_errormsg();
			return @odbc_errormsg($this->_connectionID);
		} else return ADOConnection::ErrorMsg();
	}

	function ErrorNo()
	{

		if ($this->_haserrorfunctions) {
			if ($this->_errorCode !== false) {
								return (strlen($this->_errorCode)<=2) ? 0 : $this->_errorCode;
			}

			if (empty($this->_connectionID)) $e = @odbc_error();
			else $e = @odbc_error($this->_connectionID);

			 			 			if (strlen($e)<=2) return 0;
			return $e;
		} else return ADOConnection::ErrorNo();
	}



	function BeginTrans()
	{
		if (!$this->hasTransactions) return false;
		if ($this->transOff) return true;
		$this->transCnt += 1;
		$this->_autocommit = false;
		return odbc_autocommit($this->_connectionID,false);
	}

	function CommitTrans($ok=true)
	{
		if ($this->transOff) return true;
		if (!$ok) return $this->RollbackTrans();
		if ($this->transCnt) $this->transCnt -= 1;
		$this->_autocommit = true;
		$ret = odbc_commit($this->_connectionID);
		odbc_autocommit($this->_connectionID,true);
		return $ret;
	}

	function RollbackTrans()
	{
		if ($this->transOff) return true;
		if ($this->transCnt) $this->transCnt -= 1;
		$this->_autocommit = true;
		$ret = odbc_rollback($this->_connectionID);
		odbc_autocommit($this->_connectionID,true);
		return $ret;
	}

	function MetaPrimaryKeys($table,$owner=false)
	{
	global $ADODB_FETCH_MODE;

		if ($this->uCaseTables) $table = strtoupper($table);
		$schema = '';
		$this->_findschema($table,$schema);

		$savem = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		$qid = @odbc_primarykeys($this->_connectionID,'',$schema,$table);

		if (!$qid) {
			$ADODB_FETCH_MODE = $savem;
			return false;
		}
		$rs = new ADORecordSet_odbc($qid);
		$ADODB_FETCH_MODE = $savem;

		if (!$rs) return false;
		$rs->_has_stupid_odbc_fetch_api_change = $this->_has_stupid_odbc_fetch_api_change;

		$arr = $rs->GetArray();
		$rs->Close();
				$arr2 = array();
		for ($i=0; $i < sizeof($arr); $i++) {
			if ($arr[$i][3]) $arr2[] = $arr[$i][3];
		}
		return $arr2;
	}



	function MetaTables($ttype=false,$showSchema=false,$mask=false)
	{
	global $ADODB_FETCH_MODE;

		$savem = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		$qid = odbc_tables($this->_connectionID);

		$rs = new ADORecordSet_odbc($qid);

		$ADODB_FETCH_MODE = $savem;
		if (!$rs) {
			$false = false;
			return $false;
		}
		$rs->_has_stupid_odbc_fetch_api_change = $this->_has_stupid_odbc_fetch_api_change;

		$arr = $rs->GetArray();
		
		$rs->Close();
		$arr2 = array();

		if ($ttype) {
			$isview = strncmp($ttype,'V',1) === 0;
		}
		for ($i=0; $i < sizeof($arr); $i++) {
			if (!$arr[$i][2]) continue;
			$type = $arr[$i][3];
			if ($ttype) {
				if ($isview) {
					if (strncmp($type,'V',1) === 0) $arr2[] = $arr[$i][2];
				} else if (strncmp($type,'SYS',3) !== 0) $arr2[] = $arr[$i][2];
			} else if (strncmp($type,'SYS',3) !== 0) $arr2[] = $arr[$i][2];
		}
		return $arr2;
	}


	function ODBCTypes($t)
	{
		switch ((integer)$t) {
		case 1:
		case 12:
		case 0:
		case -95:
		case -96:
			return 'C';
		case -97:
		case -1: 			return 'X';
		case -4: 			return 'B';

		case 9:
		case 91:
			return 'D';

		case 10:
		case 11:
		case 92:
		case 93:
			return 'T';

		case 4:
		case 5:
		case -6:
			return 'I';

		case -11: 			return 'R';
		case -7: 			return 'L';

		default:
			return 'N';
		}
	}

	function MetaColumns($table, $normalize=true)
	{
	global $ADODB_FETCH_MODE;

		$false = false;
		if ($this->uCaseTables) $table = strtoupper($table);
		$schema = '';
		$this->_findschema($table,$schema);

		$savem = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;

		

		switch ($this->databaseType) {
		case 'access':
		case 'vfp':
			$qid = odbc_columns($this->_connectionID);			break;


		case 'db2':
            $colname = "%";
            $qid = odbc_columns($this->_connectionID, "", $schema, $table, $colname);
            break;

		default:
			$qid = @odbc_columns($this->_connectionID,'%','%',strtoupper($table),'%');
			if (empty($qid)) $qid = odbc_columns($this->_connectionID);
			break;
		}
		if (empty($qid)) return $false;

		$rs = new ADORecordSet_odbc($qid);
		$ADODB_FETCH_MODE = $savem;

		if (!$rs) return $false;
		$rs->_has_stupid_odbc_fetch_api_change = $this->_has_stupid_odbc_fetch_api_change;
		$rs->_fetch();

		$retarr = array();

		
		while (!$rs->EOF) {
					if (strtoupper(trim($rs->fields[2])) == $table && (!$schema || strtoupper($rs->fields[1]) == $schema)) {
				$fld = new ADOFieldObject();
				$fld->name = $rs->fields[3];
				$fld->type = $this->ODBCTypes($rs->fields[4]);

												if ($fld->type == 'C' or $fld->type == 'X') {
					if ($this->databaseType == 'access')
						$fld->max_length = $rs->fields[6];
					else if ($rs->fields[4] <= -95) 						$fld->max_length = $rs->fields[7]/2;
					else
						$fld->max_length = $rs->fields[7];
				} else
					$fld->max_length = $rs->fields[7];
				$fld->not_null = !empty($rs->fields[10]);
				$fld->scale = $rs->fields[8];
				$retarr[strtoupper($fld->name)] = $fld;
			} else if (sizeof($retarr)>0)
				break;
			$rs->MoveNext();
		}
		$rs->Close(); 
		if (empty($retarr)) $retarr = false;
		return $retarr;
	}

	function Prepare($sql)
	{
		if (! $this->_bindInputArray) return $sql; 		$stmt = odbc_prepare($this->_connectionID,$sql);
		if (!$stmt) {
						return $sql;
		}
		return array($sql,$stmt,false);
	}

	
	function _query($sql,$inputarr=false)
	{
	GLOBAL $php_errormsg;
		if (isset($php_errormsg)) $php_errormsg = '';
		$this->_error = '';

		if ($inputarr) {
			if (is_array($sql)) {
				$stmtid = $sql[1];
			} else {
				$stmtid = odbc_prepare($this->_connectionID,$sql);

				if ($stmtid == false) {
					$this->_errorMsg = isset($php_errormsg) ? $php_errormsg : '';
					return false;
				}
			}

			if (! odbc_execute($stmtid,$inputarr)) {
								if ($this->_haserrorfunctions) {
					$this->_errorMsg = odbc_errormsg();
					$this->_errorCode = odbc_error();
				}
				return false;
			}

		} else if (is_array($sql)) {
			$stmtid = $sql[1];
			if (!odbc_execute($stmtid)) {
								if ($this->_haserrorfunctions) {
					$this->_errorMsg = odbc_errormsg();
					$this->_errorCode = odbc_error();
				}
				return false;
			}
		} else
			$stmtid = odbc_exec($this->_connectionID,$sql);

		$this->_lastAffectedRows = 0;
		if ($stmtid) {
			if (@odbc_num_fields($stmtid) == 0) {
				$this->_lastAffectedRows = odbc_num_rows($stmtid);
				$stmtid = true;
			} else {
				$this->_lastAffectedRows = 0;
				odbc_binmode($stmtid,$this->binmode);
				odbc_longreadlen($stmtid,$this->maxblobsize);
			}

			if ($this->_haserrorfunctions) {
				$this->_errorMsg = '';
				$this->_errorCode = 0;
			} else
				$this->_errorMsg = isset($php_errormsg) ? $php_errormsg : '';
		} else {
			if ($this->_haserrorfunctions) {
				$this->_errorMsg = odbc_errormsg();
				$this->_errorCode = odbc_error();
			} else
				$this->_errorMsg = isset($php_errormsg) ? $php_errormsg : '';
		}
		return $stmtid;
	}

	
	function UpdateBlob($table,$column,$val,$where,$blobtype='BLOB')
	{
		return $this->Execute("UPDATE $table SET $column=? WHERE $where",array($val)) != false;
	}

		function _close()
	{
		$ret = @odbc_close($this->_connectionID);
		$this->_connectionID = false;
		return $ret;
	}

	function _affectedrows()
	{
		return $this->_lastAffectedRows;
	}

}



class ADORecordSet_odbc extends ADORecordSet {

	var $bind = false;
	var $databaseType = "odbc";
	var $dataProvider = "odbc";
	var $useFetchArray;
	var $_has_stupid_odbc_fetch_api_change;

	function __construct($id,$mode=false)
	{
		if ($mode === false) {
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;
		}
		$this->fetchMode = $mode;

		$this->_queryID = $id;

				$this->EOF = false;
		$this->_currentRow = -1;
			}


		function FetchField($fieldOffset = -1)
	{

		$off=$fieldOffset+1; 
		$o= new ADOFieldObject();
		$o->name = @odbc_field_name($this->_queryID,$off);
		$o->type = @odbc_field_type($this->_queryID,$off);
		$o->max_length = @odbc_field_len($this->_queryID,$off);
		if (ADODB_ASSOC_CASE == 0) $o->name = strtolower($o->name);
		else if (ADODB_ASSOC_CASE == 1) $o->name = strtoupper($o->name);
		return $o;
	}

	
	function Fields($colname)
	{
		if ($this->fetchMode & ADODB_FETCH_ASSOC) return $this->fields[$colname];
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
	global $ADODB_COUNTRECS;
		$this->_numOfRows = ($ADODB_COUNTRECS) ? @odbc_num_rows($this->_queryID) : -1;
		$this->_numOfFields = @odbc_num_fields($this->_queryID);
				if ($this->_numOfRows == 0) $this->_numOfRows = -1;
				$this->_has_stupid_odbc_fetch_api_change = ADODB_PHPVER >= 0x4200;
	}

	function _seek($row)
	{
		return false;
	}

		function GetArrayLimit($nrows,$offset=-1)
	{
		if ($offset <= 0) {
			$rs = $this->GetArray($nrows);
			return $rs;
		}
		$savem = $this->fetchMode;
		$this->fetchMode = ADODB_FETCH_NUM;
		$this->Move($offset);
		$this->fetchMode = $savem;

		if ($this->fetchMode & ADODB_FETCH_ASSOC) {
			$this->fields = $this->GetRowAssoc();
		}

		$results = array();
		$cnt = 0;
		while (!$this->EOF && $nrows != $cnt) {
			$results[$cnt++] = $this->fields;
			$this->MoveNext();
		}

		return $results;
	}


	function MoveNext()
	{
		if ($this->_numOfRows != 0 && !$this->EOF) {
			$this->_currentRow++;
			if( $this->_fetch() ) {
				return true;
			}
		}
		$this->fields = false;
		$this->EOF = true;
		return false;
	}

	function _fetch()
	{
		$this->fields = false;
		if ($this->_has_stupid_odbc_fetch_api_change)
			$rez = @odbc_fetch_into($this->_queryID,$this->fields);
		else {
			$row = 0;
			$rez = @odbc_fetch_into($this->_queryID,$row,$this->fields);
		}
		if ($rez) {
			if ($this->fetchMode & ADODB_FETCH_ASSOC) {
				$this->fields = $this->GetRowAssoc();
			}
			return true;
		}
		return false;
	}

	function _close()
	{
		return @odbc_free_result($this->_queryID);
	}

}
