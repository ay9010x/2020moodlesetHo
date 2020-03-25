<?php


if (!defined('ADODB_DIR')) die();

if (!function_exists('sqlsrv_configure')) {
	die("mssqlnative extension not installed");
}

if (!function_exists('sqlsrv_set_error_handling')) {
	function sqlsrv_set_error_handling($constant) {
		sqlsrv_configure("WarningsReturnAsErrors", $constant);
	}
}
if (!function_exists('sqlsrv_log_set_severity')) {
	function sqlsrv_log_set_severity($constant) {
		sqlsrv_configure("LogSeverity", $constant);
	}
}
if (!function_exists('sqlsrv_log_set_subsystems')) {
	function sqlsrv_log_set_subsystems($constant) {
		sqlsrv_configure("LogSubsystems", $constant);
	}
}




if (ADODB_PHPVER >= 0x4300) {
	ini_set('mssql.datetimeconvert',0);
} else {
    global $ADODB_mssql_mths;			$ADODB_mssql_date_order = 'mdy';
	$ADODB_mssql_mths = array(
		'JAN'=>1,'FEB'=>2,'MAR'=>3,'APR'=>4,'MAY'=>5,'JUN'=>6,
		'JUL'=>7,'AUG'=>8,'SEP'=>9,'OCT'=>10,'NOV'=>11,'DEC'=>12);
}

class ADODB_mssqlnative extends ADOConnection {
	var $databaseType = "mssqlnative";
	var $dataProvider = "mssqlnative";
	var $replaceQuote = "''"; 	var $fmtDate = "'Y-m-d'";
	var $fmtTimeStamp = "'Y-m-d\TH:i:s'";
	var $hasInsertID = true;
	var $substr = "substring";
	var $length = 'len';
	var $hasAffectedRows = true;
	var $poorAffectedRows = false;
	var $metaDatabasesSQL = "select name from sys.sysdatabases where name <> 'master'";
	var $metaTablesSQL="select name,case when type='U' then 'T' else 'V' end from sysobjects where (type='U' or type='V') and (name not in ('sysallocations','syscolumns','syscomments','sysdepends','sysfilegroups','sysfiles','sysfiles1','sysforeignkeys','sysfulltextcatalogs','sysindexes','sysindexkeys','sysmembers','sysobjects','syspermissions','sysprotects','sysreferences','systypes','sysusers','sysalternates','sysconstraints','syssegments','REFERENTIAL_CONSTRAINTS','CHECK_CONSTRAINTS','CONSTRAINT_TABLE_USAGE','CONSTRAINT_COLUMN_USAGE','VIEWS','VIEW_TABLE_USAGE','VIEW_COLUMN_USAGE','SCHEMATA','TABLES','TABLE_CONSTRAINTS','TABLE_PRIVILEGES','COLUMNS','COLUMN_DOMAIN_USAGE','COLUMN_PRIVILEGES','DOMAINS','DOMAIN_CONSTRAINTS','KEY_COLUMN_USAGE','dtproperties'))";
	var $metaColumnsSQL =
		"select c.name,
		t.name as type,
		c.length,
		c.xprec as precision,
		c.xscale as scale,
		c.isnullable as nullable,
		c.cdefault as default_value,
		c.xtype,
		t.length as type_length,
		sc.is_identity
		from syscolumns c
		join systypes t on t.xusertype=c.xusertype
		join sysobjects o on o.id=c.id
		join sys.tables st on st.name=o.name
		join sys.columns sc on sc.object_id = st.object_id and sc.name=c.name
		where o.name='%s'";
	var $hasTop = 'top';			var $hasGenID = true;
	var $sysDate = 'convert(datetime,convert(char,GetDate(),102),102)';
	var $sysTimeStamp = 'GetDate()';
	var $maxParameterLen = 4000;
	var $arrayClass = 'ADORecordSet_array_mssqlnative';
	var $uniqueSort = true;
	var $leftOuter = '*=';
	var $rightOuter = '=*';
	var $ansiOuter = true; 	var $identitySQL = 'select SCOPE_IDENTITY()'; 	var $uniqueOrderBy = true;
	var $_bindInputArray = true;
	var $_dropSeqSQL = "drop table %s";
	var $connectionInfo = array();
	var $sequences = false;
	var $mssql_version = '';

