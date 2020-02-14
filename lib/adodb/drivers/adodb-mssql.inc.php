<?php



if (!defined('ADODB_DIR')) die();



if (ADODB_PHPVER >= 0x4300) {
	ini_set('mssql.datetimeconvert',0);
} else {
global $ADODB_mssql_mths;		

	$ADODB_mssql_date_order = 'mdy';
	$ADODB_mssql_mths = array(
		'JAN'=>1,'FEB'=>2,'MAR'=>3,'APR'=>4,'MAY'=>5,'JUN'=>6,
		'JUL'=>7,'AUG'=>8,'SEP'=>9,'OCT'=>10,'NOV'=>11,'DEC'=>12);
}

function AutoDetect_MSSQL_Date_Order($conn)
{
global $ADODB_mssql_date_order;
	$adate = $conn->GetOne('select getdate()');
	if ($adate) {
		$anum = (int) $adate;
		if ($anum > 0) {
			if ($anum > 31) {
							} else
				$ADODB_mssql_date_order = 'dmy';
		} else
			$ADODB_mssql_date_order = 'mdy';
	}
}

class ADODB_mssql extends ADOConnection {
	var $databaseType = "mssql";
	var $dataProvider = "mssql";
	var $replaceQuote = "''"; 	var $fmtDate = "'Y-m-d'";
	var $fmtTimeStamp = "'Y-m-d\TH:i:s'";
	var $hasInsertID = true;
	var $substr = "substring";
	var $length = 'len';
	var $hasAffectedRows = true;
	var $metaDatabasesSQL = "select name from sysdatabases where name <> 'master'";
	var $metaTablesSQL="select name,case when type='U' then 'T' else 'V' end from sysobjects where (type='U' or type='V') and (name not in ('sysallocations','syscolumns','syscomments','sysdepends','sysfilegroups','sysfiles','sysfiles1','sysforeignkeys','sysfulltextcatalogs','sysindexes','sysindexkeys','sysmembers','sysobjects','syspermissions','sysprotects','sysreferences','systypes','sysusers','sysalternates','sysconstraints','syssegments','REFERENTIAL_CONSTRAINTS','CHECK_CONSTRAINTS','CONSTRAINT_TABLE_USAGE','CONSTRAINT_COLUMN_USAGE','VIEWS','VIEW_TABLE_USAGE','VIEW_COLUMN_USAGE','SCHEMATA','TABLES','TABLE_CONSTRAINTS','TABLE_PRIVILEGES','COLUMNS','COLUMN_DOMAIN_USAGE','COLUMN_PRIVILEGES','DOMAINS','DOMAIN_CONSTRAINTS','KEY_COLUMN_USAGE','dtproperties'))";
	var $metaColumnsSQL = 	"select c.name,t.name,c.length,c.isnullable, c.status,
		(case when c.xusertype=61 then 0 else c.xprec end),
		(case when c.xusertype=61 then 0 else c.xscale end)
	from syscolumns c join systypes t on t.xusertype=c.xusertype join sysobjects o on o.id=c.id where o.name='%s'";
	var $hasTop = 'top';			var $hasGenID = true;
	var $sysDate = 'convert(datetime,convert(char,GetDate(),102),102)';
	var $sysTimeStamp = 'GetDate()';
	var $_has_mssql_init;
	var $maxParameterLen = 4000;
	var $arrayClass = 'ADORecordSet_array_mssql';
	var $uniqueSort = true;
	var $leftOuter = '*=';
	var $rightOuter = '=*';
	var $ansiOuter = true; 	var $poorAffectedRows = true;
	var $identitySQL = 'select SCOPE_IDENTITY()'; 	var $uniqueOrderBy = true;
	var $_bindInputArray = true;
	var $forceNewConnect = false;

	function __construct()
	{
		$this->_has_mssql_init = (strnatcmp(PHP_VERSION,'4.1.0')>=0);
	}

