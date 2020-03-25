<?php


if (!defined('ADODB_DIR')) die();

if (! defined("_ADODB_MYSQL_LAYER")) {
	define("_ADODB_MYSQL_LAYER", 1 );

class ADODB_mysql extends ADOConnection {
	var $databaseType = 'mysql';
	var $dataProvider = 'mysql';
	var $hasInsertID = true;
	var $hasAffectedRows = true;
	var $metaTablesSQL = "SELECT
			TABLE_NAME,
			CASE WHEN TABLE_TYPE = 'VIEW' THEN 'V' ELSE 'T' END
		FROM INFORMATION_SCHEMA.TABLES
		WHERE TABLE_SCHEMA=";
	var $metaColumnsSQL = "SHOW COLUMNS FROM `%s`";
	var $fmtTimeStamp = "'Y-m-d H:i:s'";
	var $hasLimit = true;
	var $hasMoveFirst = true;
	var $hasGenID = true;
	var $isoDates = true; 	var $sysDate = 'CURDATE()';
	var $sysTimeStamp = 'NOW()';
	var $hasTransactions = false;
	var $forceNewConnect = false;
	var $poorAffectedRows = true;
	var $clientFlags = 0;
	var $charSet = '';
	var $substr = "substring";
	var $nameQuote = '`';			var $compat323 = false; 		
	function __construct()
	{
		if (defined('ADODB_EXTENSION')) $this->rsPrefix .= 'ext_';
	}


		function SetCharSet($charset_name)
	{
		if (!function_exists('mysql_set_charset')) {
			return false;
		}

		if ($this->charSet !== $charset_name) {
			$ok = @mysql_set_charset($charset_name,$this->_connectionID);
			if ($ok) {
				$this->charSet = $charset_name;
				return true;
			}
			return false;
		}
		return true;
	}

	function ServerInfo()
	{
		$arr['description'] = ADOConnection::GetOne("select version()");
		$arr['version'] = ADOConnection::_findvers($arr['description']);
		return $arr;
	}

	function IfNull( $field, $ifNull )
	{
		return " IFNULL($field, $ifNull) "; 	}

	function MetaProcedures($NamePattern = false, $catalog = null, $schemaPattern = null)
	{
				global $ADODB_FETCH_MODE;

		$false = false;
		$save = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;

		if ($this->fetchMode !== FALSE) {
			$savem = $this->SetFetchMode(FALSE);
		}

		$procedures = array ();

		
		$likepattern = '';
		if ($NamePattern) {
			$likepattern = " LIKE '".$NamePattern."'";
		}
		$rs = $this->Execute('SHOW PROCEDURE STATUS'.$likepattern);
		if (is_object($rs)) {

						while ($row = $rs->FetchRow()) {
				$procedures[$row[1]] = array(
					'type' => 'PROCEDURE',
					'catalog' => '',
					'schema' => '',
					'remarks' => $row[7],
				);
			}
		}

		$rs = $this->Execute('SHOW FUNCTION STATUS'.$likepattern);
		if (is_object($rs)) {
						while ($row = $rs->FetchRow()) {
				$procedures[$row[1]] = array(
					'type' => 'FUNCTION',
					'catalog' => '',
					'schema' => '',
					'remarks' => $row[7]
				);
			}
		}

				if (isset($savem)) {
			$this->SetFetchMode($savem);
		}
		$ADODB_FETCH_MODE = $save;

		return $procedures;
	}

	
	function MetaTables($ttype=false,$showSchema=false,$mask=false)
	{
		$save = $this->metaTablesSQL;
		if ($showSchema && is_string($showSchema)) {
			$this->metaTablesSQL .= $this->qstr($showSchema);
		} else {
			$this->metaTablesSQL .= "schema()";
		}

		if ($mask) {
			$mask = $this->qstr($mask);
			$this->metaTablesSQL .= " AND table_name LIKE $mask";
		}
		$ret = ADOConnection::MetaTables($ttype,$showSchema);

		$this->metaTablesSQL = $save;
		return $ret;
	}


	function MetaIndexes ($table, $primary = FALSE, $owner=false)
	{
				global $ADODB_FETCH_MODE;

		$false = false;
		$save = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		if ($this->fetchMode !== FALSE) {
			$savem = $this->SetFetchMode(FALSE);
		}

				$rs = $this->Execute(sprintf('SHOW INDEX FROM %s',$table));

				if (isset($savem)) {
			$this->SetFetchMode($savem);
		}
		$ADODB_FETCH_MODE = $save;

		if (!is_object($rs)) {
			return $false;
		}

		$indexes = array ();

				while ($row = $rs->FetchRow()) {
			if ($primary == FALSE AND $row[2] == 'PRIMARY') {
				continue;
			}

			if (!isset($indexes[$row[2]])) {
				$indexes[$row[2]] = array(
					'unique' => ($row[1] == 0),
					'columns' => array()
				);
			}

			$indexes[$row[2]]['columns'][$row[3] - 1] = $row[4];
		}

				foreach ( array_keys ($indexes) as $index )
		{
			ksort ($indexes[$index]['columns']);
		}

		return $indexes;
	}


		function qstr($s,$magic_quotes=false)
	{
		if (is_null($s)) return 'NULL';
		if (!$magic_quotes) {

			if (ADODB_PHPVER >= 0x4300) {
				if (is_resource($this->_connectionID))
					return "'".mysql_real_escape_string($s,$this->_connectionID)."'";
			}
			if ($this->replaceQuote[0] == '\\'){
				$s = adodb_str_replace(array('\\',"\0"),array('\\\\',"\\\0"),$s);
			}
			return "'".str_replace("'",$this->replaceQuote,$s)."'";
		}

				$s = str_replace('\\"','"',$s);
		return "'$s'";
	}

	function _insertid()
	{
		return ADOConnection::GetOne('SELECT LAST_INSERT_ID()');
			}

	function GetOne($sql,$inputarr=false)
	{
	global $ADODB_GETONE_EOF;
		if ($this->compat323 == false && strncasecmp($sql,'sele',4) == 0) {
			$rs = $this->SelectLimit($sql,1,-1,$inputarr);
			if ($rs) {
				$rs->Close();
				if ($rs->EOF) return $ADODB_GETONE_EOF;
				return reset($rs->fields);
			}
		} else {
			return ADOConnection::GetOne($sql,$inputarr);
		}
		return false;
	}

	function BeginTrans()
	{
		if ($this->debug) ADOConnection::outp("Transactions not supported in 'mysql' driver. Use 'mysqlt' or 'mysqli' driver");
	}

	function _affectedrows()
	{
			return mysql_affected_rows($this->_connectionID);
	}

	 		var $_genIDSQL = "update %s set id=LAST_INSERT_ID(id+1);";
	var $_genSeqSQL = "create table if not exists %s (id int not null)";
	var $_genSeqCountSQL = "select count(*) from %s";
	var $_genSeq2SQL = "insert into %s values (%s)";
	var $_dropSeqSQL = "drop table if exists %s";

	function CreateSequence($seqname='adodbseq',$startID=1)
	{
		if (empty($this->_genSeqSQL)) return false;
		$u = strtoupper($seqname);

		$ok = $this->Execute(sprintf($this->_genSeqSQL,$seqname));
		if (!$ok) return false;
		return $this->Execute(sprintf($this->_genSeq2SQL,$seqname,$startID-1));
	}


	function GenID($seqname='adodbseq',$startID=1)
	{
				if (!$this->hasGenID) return false;

		$savelog = $this->_logsql;
		$this->_logsql = false;
		$getnext = sprintf($this->_genIDSQL,$seqname);
		$holdtransOK = $this->_transOK; 		$rs = @$this->Execute($getnext);
		if (!$rs) {
			if ($holdtransOK) $this->_transOK = true; 			$u = strtoupper($seqname);
			$this->Execute(sprintf($this->_genSeqSQL,$seqname));
			$cnt = $this->GetOne(sprintf($this->_genSeqCountSQL,$seqname));
			if (!$cnt) $this->Execute(sprintf($this->_genSeq2SQL,$seqname,$startID-1));
			$rs = $this->Execute($getnext);
		}

		if ($rs) {
			$this->genID = mysql_insert_id($this->_connectionID);
			$rs->Close();
		} else
			$this->genID = 0;

		$this->_logsql = $savelog;
		return $this->genID;
	}

	function MetaDatabases()
	{
		$qid = mysql_list_dbs($this->_connectionID);
		$arr = array();
		$i = 0;
		$max = mysql_num_rows($qid);
		while ($i < $max) {
			$db = mysql_tablename($qid,$i);
			if ($db != 'mysql') $arr[] = $db;
			$i += 1;
		}
		return $arr;
	}


		function SQLDate($fmt, $col=false)
	{
		if (!$col) $col = $this->sysTimeStamp;
		$s = 'DATE_FORMAT('.$col.",'";
		$concat = false;
		$len = strlen($fmt);
		for ($i=0; $i < $len; $i++) {
			$ch = $fmt[$i];
			switch($ch) {

			default:
				if ($ch == '\\') {
					$i++;
					$ch = substr($fmt,$i,1);
				}
				
			case '-':
			case '/':
				$s .= $ch;
				break;

			case 'Y':
			case 'y':
				$s .= '%Y';
				break;
			case 'M':
				$s .= '%b';
				break;

			case 'm':
				$s .= '%m';
				break;
			case 'D':
			case 'd':
				$s .= '%d';
				break;

			case 'Q':
			case 'q':
				$s .= "'),Quarter($col)";

				if ($len > $i+1) $s .= ",DATE_FORMAT($col,'";
				else $s .= ",('";
				$concat = true;
				break;

			case 'H':
				$s .= '%H';
				break;

			case 'h':
				$s .= '%I';
				break;

			case 'i':
				$s .= '%i';
				break;

			case 's':
				$s .= '%s';
				break;

			case 'a':
			case 'A':
				$s .= '%p';
				break;

			case 'w':
				$s .= '%w';
				break;

			 case 'W':
				$s .= '%U';
				break;

			case 'l':
				$s .= '%W';
				break;
			}
		}
		$s.="')";
		if ($concat) $s = "CONCAT($s)";
		return $s;
	}


			function Concat()
	{
		$s = "";
		$arr = func_get_args();

				$s = implode(',',$arr);
		if (strlen($s) > 0) return "CONCAT($s)";
		else return '';
	}

	function OffsetDate($dayFraction,$date=false)
	{
		if (!$date) $date = $this->sysDate;

		$fraction = $dayFraction * 24 * 3600;
		return '('. $date . ' + INTERVAL ' .	 $fraction.' SECOND)';

	}

		function _connect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
		if (!empty($this->port)) $argHostname .= ":".$this->port;

		if (ADODB_PHPVER >= 0x4300)
			$this->_connectionID = mysql_connect($argHostname,$argUsername,$argPassword,
												$this->forceNewConnect,$this->clientFlags);
		else if (ADODB_PHPVER >= 0x4200)
			$this->_connectionID = mysql_connect($argHostname,$argUsername,$argPassword,
												$this->forceNewConnect);
		else
			$this->_connectionID = mysql_connect($argHostname,$argUsername,$argPassword);

		if ($this->_connectionID === false) return false;
		if ($argDatabasename) return $this->SelectDB($argDatabasename);
		return true;
	}

		function _pconnect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
		if (!empty($this->port)) $argHostname .= ":".$this->port;

		if (ADODB_PHPVER >= 0x4300)
			$this->_connectionID = mysql_pconnect($argHostname,$argUsername,$argPassword,$this->clientFlags);
		else
			$this->_connectionID = mysql_pconnect($argHostname,$argUsername,$argPassword);
		if ($this->_connectionID === false) return false;
		if ($this->autoRollback) $this->RollbackTrans();
		if ($argDatabasename) return $this->SelectDB($argDatabasename);
		return true;
	}

	function _nconnect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
		$this->forceNewConnect = true;
		return $this->_connect($argHostname, $argUsername, $argPassword, $argDatabasename);
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
			$type = $rs->fields[1];

						$fld->scale = null;
			if (preg_match("/^(.+)\((\d+),(\d+)/", $type, $query_array)) {
				$fld->type = $query_array[1];
				$fld->max_length = is_numeric($query_array[2]) ? $query_array[2] : -1;
				$fld->scale = is_numeric($query_array[3]) ? $query_array[3] : -1;
			} elseif (preg_match("/^(.+)\((\d+)/", $type, $query_array)) {
				$fld->type = $query_array[1];
				$fld->max_length = is_numeric($query_array[2]) ? $query_array[2] : -1;
			} elseif (preg_match("/^(enum)\((.*)\)$/i", $type, $query_array)) {
				$fld->type = $query_array[1];
				$arr = explode(",",$query_array[2]);
				$fld->enums = $arr;
				$zlen = max(array_map("strlen",$arr)) - 2; 				$fld->max_length = ($zlen > 0) ? $zlen : 1;
			} else {
				$fld->type = $type;
				$fld->max_length = -1;
			}
			$fld->not_null = ($rs->fields[2] != 'YES');
			$fld->primary_key = ($rs->fields[3] == 'PRI');
			$fld->auto_increment = (strpos($rs->fields[5], 'auto_increment') !== false);
			$fld->binary = (strpos($type,'blob') !== false || strpos($type,'binary') !== false);
			$fld->unsigned = (strpos($type,'unsigned') !== false);
			$fld->zerofill = (strpos($type,'zerofill') !== false);

			if (!$fld->binary) {
				$d = $rs->fields[4];
				if ($d != '' && $d != 'NULL') {
					$fld->has_default = true;
					$fld->default_value = $d;
				} else {
					$fld->has_default = false;
				}
			}

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

		function SelectDB($dbName)
	{
		$this->database = $dbName;
		$this->databaseName = $dbName; 		if ($this->_connectionID) {
			return @mysql_select_db($dbName,$this->_connectionID);
		}
		else return false;
	}

		function SelectLimit($sql,$nrows=-1,$offset=-1,$inputarr=false,$secs=0)
	{
		$offsetStr =($offset>=0) ? ((integer)$offset)."," : '';
				if ($nrows < 0) $nrows = '18446744073709551615';

		if ($secs)
			$rs = $this->CacheExecute($secs,$sql." LIMIT $offsetStr".((integer)$nrows),$inputarr);
		else
			$rs = $this->Execute($sql." LIMIT $offsetStr".((integer)$nrows),$inputarr);
		return $rs;
	}

		function _query($sql,$inputarr=false)
	{

	return mysql_query($sql,$this->_connectionID);
	
	}

	
	function ErrorMsg()
	{

		if ($this->_logsql) return $this->_errorMsg;
		if (empty($this->_connectionID)) $this->_errorMsg = @mysql_error();
		else $this->_errorMsg = @mysql_error($this->_connectionID);
		return $this->_errorMsg;
	}

	
	function ErrorNo()
	{
		if ($this->_logsql) return $this->_errorCode;
		if (empty($this->_connectionID)) return @mysql_errno();
		else return @mysql_errno($this->_connectionID);
	}

		function _close()
	{
		@mysql_close($this->_connectionID);

		$this->charSet = '';
		$this->_connectionID = false;
	}


	
	function CharMax()
	{
		return 255;
	}

	
	function TextMax()
	{
		return 4294967295;
	}

		function MetaForeignKeys( $table, $owner = FALSE, $upper = FALSE, $associative = FALSE )
	{
	 global $ADODB_FETCH_MODE;
		if ($ADODB_FETCH_MODE == ADODB_FETCH_ASSOC || $this->fetchMode == ADODB_FETCH_ASSOC) $associative = true;

		if ( !empty($owner) ) {
			$table = "$owner.$table";
		}
		$a_create_table = $this->getRow(sprintf('SHOW CREATE TABLE %s', $table));
		if ($associative) {
			$create_sql = isset($a_create_table["Create Table"]) ? $a_create_table["Create Table"] : $a_create_table["Create View"];
		} else {
			$create_sql = $a_create_table[1];
		}

		$matches = array();

		if (!preg_match_all("/FOREIGN KEY \(`(.*?)`\) REFERENCES `(.*?)` \(`(.*?)`\)/", $create_sql, $matches)) return false;
		$foreign_keys = array();
		$num_keys = count($matches[0]);
		for ( $i = 0; $i < $num_keys; $i ++ ) {
			$my_field  = explode('`, `', $matches[1][$i]);
			$ref_table = $matches[2][$i];
			$ref_field = explode('`, `', $matches[3][$i]);

			if ( $upper ) {
				$ref_table = strtoupper($ref_table);
			}

						if (!isset($foreign_keys[$ref_table])) {
				$foreign_keys[$ref_table] = array();
			}
			$num_fields = count($my_field);
			for ( $j = 0; $j < $num_fields; $j ++ ) {
				if ( $associative ) {
					$foreign_keys[$ref_table][$ref_field[$j]] = $my_field[$j];
				} else {
					$foreign_keys[$ref_table][] = "{$my_field[$j]}={$ref_field[$j]}";
				}
			}
		}

		return $foreign_keys;
	}


}