	function __construct()
	{
		if ($this->debug) {
			ADOConnection::outp("<pre>");
			sqlsrv_set_error_handling( SQLSRV_ERRORS_LOG_ALL );
			sqlsrv_log_set_severity( SQLSRV_LOG_SEVERITY_ALL );
			sqlsrv_log_set_subsystems(SQLSRV_LOG_SYSTEM_ALL);
			sqlsrv_configure('WarningsReturnAsErrors', 0);
		} else {
			sqlsrv_set_error_handling(0);
			sqlsrv_log_set_severity(0);
			sqlsrv_log_set_subsystems(SQLSRV_LOG_SYSTEM_ALL);
			sqlsrv_configure('WarningsReturnAsErrors', 0);
		}
	}
	function ServerVersion() {
		$data = $this->ServerInfo();
		if (preg_match('/^09/',$data['version'])){
			
			$this->mssql_version = 9;
		} elseif (preg_match('/^10/',$data['version'])){
			
			$this->mssql_version = 10;
		} elseif (preg_match('/^11/',$data['version'])){
			
			$this->mssql_version = 11;
		} else
			die("SQL SERVER VERSION {$data['version']} NOT SUPPORTED IN mssqlnative DRIVER");
	}

	function ServerInfo() {
    	global $ADODB_FETCH_MODE;
		static $arr = false;
		if (is_array($arr))
			return $arr;
		if ($this->fetchMode === false) {
			$savem = $ADODB_FETCH_MODE;
			$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		} elseif ($this->fetchMode >=0 && $this->fetchMode <=2) {
			$savem = $this->fetchMode;
		} else
			$savem = $this->SetFetchMode(ADODB_FETCH_NUM);

		$arrServerInfo = sqlsrv_server_info($this->_connectionID);
		$ADODB_FETCH_MODE = $savem;
		$arr['description'] = $arrServerInfo['SQLServerName'].' connected to '.$arrServerInfo['CurrentDatabase'];
		$arr['version'] = $arrServerInfo['SQLServerVersion'];		return $arr;
	}

	function IfNull( $field, $ifNull )
	{
		return " ISNULL($field, $ifNull) "; 	}

	function _insertid()
	{
							return $this->lastInsertID;
	}

	function _affectedrows()
	{
		if ($this->_queryID)
		return sqlsrv_rows_affected($this->_queryID);
	}

	function GenID($seq='adodbseq',$start=1) {
		if (!$this->mssql_version)
			$this->ServerVersion();
		switch($this->mssql_version){
		case 9:
		case 10:
			return $this->GenID2008();
			break;
		case 11:
			return $this->GenID2012();
			break;
		}
	}

	function CreateSequence($seq='adodbseq',$start=1)
	{
		if (!$this->mssql_vesion)
			$this->ServerVersion();

		switch($this->mssql_version){
		case 9:
		case 10:
			return $this->CreateSequence2008();
			break;
		case 11:
			return $this->CreateSequence2012();
			break;
		}

	}

	
	function CreateSequence2008($seq='adodbseq',$start=1)
	{
		if($this->debug) ADOConnection::outp("<hr>CreateSequence($seq,$start)");
		sqlsrv_begin_transaction($this->_connectionID);
		$start -= 1;
		$this->Execute("create table $seq (id int)");		$ok = $this->Execute("insert into $seq with (tablock,holdlock) values($start)");
		if (!$ok) {
			if($this->debug) ADOConnection::outp("<hr>Error: ROLLBACK");
			sqlsrv_rollback($this->_connectionID);
			return false;
		}
		sqlsrv_commit($this->_connectionID);
		return true;
	}

	
	function CreateSequence2012($seq='adodb',$start=1){
		if (!$this->sequences){
			$sql = "SELECT name FROM sys.sequences";
			$this->sequences = $this->GetCol($sql);
		}
		$ok = $this->Execute("CREATE SEQUENCE $seq START WITH $start INCREMENT BY 1");
		if (!$ok)
			die("CANNOT CREATE SEQUENCE" . print_r(sqlsrv_errors(),true));
		$this->sequences[] = $seq;
	}

	
	function GenID2008($seq='adodbseq',$start=1)
	{
		if($this->debug) ADOConnection::outp("<hr>CreateSequence($seq,$start)");
		sqlsrv_begin_transaction($this->_connectionID);
		$ok = $this->Execute("update $seq with (tablock,holdlock) set id = id + 1");
		if (!$ok) {
			$start -= 1;
			$this->Execute("create table $seq (id int)");			$ok = $this->Execute("insert into $seq with (tablock,holdlock) values($start)");
			if (!$ok) {
				if($this->debug) ADOConnection::outp("<hr>Error: ROLLBACK");
				sqlsrv_rollback($this->_connectionID);
				return false;
			}
		}
		$num = $this->GetOne("select id from $seq");
		sqlsrv_commit($this->_connectionID);
		return true;
	}
	
