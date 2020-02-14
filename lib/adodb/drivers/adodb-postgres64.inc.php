<?php


if (!defined('ADODB_DIR')) die();

function adodb_addslashes($s)
{
	$len = strlen($s);
	if ($len == 0) return "''";
	if (strncmp($s,"'",1) === 0 && substr($s,$len-1) == "'") return $s; 
	return "'".addslashes($s)."'";
}

class ADODB_postgres64 extends ADOConnection{
	var $databaseType = 'postgres64';
	var $dataProvider = 'postgres';
	var $hasInsertID = true;
	var $_resultid = false;
	var $concat_operator='||';
	var $metaDatabasesSQL = "select datname from pg_database where datname not in ('template0','template1') order by 1";
	var $metaTablesSQL = "select tablename,'T' from pg_tables where tablename not like 'pg\_%'
		and tablename not in ('sql_features', 'sql_implementation_info', 'sql_languages',
			'sql_packages', 'sql_sizing', 'sql_sizing_profiles')
	union
		select viewname,'V' from pg_views where viewname not like 'pg\_%'";
		var $isoDates = true; 	var $sysDate = "CURRENT_DATE";
	var $sysTimeStamp = "CURRENT_TIMESTAMP";
	var $blobEncodeType = 'C';
	var $metaColumnsSQL = "SELECT a.attname,t.typname,a.attlen,a.atttypmod,a.attnotnull,a.atthasdef,a.attnum
		FROM pg_class c, pg_attribute a,pg_type t
		WHERE relkind in ('r','v') AND (c.relname='%s' or c.relname = lower('%s')) and a.attname not like '....%%'
		AND a.attnum > 0 AND a.atttypid = t.oid AND a.attrelid = c.oid ORDER BY a.attnum";

		var $metaColumnsSQL1 = "SELECT a.attname, t.typname, a.attlen, a.atttypmod, a.attnotnull, a.atthasdef, a.attnum
		FROM pg_class c, pg_attribute a, pg_type t, pg_namespace n
		WHERE relkind in ('r','v') AND (c.relname='%s' or c.relname = lower('%s'))
		and c.relnamespace=n.oid and n.nspname='%s'
		and a.attname not like '....%%' AND a.attnum > 0
		AND a.atttypid = t.oid AND a.attrelid = c.oid ORDER BY a.attnum";

		var $metaKeySQL = "SELECT ic.relname AS index_name, a.attname AS column_name,i.indisunique AS unique_key, i.indisprimary AS primary_key
		FROM pg_class bc, pg_class ic, pg_index i, pg_attribute a
		WHERE bc.oid = i.indrelid AND ic.oid = i.indexrelid
		AND (i.indkey[0] = a.attnum OR i.indkey[1] = a.attnum OR i.indkey[2] = a.attnum OR i.indkey[3] = a.attnum OR i.indkey[4] = a.attnum OR i.indkey[5] = a.attnum OR i.indkey[6] = a.attnum OR i.indkey[7] = a.attnum)
		AND a.attrelid = bc.oid AND bc.relname = '%s'";

	var $hasAffectedRows = true;
	var $hasLimit = false;			var $true = 'TRUE';			var $false = 'FALSE';			var $fmtDate = "'Y-m-d'";		var $fmtTimeStamp = "'Y-m-d H:i:s'"; 	var $hasMoveFirst = true;
	var $hasGenID = true;
	var $_genIDSQL = "SELECT NEXTVAL('%s')";
	var $_genSeqSQL = "CREATE SEQUENCE %s START %s";
	var $_dropSeqSQL = "DROP SEQUENCE %s";
	var $metaDefaultsSQL = "SELECT d.adnum as num, d.adsrc as def from pg_attrdef d, pg_class c where d.adrelid=c.oid and c.relname='%s' order by d.adnum";
	var $random = 'random()';			var $autoRollback = true; 							
	var $uniqueIisR = true;
	var $_bindInputArray = false; 	var $disableBlobs = false; 
	var $_pnum = 0;

							
	function __construct()
	{
			}

