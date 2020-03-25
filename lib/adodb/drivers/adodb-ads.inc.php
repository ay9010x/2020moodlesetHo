<?php

if (!defined('ADODB_DIR')) die();

  define("_ADODB_ADS_LAYER", 2 );




class ADODB_ads extends ADOConnection {
  var $databaseType = "ads";
  var $fmt = "'m-d-Y'";
  var $fmtTimeStamp = "'Y-m-d H:i:s'";
        var $concat_operator = '';
  var $replaceQuote = "''";   var $dataProvider = "ads";
  var $hasAffectedRows = true;
  var $binmode = ODBC_BINMODE_RETURN;
  var $useFetchArray = false;                             var $_bindInputArray = false;
  var $curmode = SQL_CUR_USE_DRIVER;   var $_genSeqSQL = "create table %s (id integer)";
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

    if (!function_exists('ads_connect')) return null;

    if ($this->debug && $argDatabasename && $this->databaseType != 'vfp') {
      ADOConnection::outp("For Advantage Connect(), $argDatabasename is not used. Place dsn in 1st parameter.");
    }
    if (isset($php_errormsg)) $php_errormsg = '';
    if ($this->curmode === false) $this->_connectionID = ads_connect($argDSN,$argUsername,$argPassword);
    else $this->_connectionID = ads_connect($argDSN,$argUsername,$argPassword,$this->curmode);
    $this->_errorMsg = isset($php_errormsg) ? $php_errormsg : '';
    if (isset($this->connectStmt)) $this->Execute($this->connectStmt);