	function GenID2012($seq='adodbseq',$start=1)
	{

		
		if (!$this->sequences){
			$sql = "SELECT name FROM sys.sequences";
			$this->sequences = $this->GetCol($sql);
		}
		if (!is_array($this->sequences)
		|| is_array($this->sequences) && !in_array($seq,$this->sequences)){
			$this->CreateSequence2012($seq='adodbseq',$start=1);

		}
		$num = $this->GetOne("SELECT NEXT VALUE FOR $seq");
		return $num;
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
				$s .= "datename(yyyy,$col)";
				break;
			case 'M':
				$s .= "convert(char(3),$col,0)";
				break;
			case 'm':
				$s .= "replace(str(month($col),2),' ','0')";
				break;
			case 'Q':
			case 'q':
				$s .= "datename(quarter,$col)";
				break;
			case 'D':
			case 'd':
				$s .= "replace(str(day($col),2),' ','0')";
				break;
			case 'h':
				$s .= "substring(convert(char(14),$col,0),13,2)";
				break;

			case 'H':
				$s .= "replace(str(datepart(hh,$col),2),' ','0')";
				break;

			case 'i':
				$s .= "replace(str(datepart(mi,$col),2),' ','0')";
				break;
			case 's':
				$s .= "replace(str(datepart(ss,$col),2),' ','0')";
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


	function BeginTrans()
	{
		if ($this->transOff) return true;
		$this->transCnt += 1;
		if ($this->debug) ADOConnection::outp('<hr>begin transaction');
		sqlsrv_begin_transaction($this->_connectionID);
		return true;
	}

	function CommitTrans($ok=true)
	{
		if ($this->transOff) return true;
		if ($this->debug) ADOConnection::outp('<hr>commit transaction');
		if (!$ok) return $this->RollbackTrans();
		if ($this->transCnt) $this->transCnt -= 1;
		sqlsrv_commit($this->_connectionID);
		return true;
	}
	function RollbackTrans()
	{
		if ($this->transOff) return true;
		if ($this->debug) ADOConnection::outp('<hr>rollback transaction');
		if ($this->transCnt) $this->transCnt -= 1;
		sqlsrv_rollback($this->_connectionID);
		return true;
	}

	function SetTransactionMode( $transaction_mode )
	{
		$this->_transmode  = $transaction_mode;
		if (empty($transaction_mode)) {
			$this->Execute('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
			return;
		}
		if (!stristr($transaction_mode,'isolation')) $transaction_mode = 'ISOLATION LEVEL '.$transaction_mode;
		$this->Execute("SET TRANSACTION ".$transaction_mode);
	}

	
	function RowLock($tables,$where,$col='1 as adodbignore')
	{
		if ($col == '1 as adodbignore') $col = 'top 1 null as ignore';
		if (!$this->transCnt) $this->BeginTrans();
		return $this->GetOne("select $col from $tables with (ROWLOCK,HOLDLOCK) where $where");
	}

	function SelectDB($dbName)
	{
		$this->database = $dbName;
		$this->databaseName = $dbName; 		if ($this->_connectionID) {
			$rs = $this->Execute('USE '.$dbName);
			if($rs) {
				return true;
			} else return false;
		}
		else return false;
	}

	function ErrorMsg()
	{
		$retErrors = sqlsrv_errors(SQLSRV_ERR_ALL);
		if($retErrors != null) {
			foreach($retErrors as $arrError) {
				$this->_errorMsg .= "SQLState: ".$arrError[ 'SQLSTATE']."\n";
				$this->_errorMsg .= "Error Code: ".$arrError[ 'code']."\n";
				$this->_errorMsg .= "Message: ".$arrError[ 'message']."\n";
			}
		} else {
			$this->_errorMsg = "No errors found";
		}
		return $this->_errorMsg;
	}

	function ErrorNo()
	{
		if ($this->_logsql && $this->_errorCode !== false) return $this->_errorCode;
		$err = sqlsrv_errors(SQLSRV_ERR_ALL);
		if($err[0]) return $err[0]['code'];
		else return -1;
	}

		function _connect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
		if (!function_exists('sqlsrv_connect')) return null;
		$connectionInfo = $this->connectionInfo;
		$connectionInfo["Database"]=$argDatabasename;
		$connectionInfo["UID"]=$argUsername;
		$connectionInfo["PWD"]=$argPassword;
		
		foreach ($this->connectionParameters as $parameter=>$value)
		    $connectionInfo[$parameter] = $value;
		
		if ($this->debug) ADOConnection::outp("<hr>connecting... hostname: $argHostname params: ".var_export($connectionInfo,true));
				if(!($this->_connectionID = sqlsrv_connect($argHostname,$connectionInfo))) {
			if ($this->debug) ADOConnection::outp( "<hr><b>errors</b>: ".print_r( sqlsrv_errors(), true));
			return false;
		}
						return true;
	}

		function _pconnect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
				return $this->_connect($argHostname, $argUsername, $argPassword, $argDatabasename);
	}

