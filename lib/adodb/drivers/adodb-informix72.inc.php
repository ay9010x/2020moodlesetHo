<?php


if (!defined('ADODB_DIR')) die();

if (!defined('IFX_SCROLL')) define('IFX_SCROLL',1);

class ADODB_informix72 extends ADOConnection {
	var $databaseType = "informix72";
	var $dataProvider = "informix";
	var $replaceQuote = "''"; 	var $fmtDate = "'Y-m-d'";
	var $fmtTimeStamp = "'Y-m-d H:i:s'";
	var $hasInsertID = true;
	var $hasAffectedRows = true;
    var $substr = 'substr';
	var $metaTablesSQL="select tabname,tabtype from systables where tabtype in ('T','V') and owner!='informix'"; 

	var $metaColumnsSQL =
		"select c.colname, c.coltype, c.collength, d.default,c.colno
		from syscolumns c, systables t,outer sysdefaults d
		where c.tabid=t.tabid and d.tabid=t.tabid and d.colno=c.colno
		and tabname='%s' order by c.colno";

	var $metaPrimaryKeySQL =
		"select part1,part2,part3,part4,part5,part6,part7,part8 from
		systables t,sysconstraints s,sysindexes i where t.tabname='%s'
		and s.tabid=t.tabid and s.constrtype='P'
		and i.idxname=s.idxname";

	var $concat_operator = '||';

	var $lastQuery = false;
	var $has_insertid = true;

	var $_autocommit = true;
	var $_bindInputArray = true;  	var $sysDate = 'TODAY';
	var $sysTimeStamp = 'CURRENT';
	var $cursorType = IFX_SCROLL; 
	function __construct()
	{
				
				putenv('GL_DATE=%Y-%m-%d');

		if (function_exists('ifx_byteasvarchar')) {
			ifx_byteasvarchar(1);         	ifx_textasvarchar(1);         	ifx_blobinfile_mode(0); 		}
	}

	function ServerInfo()
	{
	    if (isset($this->version)) return $this->version;

	    $arr['description'] = $this->GetOne("select DBINFO('version','full') from systables where tabid = 1");
	    $arr['version'] = $this->GetOne("select DBINFO('version','major') || DBINFO('version','minor') from systables where tabid = 1");
	    $this->version = $arr;
	    return $arr;
	}



	function _insertid()
	{
		$sqlca =ifx_getsqlca($this->lastQuery);
		return @$sqlca["sqlerrd1"];
	}

	function _affectedrows()
	{
		if ($this->lastQuery) {
		   return @ifx_affected_rows ($this->lastQuery);
		}
		return 0;
	}

	function BeginTrans()
	{
		if ($this->transOff) return true;
		$this->transCnt += 1;
		$this->Execute('BEGIN');
		$this->_autocommit = false;
		return true;
	}

	function CommitTrans($ok=true)
	{
		if (!$ok) return $this->RollbackTrans();
		if ($this->transOff) return true;
		if ($this->transCnt) $this->transCnt -= 1;
		$this->Execute('COMMIT');
		$this->_autocommit = true;
		return true;
	}

	function RollbackTrans()
	{
		if ($this->transOff) return true;
		if ($this->transCnt) $this->transCnt -= 1;
		$this->Execute('ROLLBACK');
		$this->_autocommit = true;
		return true;
	}

	function RowLock($tables,$where,$col='1 as adodbignore')
	{
		if ($this->_autocommit) $this->BeginTrans();
		return $this->GetOne("select $col from $tables where $where for update");
	}

	

	function ErrorMsg()
	{
		if (!empty($this->_logsql)) return $this->_errorMsg;
		$this->_errorMsg = ifx_errormsg();
		return $this->_errorMsg;
	}

	function ErrorNo()
	{
		preg_match("/.*SQLCODE=([^\]]*)/",ifx_error(),$parse);
		if (is_array($parse) && isset($parse[1])) return (int)$parse[1];
		return 0;
	}