    return $this->_connectionID != false;
  }

    function _pconnect($argDSN, $argUsername, $argPassword, $argDatabasename)
  {
  global $php_errormsg;

    if (!function_exists('ads_connect')) return null;

    if (isset($php_errormsg)) $php_errormsg = '';
    $this->_errorMsg = isset($php_errormsg) ? $php_errormsg : '';
    if ($this->debug && $argDatabasename) {
            ADOConnection::outp("For PConnect(), $argDatabasename is not used. Place dsn in 1st parameter.");
    }
      if ($this->curmode === false) $this->_connectionID = ads_connect($argDSN,$argUsername,$argPassword);
    else $this->_connectionID = ads_pconnect($argDSN,$argUsername,$argPassword,$this->curmode);

    $this->_errorMsg = isset($php_errormsg) ? $php_errormsg : '';
    if ($this->_connectionID && $this->autoRollback) @ads_rollback($this->_connectionID);
    if (isset($this->connectStmt)) $this->Execute($this->connectStmt);

    return $this->_connectionID != false;
  }

    function ServerInfo()
  {

    if (!empty($this->host) && ADODB_PHPVER >= 0x4300) {
      $stmt = $this->Prepare('EXECUTE PROCEDURE sp_mgGetInstallInfo()');
                        $res =  $this->Execute($stmt);
                        if(!$res)
                                print $this->ErrorMsg();
                        else{
                                $ret["version"]= $res->fields[3];
                                $ret["description"]="Advantage Database Server";
                                return $ret;
                        }
                }
                else {
            return ADOConnection::ServerInfo();
    }
  }


                function CreateSequence($seqname = 'adodbseq', $start = 1)
  {
                $res =  $this->Execute("CREATE TABLE $seqname ( ID autoinc( 1 ) ) IN DATABASE");
                if(!$res){
                        print $this->ErrorMsg();
                        return false;
                }
                else
                        return true;

        }

                function DropSequence($seqname = 'adodbseq')
  {
                $res = $this->Execute("DROP TABLE $seqname");
                if(!$res){
                        print $this->ErrorMsg();
                        return false;
                }
                else
                        return true;
        }


                          function GenID($seqname = 'adodbseq', $start = 1)
        {
                $go = $this->Execute("select * from $seqname");
                if (!$go){
                        $res = $this->Execute("CREATE TABLE $seqname ( ID autoinc( 1 ) ) IN DATABASE");
                        if(!res){
                                print $this->ErrorMsg();
                                return false;
                        }
                }
                $res = $this->Execute("INSERT INTO $seqname VALUES( DEFAULT )");
                if(!$res){
                        print $this->ErrorMsg();
                        return false;
                }
                else{
                        $gen = $this->Execute("SELECT LastAutoInc( STATEMENT ) FROM system.iota");
                        $ret = $gen->fields[0];
                        return $ret;
                }

        }




  function ErrorMsg()
  {
    if ($this->_haserrorfunctions) {
      if ($this->_errorMsg !== false) return $this->_errorMsg;
      if (empty($this->_connectionID)) return @ads_errormsg();
      return @ads_errormsg($this->_connectionID);
    } else return ADOConnection::ErrorMsg();
  }


  function ErrorNo()
  {

                if ($this->_haserrorfunctions) {
      if ($this->_errorCode !== false) {
                return (strlen($this->_errorCode)<=2) ? 0 : $this->_errorCode;
      }

      if (empty($this->_connectionID)) $e = @ads_error();
      else $e = @ads_error($this->_connectionID);

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
    return ads_autocommit($this->_connectionID,false);
  }

  function CommitTrans($ok=true)
  {
    if ($this->transOff) return true;
    if (!$ok) return $this->RollbackTrans();
    if ($this->transCnt) $this->transCnt -= 1;
    $this->_autocommit = true;
    $ret = ads_commit($this->_connectionID);
    ads_autocommit($this->_connectionID,true);
    return $ret;
  }

  function RollbackTrans()
  {
    if ($this->transOff) return true;
    if ($this->transCnt) $this->transCnt -= 1;
    $this->_autocommit = true;
    $ret = ads_rollback($this->_connectionID);
    ads_autocommit($this->_connectionID,true);
    return $ret;
  }


            function &MetaTables($ttype = false, $showSchema = false, $mask = false)
  {
          $recordSet1 = $this->Execute("select * from system.tables");
                if(!$recordSet1){
                        print $this->ErrorMsg();
                        return false;
                }
                $recordSet2 = $this->Execute("select * from system.views");
                if(!$recordSet2){
                        print $this->ErrorMsg();
                        return false;
                }
                $i=0;
                while (!$recordSet1->EOF){
                                 $arr["$i"] = $recordSet1->fields[0];
                                 $recordSet1->MoveNext();
                                 $i=$i+1;
                }
                if($ttype=='FALSE'){
                        while (!$recordSet2->EOF){
                                $arr["$i"] = $recordSet2->fields[0];
                                $recordSet2->MoveNext();
                                $i=$i+1;
                        }
                        return $arr;
                }
                elseif($ttype=='VIEWS'){
                        while (!$recordSet2->EOF){
                                $arrV["$i"] = $recordSet2->fields[0];
                                $recordSet2->MoveNext();
                                $i=$i+1;
                        }
                        return $arrV;
                }
                else{
                        return $arr;
                }

  }

        function &MetaPrimaryKeys($table, $owner = false)
  {
          $recordSet = $this->Execute("select table_primary_key from system.tables where name='$table'");
                if(!$recordSet){
                        print $this->ErrorMsg();
                        return false;
                }
                $i=0;
                while (!$recordSet->EOF){
                                 $arr["$i"] = $recordSet->fields[0];
                                 $recordSet->MoveNext();
                                 $i=$i+1;
                }
                return $arr;
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
    case -1:       return 'X';
    case -4:       return 'B';

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

    case -11:       return 'R';
    case -7:       return 'L';

    default:
      return 'N';
    }
  }

  function &MetaColumns($table, $normalize = true)
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
      $qid = ads_columns($this->_connectionID);      break;


    case 'db2':
            $colname = "%";
            $qid = ads_columns($this->_connectionID, "", $schema, $table, $colname);
            break;

    default:
      $qid = @ads_columns($this->_connectionID,'%','%',strtoupper($table),'%');
      if (empty($qid)) $qid = ads_columns($this->_connectionID);
      break;
    }
    if (empty($qid)) return $false;

    $rs = new ADORecordSet_ads($qid);
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
          else if ($rs->fields[4] <= -95)             $fld->max_length = $rs->fields[7]/2;
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

                function &MetaColumnNames($table, $numIndexes = false, $useattnum = false)
        {
                $recordSet = $this->Execute("select name from system.columns where parent='$table'");
                if(!$recordSet){
                        print $this->ErrorMsg();
                        return false;
                }
                else{
                        $i=0;
                        while (!$recordSet->EOF){
                                $arr["FIELD$i"] = $recordSet->fields[0];
                                $recordSet->MoveNext();
                                $i=$i+1;
                        }
                        return $arr;
                }
        }


  function Prepare($sql)
  {
    if (! $this->_bindInputArray) return $sql;     $stmt = ads_prepare($this->_connectionID,$sql);
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
        $stmtid = ads_prepare($this->_connectionID,$sql);

        if ($stmtid == false) {
          $this->_errorMsg = isset($php_errormsg) ? $php_errormsg : '';
          return false;
        }
      }

      if (! ads_execute($stmtid,$inputarr)) {
                if ($this->_haserrorfunctions) {
          $this->_errorMsg = ads_errormsg();
          $this->_errorCode = ads_error();
        }
        return false;
      }

    } else if (is_array($sql)) {
      $stmtid = $sql[1];
      if (!ads_execute($stmtid)) {
                if ($this->_haserrorfunctions) {
          $this->_errorMsg = ads_errormsg();
          $this->_errorCode = ads_error();
        }
        return false;
      }
    } else
                        {

      $stmtid = ads_exec($this->_connectionID,$sql);

                        }

                $this->_lastAffectedRows = 0;

    if ($stmtid)
                {

      if (@ads_num_fields($stmtid) == 0) {
        $this->_lastAffectedRows = ads_num_rows($stmtid);
        $stmtid = true;

      } else {

        $this->_lastAffectedRows = 0;
        ads_binmode($stmtid,$this->binmode);
        ads_longreadlen($stmtid,$this->maxblobsize);

      }

      if ($this->_haserrorfunctions)
                        {

        $this->_errorMsg = '';
        $this->_errorCode = 0;
      }
                        else
        $this->_errorMsg = isset($php_errormsg) ? $php_errormsg : '';
    }
                else
                {
      if ($this->_haserrorfunctions) {
        $this->_errorMsg = ads_errormsg();
        $this->_errorCode = ads_error();
      } else
        $this->_errorMsg = isset($php_errormsg) ? $php_errormsg : '';
    }

    return $stmtid;

  }

  
  function UpdateBlob($table,$column,$val,$where,$blobtype='BLOB')
  {
                $sql = "UPDATE $table SET $column=? WHERE $where";
                $stmtid = ads_prepare($this->_connectionID,$sql);
                if ($stmtid == false){
                  $this->_errorMsg = isset($php_errormsg) ? $php_errormsg : '';
                  return false;
          }
                if (! ads_execute($stmtid,array($val),array(SQL_BINARY) )){
                        if ($this->_haserrorfunctions){
                                $this->_errorMsg = ads_errormsg();
                    $this->_errorCode = ads_error();
            }
                        return false;
           }
                 return TRUE;
        }

    function _close()
  {
    $ret = @ads_close($this->_connectionID);
    $this->_connectionID = false;
    return $ret;
  }

  function _affectedrows()
  {
    return $this->_lastAffectedRows;
  }

}