	function ServerInfo()
	{
	global $ADODB_FETCH_MODE;


		if ($this->fetchMode === false) {
			$savem = $ADODB_FETCH_MODE;
			$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		} else
			$savem = $this->SetFetchMode(ADODB_FETCH_NUM);

		if (0) {
			$stmt = $this->PrepareSP('sp_server_info');
			$val = 2;
			$this->Parameter($stmt,$val,'attribute_id');
			$row = $this->GetRow($stmt);
		}

		$row = $this->GetRow("execute sp_server_info 2");


		if ($this->fetchMode === false) {
			$ADODB_FETCH_MODE = $savem;
		} else
			$this->SetFetchMode($savem);

		$arr['description'] = $row[2];
		$arr['version'] = ADOConnection::_findvers($arr['description']);
		return $arr;
	}

	function IfNull( $field, $ifNull )
	{
		return " ISNULL($field, $ifNull) "; 	}

	function _insertid()
	{
					        if ($this->lastInsID !== false) {
            return $this->lastInsID;         } else {
			return $this->GetOne($this->identitySQL);
		}
	}



	
	function qstr($s,$magic_quotes=false)
	{
 		if (!$magic_quotes) {
 			return  "'".str_replace("'",$this->replaceQuote,$s)."'";
		}

 		 		$sybase = ini_get('magic_quotes_sybase');
 		if (!$sybase) {
 			$s = str_replace('\\"','"',$s);
 			if ($this->replaceQuote == "\\'")   				return "'$s'";
 			else { 				$s = str_replace('\\\\','\\',$s);
 				return "'".str_replace("\\'",$this->replaceQuote,$s)."'";
 			}
 		} else {
 			return "'".$s."'";
		}
	}

	function _affectedrows()
	{
		return $this->GetOne('select @@rowcount');
	}

	var $_dropSeqSQL = "drop table %s";

	function CreateSequence($seq='adodbseq',$start=1)
	{

		$this->Execute('BEGIN TRANSACTION adodbseq');
		$start -= 1;
		$this->Execute("create table $seq (id float(53))");
		$ok = $this->Execute("insert into $seq with (tablock,holdlock) values($start)");
		if (!$ok) {
				$this->Execute('ROLLBACK TRANSACTION adodbseq');
				return false;
		}
		$this->Execute('COMMIT TRANSACTION adodbseq');
		return true;
	}

	function GenID($seq='adodbseq',$start=1)
	{
				$this->Execute('BEGIN TRANSACTION adodbseq');
		$ok = $this->Execute("update $seq with (tablock,holdlock) set id = id + 1");
		if (!$ok) {
			$this->Execute("create table $seq (id float(53))");
			$ok = $this->Execute("insert into $seq with (tablock,holdlock) values($start)");
			if (!$ok) {
				$this->Execute('ROLLBACK TRANSACTION adodbseq');
				return false;
			}
			$this->Execute('COMMIT TRANSACTION adodbseq');
			return $start;
		}
		$num = $this->GetOne("select id from $seq");
		$this->Execute('COMMIT TRANSACTION adodbseq');
		return $num;

					}


	function SelectLimit($sql,$nrows=-1,$offset=-1, $inputarr=false,$secs2cache=0)
	{
		if ($nrows > 0 && $offset <= 0) {
			$sql = preg_replace(
				'/(^\s*select\s+(distinctrow|distinct)?)/i','\\1 '.$this->hasTop." $nrows ",$sql);

			if ($secs2cache)
				$rs = $this->CacheExecute($secs2cache, $sql, $inputarr);
			else
				$rs = $this->Execute($sql,$inputarr);
		} else
			$rs = ADOConnection::SelectLimit($sql,$nrows,$offset,$inputarr,$secs2cache);

		return $rs;
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
	   	$ok = $this->Execute('BEGIN TRAN');
	   	return $ok;
	}

	function CommitTrans($ok=true)
	{
		if ($this->transOff) return true;
		if (!$ok) return $this->RollbackTrans();
		if ($this->transCnt) $this->transCnt -= 1;
		$ok = $this->Execute('COMMIT TRAN');
		return $ok;
	}
	function RollbackTrans()
	{
		if ($this->transOff) return true;
		if ($this->transCnt) $this->transCnt -= 1;
		$ok = $this->Execute('ROLLBACK TRAN');
		return $ok;
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


	function MetaColumns($table, $normalize=true)
	{

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
			$fld->name = $rs->fields[0];
			$fld->type = $rs->fields[1];

			$fld->not_null = (!$rs->fields[3]);
			$fld->auto_increment = ($rs->fields[4] == 128);		
			if (isset($rs->fields[5]) && $rs->fields[5]) {
				if ($rs->fields[5]>0) $fld->max_length = $rs->fields[5];
				$fld->scale = $rs->fields[6];
				if ($fld->scale>0) $fld->max_length += 1;
			} else
				$fld->max_length = $rs->fields[2];

			if ($save == ADODB_FETCH_NUM) {
				$retarr[] = $fld;
			} else {
				$retarr[strtoupper($fld->name)] = $fld;
			}
				$rs->MoveNext();
			}

			$rs->Close();
			return $retarr;

	}


