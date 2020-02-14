<?php


if (!defined('ADODB_DIR')) die();




function adodb_pdo_type($t)
{
	switch($t) {
	case 2: return 'VARCHAR';
	case 3: return 'BLOB';
	default: return 'NUMERIC';
	}
}




class ADODB_pdo extends ADOConnection {
	var $databaseType = "pdo";
	var $dataProvider = "pdo";
	var $fmtDate = "'Y-m-d'";
	var $fmtTimeStamp = "'Y-m-d, h:i:sA'";
	var $replaceQuote = "''"; 	var $hasAffectedRows = true;
	var $_bindInputArray = true;
	var $_genIDSQL;
	var $_genSeqSQL = "create table %s (id integer)";
	var $_dropSeqSQL;
	var $_autocommit = true;
	var $_haserrorfunctions = true;
	var $_lastAffectedRows = 0;

	var $_errormsg = false;
	var $_errorno = false;

	var $dsnType = '';
	var $stmt = false;
	var $_driver;

	function __construct()
	{
	}

	function _UpdatePDO()
	{
		$d = $this->_driver;
		$this->fmtDate = $d->fmtDate;
		$this->fmtTimeStamp = $d->fmtTimeStamp;
		$this->replaceQuote = $d->replaceQuote;
		$this->sysDate = $d->sysDate;
		$this->sysTimeStamp = $d->sysTimeStamp;
		$this->random = $d->random;
		$this->concat_operator = $d->concat_operator;
		$this->nameQuote = $d->nameQuote;

		$this->hasGenID = $d->hasGenID;
		$this->_genIDSQL = $d->_genIDSQL;
		$this->_genSeqSQL = $d->_genSeqSQL;
		$this->_dropSeqSQL = $d->_dropSeqSQL;

		$d->_init($this);
	}

	function Time()
	{
		if (!empty($this->_driver->_hasdual)) {
			$sql = "select $this->sysTimeStamp from dual";
		}
		else {
			$sql = "select $this->sysTimeStamp";
		}

		$rs = $this->_Execute($sql);
		if ($rs && !$rs->EOF) {
			return $this->UnixTimeStamp(reset($rs->fields));
		}

		return false;
	}

		function _connect($argDSN, $argUsername, $argPassword, $argDatabasename, $persist=false)
	{
		$at = strpos($argDSN,':');
		$this->dsnType = substr($argDSN,0,$at);

		if ($argDatabasename) {
			switch($this->dsnType){
				case 'sqlsrv':
					$argDSN .= ';database='.$argDatabasename;
					break;
				case 'mssql':
				case 'mysql':
				case 'oci':
				case 'pgsql':
				case 'sqlite':
				default:
					$argDSN .= ';dbname='.$argDatabasename;
			}
		}
		try {
			$this->_connectionID = new PDO($argDSN, $argUsername, $argPassword);
		} catch (Exception $e) {
			$this->_connectionID = false;
			$this->_errorno = -1;
						$this->_errormsg = 'Connection attempt failed: '.$e->getMessage();
			return false;
		}

		if ($this->_connectionID) {
			switch(ADODB_ASSOC_CASE){
				case ADODB_ASSOC_CASE_LOWER:
					$m = PDO::CASE_LOWER;
					break;
				case ADODB_ASSOC_CASE_UPPER:
					$m = PDO::CASE_UPPER;
					break;
				default:
				case ADODB_ASSOC_CASE_NATIVE:
					$m = PDO::CASE_NATURAL;
					break;
			}

						$this->_connectionID->setAttribute(PDO::ATTR_CASE,$m);

			$class = 'ADODB_pdo_'.$this->dsnType;
						switch($this->dsnType) {
				case 'mssql':
				case 'mysql':
				case 'oci':
				case 'pgsql':
				case 'sqlite':
				case 'sqlsrv':
					include_once(ADODB_DIR.'/drivers/adodb-pdo_'.$this->dsnType.'.inc.php');
					break;
			}
			if (class_exists($class)) {
				$this->_driver = new $class();
			}
			else {
				$this->_driver = new ADODB_pdo_base();
			}

			$this->_driver->_connectionID = $this->_connectionID;
			$this->_UpdatePDO();
			return true;
		}
		$this->_driver = new ADODB_pdo_base();
		return false;
	}

