<?php


if (!defined('ADODB_DIR')) die();

define("_ADODB_ADO_LAYER", 1 );



class ADODB_ado extends ADOConnection {
	var $databaseType = "ado";
	var $_bindInputArray = false;
	var $fmtDate = "'Y-m-d'";
	var $fmtTimeStamp = "'Y-m-d, h:i:sA'";
	var $replaceQuote = "''"; 	var $dataProvider = "ado";
	var $hasAffectedRows = true;
	var $adoParameterType = 201; 	var $_affectedRows = false;
	var $_thisTransactions;
	var $_cursor_type = 3; 	var $_cursor_location = 3; 	var $_lock_type = -1;
	var $_execute_option = -1;
	var $poorAffectedRows = true;
	var $charPage;

	function __construct()
	{
		$this->_affectedRows = new VARIANT;
	}

	function ServerInfo()
	{
		if (!empty($this->_connectionID)) $desc = $this->_connectionID->provider;
		return array('description' => $desc, 'version' => '');
	}

	function _affectedrows()
	{
		if (PHP_VERSION >= 5) return $this->_affectedRows;

		return $this->_affectedRows->value;
	}

				function _connect($argHostname, $argUsername, $argPassword,$argDBorProvider, $argProvider= '')
	{
			

		 if ($argProvider) {
		 	$argDatabasename = $argDBorProvider;
		 } else {
		 	$argDatabasename = '';
		 	if ($argDBorProvider) $argProvider = $argDBorProvider;
			else if (stripos($argHostname,'PROVIDER') === false) 
				$argProvider = 'MSDASQL';
		}


		try {
		$u = 'UID';
		$p = 'PWD';

		if (!empty($this->charPage))
			$dbc = new COM('ADODB.Connection',null,$this->charPage);
		else
			$dbc = new COM('ADODB.Connection');

		if (! $dbc) return false;

		
		if ($argProvider=='mssql') {
			$u = 'User Id';  			$p = 'Password';
			$argProvider = "SQLOLEDB"; 
						
						if (!$argUsername) $argHostname .= ";Trusted_Connection=Yes";
		} else if ($argProvider=='access')
			$argProvider = "Microsoft.Jet.OLEDB.4.0"; 
		if ($argProvider) $dbc->Provider = $argProvider;

		if ($argProvider) $argHostname = "PROVIDER=$argProvider;DRIVER={SQL Server};SERVER=$argHostname";


		if ($argDatabasename) $argHostname .= ";DATABASE=$argDatabasename";
		if ($argUsername) $argHostname .= ";$u=$argUsername";
		if ($argPassword)$argHostname .= ";$p=$argPassword";

		if ($this->debug) ADOConnection::outp( "Host=".$argHostname."<BR>\n version=$dbc->version");
				@$dbc->Open((string) $argHostname);

		$this->_connectionID = $dbc;

		$dbc->CursorLocation = $this->_cursor_location;
		return  $dbc->State > 0;
		} catch (exception $e) {
			if ($this->debug) echo "<pre>",$argHostname,"\n",$e,"</pre>\n";
		}

		return false;
	}

		function _pconnect($argHostname, $argUsername, $argPassword, $argProvider='MSDASQL')
	{
		return $this->_connect($argHostname,$argUsername,$argPassword,$argProvider);
	}



	function MetaTables($ttype = false, $showSchema = false, $mask = false)
	{
		$arr= array();
		$dbc = $this->_connectionID;

		$adors=@$dbc->OpenSchema(20);		if ($adors){
			$f = $adors->Fields(2);			$t = $adors->Fields(3);			while (!$adors->EOF){
				$tt=substr($t->value,0,6);
				if ($tt!='SYSTEM' && $tt !='ACCESS')
					$arr[]=$f->value;
								$adors->MoveNext();
			}
			$adors->Close();
		}

		return $arr;
	}