	function Prepare($sql)
	{
		return $sql; 
		$stmt = sqlsrv_prepare( $this->_connectionID, $sql);
		if (!$stmt)  return $sql;
		return array($sql,$stmt);
	}

					function Concat()
	{
		$s = "";
		$arr = func_get_args();

				if (sizeof($arr) == 1) {
			foreach ($arr as $arg) {
				$args = explode(',', $arg);
			}
			$arr = $args;
		}

		array_walk($arr, create_function('&$v', '$v = "CAST(" . $v . " AS VARCHAR(255))";'));
		$s = implode('+',$arr);
		if (sizeof($arr) > 0) return "$s";

		return '';
	}

	
	function UpdateBlob($table,$column,$val,$where,$blobtype='BLOB')
	{

		if (strtoupper($blobtype) == 'CLOB') {
			$sql = "UPDATE $table SET $column='" . $val . "' WHERE $where";
			return $this->Execute($sql) != false;
		}
		$sql = "UPDATE $table SET $column=0x".bin2hex($val)." WHERE $where";
		return $this->Execute($sql) != false;
	}

		function _query($sql,$inputarr=false)
	{
		$this->_errorMsg = false;

		if (is_array($sql)) $sql = $sql[1];

		$insert = false;
				if(preg_match('/^\W*insert\s(?:(?:(?:\'\')*\'[^\']+\'(?:\'\')*)|[^;\'])*;?$/i', $sql)) {
			$insert = true;
			$sql .= '; '.$this->identitySQL; 		}
		if($inputarr) {
			$rez = sqlsrv_query($this->_connectionID, $sql, $inputarr);
		} else {
			$rez = sqlsrv_query($this->_connectionID,$sql);
		}

		if ($this->debug) ADOConnection::outp("<hr>running query: ".var_export($sql,true)."<hr>input array: ".var_export($inputarr,true)."<hr>result: ".var_export($rez,true));

		if(!$rez) {
			$rez = false;
		} else if ($insert) {
						while ( sqlsrv_next_result($rez) ) {
				sqlsrv_fetch($rez);
				$this->lastInsertID = sqlsrv_get_field($rez, 0);
			}
		}
		return $rez;
	}

