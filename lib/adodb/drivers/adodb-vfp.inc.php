<?php


if (!defined('ADODB_DIR')) die();

if (!defined('_ADODB_ODBC_LAYER')) {
	include(ADODB_DIR."/drivers/adodb-odbc.inc.php");
}
if (!defined('ADODB_VFP')){
define('ADODB_VFP',1);
class ADODB_vfp extends ADODB_odbc {
	var $databaseType = "vfp";
	var $fmtDate = "{^Y-m-d}";
	var $fmtTimeStamp = "{^Y-m-d, h:i:sA}";
	var $replaceQuote = "'+chr(39)+'" ;
	var $true = '.T.';
	var $false = '.F.';
	var $hasTop = 'top';			var $_bindInputArray = false; 	var $sysTimeStamp = 'datetime()';
	var $sysDate = 'date()';
	var $ansiOuter = true;
	var $hasTransactions = false;
	var $curmode = false ; 
	function Time()
	{
		return time();
	}

	function BeginTrans() { return false;}

		function qstr($s,$nofixquotes=false)
	{
		if (!$nofixquotes) return  "'".str_replace("\r\n","'+chr(13)+'",str_replace("'",$this->replaceQuote,$s))."'";
		return "'".$s."'";
	}


		function SelectLimit($sql,$nrows=-1,$offset=-1, $inputarr=false,$secs2cache=0)
	{
		$this->hasTop = preg_match('/ORDER[ \t\r\n]+BY/is',$sql) ? 'top' : false;
		$ret = ADOConnection::SelectLimit($sql,$nrows,$offset,$inputarr,$secs2cache);
		return $ret;
	}



};


class  ADORecordSet_vfp extends ADORecordSet_odbc {

	var $databaseType = "vfp";


	function __construct($id,$mode=false)
	{
		return parent::__construct($id,$mode);
	}

	function MetaType($t, $len = -1, $fieldobj = false)
	{
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
		}
		switch (strtoupper($t)) {
		case 'C':
			if ($len <= $this->blobSize) return 'C';
		case 'M':
			return 'X';

		case 'D': return 'D';

		case 'T': return 'T';

		case 'L': return 'L';

		case 'I': return 'I';

		default: return 'N';
		}
	}
}

} 