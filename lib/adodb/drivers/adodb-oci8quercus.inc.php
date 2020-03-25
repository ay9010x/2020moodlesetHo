<?php


if (!defined('ADODB_DIR')) die();

include_once(ADODB_DIR.'/drivers/adodb-oci8.inc.php');

class ADODB_oci8quercus extends ADODB_oci8 {
	var $databaseType = 'oci8quercus';
	var $dataProvider = 'oci8';

	function __construct()
	{
	}

}



class ADORecordset_oci8quercus extends ADORecordset_oci8 {

	var $databaseType = 'oci8quercus';

	function __construct($queryID,$mode=false)
	{
		parent::__construct($queryID,$mode);
	}

	function _FetchField($fieldOffset = -1)
	{
	global $QUERCUS;
		$fld = new ADOFieldObject;

		if (!empty($QUERCUS)) {
			$fld->name = oci_field_name($this->_queryID, $fieldOffset);
			$fld->type = oci_field_type($this->_queryID, $fieldOffset);
			$fld->max_length = oci_field_size($this->_queryID, $fieldOffset);

						switch($fld->type) {
				case 'string': $fld->type = 'VARCHAR'; break;
				case 'real': $fld->type = 'NUMBER'; break;
			}
		} else {
			$fieldOffset += 1;
			$fld->name = oci_field_name($this->_queryID, $fieldOffset);
			$fld->type = oci_field_type($this->_queryID, $fieldOffset);
			$fld->max_length = oci_field_size($this->_queryID, $fieldOffset);
		}
	 	switch($fld->type) {
		case 'NUMBER':
	 		$p = oci_field_precision($this->_queryID, $fieldOffset);
			$sc = oci_field_scale($this->_queryID, $fieldOffset);
			if ($p != 0 && $sc == 0) $fld->type = 'INT';
			$fld->scale = $p;
			break;

	 	case 'CLOB':
		case 'NCLOB':
		case 'BLOB':
			$fld->max_length = -1;
			break;
		}

		return $fld;
	}

}