		function _close()
	{
		if ($this->transCnt) $this->RollbackTrans();
		$rez = @sqlsrv_close($this->_connectionID);
		$this->_connectionID = false;
		return $rez;
	}

		static function UnixDate($v)
	{
		return ADORecordSet_array_mssqlnative::UnixDate($v);
	}

	static function UnixTimeStamp($v)
	{
		return ADORecordSet_array_mssqlnative::UnixTimeStamp($v);
	}

	function MetaIndexes($table,$primary=false, $owner = false)
	{
		$table = $this->qstr($table);

		$sql = "SELECT i.name AS ind_name, C.name AS col_name, USER_NAME(O.uid) AS Owner, c.colid, k.Keyno,
			CASE WHEN I.indid BETWEEN 1 AND 254 AND (I.status & 2048 = 2048 OR I.Status = 16402 AND O.XType = 'V') THEN 1 ELSE 0 END AS IsPK,
			CASE WHEN I.status & 2 = 2 THEN 1 ELSE 0 END AS IsUnique
			FROM dbo.sysobjects o INNER JOIN dbo.sysindexes I ON o.id = i.id
			INNER JOIN dbo.sysindexkeys K ON I.id = K.id AND I.Indid = K.Indid
			INNER JOIN dbo.syscolumns c ON K.id = C.id AND K.colid = C.Colid
			WHERE LEFT(i.name, 8) <> '_WA_Sys_' AND o.status >= 0 AND O.Name LIKE $table
			ORDER BY O.name, I.Name, K.keyno";

		global $ADODB_FETCH_MODE;
		$save = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		if ($this->fetchMode !== FALSE) {
			$savem = $this->SetFetchMode(FALSE);
		}

		$rs = $this->Execute($sql);
		if (isset($savem)) {
			$this->SetFetchMode($savem);
		}
		$ADODB_FETCH_MODE = $save;

		if (!is_object($rs)) {
			return FALSE;
		}

		$indexes = array();
		while ($row = $rs->FetchRow()) {
			if (!$primary && $row[5]) continue;

			$indexes[$row[0]]['unique'] = $row[6];
			$indexes[$row[0]]['columns'][] = $row[1];
		}
		return $indexes;
	}

	function MetaForeignKeys($table, $owner=false, $upper=false)
	{
		global $ADODB_FETCH_MODE;

		$save = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		$table = $this->qstr(strtoupper($table));

		$sql =
			"select object_name(constid) as constraint_name,
				col_name(fkeyid, fkey) as column_name,
				object_name(rkeyid) as referenced_table_name,
				col_name(rkeyid, rkey) as referenced_column_name
			from sysforeignkeys
			where upper(object_name(fkeyid)) = $table
			order by constraint_name, referenced_table_name, keyno";

		$constraints =& $this->GetArray($sql);

		$ADODB_FETCH_MODE = $save;

		$arr = false;
		foreach($constraints as $constr) {
						$arr[$constr[0]][$constr[2]][] = $constr[1].'='.$constr[3];
		}
		if (!$arr) return false;

		$arr2 = false;

		foreach($arr as $k => $v) {
			foreach($v as $a => $b) {
				if ($upper) $a = strtoupper($a);
				$arr2[$a] = $b;
			}
		}
		return $arr2;
	}

		function MetaDatabases()
	{
		$this->SelectDB("master");
		$rs =& $this->Execute($this->metaDatabasesSQL);
		$rows = $rs->GetRows();
		$ret = array();
		for($i=0;$i<count($rows);$i++) {
			$ret[] = $rows[$i][0];
		}
		$this->SelectDB($this->database);
		if($ret)
			return $ret;
		else
			return false;
	}

