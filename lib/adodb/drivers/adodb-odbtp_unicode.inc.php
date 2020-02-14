<?php



if (!defined('ADODB_DIR')) die();



if (!defined('_ADODB_ODBTP_LAYER')) {
	include(ADODB_DIR."/drivers/adodb-odbtp.inc.php");
}

class ADODB_odbtp_unicode extends ADODB_odbtp {
	var $databaseType = 'odbtp';
	var $_useUnicodeSQL = true;
}