	function Concat()
	{
		$args = func_get_args();
		if(method_exists($this->_driver, 'Concat')) {
			return call_user_func_array(array($this->_driver, 'Concat'), $args);
		}

		if (PHP_VERSION >= 5.3) {
			return call_user_func_array('parent::Concat', $args);
		}
		return call_user_func_array(array($this,'parent::Concat'), $args);
	}

		function _pconnect($argDSN, $argUsername, $argPassword, $argDatabasename)
	{
		return $this->_connect($argDSN, $argUsername, $argPassword, $argDatabasename, true);
	}

	


	function SelectLimit($sql,$nrows=-1,$offset=-1,$inputarr=false,$secs2cache=0)
	{
		$save = $this->_driver->fetchMode;
		$this->_driver->fetchMode = $this->fetchMode;
		$this->_driver->debug = $this->debug;
		$ret = $this->_driver->SelectLimit($sql,$nrows,$offset,$inputarr,$secs2cache);
		$this->_driver->fetchMode = $save;
		return $ret;
	}


	function ServerInfo()
	{
		return $this->_driver->ServerInfo();
	}

	function MetaTables($ttype=false,$showSchema=false,$mask=false)
	{
		return $this->_driver->MetaTables($ttype,$showSchema,$mask);
	}

	function MetaColumns($table,$normalize=true)
	{
		return $this->_driver->MetaColumns($table,$normalize);
	}

	function InParameter(&$stmt,&$var,$name,$maxLen=4000,$type=false)
	{
		$obj = $stmt[1];
		if ($type) {
			$obj->bindParam($name, $var, $type, $maxLen);
		}
		else {
			$obj->bindParam($name, $var);
		}
	}

	function OffsetDate($dayFraction,$date=false)
	{
		return $this->_driver->OffsetDate($dayFraction,$date);
	}

	function ErrorMsg()
	{
		if ($this->_errormsg !== false) {
			return $this->_errormsg;
		}
		if (!empty($this->_stmt)) {
			$arr = $this->_stmt->errorInfo();
		}
		else if (!empty($this->_connectionID)) {
			$arr = $this->_connectionID->errorInfo();
		}
		else {
			return 'No Connection Established';
		}

		if ($arr) {
			if (sizeof($arr)<2) {
				return '';
			}
			if ((integer)$arr[0]) {
				return $arr[2];
			}
			else {
				return '';
			}
		}
		else {
			return '-1';
		}
	}


	function ErrorNo()
	{
		if ($this->_errorno !== false) {
			return $this->_errorno;
		}
		if (!empty($this->_stmt)) {
			$err = $this->_stmt->errorCode();
		}
		else if (!empty($this->_connectionID)) {
			$arr = $this->_connectionID->errorInfo();
			if (isset($arr[0])) {
				$err = $arr[0];
			}
			else {
				$err = -1;
			}
		} else {
			return 0;
		}

		if ($err == '00000') {
			return 0; 		}
		return $err;
	}

	function SetTransactionMode($transaction_mode)
	{
		if(method_exists($this->_driver, 'SetTransactionMode')) {
			return $this->_driver->SetTransactionMode($transaction_mode);
		}

		return parent::SetTransactionMode($seqname);
	}

	function BeginTrans()
	{
		if(method_exists($this->_driver, 'BeginTrans')) {
			return $this->_driver->BeginTrans();
		}

		if (!$this->hasTransactions) {
			return false;
		}
		if ($this->transOff) {
			return true;
		}
		$this->transCnt += 1;
		$this->_autocommit = false;
		$this->_connectionID->setAttribute(PDO::ATTR_AUTOCOMMIT,false);

		return $this->_connectionID->beginTransaction();
	}

	function CommitTrans($ok=true)
	{
		if(method_exists($this->_driver, 'CommitTrans')) {
			return $this->_driver->CommitTrans($ok);
		}

		if (!$this->hasTransactions) {
			return false;
		}
		if ($this->transOff) {
			return true;
		}
		if (!$ok) {
			return $this->RollbackTrans();
		}
		if ($this->transCnt) {
			$this->transCnt -= 1;
		}
		$this->_autocommit = true;

		$ret = $this->_connectionID->commit();
		$this->_connectionID->setAttribute(PDO::ATTR_AUTOCOMMIT,true);
		return $ret;
	}