			function MetaPrimaryKeys($table, $owner=false)
	{
		global $ADODB_FETCH_MODE;

		$schema = '';
		$this->_findschema($table,$schema);
		if (!$schema) $schema = $this->database;
		if ($schema) $schema = "and k.table_catalog like '$schema%'";

		$sql = "select distinct k.column_name,ordinal_position from information_schema.key_column_usage k,
		information_schema.table_constraints tc
		where tc.constraint_name = k.constraint_name and tc.constraint_type =
		'PRIMARY KEY' and k.table_name = '$table' $schema order by ordinal_position ";

		$savem = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		$a = $this->GetCol($sql);
		$ADODB_FETCH_MODE = $savem;

		if ($a && sizeof($a)>0) return $a;
		$false = false;
		return $false;
	}


	function MetaTables($ttype=false,$showSchema=false,$mask=false)
	{
		if ($mask) {
			$save = $this->metaTablesSQL;
			$mask = $this->qstr(($mask));
			$this->metaTablesSQL .= " AND name like $mask";
		}
		$ret = ADOConnection::MetaTables($ttype,$showSchema);

		if ($mask) {
			$this->metaTablesSQL = $save;
		}
		return $ret;
	}
	function MetaColumns($table, $upper=true, $schema=false){

				static $cached_columns = array();
		if ($this->cachedSchemaFlush)
			$cached_columns = array();

		if (array_key_exists($table,$cached_columns)){
			return $cached_columns[$table];
		}
		
		if (!$this->mssql_version)
			$this->ServerVersion();

		$this->_findschema($table,$schema);
		if ($schema) {
			$dbName = $this->database;
			$this->SelectDB($schema);
		}
		global $ADODB_FETCH_MODE;
		$save = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;

		if ($this->fetchMode !== false) $savem = $this->SetFetchMode(false);
		$rs = $this->Execute(sprintf($this->metaColumnsSQL,$table));

		if ($schema) {
			$this->SelectDB($dbName);
		}

		if (isset($savem)) $this->SetFetchMode($savem);
		$ADODB_FETCH_MODE = $save;
		if (!is_object($rs)) {
			$false = false;
			return $false;
		}

		$retarr = array();
		while (!$rs->EOF){

			$fld = new ADOFieldObject();
			if (array_key_exists(0,$rs->fields)) {
				$fld->name          = $rs->fields[0];
				$fld->type          = $rs->fields[1];
				$fld->max_length    = $rs->fields[2];
				$fld->precision     = $rs->fields[3];
				$fld->scale     	= $rs->fields[4];
				$fld->not_null      =!$rs->fields[5];
				$fld->has_default   = $rs->fields[6];
				$fld->xtype         = $rs->fields[7];
				$fld->type_length   = $rs->fields[8];
				$fld->auto_increment= $rs->fields[9];
			} else {
				$fld->name          = $rs->fields['name'];
				$fld->type          = $rs->fields['type'];
				$fld->max_length    = $rs->fields['length'];
				$fld->precision     = $rs->fields['precision'];
				$fld->scale     	= $rs->fields['scale'];
				$fld->not_null      =!$rs->fields['nullable'];
				$fld->has_default   = $rs->fields['default_value'];
				$fld->xtype         = $rs->fields['xtype'];
				$fld->type_length   = $rs->fields['type_length'];
				$fld->auto_increment= $rs->fields['is_identity'];
			}

			if ($save == ADODB_FETCH_NUM)
				$retarr[] = $fld;
			else
				$retarr[strtoupper($fld->name)] = $fld;

			$rs->MoveNext();

		}
		$rs->Close();
				$cached_columns[$table] = $retarr;
				return $retarr;
	}

}



class ADORecordset_mssqlnative extends ADORecordSet {

	var $databaseType = "mssqlnative";
	var $canSeek = false;
	var $fieldOffset = 0;
	
	function __construct($id,$mode=false)
	{
		if ($mode === false) {
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;

		}
		$this->fetchMode = $mode;
		return parent::__construct($id,$mode);
	}


	function _initrs()
	{
		global $ADODB_COUNTRECS;
				
		$this->_numOfRows = -1;		$fieldmeta = sqlsrv_field_metadata($this->_queryID);
		$this->_numOfFields = ($fieldmeta)? count($fieldmeta):-1;
				
		if ($this->_numOfFields>0) {
			$this->_fieldobjs = array();
			$max = $this->_numOfFields;
			for ($i=0;$i<$max; $i++) $this->_fieldobjs[] = $this->_FetchField($i);
		}

	}


