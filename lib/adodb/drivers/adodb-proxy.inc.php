<?php


if (!defined('ADODB_DIR')) die();

if (! defined("_ADODB_PROXY_LAYER")) {
	 define("_ADODB_PROXY_LAYER", 1 );
	 include(ADODB_DIR."/drivers/adodb-csv.inc.php");

	class ADODB_proxy extends ADODB_csv {
		var $databaseType = 'proxy';
		var $databaseProvider = 'csv';
	}
	class ADORecordset_proxy extends ADORecordset_csv {
	var $databaseType = "proxy";

		function __construct($id,$mode=false)
		{
			parent::__construct($id,$mode);
		}
	};
} 