	function MetaIndexes($table,$primary=false, $owner=false)
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
			if ($primary && !$row[5]) continue;

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

		$constraints = $this->GetArray($sql);

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
		if(@mssql_select_db("master")) {
				 $qry=$this->metaDatabasesSQL;
				 if($rs=@mssql_query($qry,$this->_connectionID)){
						 $tmpAr=$ar=array();
						 while($tmpAr=@mssql_fetch_row($rs))
								 $ar[]=$tmpAr[0];
						@mssql_select_db($this->database);
						 if(sizeof($ar))
								 return($ar);
						 else
								 return(false);
				 } else {
						 @mssql_select_db($this->database);
						 return(false);
				 }
		 }
		 return(false);
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

	function SelectDB($dbName)
	{
		$this->database = $dbName;
		$this->databaseName = $dbName; 		if ($this->_connectionID) {
			return @mssql_select_db($dbName);
		}
		else return false;
	}

	function ErrorMsg()
	{
		if (empty($this->_errorMsg)){
			$this->_errorMsg = mssql_get_last_message();
		}
		return $this->_errorMsg;
	}

	function ErrorNo()
	{
		if ($this->_logsql && $this->_errorCode !== false) return $this->_errorCode;
		if (empty($this->_errorMsg)) {
			$this->_errorMsg = mssql_get_last_message();
		}
		$id = @mssql_query("select @@ERROR",$this->_connectionID);
		if (!$id) return false;
		$arr = mssql_fetch_array($id);
		@mssql_free_result($id);
		if (is_array($arr)) return $arr[0];
	   else return -1;
	}

		function _connect($argHostname, $argUsername, $argPassword, $argDatabasename,$newconnect=false)
	{
		if (!function_exists('mssql_pconnect')) return null;
		$this->_connectionID = mssql_connect($argHostname,$argUsername,$argPassword,$newconnect);
		if ($this->_connectionID === false) return false;
		if ($argDatabasename) return $this->SelectDB($argDatabasename);
		return true;
	}


		function _pconnect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
		if (!function_exists('mssql_pconnect')) return null;
		$this->_connectionID = mssql_pconnect($argHostname,$argUsername,$argPassword);
		if ($this->_connectionID === false) return false;

