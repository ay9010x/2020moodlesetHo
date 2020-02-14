<?php



if (!defined('ADODB_DIR')) die();

class ADODB2_sqlite extends ADODB_DataDict {
	var $databaseType = 'sqlite';
	var $seqField = false;
	var $addCol=' ADD COLUMN';
	var $dropTable = 'DROP TABLE IF EXISTS %s';
	var $dropIndex = 'DROP INDEX IF EXISTS %s';
	var $renameTable = 'ALTER TABLE %s RENAME TO %s';



	function ActualType($meta)
	{
		switch(strtoupper($meta)) {
		case 'C': return 'VARCHAR'; 		case 'XL':return 'LONGTEXT'; 		case 'X': return 'TEXT'; 
		case 'C2': return 'VARCHAR'; 		case 'X2': return 'LONGTEXT'; 
		case 'B': return 'LONGBLOB'; 
		case 'D': return 'DATE'; 		case 'T': return 'DATETIME'; 		case 'L': return 'TINYINT'; 
		case 'R':
		case 'I4':
		case 'I': return 'INTEGER'; 		case 'I1': return 'TINYINT'; 		case 'I2': return 'SMALLINT'; 		case 'I8': return 'BIGINT'; 
		case 'F': return 'DOUBLE'; 		case 'N': return 'NUMERIC'; 		default:
			return $meta;
		}
	}

		function _CreateSuffix($fname,$ftype,$fnotnull,$fdefault,$fautoinc,$fconstraint,$funsigned)
	{
		$suffix = '';
		if ($funsigned) $suffix .= ' UNSIGNED';
		if ($fnotnull) $suffix .= ' NOT NULL';
		if (strlen($fdefault)) $suffix .= " DEFAULT $fdefault";
		if ($fautoinc) $suffix .= ' AUTOINCREMENT';
		if ($fconstraint) $suffix .= ' '.$fconstraint;
		return $suffix;
	}

	function AlterColumnSQL($tabname, $flds, $tableflds='', $tableoptions='')
	{
		if ($this->debug) ADOConnection::outp("AlterColumnSQL not supported natively by SQLite");
		return array();
	}

	function DropColumnSQL($tabname, $flds, $tableflds='', $tableoptions='')
	{
		if ($this->debug) ADOConnection::outp("DropColumnSQL not supported natively by SQLite");
		return array();
	}

	function RenameColumnSQL($tabname,$oldcolumn,$newcolumn,$flds='')
	{
		if ($this->debug) ADOConnection::outp("RenameColumnSQL not supported natively by SQLite");
		return array();
	}

}