	function MetaColumns($table, $normalize=true)
	{
		$table = strtoupper($table);
		$arr= array();
		$dbc = $this->_connectionID;

		$adors=@$dbc->OpenSchema(4);
		if ($adors){
			$t = $adors->Fields(2);			while (!$adors->EOF){


				if (strtoupper($t->Value) == $table) {

					$fld = new ADOFieldObject();
					$c = $adors->Fields(3);
					$fld->name = $c->Value;
					$fld->type = 'CHAR'; 					$fld->max_length = -1;
					$arr[strtoupper($fld->name)]=$fld;
				}

				$adors->MoveNext();
			}
			$adors->Close();
		}

		return $arr;
	}

	
	function _query($sql,$inputarr=false)
	{
		try { 
		$dbc = $this->_connectionID;

	
		$false = false;

		if ($inputarr) {

			if (!empty($this->charPage))
				$oCmd = new COM('ADODB.Command',null,$this->charPage);
			else
				$oCmd = new COM('ADODB.Command');
			$oCmd->ActiveConnection = $dbc;
			$oCmd->CommandText = $sql;
			$oCmd->CommandType = 1;

			while(list(, $val) = each($inputarr)) {
				$type = gettype($val);
				$len=strlen($val);
				if ($type == 'boolean')
					$this->adoParameterType = 11;
				else if ($type == 'integer')
					$this->adoParameterType = 3;
				else if ($type == 'double')
					$this->adoParameterType = 5;
				elseif ($type == 'string')
					$this->adoParameterType = 202;
				else if (($val === null) || (!defined($val)))
					$len=1;
				else
					$this->adoParameterType = 130;

				        		$p = $oCmd->CreateParameter('name',$this->adoParameterType,1,$len,$val);

				$oCmd->Parameters->Append($p);
			}

			$p = false;
			$rs = $oCmd->Execute();
			$e = $dbc->Errors;
			if ($dbc->Errors->Count > 0) return $false;
			return $rs;
		}

		$rs = @$dbc->Execute($sql,$this->_affectedRows, $this->_execute_option);

		if ($dbc->Errors->Count > 0) return $false;
		if (! $rs) return $false;

		if ($rs->State == 0) {
			$true = true;
			return $true; 		}
		return $rs;

		} catch (exception $e) {

		}
		return $false;
	}


	function BeginTrans()
	{
		if ($this->transOff) return true;

		if (isset($this->_thisTransactions))
			if (!$this->_thisTransactions) return false;
		else {
			$o = $this->_connectionID->Properties("Transaction DDL");
			$this->_thisTransactions = $o ? true : false;
			if (!$o) return false;
		}
		@$this->_connectionID->BeginTrans();
		$this->transCnt += 1;
		return true;
	}
	function CommitTrans($ok=true)
	{
		if (!$ok) return $this->RollbackTrans();
		if ($this->transOff) return true;

		@$this->_connectionID->CommitTrans();
		if ($this->transCnt) @$this->transCnt -= 1;
		return true;
	}
	function RollbackTrans() {
		if ($this->transOff) return true;
		@$this->_connectionID->RollbackTrans();
		if ($this->transCnt) @$this->transCnt -= 1;
		return true;
	}

	

	function ErrorMsg()
	{
		if (!$this->_connectionID) return "No connection established";
		$errmsg = '';

		try {
			$errc = $this->_connectionID->Errors;
			if (!$errc) return "No Errors object found";
			if ($errc->Count == 0) return '';
			$err = $errc->Item($errc->Count-1);
			$errmsg = $err->Description;
		}catch(exception $e) {
		}
		return $errmsg;
	}

	function ErrorNo()
	{
		$errc = $this->_connectionID->Errors;
		if ($errc->Count == 0) return 0;
		$err = $errc->Item($errc->Count-1);
		return $err->NativeError;
	}

		function _close()
	{
		if ($this->_connectionID) $this->_connectionID->Close();
		$this->_connectionID = false;
		return true;
	}


}



class ADORecordSet_ado extends ADORecordSet {

	var $bind = false;
	var $databaseType = "ado";
	var $dataProvider = "ado";
	var $_tarr = false; 	var $_flds; 	var $canSeek = true;
  	var $hideErrors = true;

	function __construct($id,$mode=false)
	{
		if ($mode === false) {
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;
		}
		$this->fetchMode = $mode;
		return parent::__construct($id,$mode);
	}


		function FetchField($fieldOffset = -1) {
		$off=$fieldOffset+1; 
		$o= new ADOFieldObject();
		$rs = $this->_queryID;
		if (!$rs) return false;

		$f = $rs->Fields($fieldOffset);
		$o->name = $f->Name;
		$t = $f->Type;
		$o->type = $this->MetaType($t);
		$o->max_length = $f->DefinedSize;
		$o->ado_type = $t;


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
		$rs = $this->_queryID;

		try {
			$this->_numOfRows = $rs->RecordCount;
		} catch (Exception $e) {
			$this->_numOfRows = -1;
		}
		$f = $rs->Fields;
		$this->_numOfFields = $f->Count;
	}