				if ($this->autoRollback) {
			$cnt = $this->GetOne('select @@TRANCOUNT');
			while (--$cnt >= 0) $this->Execute('ROLLBACK TRAN');
		}
		if ($argDatabasename) return $this->SelectDB($argDatabasename);
		return true;
	}

	function _nconnect($argHostname, $argUsername, $argPassword, $argDatabasename)
    {
		return $this->_connect($argHostname, $argUsername, $argPassword, $argDatabasename, true);
    }

	function Prepare($sql)
	{
		$sqlarr = explode('?',$sql);
		if (sizeof($sqlarr) <= 1) return $sql;
		$sql2 = $sqlarr[0];
		for ($i = 1, $max = sizeof($sqlarr); $i < $max; $i++) {
			$sql2 .=  '@P'.($i-1) . $sqlarr[$i];
		}
		return array($sql,$this->qstr($sql2),$max,$sql2);
	}

	function PrepareSP($sql,$param=true)
	{
		if (!$this->_has_mssql_init) {
			ADOConnection::outp( "PrepareSP: mssql_init only available since PHP 4.1.0");
			return $sql;
		}
		$stmt = mssql_init($sql,$this->_connectionID);
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

	
	function Parameter(&$stmt, &$var, $name, $isOutput=false, $maxLen=4000, $type=false)
	{
		if (!$this->_has_mssql_init) {
			ADOConnection::outp( "Parameter: mssql_bind only available since PHP 4.1.0");
			return false;
		}

		$isNull = is_null($var); 
		if ($type === false)
			switch(gettype($var)) {
			default:
			case 'string': $type = SQLVARCHAR; break;
			case 'double': $type = SQLFLT8; break;
			case 'integer': $type = SQLINT4; break;
			case 'boolean': $type = SQLINT1; break; 			}

		if  ($this->debug) {
			$prefix = ($isOutput) ? 'Out' : 'In';
			$ztype = (empty($type)) ? 'false' : $type;
			ADOConnection::outp( "{$prefix}Parameter(\$stmt, \$php_var='$var', \$name='$name', \$maxLen=$maxLen, \$type=$ztype);");
		}
		
		if ($name !== 'RETVAL') $name = '@'.$name;
		return mssql_bind($stmt[1], $name, $var, $type, $isOutput, $isNull, $maxLen);
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
		if (is_array($inputarr)) {

									            $getIdentity = false;
            if (!is_array($sql) && preg_match('/^\\s*insert/i', $sql)) {
                $getIdentity = true;
                $sql .= (preg_match('/;\\s*$/i', $sql) ? ' ' : '; ') . $this->identitySQL;
            }
			if (!is_array($sql)) $sql = $this->Prepare($sql);
			$params = '';
			$decl = '';
			$i = 0;
			foreach($inputarr as $v) {
				if ($decl) {
					$decl .= ', ';
					$params .= ', ';
				}
				if (is_string($v)) {
					$len = strlen($v);
					if ($len == 0) $len = 1;

					if ($len > 4000 ) {
												$decl .= "@P$i NTEXT";
					} else {
						$decl .= "@P$i NVARCHAR($len)";
					}

					$params .= "@P$i=N". (strncmp($v,"'",1)==0? $v : $this->qstr($v));
				} else if (is_integer($v)) {
					$decl .= "@P$i INT";
					$params .= "@P$i=".$v;
				} else if (is_float($v)) {
					$decl .= "@P$i FLOAT";
					$params .= "@P$i=".$v;
				} else if (is_bool($v)) {
					$decl .= "@P$i INT"; 					$params .= "@P$i=".(($v)?'1':'0'); 				} else {
					$decl .= "@P$i CHAR"; 					$params .= "@P$i=NULL";
					}
				$i += 1;
			}
			$decl = $this->qstr($decl);
			if ($this->debug) ADOConnection::outp("<font size=-1>sp_executesql N{$sql[1]},N$decl,$params</font>");
			$rez = mssql_query("sp_executesql N{$sql[1]},N$decl,$params", $this->_connectionID);
            if ($getIdentity) {
                $arr = @mssql_fetch_row($rez);
                $this->lastInsID = isset($arr[0]) ? $arr[0] : false;
                @mssql_data_seek($rez, 0);
            }

		} else if (is_array($sql)) {
						$rez = mssql_execute($sql[1]);
            $this->lastInsID = false;

		} else {
			$rez = mssql_query($sql,$this->_connectionID);
            $this->lastInsID = false;
		}
		return $rez;
	}

		function _close()
	{
		if ($this->transCnt) $this->RollbackTrans();
		$rez = @mssql_close($this->_connectionID);
		$this->_connectionID = false;
		return $rez;
	}

		static function UnixDate($v)
	{
		return ADORecordSet_array_mssql::UnixDate($v);
	}

	static function UnixTimeStamp($v)
	{
		return ADORecordSet_array_mssql::UnixTimeStamp($v);
	}
}



class ADORecordset_mssql extends ADORecordSet {

	var $databaseType = "mssql";
	var $canSeek = true;
	var $hasFetchAssoc; 	
	function __construct($id,$mode=false)
	{
				$this->hasFetchAssoc = function_exists('mssql_fetch_assoc');

		if ($mode === false) {
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;

		}
		$this->fetchMode = $mode;
		return parent::__construct($id,$mode);
	}


	function _initrs()
	{
	GLOBAL $ADODB_COUNTRECS;
		$this->_numOfRows = ($ADODB_COUNTRECS)? @mssql_num_rows($this->_queryID):-1;
		$this->_numOfFields = @mssql_num_fields($this->_queryID);
	}