	function MetaProcedures($NamePattern = false, $catalog  = null, $schemaPattern  = null)
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
           $likepattern = " WHERE procname LIKE '".$NamePattern."'";
        }

        $rs = $this->Execute('SELECT procname, isproc FROM sysprocedures'.$likepattern);

        if (is_object($rs)) {
            
            while ($row = $rs->FetchRow()) {
                $procedures[$row[0]] = array(
                        'type' => ($row[1] == 'f' ? 'FUNCTION' : 'PROCEDURE'),
                        'catalog' => '',
                        'schema' => '',
                        'remarks' => ''
                    );
            }
	    }

                if (isset($savem)) {
                $this->SetFetchMode($savem);
        }
        $ADODB_FETCH_MODE = $save;

        return $procedures;
    }

    function MetaColumns($table, $normalize=true)
	{
	global $ADODB_FETCH_MODE;

		$false = false;
		if (!empty($this->metaColumnsSQL)) {
			$save = $ADODB_FETCH_MODE;
			$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
			if ($this->fetchMode !== false) $savem = $this->SetFetchMode(false);
          		$rs = $this->Execute(sprintf($this->metaColumnsSQL,$table));
			if (isset($savem)) $this->SetFetchMode($savem);
			$ADODB_FETCH_MODE = $save;
			if ($rs === false) return $false;
			$rspkey = $this->Execute(sprintf($this->metaPrimaryKeySQL,$table)); 
			$retarr = array();
			while (!$rs->EOF) { 				$fld = new ADOFieldObject();
				$fld->name = $rs->fields[0];

				$pr=ifx_props($rs->fields[1],$rs->fields[2]); 				$fld->type = $pr[0] ;				$fld->primary_key=$rspkey->fields && array_search($rs->fields[4],$rspkey->fields);
				$fld->max_length = $pr[1]; 				$fld->precision = $pr[2] ;				$fld->not_null = $pr[3]=="N"; 
				if (trim($rs->fields[3]) != "AAAAAA 0") {
	                    		$fld->has_default = 1;
	                    		$fld->default_value = $rs->fields[3];
				} else {
					$fld->has_default = 0;
				}

                $retarr[strtolower($fld->name)] = $fld;
				$rs->MoveNext();
			}

			$rs->Close();
			$rspkey->Close(); 			return $retarr;
		}

		return $false;
	}

   function xMetaColumns($table)
   {
		return ADOConnection::MetaColumns($table,false);
   }

	 function MetaForeignKeys($table, $owner=false, $upper=false) 	{
		$sql = "
			select tr.tabname,updrule,delrule,
			i.part1 o1,i2.part1 d1,i.part2 o2,i2.part2 d2,i.part3 o3,i2.part3 d3,i.part4 o4,i2.part4 d4,
			i.part5 o5,i2.part5 d5,i.part6 o6,i2.part6 d6,i.part7 o7,i2.part7 d7,i.part8 o8,i2.part8 d8
			from systables t,sysconstraints s,sysindexes i,
			sysreferences r,systables tr,sysconstraints s2,sysindexes i2
			where t.tabname='$table'
			and s.tabid=t.tabid and s.constrtype='R' and r.constrid=s.constrid
			and i.idxname=s.idxname and tr.tabid=r.ptabid
			and s2.constrid=r.primary and i2.idxname=s2.idxname";

		$rs = $this->Execute($sql);
		if (!$rs || $rs->EOF)  return false;
		$arr = $rs->GetArray();
		$a = array();
		foreach($arr as $v) {
			$coldest=$this->metaColumnNames($v["tabname"]);
			$colorig=$this->metaColumnNames($table);
			$colnames=array();
			for($i=1;$i<=8 && $v["o$i"] ;$i++) {
				$colnames[]=$coldest[$v["d$i"]-1]."=".$colorig[$v["o$i"]-1];
			}
			if($upper)
				$a[strtoupper($v["tabname"])] =  $colnames;
			else
				$a[$v["tabname"]] =  $colnames;
		}
		return $a;
	 }

   function UpdateBlob($table, $column, $val, $where, $blobtype = 'BLOB')
   {
   		$type = ($blobtype == 'TEXT') ? 1 : 0;
		$blobid = ifx_create_blob($type,0,$val);
		return $this->Execute("UPDATE $table SET $column=(?) WHERE $where",array($blobid));
   }

   function BlobDecode($blobid)
   {
   		return function_exists('ifx_byteasvarchar') ? $blobid : @ifx_get_blob($blobid);
   }

	   function _connect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
		if (!function_exists('ifx_connect')) return null;

		$dbs = $argDatabasename . "@" . $argHostname;
		if ($argHostname) putenv("INFORMIXSERVER=$argHostname");
		putenv("INFORMIXSERVER=".trim($argHostname));
		$this->_connectionID = ifx_connect($dbs,$argUsername,$argPassword);
		if ($this->_connectionID === false) return false;
				return true;
	}

	   function _pconnect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
		if (!function_exists('ifx_connect')) return null;

		$dbs = $argDatabasename . "@" . $argHostname;
		putenv("INFORMIXSERVER=".trim($argHostname));
		$this->_connectionID = ifx_pconnect($dbs,$argUsername,$argPassword);
		if ($this->_connectionID === false) return false;
				return true;
	}

		function _query($sql,$inputarr=false)
	{
	global $ADODB_COUNTRECS;

	  	  if ($inputarr) {
		 foreach($inputarr as $v) {
			if (gettype($v) == 'string') {
			   $tab[] = ifx_create_char($v);
			}
			else {
			   $tab[] = $v;
			}
		 }
	  }

	  	  	  if (!$ADODB_COUNTRECS && preg_match("/^\s*select/is", $sql)) {
		 if ($inputarr) {
			$this->lastQuery = ifx_query($sql,$this->_connectionID, $this->cursorType, $tab);
		 }
		 else {
			$this->lastQuery = ifx_query($sql,$this->_connectionID, $this->cursorType);
		 }
	  }
	  else {
		 if ($inputarr) {
			$this->lastQuery = ifx_query($sql,$this->_connectionID, $tab);
		 }
		 else {
			$this->lastQuery = ifx_query($sql,$this->_connectionID);
		 }
	  }

	  	  
	  
		return $this->lastQuery;
	}

		function _close()
	{
		$this->lastQuery = false;
		if($this->_connectionID) {
			return ifx_close($this->_connectionID);
		}
		return true;
	}
}