class ADORecordSet_ads extends ADORecordSet {

  var $bind = false;
  var $databaseType = "ads";
  var $dataProvider = "ads";
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


    function &FetchField($fieldOffset = -1)
  {

    $off=$fieldOffset+1; 
    $o= new ADOFieldObject();
    $o->name = @ads_field_name($this->_queryID,$off);
    $o->type = @ads_field_type($this->_queryID,$off);
    $o->max_length = @ads_field_len($this->_queryID,$off);
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
    $this->_numOfRows = ($ADODB_COUNTRECS) ? @ads_num_rows($this->_queryID) : -1;
    $this->_numOfFields = @ads_num_fields($this->_queryID);
        if ($this->_numOfRows == 0) $this->_numOfRows = -1;
        $this->_has_stupid_odbc_fetch_api_change = ADODB_PHPVER >= 0x4200;
  }

  function _seek($row)
  {
    return false;
  }

    function &GetArrayLimit($nrows,$offset=-1)
  {
    if ($offset <= 0) {
      $rs =& $this->GetArray($nrows);
      return $rs;
    }
    $savem = $this->fetchMode;
    $this->fetchMode = ADODB_FETCH_NUM;
    $this->Move($offset);
    $this->fetchMode = $savem;

    if ($this->fetchMode & ADODB_FETCH_ASSOC) {
      $this->fields =& $this->GetRowAssoc();
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
      $rez = @ads_fetch_into($this->_queryID,$this->fields);
    else {
      $row = 0;
      $rez = @ads_fetch_into($this->_queryID,$row,$this->fields);
    }
    if ($rez) {
      if ($this->fetchMode & ADODB_FETCH_ASSOC) {
        $this->fields =& $this->GetRowAssoc();
      }
      return true;
    }
    return false;
  }

  function _close()
  {
    return @ads_free_result($this->_queryID);
  }

}