			function NextRecordSet()
	{
		if (!mssql_next_result($this->_queryID)) return false;
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

	

	function FetchField($fieldOffset = -1)
	{
		if ($fieldOffset != -1) {
			$f = @mssql_fetch_field($this->_queryID, $fieldOffset);
		}
		else if ($fieldOffset == -1) {	
			$f = @mssql_fetch_field($this->_queryID);
		}
		$false = false;
		if (empty($f)) return $false;
		return $f;
	}

	function _seek($row)
	{
		return @mssql_data_seek($this->_queryID, $row);
	}

		function MoveNext()
	{
		if ($this->EOF) return false;

		$this->_currentRow++;

		if ($this->fetchMode & ADODB_FETCH_ASSOC) {
			if ($this->fetchMode & ADODB_FETCH_NUM) {
								$this->fields = @mssql_fetch_array($this->_queryID);
			}
			else {
				if ($this->hasFetchAssoc) {					 $this->fields = @mssql_fetch_assoc($this->_queryID);
				} else {
					$flds = @mssql_fetch_array($this->_queryID);
					if (is_array($flds)) {
						$fassoc = array();
						foreach($flds as $k => $v) {
							if (is_numeric($k)) continue;
							$fassoc[$k] = $v;
						}
						$this->fields = $fassoc;
					} else
						$this->fields = false;
				}
			}

			if (is_array($this->fields)) {
				if (ADODB_ASSOC_CASE == 0) {
					foreach($this->fields as $k=>$v) {
						$kn = strtolower($k);
						if ($kn <> $k) {
							unset($this->fields[$k]);
							$this->fields[$kn] = $v;
						}
					}
				} else if (ADODB_ASSOC_CASE == 1) {
					foreach($this->fields as $k=>$v) {
						$kn = strtoupper($k);
						if ($kn <> $k) {
							unset($this->fields[$k]);
							$this->fields[$kn] = $v;
						}
					}
				}
			}
		} else {
			$this->fields = @mssql_fetch_row($this->_queryID);
		}
		if ($this->fields) return true;
		$this->EOF = true;

		return false;
	}


			function _fetch($ignore_fields=false)
	{
		if ($this->fetchMode & ADODB_FETCH_ASSOC) {
			if ($this->fetchMode & ADODB_FETCH_NUM) {
								$this->fields = @mssql_fetch_array($this->_queryID);
			} else {
				if ($this->hasFetchAssoc) 					$this->fields = @mssql_fetch_assoc($this->_queryID);
				else {
					$this->fields = @mssql_fetch_array($this->_queryID);
					if (@is_array($$this->fields)) {
						$fassoc = array();
						foreach($$this->fields as $k => $v) {
							if (is_integer($k)) continue;
							$fassoc[$k] = $v;
						}
						$this->fields = $fassoc;
					}
				}
			}

			if (!$this->fields) {
			} else if (ADODB_ASSOC_CASE == 0) {
				foreach($this->fields as $k=>$v) {
					$kn = strtolower($k);
					if ($kn <> $k) {
						unset($this->fields[$k]);
						$this->fields[$kn] = $v;
					}
				}
			} else if (ADODB_ASSOC_CASE == 1) {
				foreach($this->fields as $k=>$v) {
					$kn = strtoupper($k);
					if ($kn <> $k) {
						unset($this->fields[$k]);
						$this->fields[$kn] = $v;
					}
				}
			}
		} else {
			$this->fields = @mssql_fetch_row($this->_queryID);
		}
		return $this->fields;
	}

	

	function _close()
	{
		if($this->_queryID) {
			$rez = mssql_free_result($this->_queryID);
			$this->_queryID = false;
			return $rez;
		}
		return true;
	}

		static function UnixDate($v)
	{
		return ADORecordSet_array_mssql::UnixDate($v);
	}

	static function UnixTimeStamp($v)
	{
		return ADORecordSet_array_mssql::UnixTimeStamp($v);
	}

}


class ADORecordSet_array_mssql extends ADORecordSet_array {
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
				return  mktime(0,0,0,$themth,$theday,$rr[3]);
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
				return  mktime($rr[4],$rr[5],0,$themth,$theday,$rr[3]);
	}
}