	function RollbackTrans()
	{
		if(method_exists($this->_driver, 'RollbackTrans')) {
			return $this->_driver->RollbackTrans();
		}

		if (!$this->hasTransactions) {
			return false;
		}
		if ($this->transOff) {
			return true;
		}
		if ($this->transCnt) {
			$this->transCnt -= 1;
		}
		$this->_autocommit = true;

		$ret = $this->_connectionID->rollback();
		$this->_connectionID->setAttribute(PDO::ATTR_AUTOCOMMIT,true);
		return $ret;
	}

	function Prepare($sql)
	{
		$this->_stmt = $this->_connectionID->prepare($sql);
		if ($this->_stmt) {
			return array($sql,$this->_stmt);
		}

		return false;
	}

	function PrepareStmt($sql)
	{
		$stmt = $this->_connectionID->prepare($sql);
		if (!$stmt) {
			return false;
		}
		$obj = new ADOPDOStatement($stmt,$this);
		return $obj;
	}

	function CreateSequence($seqname='adodbseq',$startID=1)
	{
		if(method_exists($this->_driver, 'CreateSequence')) {
			return $this->_driver->CreateSequence($seqname, $startID);
		}

		return parent::CreateSequence($seqname, $startID);
	}

	function DropSequence($seqname='adodbseq')
	{
		if(method_exists($this->_driver, 'DropSequence')) {
			return $this->_driver->DropSequence($seqname);
		}

		return parent::DropSequence($seqname);
	}

	function GenID($seqname='adodbseq',$startID=1)
	{
		if(method_exists($this->_driver, 'GenID')) {
			return $this->_driver->GenID($seqname, $startID);
		}

		return parent::GenID($seqname, $startID);
	}


	
	function _query($sql,$inputarr=false)
	{
		if (is_array($sql)) {
			$stmt = $sql[1];
		} else {
			$stmt = $this->_connectionID->prepare($sql);
		}
						if ($stmt) {
			$this->_driver->debug = $this->debug;
			if ($inputarr) {
				$ok = $stmt->execute($inputarr);
			}
			else {
				$ok = $stmt->execute();
			}
		}


		$this->_errormsg = false;
		$this->_errorno = false;

		if ($ok) {
			$this->_stmt = $stmt;
			return $stmt;
		}

		if ($stmt) {

			$arr = $stmt->errorinfo();
			if ((integer)$arr[1]) {
				$this->_errormsg = $arr[2];
				$this->_errorno = $arr[1];
			}

		} else {
			$this->_errormsg = false;
			$this->_errorno = false;
		}
		return false;
	}

		function _close()
	{
		$this->_stmt = false;
		return true;
	}

	function _affectedrows()
	{
		return ($this->_stmt) ? $this->_stmt->rowCount() : 0;
	}

	function _insertid()
	{
		return ($this->_connectionID) ? $this->_connectionID->lastInsertId() : 0;
	}
}

class ADODB_pdo_base extends ADODB_pdo {

	var $sysDate = "'?'";
	var $sysTimeStamp = "'?'";


	function _init($parentDriver)
	{
		$parentDriver->_bindInputArray = true;
			}

	function ServerInfo()
	{
		return ADOConnection::ServerInfo();
	}

	function SelectLimit($sql,$nrows=-1,$offset=-1,$inputarr=false,$secs2cache=0)
	{
		$ret = ADOConnection::SelectLimit($sql,$nrows,$offset,$inputarr,$secs2cache);
		return $ret;
	}

	function MetaTables($ttype=false,$showSchema=false,$mask=false)
	{
		return false;
	}

	function MetaColumns($table,$normalize=true)
	{
		return false;
	}
}

class ADOPDOStatement {

	var $databaseType = "pdo";
	var $dataProvider = "pdo";
	var $_stmt;
	var $_connectionID;

	function __construct($stmt,$connection)
	{
		$this->_stmt = $stmt;
		$this->_connectionID = $connection;
	}

	function Execute($inputArr=false)
	{
		$savestmt = $this->_connectionID->_stmt;
		$rs = $this->_connectionID->Execute(array(false,$this->_stmt),$inputArr);
		$this->_connectionID->_stmt = $savestmt;
		return $rs;
	}

	function InParameter(&$var,$name,$maxLen=4000,$type=false)
	{

		if ($type) {
			$this->_stmt->bindParam($name,$var,$type,$maxLen);
		}
		else {
			$this->_stmt->bindParam($name, $var);
		}
	}

