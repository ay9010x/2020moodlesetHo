<?php


if (!defined('ADODB_DIR')) die();

if (! defined("_ADODB_FBSQL_LAYER")) {
 define("_ADODB_FBSQL_LAYER", 1 );

class ADODB_fbsql extends ADOConnection {
	var $databaseType = 'fbsql';
	var $hasInsertID = true;
	var $hasAffectedRows = true;
	var $metaTablesSQL = "SHOW TABLES";
	var $metaColumnsSQL = "SHOW COLUMNS FROM %s";
	var $fmtTimeStamp = "'Y-m-d H:i:s'";
	var $hasLimit = false;

	function __construct()
	{
	}

	function _insertid()
	{
			return fbsql_insert_id($this->_connectionID);
	}

	function _affectedrows()
	{
			return fbsql_affected_rows($this->_connectionID);
	}

  	function MetaDatabases()
	{
		$qid = fbsql_list_dbs($this->_connectionID);
		$arr = array();
		$i = 0;
		$max = fbsql_num_rows($qid);
		while ($i < $max) {
			$arr[] = fbsql_tablename($qid,$i);
			$i += 1;
		}
		return $arr;
	}

		function Concat()
	{
		$s = "";
		$arr = func_get_args();
		$first = true;

		$s = implode(',',$arr);
		if (sizeof($arr) > 0) return "CONCAT($s)";
		else return '';
	}

		function _connect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
		$this->_connectionID = fbsql_connect($argHostname,$argUsername,$argPassword);
		if ($this->_connectionID === false) return false;
		if ($argDatabasename) return $this->SelectDB($argDatabasename);
		return true;
	}

		function _pconnect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
		$this->_connectionID = fbsql_pconnect($argHostname,$argUsername,$argPassword);
		if ($this->_connectionID === false) return false;
		if ($argDatabasename) return $this->SelectDB($argDatabasename);
		return true;
	}

 	function MetaColumns($table, $normalize=true)
	{
		if ($this->metaColumnsSQL) {

			$rs = $this->Execute(sprintf($this->metaColumnsSQL,$table));

			if ($rs === false) return false;

			$retarr = array();
			while (!$rs->EOF){
				$fld = new ADOFieldObject();
				$fld->name = $rs->fields[0];
				$fld->type = $rs->fields[1];

								if (preg_match("/^(.+)\((\d+)\)$/", $fld->type, $query_array)) {
					$fld->type = $query_array[1];
					$fld->max_length = $query_array[2];
				} else {
					$fld->max_length = -1;
				}
				$fld->not_null = ($rs->fields[2] != 'YES');
				$fld->primary_key = ($rs->fields[3] == 'PRI');
				$fld->auto_increment = (strpos($rs->fields[5], 'auto_increment') !== false);
				$fld->binary = (strpos($fld->type,'blob') !== false);

				$retarr[strtoupper($fld->name)] = $fld;
				$rs->MoveNext();
			}
			$rs->Close();
			return $retarr;
		}
		return false;
	}

		function SelectDB($dbName)
	{
		$this->database = $dbName;
		if ($this->_connectionID) {
			return @fbsql_select_db($dbName,$this->_connectionID);
		}
		else return false;
	}


		function _query($sql,$inputarr=false)
	{
		return fbsql_query("$sql;",$this->_connectionID);
	}

	
	function ErrorMsg()
	{
		$this->_errorMsg = @fbsql_error($this->_connectionID);
			return $this->_errorMsg;
	}

	
	function ErrorNo()
	{
		return @fbsql_errno($this->_connectionID);
	}

		function _close()
	{
		return @fbsql_close($this->_connectionID);
	}

}



class ADORecordSet_fbsql extends ADORecordSet{

	var $databaseType = "fbsql";
	var $canSeek = true;

	function __construct($queryID,$mode=false)
	{
		if (!$mode) {
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;
		}
		switch ($mode) {
		case ADODB_FETCH_NUM: $this->fetchMode = FBSQL_NUM; break;
		case ADODB_FETCH_ASSOC: $this->fetchMode = FBSQL_ASSOC; break;
		case ADODB_FETCH_BOTH:
		default:
		$this->fetchMode = FBSQL_BOTH; break;
		}
		return parent::__construct($queryID);
	}

	function _initrs()
	{
	GLOBAL $ADODB_COUNTRECS;
		$this->_numOfRows = ($ADODB_COUNTRECS) ? @fbsql_num_rows($this->_queryID):-1;
		$this->_numOfFields = @fbsql_num_fields($this->_queryID);
	}



	function FetchField($fieldOffset = -1) {
		if ($fieldOffset != -1) {
			$o =  @fbsql_fetch_field($this->_queryID, $fieldOffset);
						$f = @fbsql_field_flags($this->_queryID,$fieldOffset);
			$o->binary = (strpos($f,'binary')!== false);
		}
		else if ($fieldOffset == -1) {	
			$o = @fbsql_fetch_field($this->_queryID);					}

		return $o;
	}

	function _seek($row)
	{
		return @fbsql_data_seek($this->_queryID,$row);
	}

	function _fetch($ignore_fields=false)
	{
		$this->fields = @fbsql_fetch_array($this->_queryID,$this->fetchMode);
		return ($this->fields == true);
	}

	function _close() {
		return @fbsql_free_result($this->_queryID);
	}

	function MetaType($t,$len=-1,$fieldobj=false)
	{
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
		}
		$len = -1; 		switch (strtoupper($t)) {
		case 'CHARACTER':
		case 'CHARACTER VARYING':
		case 'BLOB':
		case 'CLOB':
		case 'BIT':
		case 'BIT VARYING':
			if ($len <= $this->blobSize) return 'C';

				case 'IMAGE':
		case 'LONGBLOB':
		case 'BLOB':
		case 'MEDIUMBLOB':
			return !empty($fieldobj->binary) ? 'B' : 'X';

		case 'DATE': return 'D';

		case 'TIME':
		case 'TIME WITH TIME ZONE':
		case 'TIMESTAMP':
		case 'TIMESTAMP WITH TIME ZONE': return 'T';

		case 'PRIMARY_KEY':
			return 'R';
		case 'INTEGER':
		case 'SMALLINT':
		case 'BOOLEAN':

			if (!empty($fieldobj->primary_key)) return 'R';
			else return 'I';

		default: return 'N';
		}
	}

} } 