	function ServerInfo()
	{
		if (isset($this->version)) return $this->version;

		$arr['description'] = $this->GetOne("select version()");
		$arr['version'] = ADOConnection::_findvers($arr['description']);
		$this->version = $arr;
		return $arr;
	}

	function IfNull( $field, $ifNull )
	{
		return " coalesce($field, $ifNull) ";
	}

		function pg_insert_id($tablename,$fieldname)
	{
		$result=pg_query($this->_connectionID, 'SELECT last_value FROM '. $tablename .'_'. $fieldname .'_seq');
		if ($result) {
			$arr = @pg_fetch_row($result,0);
			pg_free_result($result);
			if (isset($arr[0])) return $arr[0];
		}
		return false;
	}

	
	function _insertid($table,$column)
	{
		if (!is_resource($this->_resultid) || get_resource_type($this->_resultid) !== 'pgsql result') return false;
		$oid = pg_getlastoid($this->_resultid);
				return empty($table) || empty($column) ? $oid : $this->GetOne("SELECT $column FROM $table WHERE oid=".(int)$oid);
	}

	function _affectedrows()
	{
		if (!is_resource($this->_resultid) || get_resource_type($this->_resultid) !== 'pgsql result') return false;
		return pg_affected_rows($this->_resultid);
	}


	
	function BeginTrans()
	{
		if ($this->transOff) return true;
		$this->transCnt += 1;
		return pg_query($this->_connectionID, 'begin '.$this->_transmode);
	}

	function RowLock($tables,$where,$col='1 as adodbignore')
	{
		if (!$this->transCnt) $this->BeginTrans();
		return $this->GetOne("select $col from $tables where $where for update");
	}

		function CommitTrans($ok=true)
	{
		if ($this->transOff) return true;
		if (!$ok) return $this->RollbackTrans();

		$this->transCnt -= 1;
		return pg_query($this->_connectionID, 'commit');
	}

		function RollbackTrans()
	{
		if ($this->transOff) return true;
		$this->transCnt -= 1;
		return pg_query($this->_connectionID, 'rollback');
	}

	function MetaTables($ttype=false,$showSchema=false,$mask=false)
	{
		$info = $this->ServerInfo();
		if ($info['version'] >= 7.3) {
		$this->metaTablesSQL = "
			select table_name,'T' from information_schema.tables where table_schema not in ( 'pg_catalog','information_schema')
			union
			select table_name,'V' from information_schema.views where table_schema not in ( 'pg_catalog','information_schema') ";
		}
		if ($mask) {
			$save = $this->metaTablesSQL;
			$mask = $this->qstr(strtolower($mask));
			if ($info['version']>=7.3)
				$this->metaTablesSQL = "
					select table_name,'T' from information_schema.tables where table_name like $mask and table_schema not in ( 'pg_catalog','information_schema')
					union
					select table_name,'V' from information_schema.views where table_name like $mask and table_schema not in ( 'pg_catalog','information_schema') ";
			else
				$this->metaTablesSQL = "
					select tablename,'T' from pg_tables where tablename like $mask
					union
					select viewname,'V' from pg_views where viewname like $mask";
		}
		$ret = ADOConnection::MetaTables($ttype,$showSchema);

		if ($mask) {
			$this->metaTablesSQL = $save;
		}
		return $ret;
	}


		function qstr($s,$magic_quotes=false)
	{
		if (is_bool($s)) return $s ? 'true' : 'false';

		if (!$magic_quotes) {
			if (ADODB_PHPVER >= 0x5200 && $this->_connectionID) {
				return  "'".pg_escape_string($this->_connectionID,$s)."'";
			}
			if (ADODB_PHPVER >= 0x4200) {
				return  "'".pg_escape_string($s)."'";
			}
			if ($this->replaceQuote[0] == '\\'){
				$s = adodb_str_replace(array('\\',"\0"),array('\\\\',"\\\\000"),$s);
			}
			return  "'".str_replace("'",$this->replaceQuote,$s)."'";
		}

				$s = str_replace('\\"','"',$s);
		return "'$s'";
	}