			function NextRecordSet()
	{
		if (!sqlsrv_next_result($this->_queryID)) return false;
		$this->_inited = false;
		$this->bind = false;
		$this->_currentRow = -1;
		$this->Init();
		return true;
	}

	
	function Fields($colname)
	{
		if ($this->fetchMode != ADODB_FETCH_NUM) return $this->fields[$colname];
		if (!$this->bind) {
			$this->bind = array();
			for ($i=0; $i < $this->_numOfFields; $i++) {
				$o = $this->FetchField($i);
				$this->bind[strtoupper($o->name)] = $i;
			}
		}

		return $this->fields[$this->bind[strtoupper($colname)]];
	}

	
	function _FetchField($fieldOffset = -1)
	{
		$_typeConversion = array(
			-155 => 'datetimeoffset',
			-154 => 'time',
			-152 => 'xml',
			-151 => 'udt',
			-11 => 'uniqueidentifier',
			-10 => 'ntext',
			-9 => 'nvarchar',
			-8 => 'nchar',
			-7 => 'bit',
			-6 => 'tinyint',
			-5 => 'bigint',
			-4 => 'image',
			-3 => 'varbinary',
			-2 => 'timestamp',
			-1 => 'text',
			1 => 'char',
			2 => 'numeric',
			3 => 'decimal',
			4 => 'int',
			5 => 'smallint',
			6 => 'float',
			7 => 'real',
			12 => 'varchar',
			91 => 'date',
			93 => 'datetime'
			);

		$fa = @sqlsrv_field_metadata($this->_queryID);
		if ($fieldOffset != -1) {
			$fa = $fa[$fieldOffset];
		}
		$false = false;
		if (empty($fa)) {
			$f = false;		}
		else
		{
						$fa = array_change_key_case($fa, CASE_LOWER);
			$fb = array();
			if ($fieldOffset != -1)
			{
				$fb = array(
					'name' => $fa['name'],
					'max_length' => $fa['size'],
					'column_source' => $fa['name'],
					'type' => $_typeConversion[$fa['type']]
					);
			}
			else
			{
				foreach ($fa as $key => $value)
				{
					$fb[] = array(
						'name' => $value['name'],
						'max_length' => $value['size'],
						'column_source' => $value['name'],
						'type' => $_typeConversion[$value['type']]
						);
				}
			}
			$f = (object) $fb;
		}
		return $f;
	}

	
	function FetchField($fieldOffset = -1)
	{
		return $this->_fieldobjs[$fieldOffset];
	}

	function _seek($row)
	{
		return false;	}

		function MoveNext()
	{
						if ($this->EOF) return false;

		$this->_currentRow++;
		
		if ($this->_fetch()) return true;
		$this->EOF = true;
		
		return false;
	}


			function _fetch($ignore_fields=false)
	{
				if ($this->fetchMode & ADODB_FETCH_ASSOC) {
			if ($this->fetchMode & ADODB_FETCH_NUM) {
								$this->fields = @sqlsrv_fetch_array($this->_queryID,SQLSRV_FETCH_BOTH);
			} else {
								$this->fields = @sqlsrv_fetch_array($this->_queryID,SQLSRV_FETCH_ASSOC);
			}

			if (is_array($this->fields)) {
				if (ADODB_ASSOC_CASE == 0) {
					foreach($this->fields as $k=>$v) {
						$this->fields[strtolower($k)] = $v;
					}
				} else if (ADODB_ASSOC_CASE == 1) {
					foreach($this->fields as $k=>$v) {
						$this->fields[strtoupper($k)] = $v;
					}
				}
			}
		} else {
						$this->fields = @sqlsrv_fetch_array($this->_queryID,SQLSRV_FETCH_NUMERIC);
		}
		if(is_array($this->fields) && array_key_exists(1,$this->fields) && !array_key_exists(0,$this->fields)) {			$arrFixed = array();
			foreach($this->fields as $key=>$value) {
				if(is_numeric($key)) {
					$arrFixed[$key-1] = $value;
				} else {
					$arrFixed[$key] = $value;
				}
			}
						$this->fields = $arrFixed;
		}
		if(is_array($this->fields)) {
			foreach($this->fields as $key=>$value) {
				if (is_object($value) && method_exists($value, 'format')) {					$this->fields[$key] = $value->format("Y-m-d\TH:i:s\Z");
				}
			}
		}
		if($this->fields === null) $this->fields = false;
				return $this->fields;
	}

	
	function _close()
	{
		if($this->_queryID) {
			$rez = sqlsrv_free_stmt($this->_queryID);
			$this->_queryID = false;
			return $rez;
		}
		return true;
	}