	 	function _seek($row)
	{
	   $rs = $this->_queryID;
								if ($this->_currentRow > $row) return false;
		@$rs->Move((integer)$row - $this->_currentRow-1); 		return true;
	}


	function MetaType($t,$len=-1,$fieldobj=false)
	{
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
		}

		if (!is_numeric($t)) return $t;

		switch ($t) {
		case 0:
		case 12: 		case 8: 		case 129: 		case 130: 		case 200: 		case 202:		case 128: 		case 204: 		case 72: 			if ($len <= $this->blobSize) return 'C';

		case 201:
		case 203:
			return 'X';
		case 128:
		case 204:
		case 205:
			 return 'B';
		case 7:
		case 133: return 'D';

		case 134:
		case 135: return 'T';

		case 11: return 'L';

		case 16:		case 2:		case 3:		case 4:		case 17:		case 18:		case 19:		case 20:			return 'I';
		default: return 'N';
		}
	}

		function _fetch()
	{
		$rs = $this->_queryID;
		if (!$rs or $rs->EOF) {
			$this->fields = false;
			return false;
		}
		$this->fields = array();

		if (!$this->_tarr) {
			$tarr = array();
			$flds = array();
			for ($i=0,$max = $this->_numOfFields; $i < $max; $i++) {
				$f = $rs->Fields($i);
				$flds[] = $f;
				$tarr[] = $f->Type;
			}
						$this->_tarr = $tarr;
			$this->_flds = $flds;
		}
		$t = reset($this->_tarr);
		$f = reset($this->_flds);

		if ($this->hideErrors)  $olde = error_reporting(E_ERROR|E_CORE_ERROR);		for ($i=0,$max = $this->_numOfFields; $i < $max; $i++) {
						switch($t) {
			case 135: 				if (!strlen((string)$f->value)) $this->fields[] = false;
				else {
					if (!is_numeric($f->value)) 												$val= (float) variant_cast($f->value,VT_R8)*3600*24-2209161600;
					else
						$val = $f->value;
					$this->fields[] = adodb_date('Y-m-d H:i:s',$val);
				}
				break;
			case 133:				if ($val = $f->value) {
					$this->fields[] = substr($val,0,4).'-'.substr($val,4,2).'-'.substr($val,6,2);
				} else
					$this->fields[] = false;
				break;
			case 7: 				if (!strlen((string)$f->value)) $this->fields[] = false;
				else {
					if (!is_numeric($f->value)) $val = variant_date_to_timestamp($f->value);
					else $val = $f->value;

					if (($val % 86400) == 0) $this->fields[] = adodb_date('Y-m-d',$val);
					else $this->fields[] = adodb_date('Y-m-d H:i:s',$val);
				}
				break;
			case 1: 				$this->fields[] = false;
				break;
			case 20:
			case 21:     			$this->fields[] = (float) $f->value;     			break;
			case 6: 				ADOConnection::outp( '<b>'.$f->Name.': currency type not supported by PHP</b>');
				$this->fields[] = (float) $f->value;
				break;
			case 11: 				$val = "";
				if(is_bool($f->value))	{
					if($f->value==true) $val = 1;
					else $val = 0;
				}
				if(is_null($f->value)) $val = null;

				$this->fields[] = $val;
				break;
			default:
				$this->fields[] = $f->value;
				break;
			}
						$f = next($this->_flds);
			$t = next($this->_tarr);
		} 		if ($this->hideErrors) error_reporting($olde);
		@$rs->MoveNext(); 
		if ($this->fetchMode & ADODB_FETCH_ASSOC) {
			$this->fields = $this->GetRowAssoc();
		}
		return true;
	}

		function NextRecordSet()
		{
			$rs = $this->_queryID;
			$this->_queryID = $rs->NextRecordSet();
						if ($this->_queryID == null) return false;

			$this->_currentRow = -1;
			$this->_currentPage = -1;
			$this->bind = false;
			$this->fields = false;
			$this->_flds = false;
			$this->_tarr = false;

			$this->_inited = false;
			$this->Init();
			return true;
		}

	function _close() {
		$this->_flds = false;
		try {
		@$this->_queryID->Close();		} catch (Exception $e) {
		}
		$this->_queryID = false;
	}

}