		function SQLDate($fmt, $col=false)
	{
		if (!$col) $col = $this->sysTimeStamp;
		$s = 'TO_CHAR('.$col.",'";

		$len = strlen($fmt);
		for ($i=0; $i < $len; $i++) {
			$ch = $fmt[$i];
			switch($ch) {
			case 'Y':
			case 'y':
				$s .= 'YYYY';
				break;
			case 'Q':
			case 'q':
				$s .= 'Q';
				break;

			case 'M':
				$s .= 'Mon';
				break;

			case 'm':
				$s .= 'MM';
				break;
			case 'D':
			case 'd':
				$s .= 'DD';
				break;

			case 'H':
				$s.= 'HH24';
				break;

			case 'h':
				$s .= 'HH';
				break;

			case 'i':
				$s .= 'MI';
				break;

			case 's':
				$s .= 'SS';
				break;

			case 'a':
			case 'A':
				$s .= 'AM';
				break;

			case 'w':
				$s .= 'D';
				break;

			case 'l':
				$s .= 'DAY';
				break;

			case 'W':
				$s .= 'WW';
				break;

			default:
							if ($ch == '\\') {
					$i++;
					$ch = substr($fmt,$i,1);
				}
				if (strpos('-/.:;, ',$ch) !== false) $s .= $ch;
				else $s .= '"'.$ch.'"';

			}
		}
		return $s. "')";
	}



	
	function UpdateBlobFile($table,$column,$path,$where,$blobtype='BLOB')
	{
		pg_query($this->_connectionID, 'begin');

		$fd = fopen($path,'r');
		$contents = fread($fd,filesize($path));
		fclose($fd);

		$oid = pg_lo_create($this->_connectionID);
		$handle = pg_lo_open($this->_connectionID, $oid, 'w');
		pg_lo_write($handle, $contents);
		pg_lo_close($handle);

				pg_query($this->_connectionID, 'commit');
		$rs = ADOConnection::UpdateBlob($table,$column,$oid,$where,$blobtype);
		$rez = !empty($rs);
		return $rez;
	}

	
	function BlobDelete( $blob )
	{
		pg_query($this->_connectionID, 'begin');
		$result = @pg_lo_unlink($blob);
		pg_query($this->_connectionID, 'commit');
		return( $result );
	}

	
	function GuessOID($oid)
	{
		if (strlen($oid)>16) return false;
		return is_numeric($oid);
	}

	
	function BlobDecode($blob,$maxsize=false,$hastrans=true)
	{
		if (!$this->GuessOID($blob)) return $blob;

		if ($hastrans) pg_query($this->_connectionID,'begin');
		$fd = @pg_lo_open($this->_connectionID,$blob,'r');
		if ($fd === false) {
			if ($hastrans) pg_query($this->_connectionID,'commit');
			return $blob;
		}
		if (!$maxsize) $maxsize = $this->maxblobsize;
		$realblob = @pg_lo_read($fd,$maxsize);
		@pg_loclose($fd);
		if ($hastrans) pg_query($this->_connectionID,'commit');
		return $realblob;
	}

	
	function BlobEncode($blob)
	{
		if (ADODB_PHPVER >= 0x5200) return pg_escape_bytea($this->_connectionID, $blob);
		if (ADODB_PHPVER >= 0x4200) return pg_escape_bytea($blob);

		
		$badch = array(chr(92),chr(0),chr(39)); 		$fixch = array('\\\\134','\\\\000','\\\\047');
		return adodb_str_replace($badch,$fixch,$blob);

			}

		function UpdateBlob($table,$column,$val,$where,$blobtype='BLOB')
	{
		if ($blobtype == 'CLOB') {
			return $this->Execute("UPDATE $table SET $column=" . $this->qstr($val) . " WHERE $where");
		}
				return $this->Execute("UPDATE $table SET $column='".$this->BlobEncode($val)."'::bytea WHERE $where");
	}

	function OffsetDate($dayFraction,$date=false)
	{
		if (!$date) $date = $this->sysDate;
		else if (strncmp($date,"'",1) == 0) {
			$len = strlen($date);
			if (10 <= $len && $len <= 12) $date = 'date '.$date;
			else $date = 'timestamp '.$date;
		}


		return "($date+interval'".($dayFraction * 1440)." minutes')";
			}

	
	protected function _generateMetaColumnsSQL($table, $schema)
	{
		if ($schema) {
			return sprintf($this->metaColumnsSQL1, $table, $table, $schema);
		}
		else {
			return sprintf($this->metaColumnsSQL, $table, $table, $schema);
		}
	}

