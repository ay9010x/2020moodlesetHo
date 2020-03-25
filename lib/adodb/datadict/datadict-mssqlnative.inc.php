<?php





if (!defined('ADODB_DIR')) die();

class ADODB2_mssqlnative extends ADODB_DataDict {
	var $databaseType = 'mssqlnative';
	var $dropIndex = 'DROP INDEX %1$s ON %2$s';
	var $renameTable = "EXEC sp_rename '%s','%s'";
	var $renameColumn = "EXEC sp_rename '%s.%s','%s'";
	var $typeX = 'TEXT';  	var $typeXL = 'TEXT';

	
	function MetaType($t,$len=-1,$fieldobj=false)
	{
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
		}

		$_typeConversion = array(
			-155 => 'D',
			  93 => 'D',
			-154 => 'D',
			  -2 => 'D',
			  91 => 'D',

			  12 => 'C',
			   1 => 'C',
			  -9 => 'C',
			  -8 => 'C',

			  -7 => 'L',
			  -6 => 'I2',
			  -5 => 'I8',
			 -11 => 'I',
			   4 => 'I',
			   5 => 'I4',

			  -1 => 'X',
			 -10 => 'X',

			   2 => 'N',
			   3 => 'N',
			   6 => 'N',
			   7 => 'N',

			-152 => 'X',
			-151 => 'X',
			  -4 => 'X',
			  -3 => 'X'
			);

		return $_typeConversion($t);

	}

	function ActualType($meta)
	{
		$DATE_TYPE = 'DATETIME';

		switch(strtoupper($meta)) {

		case 'C': return 'VARCHAR';
		case 'XL': return (isset($this)) ? $this->typeXL : 'TEXT';
		case 'X': return (isset($this)) ? $this->typeX : 'TEXT'; 		case 'C2': return 'NVARCHAR';
		case 'X2': return 'NTEXT';

		case 'B': return 'IMAGE';

		case 'D': return $DATE_TYPE;
		case 'T': return 'TIME';
		case 'L': return 'BIT';

		case 'R':
		case 'I': return 'INT';
		case 'I1': return 'TINYINT';
		case 'I2': return 'SMALLINT';
		case 'I4': return 'INT';
		case 'I8': return 'BIGINT';

		case 'F': return 'REAL';
		case 'N': return 'NUMERIC';
		default:
			print "RETURN $meta";
			return $meta;
		}
	}


	function AddColumnSQL($tabname, $flds)
	{
		$tabname = $this->TableName ($tabname);
		$f = array();
		list($lines,$pkey) = $this->_GenFields($flds);
		$s = "ALTER TABLE $tabname $this->addCol";
		foreach($lines as $v) {
			$f[] = "\n $v";
		}
		$s .= implode(', ',$f);
		$sql[] = $s;
		return $sql;
	}

	

	
	function DropColumnSQL($tabname, $flds, $tableflds='',$tableoptions='')
	{
		$tabname = $this->TableName ($tabname);
		if (!is_array($flds))
			$flds = explode(',',$flds);
		$f = array();
		$s = 'ALTER TABLE ' . $tabname . ' DROP COLUMN ';
		foreach($flds as $v) {
						$f[] = $this->NameQuote($v);
		}
		$s .= implode(', ',$f);
		$sql[] = $s;
		return $sql;
	}

		function _CreateSuffix($fname,&$ftype,$fnotnull,$fdefault,$fautoinc,$fconstraint,$funsigned)
	{
		$suffix = '';
		if (strlen($fdefault)) $suffix .= " DEFAULT $fdefault";
		if ($fautoinc) $suffix .= ' IDENTITY(1,1)';
		if ($fnotnull) $suffix .= ' NOT NULL';
		else if ($suffix == '') $suffix .= ' NULL';
		if ($fconstraint) $suffix .= ' '.$fconstraint;
		return $suffix;
	}

	

	
	function _IndexSQL($idxname, $tabname, $flds, $idxoptions)
	{
		$sql = array();

		if ( isset($idxoptions['REPLACE']) || isset($idxoptions['DROP']) ) {
			$sql[] = sprintf ($this->dropIndex, $idxname, $tabname);
			if ( isset($idxoptions['DROP']) )
				return $sql;
		}

		if ( empty ($flds) ) {
			return $sql;
		}

		$unique = isset($idxoptions['UNIQUE']) ? ' UNIQUE' : '';
		$clustered = isset($idxoptions['CLUSTERED']) ? ' CLUSTERED' : '';

		if ( is_array($flds) )
			$flds = implode(', ',$flds);
		$s = 'CREATE' . $unique . $clustered . ' INDEX ' . $idxname . ' ON ' . $tabname . ' (' . $flds . ')';

		if ( isset($idxoptions[$this->upperName]) )
			$s .= $idxoptions[$this->upperName];


		$sql[] = $s;

		return $sql;
	}


	function _GetSize($ftype, $ty, $fsize, $fprec)
	{
		switch ($ftype) {
		case 'INT':
		case 'SMALLINT':
		case 'TINYINT':
		case 'BIGINT':
			return $ftype;
		}
    	if ($ty == 'T') return $ftype;
    	return parent::_GetSize($ftype, $ty, $fsize, $fprec);

	}
}