	function Affected_Rows()
	{
		return ($this->_stmt) ? $this->_stmt->rowCount() : 0;
	}

	function ErrorMsg()
	{
		if ($this->_stmt) {
			$arr = $this->_stmt->errorInfo();
		}
		else {
			$arr = $this->_connectionID->errorInfo();
		}

		if (is_array($arr)) {
			if ((integer) $arr[0] && isset($arr[2])) {
				return $arr[2];
			}
			else {
				return '';
			}
		} else {
			return '-1';
		}
	}

	function NumCols()
	{
		return ($this->_stmt) ? $this->_stmt->columnCount() : 0;
	}

	function ErrorNo()
	{
		if ($this->_stmt) {
			return $this->_stmt->errorCode();
		}
		else {
			return $this->_connectionID->errorInfo();
		}
	}
}



class ADORecordSet_pdo extends ADORecordSet {

	var $bind = false;
	var $databaseType = "pdo";
	var $dataProvider = "pdo";

	function __construct($id,$mode=false)
	{
		if ($mode === false) {
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;
		}
		$this->adodbFetchMode = $mode;
		switch($mode) {
		case ADODB_FETCH_NUM: $mode = PDO::FETCH_NUM; break;
		case ADODB_FETCH_ASSOC:  $mode = PDO::FETCH_ASSOC; break;

		case ADODB_FETCH_BOTH:
		default: $mode = PDO::FETCH_BOTH; break;
		}
		$this->fetchMode = $mode;

		$this->_queryID = $id;
		parent::__construct($id);
	}


	function Init()
	{
		if ($this->_inited) {
			return;
		}
		$this->_inited = true;
		if ($this->_queryID) {
			@$this->_initrs();
		}
		else {
			$this->_numOfRows = 0;
			$this->_numOfFields = 0;
		}
		if ($this->_numOfRows != 0 && $this->_currentRow == -1) {
			$this->_currentRow = 0;
			if ($this->EOF = ($this->_fetch() === false)) {
				$this->_numOfRows = 0; 			}
		} else {
			$this->EOF = true;
		}
	}

	function _initrs()
	{
	global $ADODB_COUNTRECS;

		$this->_numOfRows = ($ADODB_COUNTRECS) ? @$this->_queryID->rowCount() : -1;
		if (!$this->_numOfRows) {
			$this->_numOfRows = -1;
		}
		$this->_numOfFields = $this->_queryID->columnCount();
	}

		function FetchField($fieldOffset = -1)
	{
		$off=$fieldOffset+1; 
		$o= new ADOFieldObject();
		$arr = @$this->_queryID->getColumnMeta($fieldOffset);
		if (!$arr) {
			$o->name = 'bad getColumnMeta()';
			$o->max_length = -1;
			$o->type = 'VARCHAR';
			$o->precision = 0;
				return $o;
		}
				$o->name = $arr['name'];
		if (isset($arr['native_type']) && $arr['native_type'] <> "null") {
			$o->type = $arr['native_type'];
		}
		else {
			$o->type = adodb_pdo_type($arr['pdo_type']);
		}
		$o->max_length = $arr['len'];
		$o->precision = $arr['precision'];

		switch(ADODB_ASSOC_CASE) {
			case ADODB_ASSOC_CASE_LOWER:
				$o->name = strtolower($o->name);
				break;
			case ADODB_ASSOC_CASE_UPPER:
				$o->name = strtoupper($o->name);
				break;
		}
		return $o;
	}

	function _seek($row)
	{
		return false;
	}

	function _fetch()
	{
		if (!$this->_queryID) {
			return false;
		}

		$this->fields = $this->_queryID->fetch($this->fetchMode);
		return !empty($this->fields);
	}

	function _close()
	{
		$this->_queryID = false;
	}

	function Fields($colname)
	{
		if ($this->adodbFetchMode != ADODB_FETCH_NUM) {
			return @$this->fields[$colname];
		}

		if (!$this->bind) {
			$this->bind = array();
			for ($i=0; $i < $this->_numOfFields; $i++) {
				$o = $this->FetchField($i);
				$this->bind[strtoupper($o->name)] = $i;
			}
		}
		return $this->fields[$this->bind[strtoupper($colname)]];
	}

}