				function MetaColumns($table,$normalize=true)
	{
		global $ADODB_FETCH_MODE;

		$schema = false;
		$false = false;
		$this->_findschema($table,$schema);

		if ($normalize) $table = strtolower($table);

		$save = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		if ($this->fetchMode !== false) $savem = $this->SetFetchMode(false);

		$rs = $this->Execute($this->_generateMetaColumnsSQL($table, $schema));
		if (isset($savem)) $this->SetFetchMode($savem);
		$ADODB_FETCH_MODE = $save;

		if ($rs === false) {
			return $false;
		}
		if (!empty($this->metaKeySQL)) {
												
			$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

			$rskey = $this->Execute(sprintf($this->metaKeySQL,($table)));
						$keys = $rskey->GetArray();
			if (isset($savem)) $this->SetFetchMode($savem);
			$ADODB_FETCH_MODE = $save;

			$rskey->Close();
			unset($rskey);
		}

		$rsdefa = array();
		if (!empty($this->metaDefaultsSQL)) {
			$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
			$sql = sprintf($this->metaDefaultsSQL, ($table));
			$rsdef = $this->Execute($sql);
			if (isset($savem)) $this->SetFetchMode($savem);
			$ADODB_FETCH_MODE = $save;

			if ($rsdef) {
				while (!$rsdef->EOF) {
					$num = $rsdef->fields['num'];
					$s = $rsdef->fields['def'];
					if (strpos($s,'::')===false && substr($s, 0, 1) == "'") { 
						$s = substr($s, 1);
						$s = substr($s, 0, strlen($s) - 1);
					}

					$rsdefa[$num] = $s;
					$rsdef->MoveNext();
				}
			} else {
				ADOConnection::outp( "==> SQL => " . $sql);
			}
			unset($rsdef);
		}

		$retarr = array();
		while (!$rs->EOF) {
			$fld = new ADOFieldObject();
			$fld->name = $rs->fields[0];
			$fld->type = $rs->fields[1];
			$fld->max_length = $rs->fields[2];
			$fld->attnum = $rs->fields[6];

			if ($fld->max_length <= 0) $fld->max_length = $rs->fields[3]-4;
			if ($fld->max_length <= 0) $fld->max_length = -1;
			if ($fld->type == 'numeric') {
				$fld->scale = $fld->max_length & 0xFFFF;
				$fld->max_length >>= 16;
			}
									$fld->has_default = ($rs->fields[5] == 't');
			if ($fld->has_default) {
				$fld->default_value = $rsdefa[$rs->fields[6]];
			}

						$fld->not_null = $rs->fields[4] == 't';


						if (is_array($keys)) {
				foreach($keys as $key) {
					if ($fld->name == $key['column_name'] AND $key['primary_key'] == 't')
						$fld->primary_key = true;
					if ($fld->name == $key['column_name'] AND $key['unique_key'] == 't')
						$fld->unique = true; 				}
			}

			if ($ADODB_FETCH_MODE == ADODB_FETCH_NUM) $retarr[] = $fld;
			else $retarr[($normalize) ? strtoupper($fld->name) : $fld->name] = $fld;

			$rs->MoveNext();
		}
		$rs->Close();
		if (empty($retarr))
			return  $false;
		else
			return $retarr;

	}

	function Param($name,$type='C')
	{
		if ($name) {
			$this->_pnum += 1;
		} else {
						$this->_pnum = 1;
		}
		return '$'.$this->_pnum;
	}