class ADORecordSet_mysql extends ADORecordSet{

	var $databaseType = "mysql";
	var $canSeek = true;

	function __construct($queryID,$mode=false)
	{
		if ($mode === false) {
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;
		}
		switch ($mode)
		{
		case ADODB_FETCH_NUM: $this->fetchMode = MYSQL_NUM; break;
		case ADODB_FETCH_ASSOC:$this->fetchMode = MYSQL_ASSOC; break;
		case ADODB_FETCH_DEFAULT:
		case ADODB_FETCH_BOTH:
		default:
			$this->fetchMode = MYSQL_BOTH; break;
		}
		$this->adodbFetchMode = $mode;
		parent::__construct($queryID);
	}

	function _initrs()
	{
				$this->_numOfRows = @mysql_num_rows($this->_queryID);
		$this->_numOfFields = @mysql_num_fields($this->_queryID);
	}

	function FetchField($fieldOffset = -1)
	{
		if ($fieldOffset != -1) {
			$o = @mysql_fetch_field($this->_queryID, $fieldOffset);
			$f = @mysql_field_flags($this->_queryID,$fieldOffset);
			if ($o) $o->max_length = @mysql_field_len($this->_queryID,$fieldOffset); 						if ($o) $o->binary = (strpos($f,'binary')!== false);
		}
		else {	
			$o = @mysql_fetch_field($this->_queryID);
						$o->max_length = -1; 		}

		return $o;
	}

