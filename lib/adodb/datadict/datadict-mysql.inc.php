<?php



if (!defined('ADODB_DIR')) die();

class ADODB2_mysql extends ADODB_DataDict {
	var $databaseType = 'mysql';
	var $alterCol = ' MODIFY COLUMN';
	var $alterTableAddIndex = true;
	var $dropTable = 'DROP TABLE IF EXISTS %s'; 
	var $dropIndex = 'DROP INDEX %s ON %s';
	var $renameColumn = 'ALTER TABLE %s CHANGE COLUMN %s %s %s';	
	function MetaType($t,$len=-1,$fieldobj=false)
	{
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
		}
		$is_serial = is_object($fieldobj) && $fieldobj->primary_key && $fieldobj->auto_increment;

		$len = -1; 		switch (strtoupper($t)) {
		case 'STRING':
		case 'CHAR':
		case 'VARCHAR':
		case 'TINYBLOB':
		case 'TINYTEXT':
		case 'ENUM':
		case 'SET':
			if ($len <= $this->blobSize) return 'C';

		case 'TEXT':
		case 'LONGTEXT':
		case 'MEDIUMTEXT':
			return 'X';

						case 'IMAGE':
		case 'LONGBLOB':
		case 'BLOB':
		case 'MEDIUMBLOB':
			return !empty($fieldobj->binary) ? 'B' : 'X';

		case 'YEAR':
		case 'DATE': return 'D';

		case 'TIME':
		case 'DATETIME':
		case 'TIMESTAMP': return 'T';

		case 'FLOAT':
		case 'DOUBLE':
			return 'F';

		case 'INT':
		case 'INTEGER': return $is_serial ? 'R' : 'I';
		case 'TINYINT': return $is_serial ? 'R' : 'I1';
		case 'SMALLINT': return $is_serial ? 'R' : 'I2';
		case 'MEDIUMINT': return $is_serial ? 'R' : 'I4';
		case 'BIGINT':  return $is_serial ? 'R' : 'I8';
		default: return 'N';
		}
	}

	function ActualType($meta)
	{
		switch(strtoupper($meta)) {
		case 'C': return 'VARCHAR';
		case 'XL':return 'LONGTEXT';
		case 'X': return 'TEXT';

		case 'C2': return 'VARCHAR';
		case 'X2': return 'LONGTEXT';

		case 'B': return 'LONGBLOB';

		case 'D': return 'DATE';
		case 'TS':
		case 'T': return 'DATETIME';
		case 'L': return 'TINYINT';

		case 'R':
		case 'I4':
		case 'I': return 'INTEGER';
		case 'I1': return 'TINYINT';
		case 'I2': return 'SMALLINT';
		case 'I8': return 'BIGINT';

		case 'F': return 'DOUBLE';
		case 'N': return 'NUMERIC';
		default:
			return $meta;
		}
	}

		function _CreateSuffix($fname,&$ftype,$fnotnull,$fdefault,$fautoinc,$fconstraint,$funsigned)
	{
		$suffix = '';
		if ($funsigned) $suffix .= ' UNSIGNED';
		if ($fnotnull) $suffix .= ' NOT NULL';
		if (strlen($fdefault)) $suffix .= " DEFAULT $fdefault";
		if ($fautoinc) $suffix .= ' AUTO_INCREMENT';
		if ($fconstraint) $suffix .= ' '.$fconstraint;
		return $suffix;
	}

	

	

	function _IndexSQL($idxname, $tabname, $flds, $idxoptions)
	{
		$sql = array();

		if ( isset($idxoptions['REPLACE']) || isset($idxoptions['DROP']) ) {
			if ($this->alterTableAddIndex) $sql[] = "ALTER TABLE $tabname DROP INDEX $idxname";
			else $sql[] = sprintf($this->dropIndex, $idxname, $tabname);

			if ( isset($idxoptions['DROP']) )
				return $sql;
		}

		if ( empty ($flds) ) {
			return $sql;
		}

		if (isset($idxoptions['FULLTEXT'])) {
			$unique = ' FULLTEXT';
		} elseif (isset($idxoptions['UNIQUE'])) {
			$unique = ' UNIQUE';
		} else {
			$unique = '';
		}

		if ( is_array($flds) ) $flds = implode(', ',$flds);

		if ($this->alterTableAddIndex) $s = "ALTER TABLE $tabname ADD $unique INDEX $idxname ";
		else $s = 'CREATE' . $unique . ' INDEX ' . $idxname . ' ON ' . $tabname;

		$s .= ' (' . $flds . ')';

		if ( isset($idxoptions[$this->upperName]) )
			$s .= $idxoptions[$this->upperName];

		$sql[] = $s;

		return $sql;
	}
}