	function MetaIndexes ($table, $primary = FALSE, $owner = false)
	{
		global $ADODB_FETCH_MODE;

		$schema = false;
		$this->_findschema($table,$schema);

		if ($schema) { 			$sql = '
				SELECT c.relname as "Name", i.indisunique as "Unique", i.indkey as "Columns"
				FROM pg_catalog.pg_class c
				JOIN pg_catalog.pg_index i ON i.indexrelid=c.oid
				JOIN pg_catalog.pg_class c2 ON c2.oid=i.indrelid
					,pg_namespace n
				WHERE (c2.relname=\'%s\' or c2.relname=lower(\'%s\'))
				and c.relnamespace=c2.relnamespace
				and c.relnamespace=n.oid
				and n.nspname=\'%s\'';
		} else {
			$sql = '
				SELECT c.relname as "Name", i.indisunique as "Unique", i.indkey as "Columns"
				FROM pg_catalog.pg_class c
				JOIN pg_catalog.pg_index i ON i.indexrelid=c.oid
				JOIN pg_catalog.pg_class c2 ON c2.oid=i.indrelid
				WHERE (c2.relname=\'%s\' or c2.relname=lower(\'%s\'))';
		}

		if ($primary == FALSE) {
			$sql .= ' AND i.indisprimary=false;';
		}

		$save = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		if ($this->fetchMode !== FALSE) {
			$savem = $this->SetFetchMode(FALSE);
		}

		$rs = $this->Execute(sprintf($sql,$table,$table,$schema));
		if (isset($savem)) {
			$this->SetFetchMode($savem);
		}
		$ADODB_FETCH_MODE = $save;

		if (!is_object($rs)) {
			$false = false;
			return $false;
		}

		$col_names = $this->MetaColumnNames($table,true,true);
						$indexes = array();
		while ($row = $rs->FetchRow()) {
			$columns = array();
			foreach (explode(' ', $row[2]) as $col) {
				$columns[] = $col_names[$col];
			}

			$indexes[$row[0]] = array(
				'unique' => ($row[1] == 't'),
				'columns' => $columns
			);
		}
		return $indexes;
	}

						function _connect($str,$user='',$pwd='',$db='',$ctype=0)
	{
		if (!function_exists('pg_connect')) return null;

		$this->_errorMsg = false;

		if ($user || $pwd || $db) {
			$user = adodb_addslashes($user);
			$pwd = adodb_addslashes($pwd);
			if (strlen($db) == 0) $db = 'template1';
			$db = adodb_addslashes($db);
			if ($str)  {
				$host = explode(":", $str);
				if ($host[0]) $str = "host=".adodb_addslashes($host[0]);
				else $str = '';
				if (isset($host[1])) $str .= " port=$host[1]";
				else if (!empty($this->port)) $str .= " port=".$this->port;
			}
			if ($user) $str .= " user=".$user;
			if ($pwd)  $str .= " password=".$pwd;
			if ($db)   $str .= " dbname=".$db;
		}

		
		if ($ctype === 1) { 			$this->_connectionID = pg_pconnect($str);
		} else {
			if ($ctype === -1) { 				static $ncnt;

				if (empty($ncnt)) $ncnt = 1;
				else $ncnt += 1;

				$str .= str_repeat(' ',$ncnt);
			}
			$this->_connectionID = pg_connect($str);
		}
		if ($this->_connectionID === false) return false;
		$this->Execute("set datestyle='ISO'");

		$info = $this->ServerInfo();
		$this->pgVersion = (float) substr($info['version'],0,3);
		if ($this->pgVersion >= 7.1) { 			$this->_nestedSQL = true;
		}

										if ( !empty($this->connection->noBlobs) && version_compare($info['version'], '9.0', '>=')) {
			$this->Execute('set bytea_output=escape');
		}

		return true;
	}

	function _nconnect($argHostname, $argUsername, $argPassword, $argDatabaseName)
	{
		return $this->_connect($argHostname, $argUsername, $argPassword, $argDatabaseName,-1);
	}

						function _pconnect($str,$user='',$pwd='',$db='')
	{
		return $this->_connect($str,$user,$pwd,$db,1);
	}


		function _query($sql,$inputarr=false)
	{
		$this->_pnum = 0;
		$this->_errorMsg = false;
		if ($inputarr) {
		
			$plan = 'P'.md5($sql);

			$execp = '';
			foreach($inputarr as $v) {
				if ($execp) $execp .= ',';
				if (is_string($v)) {
					if (strncmp($v,"'",1) !== 0) $execp .= $this->qstr($v);
				} else {
					$execp .= $v;
				}
			}

			if ($execp) $exsql = "EXECUTE $plan ($execp)";
			else $exsql = "EXECUTE $plan";


			$rez = @pg_execute($this->_connectionID,$exsql);
			if (!$rez) {
							$params = '';
				foreach($inputarr as $v) {
					if ($params) $params .= ',';
					if (is_string($v)) {
						$params .= 'VARCHAR';
					} else if (is_integer($v)) {
						$params .= 'INTEGER';
					} else {
						$params .= "REAL";
					}
				}
				$sqlarr = explode('?',$sql);
								$sql = '';
				$i = 1;
				foreach($sqlarr as $v) {
					$sql .= $v.' $'.$i;
					$i++;
				}
				$s = "PREPARE $plan ($params) AS ".substr($sql,0,strlen($sql)-2);
								$rez = pg_execute($this->_connectionID,$s);
							}
			if ($rez)
				$rez = pg_execute($this->_connectionID,$exsql);
		} else {
						$rez = pg_query($this->_connectionID,$sql);
		}
				if ($rez && pg_num_fields($rez) <= 0) {
			if (is_resource($this->_resultid) && get_resource_type($this->_resultid) === 'pgsql result') {
				pg_free_result($this->_resultid);
			}
			$this->_resultid = $rez;
			return true;
		}

		return $rez;
	}

	function _errconnect()
	{
		if (defined('DB_ERROR_CONNECT_FAILED')) return DB_ERROR_CONNECT_FAILED;
		else return 'Database connection failed';
	}

	
	function ErrorMsg()
	{
		if ($this->_errorMsg !== false) return $this->_errorMsg;
		if (ADODB_PHPVER >= 0x4300) {
			if (!empty($this->_resultid)) {
				$this->_errorMsg = @pg_result_error($this->_resultid);
				if ($this->_errorMsg) return $this->_errorMsg;
			}

			if (!empty($this->_connectionID)) {
				$this->_errorMsg = @pg_last_error($this->_connectionID);
			} else $this->_errorMsg = $this->_errconnect();
		} else {
			if (empty($this->_connectionID)) $this->_errconnect();
			else $this->_errorMsg = @pg_errormessage($this->_connectionID);
		}
		return $this->_errorMsg;
	}

	function ErrorNo()
	{
		$e = $this->ErrorMsg();
		if (strlen($e)) {
			return ADOConnection::MetaError($e);
		}
		return 0;
	}

		function _close()
	{
		if ($this->transCnt) $this->RollbackTrans();
		if ($this->_resultid) {
			@pg_free_result($this->_resultid);
			$this->_resultid = false;
		}
		@pg_close($this->_connectionID);
		$this->_connectionID = false;
		return true;
	}


	
	function CharMax()
	{
		return 1000000000;  	}

	
	function TextMax()
	{
		return 1000000000; 	}


}



class ADORecordSet_postgres64 extends ADORecordSet{
	var $_blobArr;
	var $databaseType = "postgres64";
	var $canSeek = true;

	function __construct($queryID, $mode=false)
	{
		if ($mode === false) {
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;
		}
		switch ($mode)
		{
		case ADODB_FETCH_NUM: $this->fetchMode = PGSQL_NUM; break;
		case ADODB_FETCH_ASSOC:$this->fetchMode = PGSQL_ASSOC; break;

		case ADODB_FETCH_DEFAULT:
		case ADODB_FETCH_BOTH:
		default: $this->fetchMode = PGSQL_BOTH; break;
		}
		$this->adodbFetchMode = $mode;

				parent::__construct($queryID);
	}

	function GetRowAssoc($upper = ADODB_ASSOC_CASE)
	{
		if ($this->fetchMode == PGSQL_ASSOC && $upper == ADODB_ASSOC_CASE_LOWER) {
			return $this->fields;
		}
		$row = ADORecordSet::GetRowAssoc($upper);
		return $row;
	}


	function _initrs()
	{
	global $ADODB_COUNTRECS;
		$qid = $this->_queryID;
		$this->_numOfRows = ($ADODB_COUNTRECS)? @pg_num_rows($qid):-1;
		$this->_numOfFields = @pg_num_fields($qid);

						if (empty($this->connection->noBlobs))
		for ($i=0, $max = $this->_numOfFields; $i < $max; $i++) {
			if (pg_field_type($qid,$i) == 'bytea') {
				$this->_blobArr[$i] = pg_field_name($qid,$i);
			}
		}
	}

		
	function Fields($colname)
	{
		if ($this->fetchMode != PGSQL_NUM) return @$this->fields[$colname];

		if (!$this->bind) {
			$this->bind = array();
			for ($i=0; $i < $this->_numOfFields; $i++) {
				$o = $this->FetchField($i);
				$this->bind[strtoupper($o->name)] = $i;
			}
		}
		return $this->fields[$this->bind[strtoupper($colname)]];
	}

	function FetchField($off = 0)
	{
		
		$o= new ADOFieldObject();
		$o->name = @pg_field_name($this->_queryID,$off);
		$o->type = @pg_field_type($this->_queryID,$off);
		$o->max_length = @pg_fieldsize($this->_queryID,$off);
		return $o;
	}

	function _seek($row)
	{
		return @pg_fetch_row($this->_queryID,$row);
	}

	function _decode($blob)
	{
		if ($blob === NULL) return NULL;
		return pg_unescape_bytea($blob);
	}

	function _fixblobs()
	{
		if ($this->fetchMode == PGSQL_NUM || $this->fetchMode == PGSQL_BOTH) {
			foreach($this->_blobArr as $k => $v) {
				$this->fields[$k] = ADORecordSet_postgres64::_decode($this->fields[$k]);
			}
		}
		if ($this->fetchMode == PGSQL_ASSOC || $this->fetchMode == PGSQL_BOTH) {
			foreach($this->_blobArr as $k => $v) {
				$this->fields[$v] = ADORecordSet_postgres64::_decode($this->fields[$v]);
			}
		}
	}

		function MoveNext()
	{
		if (!$this->EOF) {
			$this->_currentRow++;
			if ($this->_numOfRows < 0 || $this->_numOfRows > $this->_currentRow) {
				$this->fields = @pg_fetch_array($this->_queryID,$this->_currentRow,$this->fetchMode);
				if (is_array($this->fields) && $this->fields) {
					if (isset($this->_blobArr)) $this->_fixblobs();
					return true;
				}
			}
			$this->fields = false;
			$this->EOF = true;
		}
		return false;
	}

	function _fetch()
	{

		if ($this->_currentRow >= $this->_numOfRows && $this->_numOfRows >= 0)
			return false;

		$this->fields = @pg_fetch_array($this->_queryID,$this->_currentRow,$this->fetchMode);

		if ($this->fields && isset($this->_blobArr)) $this->_fixblobs();

		return (is_array($this->fields));
	}

	function _close()
	{
		return @pg_free_result($this->_queryID);
	}

	function MetaType($t,$len=-1,$fieldobj=false)
	{
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
		}
		switch (strtoupper($t)) {
				case 'MONEY': 				case 'INTERVAL':
				case 'CHAR':
				case 'CHARACTER':
				case 'VARCHAR':
				case 'NAME':
				case 'BPCHAR':
				case '_VARCHAR':
				case 'INET':
				case 'MACADDR':
					if ($len <= $this->blobSize) return 'C';

				case 'TEXT':
					return 'X';

				case 'IMAGE': 				case 'BLOB': 				case 'BIT':					case 'VARBIT':
				case 'BYTEA':
					return 'B';

				case 'BOOL':
				case 'BOOLEAN':
					return 'L';

				case 'DATE':
					return 'D';


				case 'TIMESTAMP WITHOUT TIME ZONE':
				case 'TIME':
				case 'DATETIME':
				case 'TIMESTAMP':
				case 'TIMESTAMPTZ':
					return 'T';

				case 'SMALLINT':
				case 'BIGINT':
				case 'INTEGER':
				case 'INT8':
				case 'INT4':
				case 'INT2':
					if (isset($fieldobj) &&
				empty($fieldobj->primary_key) && (!$this->connection->uniqueIisR || empty($fieldobj->unique))) return 'I';

				case 'OID':
				case 'SERIAL':
					return 'R';

				default:
					return 'N';
			}
	}

}