	function GetRowAssoc($upper = ADODB_ASSOC_CASE)
	{
		if ($this->fetchMode == MYSQL_ASSOC && $upper == ADODB_ASSOC_CASE_LOWER) {
			$row = $this->fields;
		}
		else {
			$row = ADORecordSet::GetRowAssoc($upper);
		}
		return $row;
	}

	
	function Fields($colname)
	{
				if ($this->fetchMode != MYSQL_NUM) return @$this->fields[$colname];

		if (!$this->bind) {
			$this->bind = array();
			for ($i=0; $i < $this->_numOfFields; $i++) {
				$o = $this->FetchField($i);
				$this->bind[strtoupper($o->name)] = $i;
			}
		}
		 return $this->fields[$this->bind[strtoupper($colname)]];
	}

	function _seek($row)
	{
		if ($this->_numOfRows == 0) return false;
		return @mysql_data_seek($this->_queryID,$row);
	}

	function MoveNext()
	{
						if (@$this->fields = mysql_fetch_array($this->_queryID,$this->fetchMode)) {
			$this->_updatefields();
			$this->_currentRow += 1;
			return true;
		}
		if (!$this->EOF) {
			$this->_currentRow += 1;
			$this->EOF = true;
		}
		return false;
	}

	function _fetch()
	{
		$this->fields = @mysql_fetch_array($this->_queryID,$this->fetchMode);
		$this->_updatefields();
		return is_array($this->fields);
	}

