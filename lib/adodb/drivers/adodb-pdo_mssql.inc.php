<?php




class ADODB_pdo_mssql extends ADODB_pdo {

	var $hasTop = 'top';
	var $sysDate = 'convert(datetime,convert(char,GetDate(),102),102)';
	var $sysTimeStamp = 'GetDate()';


	function _init($parentDriver)
	{

		$parentDriver->hasTransactions = false; 		$parentDriver->_bindInputArray = false;
		$parentDriver->hasInsertID = true;
	}

	function ServerInfo()
	{
		return ADOConnection::ServerInfo();
	}

	function SelectLimit($sql,$nrows=-1,$offset=-1,$inputarr=false,$secs2cache=0)
	{
		$ret = ADOConnection::SelectLimit($sql,$nrows,$offset,$inputarr,$secs2cache);
		return $ret;
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

	function MetaTables($ttype=false,$showSchema=false,$mask=false)
	{
		return false;
	}

	function MetaColumns($table,$normalize=true)
	{
		return false;
	}

}
