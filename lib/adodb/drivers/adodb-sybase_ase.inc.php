<?php



require_once ADODB_DIR."/drivers/adodb-sybase.inc.php";

class ADODB_sybase_ase extends ADODB_sybase {
 	var $databaseType = "sybase_ase";

	 var $metaTablesSQL="SELECT sysobjects.name FROM sysobjects, sysusers WHERE sysobjects.type='U' AND sysobjects.uid = sysusers.uid";
	 var $metaColumnsSQL = "SELECT syscolumns.name AS field_name, systypes.name AS type, systypes.length AS width FROM sysobjects, syscolumns, systypes WHERE sysobjects.name='%s' AND syscolumns.id = sysobjects.id AND systypes.type=syscolumns.type";
	 var $metaDatabasesSQL ="SELECT a.name FROM master.dbo.sysdatabases a, master.dbo.syslogins b WHERE a.suid = b.suid and a.name like '%' and a.name != 'tempdb' and a.status3 != 256  order by 1";

	function __construct()
	{
	}

		function MetaTables($ttype=false,$showSchema=false,$mask=false)
	{
		$false = false;
		if ($this->metaTablesSQL) {
			
			if ($ttype == 'VIEWS'){
						$sql = str_replace('U', 'V', $this->metaTablesSQL);
			}elseif (false === $ttype){
						$sql = str_replace('U',"U' OR type='V", $this->metaTablesSQL);
			}else{ 						$sql = $this->metaTablesSQL;
			}
			$rs = $this->Execute($sql);

			if ($rs === false || !method_exists($rs, 'GetArray')){
					return $false;
			}
			$arr = $rs->GetArray();

			$arr2 = array();
			foreach($arr as $key=>$value){
					$arr2[] = trim($value['name']);
			}
			return $arr2;
		}
		return $false;
	}

	function MetaDatabases()
	{
			$arr = array();
			if ($this->metaDatabasesSQL!='') {
				$rs = $this->Execute($this->metaDatabasesSQL);
				if ($rs && !$rs->EOF){
					while (!$rs->EOF){
						$arr[] = $rs->Fields('name');
						$rs->MoveNext();
					}
					return $arr;
				}
			}
			return false;
	}

		function MetaColumns($table,$upper=false)
	{
		$false = false;
		if (!empty($this->metaColumnsSQL)) {

			$rs = $this->Execute(sprintf($this->metaColumnsSQL,$table));
			if ($rs === false) return $false;

			$retarr = array();
			while (!$rs->EOF) {
				$fld = new ADOFieldObject();
				$fld->name = $rs->Fields('field_name');
				$fld->type = $rs->Fields('type');
				$fld->max_length = $rs->Fields('width');
				$retarr[strtoupper($fld->name)] = $fld;
				$rs->MoveNext();
			}
			$rs->Close();
			return $retarr;
		}
		return $false;
	}

	function getProcedureList($schema)
	{
			return false;
	}

	function ErrorMsg()
	{
		if (!function_exists('sybase_connect')){
				return 'Your PHP doesn\'t contain the Sybase connection module!';
		}
		return parent::ErrorMsg();
	}
}

class adorecordset_sybase_ase extends ADORecordset_sybase {
var $databaseType = "sybase_ase";
function __construct($id,$mode=false)
	{
		parent::__construct($id,$mode);
	}

}