	function _close() {
		@mysql_free_result($this->_queryID);
		$this->_queryID = false;
	}

	function MetaType($t,$len=-1,$fieldobj=false)
	{
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
		}

		$len = -1; 		switch (strtoupper($t)) {
		case 'STRING':
		case 'CHAR':
		case 'VARCHAR':
		case 'TINYBLOB':
		case 'TINYTEXT':
		case 'ENUM':
		case 'SET':
			if ($len <= $this->blobSize) return 'C';

		case 'TEXT':
		case 'LONGTEXT':
		case 'MEDIUMTEXT':
			return 'X';

						case 'IMAGE':
		case 'LONGBLOB':
		case 'BLOB':
		case 'MEDIUMBLOB':
		case 'BINARY':
			return !empty($fieldobj->binary) ? 'B' : 'X';

		case 'YEAR':
		case 'DATE': return 'D';

		case 'TIME':
		case 'DATETIME':
		case 'TIMESTAMP': return 'T';

		case 'INT':
		case 'INTEGER':
		case 'BIGINT':
		case 'TINYINT':
		case 'MEDIUMINT':
		case 'SMALLINT':

			if (!empty($fieldobj->primary_key)) return 'R';
			else return 'I';

		default: return 'N';
		}
	}

}

class ADORecordSet_ext_mysql extends ADORecordSet_mysql {
	function __construct($queryID,$mode=false)
	{
		parent::__construct($queryID,$mode);
	}

	function MoveNext()
	{
		return @adodb_movenext($this);
	}
}

}
