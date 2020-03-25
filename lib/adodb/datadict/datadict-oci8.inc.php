<?php



if (!defined('ADODB_DIR')) die();

class ADODB2_oci8 extends ADODB_DataDict {

	var $databaseType = 'oci8';
	var $seqField = false;
	var $seqPrefix = 'SEQ_';
	var $dropTable = "DROP TABLE %s CASCADE CONSTRAINTS";
	var $trigPrefix = 'TRIG_';
	var $alterCol = ' MODIFY ';
	var $typeX = 'VARCHAR(4000)';
	var $typeXL = 'CLOB';

	function MetaType($t, $len=-1, $fieldobj=false)
	{
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
		}
		switch (strtoupper($t)) {
	 	case 'VARCHAR':
	 	case 'VARCHAR2':
		case 'CHAR':
		case 'VARBINARY':
		case 'BINARY':
			if (isset($this) && $len <= $this->blobSize) return 'C';
			return 'X';

		case 'NCHAR':
		case 'NVARCHAR2':
		case 'NVARCHAR':
			if (isset($this) && $len <= $this->blobSize) return 'C2';
			return 'X2';

		case 'NCLOB':
		case 'CLOB':
			return 'XL';

		case 'LONG RAW':
		case 'LONG VARBINARY':
		case 'BLOB':
			return 'B';

		case 'TIMESTAMP':
			return 'TS';

		case 'DATE':
			return 'T';

		case 'INT':
		case 'SMALLINT':
		case 'INTEGER':
			return 'I';

		default:
			return 'N';
		}
	}

 	function ActualType($meta)
	{
		switch($meta) {
		case 'C': return 'VARCHAR';
		case 'X': return $this->typeX;
		case 'XL': return $this->typeXL;

		case 'C2': return 'NVARCHAR2';
		case 'X2': return 'NVARCHAR2(4000)';

		case 'B': return 'BLOB';

		case 'TS':
				return 'TIMESTAMP';

		case 'D':
		case 'T': return 'DATE';
		case 'L': return 'NUMBER(1)';
		case 'I1': return 'NUMBER(3)';
		case 'I2': return 'NUMBER(5)';
		case 'I':
		case 'I4': return 'NUMBER(10)';

		case 'I8': return 'NUMBER(20)';
		case 'F': return 'NUMBER';
		case 'N': return 'NUMBER';
		case 'R': return 'NUMBER(20)';
		default:
			return $meta;
		}
	}

	function CreateDatabase($dbname, $options=false)
	{
		$options = $this->_Options($options);
		$password = isset($options['PASSWORD']) ? $options['PASSWORD'] : 'tiger';
		$tablespace = isset($options["TABLESPACE"]) ? " DEFAULT TABLESPACE ".$options["TABLESPACE"] : '';
		$sql[] = "CREATE USER ".$dbname." IDENTIFIED BY ".$password.$tablespace;
		$sql[] = "GRANT CREATE SESSION, CREATE TABLE,UNLIMITED TABLESPACE,CREATE SEQUENCE TO $dbname";

		return $sql;
	}

	function AddColumnSQL($tabname, $flds)
	{
		$tabname = $this->TableName($tabname);
		$f = array();
		list($lines,$pkey) = $this->_GenFields($flds);
		$s = "ALTER TABLE $tabname ADD (";
		foreach($lines as $v) {
			$f[] = "\n $v";
		}

		$s .= implode(', ',$f).')';
		$sql[] = $s;
		return $sql;
	}

	function AlterColumnSQL($tabname, $flds, $tableflds='', $tableoptions='')
	{
		$tabname = $this->TableName($tabname);
		$f = array();
		list($lines,$pkey) = $this->_GenFields($flds);
		$s = "ALTER TABLE $tabname MODIFY(";
		foreach($lines as $v) {
			$f[] = "\n $v";
		}
		$s .= implode(', ',$f).')';
		$sql[] = $s;
		return $sql;
	}

	function DropColumnSQL($tabname, $flds, $tableflds='', $tableoptions='')
	{
		if (!is_array($flds)) $flds = explode(',',$flds);
		foreach ($flds as $k => $v) $flds[$k] = $this->NameQuote($v);

		$sql = array();
		$s = "ALTER TABLE $tabname DROP(";
		$s .= implode(', ',$flds).') CASCADE CONSTRAINTS';
		$sql[] = $s;
		return $sql;
	}

	function _DropAutoIncrement($t)
	{
		if (strpos($t,'.') !== false) {
			$tarr = explode('.',$t);
			return "drop sequence ".$tarr[0].".seq_".$tarr[1];
		}
		return "drop sequence seq_".$t;
	}

		function _CreateSuffix($fname,&$ftype,$fnotnull,$fdefault,$fautoinc,$fconstraint,$funsigned)
	{
		$suffix = '';

		if ($fdefault == "''" && $fnotnull) {			$fnotnull = false;
			if ($this->debug) ADOConnection::outp("NOT NULL and DEFAULT='' illegal in Oracle");
		}

		if (strlen($fdefault)) $suffix .= " DEFAULT $fdefault";
		if ($fnotnull) $suffix .= ' NOT NULL';

		if ($fautoinc) $this->seqField = $fname;
		if ($fconstraint) $suffix .= ' '.$fconstraint;

		return $suffix;
	}


	function _Triggers($tabname,$tableoptions)
	{
		if (!$this->seqField) return array();

		if ($this->schema) {
			$t = strpos($tabname,'.');
			if ($t !== false) $tab = substr($tabname,$t+1);
			else $tab = $tabname;
			$seqname = $this->schema.'.'.$this->seqPrefix.$tab;
			$trigname = $this->schema.'.'.$this->trigPrefix.$this->seqPrefix.$tab;
		} else {
			$seqname = $this->seqPrefix.$tabname;
			$trigname = $this->trigPrefix.$seqname;
		}

		if (strlen($seqname) > 30) {
			$seqname = $this->seqPrefix.uniqid('');
		} 		if (strlen($trigname) > 30) {
			$trigname = $this->trigPrefix.uniqid('');
		} 
		if (isset($tableoptions['REPLACE'])) $sql[] = "DROP SEQUENCE $seqname";
		$seqCache = '';
		if (isset($tableoptions['SEQUENCE_CACHE'])){$seqCache = $tableoptions['SEQUENCE_CACHE'];}
		$seqIncr = '';
		if (isset($tableoptions['SEQUENCE_INCREMENT'])){$seqIncr = ' INCREMENT BY '.$tableoptions['SEQUENCE_INCREMENT'];}
		$seqStart = '';
		if (isset($tableoptions['SEQUENCE_START'])){$seqIncr = ' START WITH '.$tableoptions['SEQUENCE_START'];}
		$sql[] = "CREATE SEQUENCE $seqname $seqStart $seqIncr $seqCache";
		$sql[] = "CREATE OR REPLACE TRIGGER $trigname BEFORE insert ON $tabname FOR EACH ROW WHEN (NEW.$this->seqField IS NULL OR NEW.$this->seqField = 0) BEGIN select $seqname.nextval into :new.$this->seqField from dual; END;";

		$this->seqField = false;
		return $sql;
	}

	



	function _IndexSQL($idxname, $tabname, $flds,$idxoptions)
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

		if (isset($idxoptions['BITMAP'])) {
			$unique = ' BITMAP';
		} elseif (isset($idxoptions['UNIQUE'])) {
			$unique = ' UNIQUE';
		} else {
			$unique = '';
		}

		if ( is_array($flds) )
			$flds = implode(', ',$flds);
		$s = 'CREATE' . $unique . ' INDEX ' . $idxname . ' ON ' . $tabname . ' (' . $flds . ')';

		if ( isset($idxoptions[$this->upperName]) )
			$s .= $idxoptions[$this->upperName];

		if (isset($idxoptions['oci8']))
			$s .= $idxoptions['oci8'];


		$sql[] = $s;

		return $sql;
	}

	function GetCommentSQL($table,$col)
	{
		$table = $this->connection->qstr($table);
		$col = $this->connection->qstr($col);
		return "select comments from USER_COL_COMMENTS where TABLE_NAME=$table and COLUMN_NAME=$col";
	}

	function SetCommentSQL($table,$col,$cmt)
	{
		$cmt = $this->connection->qstr($cmt);
		return  "COMMENT ON COLUMN $table.$col IS $cmt";
	}
}