class ADORecordset_informix72 extends ADORecordSet {

	var $databaseType = "informix72";
	var $canSeek = true;
	var $_fieldprops = false;

	function __construct($id,$mode=false)
	{
		if ($mode === false) {
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;
		}
		$this->fetchMode = $mode;
		return parent::__construct($id);
	}



	
	function FetchField($fieldOffset = -1)
	{
		if (empty($this->_fieldprops)) {
			$fp = ifx_fieldproperties($this->_queryID);
			foreach($fp as $k => $v) {
				$o = new ADOFieldObject;
				$o->name = $k;
				$arr = explode(';',$v); 				$o->type = $arr[0];
				$o->max_length = $arr[1];
				$this->_fieldprops[] = $o;
				$o->not_null = $arr[4]=="N";
			}
		}
		$ret = $this->_fieldprops[$fieldOffset];
		return $ret;
	}

	function _initrs()
	{
		$this->_numOfRows = -1; 		$this->_numOfFields = ifx_num_fields($this->_queryID);
	}

	function _seek($row)
	{
		return @ifx_fetch_row($this->_queryID, (int) $row);
	}

   function MoveLast()
   {
	  $this->fields = @ifx_fetch_row($this->_queryID, "LAST");
	  if ($this->fields) $this->EOF = false;
	  $this->_currentRow = -1;

	  if ($this->fetchMode == ADODB_FETCH_NUM) {
		 foreach($this->fields as $v) {
			$arr[] = $v;
		 }
		 $this->fields = $arr;
	  }

	  return true;
   }

   function MoveFirst()
	{
	  $this->fields = @ifx_fetch_row($this->_queryID, "FIRST");
	  if ($this->fields) $this->EOF = false;
	  $this->_currentRow = 0;

	  if ($this->fetchMode == ADODB_FETCH_NUM) {
		 foreach($this->fields as $v) {
			$arr[] = $v;
		 }
		 $this->fields = $arr;
	  }

	  return true;
   }

   function _fetch($ignore_fields=false)
   {

		$this->fields = @ifx_fetch_row($this->_queryID);

		if (!is_array($this->fields)) return false;

		if ($this->fetchMode == ADODB_FETCH_NUM) {
			foreach($this->fields as $v) {
				$arr[] = $v;
			}
			$this->fields = $arr;
		}
		return true;
	}

	
	function _close()
	{
		if($this->_queryID) {
			return ifx_free_result($this->_queryID);
		}
		return true;
	}

}

function ifx_props($coltype,$collength){
	$itype=fmod($coltype+1,256);
	$nullable=floor(($coltype+1) /256) ?"N":"Y";
	$mtype=substr(" CIIFFNNDN TBXCC     ",$itype,1);
	switch ($itype){
		case 2:
			$length=4;
		case 6:
		case 9:
		case 14:
			$length=floor($collength/256);
			$precision=fmod($collength,256);
			break;
		default:
			$precision=0;
			$length=$collength;
	}
	return array($mtype,$length,$precision,$nullable);
}
