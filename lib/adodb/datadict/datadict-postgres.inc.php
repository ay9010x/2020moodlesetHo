<?php



if (!defined('ADODB_DIR')) die();

class ADODB2_postgres extends ADODB_DataDict {

	var $databaseType = 'postgres';
	var $seqField = false;
	var $seqPrefix = 'SEQ_';
	var $addCol = ' ADD COLUMN';
	var $quote = '"';
	var $renameTable = 'ALTER TABLE %s RENAME TO %s'; 	var $dropTable = 'DROP TABLE %s CASCADE';

	function MetaType($t,$len=-1,$fieldobj=false)
	{
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
		}
		$is_serial = is_object($fieldobj) && !empty($fieldobj->primary_key) && !empty($fieldobj->unique) &&
			!empty($fieldobj->has_default) && substr($fieldobj->default_value,0,8) == 'nextval(';

		switch (strtoupper($t)) {
			case 'INTERVAL':
			case 'CHAR':
			case 'CHARACTER':
			case 'VARCHAR':
			case 'NAME':
	   		case 'BPCHAR':
				if ($len <= $this->blobSize) return 'C';

			case 'TEXT':
				return 'X';

			case 'IMAGE': 			case 'BLOB': 			case 'BIT':				case 'VARBIT':
			case 'BYTEA':
				return 'B';

			case 'BOOL':
			case 'BOOLEAN':
				return 'L';

			case 'DATE':
				return 'D';

			case 'TIME':
			case 'DATETIME':
			case 'TIMESTAMP':
			case 'TIMESTAMPTZ':
				return 'T';

			case 'INTEGER': return !$is_serial ? 'I' : 'R';
			case 'SMALLINT':
			case 'INT2': return !$is_serial ? 'I2' : 'R';
			case 'INT4': return !$is_serial ? 'I4' : 'R';
			case 'BIGINT':
			case 'INT8': return !$is_serial ? 'I8' : 'R';

			case 'OID':
			case 'SERIAL':
				return 'R';

			case 'FLOAT4':
			case 'FLOAT8':
			case 'DOUBLE PRECISION':
			case 'REAL':
				return 'F';

			 default:
			 	return 'N';
		}
	}

 	function ActualType($meta)
	{
		switch($meta) {
		case 'C': return 'VARCHAR';
		case 'XL':
		case 'X': return 'TEXT';

		case 'C2': return 'VARCHAR';
		case 'X2': return 'TEXT';

		case 'B': return 'BYTEA';

		case 'D': return 'DATE';
		case 'TS':
		case 'T': return 'TIMESTAMP';

		case 'L': return 'BOOLEAN';
		case 'I': return 'INTEGER';
		case 'I1': return 'SMALLINT';
		case 'I2': return 'INT2';
		case 'I4': return 'INT4';
		case 'I8': return 'INT8';

		case 'F': return 'FLOAT8';
		case 'N': return 'NUMERIC';
		default:
			return $meta;
		}
	}

	
	function AddColumnSQL($tabname, $flds)
	{
		$tabname = $this->TableName ($tabname);
		$sql = array();
		$not_null = false;
		list($lines,$pkey) = $this->_GenFields($flds);
		$alter = 'ALTER TABLE ' . $tabname . $this->addCol . ' ';
		foreach($lines as $v) {
			if (($not_null = preg_match('/NOT NULL/i',$v))) {
				$v = preg_replace('/NOT NULL/i','',$v);
			}
			if (preg_match('/^([^ ]+) .*DEFAULT (\'[^\']+\'|\"[^\"]+\"|[^ ]+)/',$v,$matches)) {
				list(,$colname,$default) = $matches;
				$sql[] = $alter . str_replace('DEFAULT '.$default,'',$v);
				$sql[] = 'UPDATE '.$tabname.' SET '.$colname.'='.$default;
				$sql[] = 'ALTER TABLE '.$tabname.' ALTER COLUMN '.$colname.' SET DEFAULT ' . $default;
			} else {
				$sql[] = $alter . $v;
			}
			if ($not_null) {
				list($colname) = explode(' ',$v);
				$sql[] = 'ALTER TABLE '.$tabname.' ALTER COLUMN '.$colname.' SET NOT NULL';
			}
		}
		return $sql;
	}


	function DropIndexSQL ($idxname, $tabname = NULL)
	{
	   return array(sprintf($this->dropIndex, $this->TableName($idxname), $this->TableName($tabname)));
	}

	
	 

	function AlterColumnSQL($tabname, $flds, $tableflds='',$tableoptions='')
	{
				$has_alter_column = 8.0 <= (float) @$this->serverInfo['version'];

		if ($has_alter_column) {
			$tabname = $this->TableName($tabname);
			$sql = array();
			list($lines,$pkey) = $this->_GenFields($flds);
			$set_null = false;
			foreach($lines as $v) {
				$alter = 'ALTER TABLE ' . $tabname . $this->alterCol . ' ';
				if ($not_null = preg_match('/NOT NULL/i',$v)) {
					$v = preg_replace('/NOT NULL/i','',$v);
				}
				 				 				else if ($set_null = preg_match('/NULL/i',$v)) {
																									$v = preg_replace('/(?<!DEFAULT)\sNULL/i','',$v);
				}

				if (preg_match('/^([^ ]+) .*DEFAULT (\'[^\']+\'|\"[^\"]+\"|[^ ]+)/',$v,$matches)) {
					$existing = $this->MetaColumns($tabname);
					list(,$colname,$default) = $matches;
					$alter .= $colname;
					if ($this->connection) {
						$old_coltype = $this->connection->MetaType($existing[strtoupper($colname)]);
					}
					else {
						$old_coltype = $t;
					}
					$v = preg_replace('/^' . preg_quote($colname) . '\s/', '', $v);
					$t = trim(str_replace('DEFAULT '.$default,'',$v));

										if ( $old_coltype == 'L' && $t == 'INTEGER' ) {
						$sql[] = $alter . ' DROP DEFAULT';
						$sql[] = $alter . " TYPE $t USING ($colname::BOOL)::INT";
						$sql[] = $alter . " SET DEFAULT $default";
					}
										else if ( $old_coltype == 'I' && $t == 'BOOLEAN' ) {
						if( strcasecmp('NULL', trim($default)) != 0 ) {
							$default = $this->connection->qstr($default);
						}
						$sql[] = $alter . ' DROP DEFAULT';
						$sql[] = $alter . " TYPE $t USING CASE WHEN $colname = 0 THEN false ELSE true END";
						$sql[] = $alter . " SET DEFAULT $default";
					}
										else {
						$sql[] = $alter . " TYPE $t";
						$sql[] = $alter . " SET DEFAULT $default";
					}

				}
				else {
										preg_match ('/^\s*(\S+)\s+(.*)$/',$v,$matches);
					list (,$colname,$rest) = $matches;
					$alter .= $colname;
					$sql[] = $alter . ' TYPE ' . $rest;
				}

				if ($not_null) {
										$sql[] = $alter . ' SET NOT NULL';
				}
				if ($set_null) {
										$sql[] = $alter . ' DROP NOT NULL';
				}
			}
			return $sql;
		}

				if (!$tableflds) {
			if ($this->debug) ADOConnection::outp("AlterColumnSQL needs a complete table-definiton for PostgreSQL");
			return array();
		}
		return $this->_recreate_copy_table($tabname,False,$tableflds,$tableoptions);
	}

	
	function DropColumnSQL($tabname, $flds, $tableflds='',$tableoptions='')
	{
		$has_drop_column = 7.3 <= (float) @$this->serverInfo['version'];
		if (!$has_drop_column && !$tableflds) {
			if ($this->debug) ADOConnection::outp("DropColumnSQL needs complete table-definiton for PostgreSQL < 7.3");
		return array();
	}
		if ($has_drop_column) {
			return ADODB_DataDict::DropColumnSQL($tabname, $flds);
		}
		return $this->_recreate_copy_table($tabname,$flds,$tableflds,$tableoptions);
	}

	
	function _recreate_copy_table($tabname,$dropflds,$tableflds,$tableoptions='')
	{
		if ($dropflds && !is_array($dropflds)) $dropflds = explode(',',$dropflds);
		$copyflds = array();
		foreach($this->MetaColumns($tabname) as $fld) {
			if (!$dropflds || !in_array($fld->name,$dropflds)) {
								if (preg_match('/'.$fld->name.' (I|I2|I4|I8|N|F)/i',$tableflds,$matches) &&
					in_array($fld->type,array('varchar','char','text','bytea'))) {
					$copyflds[] = "to_number($fld->name,'S9999999999999D99')";
				} else {
					$copyflds[] = $fld->name;
				}
								if ($fld->primary_key && $fld->has_default &&
					preg_match("/nextval\('([^']+)'::text\)/",$fld->default_value,$matches)) {
					$seq_name = $matches[1];
					$seq_fld = $fld->name;
				}
			}
		}
		$copyflds = implode(', ',$copyflds);

		$tempname = $tabname.'_tmp';
		$aSql[] = 'BEGIN';				$aSql[] = "SELECT * INTO TEMPORARY TABLE $tempname FROM $tabname";
		$aSql = array_merge($aSql,$this->DropTableSQL($tabname));
		$aSql = array_merge($aSql,$this->CreateTableSQL($tabname,$tableflds,$tableoptions));
		$aSql[] = "INSERT INTO $tabname SELECT $copyflds FROM $tempname";
		if ($seq_name && $seq_fld) {				$seq_name = $tabname.'_'.$seq_fld.'_seq';				$aSql[] = "SELECT setval('$seq_name',MAX($seq_fld)) FROM $tabname";
		}
		$aSql[] = "DROP TABLE $tempname";
				foreach($this->MetaIndexes($tabname) as $idx_name => $idx_data)
		{
			if (substr($idx_name,-5) != '_pkey' && (!$dropflds || !count(array_intersect($dropflds,$idx_data['columns'])))) {
				$aSql = array_merge($aSql,$this->CreateIndexSQL($idx_name,$tabname,$idx_data['columns'],
					$idx_data['unique'] ? array('UNIQUE') : False));
			}
		}
		$aSql[] = 'COMMIT';
		return $aSql;
	}

	function DropTableSQL($tabname)
	{
		$sql = ADODB_DataDict::DropTableSQL($tabname);

		$drop_seq = $this->_DropAutoIncrement($tabname);
		if ($drop_seq) $sql[] = $drop_seq;

		return $sql;
	}

		function _CreateSuffix($fname, &$ftype, $fnotnull,$fdefault,$fautoinc,$fconstraint,$funsigned)
	{
		if ($fautoinc) {
			$ftype = 'SERIAL';
			return '';
		}
		$suffix = '';
		if (strlen($fdefault)) $suffix .= " DEFAULT $fdefault";
		if ($fnotnull) $suffix .= ' NOT NULL';
		if ($fconstraint) $suffix .= ' '.$fconstraint;
		return $suffix;
	}

				function _DropAutoIncrement($tabname)
	{
		$tabname = $this->connection->quote('%'.$tabname.'%');

		$seq = $this->connection->GetOne("SELECT relname FROM pg_class WHERE NOT relname ~ 'pg_.*' AND relname LIKE $tabname AND relkind='S'");

				if (!$seq || $this->connection->GetOne("SELECT relname FROM pg_class JOIN pg_depend ON pg_class.relfilenode=pg_depend.objid WHERE relname='$seq' AND relkind='S' AND deptype='i'")) {
			return False;
		}
		return "DROP SEQUENCE ".$seq;
	}

	function RenameTableSQL($tabname,$newname)
	{
		if (!empty($this->schema)) {
			$rename_from = $this->TableName($tabname);
			$schema_save = $this->schema;
			$this->schema = false;
			$rename_to = $this->TableName($newname);
			$this->schema = $schema_save;
			return array (sprintf($this->renameTable, $rename_from, $rename_to));
		}

		return array (sprintf($this->renameTable, $this->TableName($tabname),$this->TableName($newname)));
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

		$s = 'CREATE' . $unique . ' INDEX ' . $idxname . ' ON ' . $tabname . ' ';

		if (isset($idxoptions['HASH']))
			$s .= 'USING HASH ';

		if ( isset($idxoptions[$this->upperName]) )
			$s .= $idxoptions[$this->upperName];

		if ( is_array($flds) )
			$flds = implode(', ',$flds);
		$s .= '(' . $flds . ')';
		$sql[] = $s;

		return $sql;
	}

	function _GetSize($ftype, $ty, $fsize, $fprec)
	{
		if (strlen($fsize) && $ty != 'X' && $ty != 'B' && $ty  != 'I' && strpos($ftype,'(') === false) {
			$ftype .= "(".$fsize;
			if (strlen($fprec)) $ftype .= ",".$fprec;
			$ftype .= ')';
		}
		return $ftype;
	}
}