		static function UnixDate($v)
	{
		return ADORecordSet_array_mssqlnative::UnixDate($v);
	}

	static function UnixTimeStamp($v)
	{
		return ADORecordSet_array_mssqlnative::UnixTimeStamp($v);
	}
}


class ADORecordSet_array_mssqlnative extends ADORecordSet_array {
	function __construct($id=-1,$mode=false)
	{
		parent::__construct($id,$mode);
	}

			static function UnixDate($v)
	{

		if (is_numeric(substr($v,0,1)) && ADODB_PHPVER >= 0x4200) return parent::UnixDate($v);

		global $ADODB_mssql_mths,$ADODB_mssql_date_order;

				if ($ADODB_mssql_date_order == 'dmy') {
			if (!preg_match( "|^([0-9]{1,2})[-/\. ]+([A-Za-z]{3})[-/\. ]+([0-9]{4})|" ,$v, $rr)) {
				return parent::UnixDate($v);
			}
			if ($rr[3] <= TIMESTAMP_FIRST_YEAR) return 0;

			$theday = $rr[1];
			$themth =  substr(strtoupper($rr[2]),0,3);
		} else {
			if (!preg_match( "|^([A-Za-z]{3})[-/\. ]+([0-9]{1,2})[-/\. ]+([0-9]{4})|" ,$v, $rr)) {
				return parent::UnixDate($v);
			}
			if ($rr[3] <= TIMESTAMP_FIRST_YEAR) return 0;

			$theday = $rr[2];
			$themth = substr(strtoupper($rr[1]),0,3);
		}
		$themth = $ADODB_mssql_mths[$themth];
		if ($themth <= 0) return false;
				return  adodb_mktime(0,0,0,$themth,$theday,$rr[3]);
	}

	static function UnixTimeStamp($v)
	{

		if (is_numeric(substr($v,0,1)) && ADODB_PHPVER >= 0x4200) return parent::UnixTimeStamp($v);

		global $ADODB_mssql_mths,$ADODB_mssql_date_order;

				 if ($ADODB_mssql_date_order == 'dmy') {
			 if (!preg_match( "|^([0-9]{1,2})[-/\. ]+([A-Za-z]{3})[-/\. ]+([0-9]{4}) +([0-9]{1,2}):([0-9]{1,2}) *([apAP]{0,1})|"
			,$v, $rr)) return parent::UnixTimeStamp($v);
			if ($rr[3] <= TIMESTAMP_FIRST_YEAR) return 0;

			$theday = $rr[1];
			$themth =  substr(strtoupper($rr[2]),0,3);
		} else {
			if (!preg_match( "|^([A-Za-z]{3})[-/\. ]+([0-9]{1,2})[-/\. ]+([0-9]{4}) +([0-9]{1,2}):([0-9]{1,2}) *([apAP]{0,1})|"
			,$v, $rr)) return parent::UnixTimeStamp($v);
			if ($rr[3] <= TIMESTAMP_FIRST_YEAR) return 0;

			$theday = $rr[2];
			$themth = substr(strtoupper($rr[1]),0,3);
		}

		$themth = $ADODB_mssql_mths[$themth];
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
				return  adodb_mktime($rr[4],$rr[5],0,$themth,$theday,$rr[3]);
	}
}


