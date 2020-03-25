<?php


if (!defined('ADODB_DIR')) die();

include_once(ADODB_DIR."/drivers/adodb-postgres64.inc.php");

class ADODB_postgres7 extends ADODB_postgres64 {
	var $databaseType = 'postgres7';
	var $hasLimit = true;		var $ansiOuter = true;
	var $charSet = true; 
		var $metaColumnsSQL = "
		SELECT
			a.attname,
			CASE
				WHEN x.sequence_name != ''
				THEN 'SERIAL'
				ELSE t.typname
			END AS typname,
			a.attlen, a.atttypmod, a.attnotnull, a.atthasdef, a.attnum
		FROM
			pg_class c,
			pg_attribute a
		JOIN pg_type t ON a.atttypid = t.oid
		LEFT JOIN (
			SELECT
				c.relname as sequence_name,
				c1.relname as related_table,
				a.attname as related_column
			FROM pg_class c
			JOIN pg_depend d ON d.objid = c.oid
			LEFT JOIN pg_class c1 ON d.refobjid = c1.oid
			LEFT JOIN pg_attribute a ON (d.refobjid, d.refobjsubid) = (a.attrelid, a.attnum)
			WHERE c.relkind = 'S' AND c1.relname = '%s'
		) x ON x.related_column= a.attname
		WHERE
			c.relkind in ('r','v')
			AND (c.relname='%s' or c.relname = lower('%s'))
			AND a.attname not like '....%%'
			AND a.attnum > 0
			AND a.attrelid = c.oid
		ORDER BY
			a.attnum";

		var $metaColumnsSQL1 = "
		SELECT
			a.attname,
			CASE
				WHEN x.sequence_name != ''
				THEN 'SERIAL'
				ELSE t.typname
			END AS typname,
			a.attlen, a.atttypmod, a.attnotnull, a.atthasdef, a.attnum
		FROM
			pg_class c,
			pg_namespace n,
			pg_attribute a
		JOIN pg_type t ON a.atttypid = t.oid
		LEFT JOIN (
			SELECT
				c.relname as sequence_name,
				c1.relname as related_table,
				a.attname as related_column
			FROM pg_class c
			JOIN pg_depend d ON d.objid = c.oid
			LEFT JOIN pg_class c1 ON d.refobjid = c1.oid
			LEFT JOIN pg_attribute a ON (d.refobjid, d.refobjsubid) = (a.attrelid, a.attnum)
			WHERE c.relkind = 'S' AND c1.relname = '%s'
		) x ON x.related_column= a.attname
		WHERE
			c.relkind in ('r','v')
			AND (c.relname='%s' or c.relname = lower('%s'))
			AND c.relnamespace=n.oid and n.nspname='%s'
			AND a.attname not like '....%%'
			AND a.attnum > 0
			AND a.atttypid = t.oid
			AND a.attrelid = c.oid
		ORDER BY a.attnum";


	function __construct()
	{
		parent::__construct();
		if (ADODB_ASSOC_CASE !== ADODB_ASSOC_CASE_NATIVE) {
			$this->rsPrefix .= 'assoc_';
		}
		$this->_bindInputArray = PHP_VERSION >= 5.1;
	}


			function SelectLimit($sql,$nrows=-1,$offset=-1,$inputarr=false,$secs2cache=0)
	{
		$offsetStr = ($offset >= 0) ? " OFFSET ".((integer)$offset) : '';
		$limitStr  = ($nrows >= 0)  ? " LIMIT ".((integer)$nrows) : '';
		if ($secs2cache)
			$rs = $this->CacheExecute($secs2cache,$sql."$limitStr$offsetStr",$inputarr);
		else
			$rs = $this->Execute($sql."$limitStr$offsetStr",$inputarr);

		return $rs;
	}

	

	
	protected function _generateMetaColumnsSQL($table, $schema)
	{
		if ($schema) {
			return sprintf($this->metaColumnsSQL1, $table, $table, $table, $schema);
		}
		else {
			return sprintf($this->metaColumnsSQL, $table, $table, $schema);
		}
	}

	
	function MetaForeignKeys($table, $owner=false, $upper=false)
	{
				$regex = '^.*\((.*)\).*\((.*)\).*$';
		$sql="
			SELECT
				lookup_table,
				regexp_replace(consrc, '$regex', '\\2') AS lookup_field,
				dep_table,
				regexp_replace(consrc, '$regex', '\\1') AS dep_field
			FROM (
				SELECT
					pg_get_constraintdef(c.oid) AS consrc,
					t.relname AS dep_table,
					ft.relname AS lookup_table
				FROM pg_constraint c
				JOIN pg_class t ON (t.oid = c.conrelid)
				JOIN pg_class ft ON (ft.oid = c.confrelid)
				JOIN pg_namespace nft ON (nft.oid = ft.relnamespace)
				LEFT JOIN pg_description ds ON (ds.objoid = c.oid)
				JOIN pg_namespace n ON (n.oid = t.relnamespace)
				WHERE c.contype = 'f'::\"char\"
				ORDER BY t.relname, n.nspname, c.conname, c.oid
				) constraints
			WHERE
				dep_table='".strtolower($table)."'
			ORDER BY
				lookup_table,
				dep_table,
				dep_field";
		$rs = $this->Execute($sql);

		if (!$rs || $rs->EOF) return false;

		$a = array();
		while (!$rs->EOF) {
			if ($upper) {
				$a[strtoupper($rs->Fields('lookup_table'))][] = strtoupper(str_replace('"','',$rs->Fields('dep_field').'='.$rs->Fields('lookup_field')));
			} else {
				$a[$rs->Fields('lookup_table')][] = str_replace('"','',$rs->Fields('dep_field').'='.$rs->Fields('lookup_field'));
			}
			$rs->MoveNext();
		}

		return $a;

	}

		function _old_MetaForeignKeys($table, $owner=false, $upper=false)
	{
		$sql = 'SELECT t.tgargs as args
		FROM
		pg_trigger t,pg_class c,pg_proc p
		WHERE
		t.tgenabled AND
		t.tgrelid = c.oid AND
		t.tgfoid = p.oid AND
		p.proname = \'RI_FKey_check_ins\' AND
		c.relname = \''.strtolower($table).'\'
		ORDER BY
			t.tgrelid';

		$rs = $this->Execute($sql);

		if (!$rs || $rs->EOF) return false;

		$arr = $rs->GetArray();
		$a = array();
		foreach($arr as $v) {
			$data = explode(chr(0), $v['args']);
			$size = count($data)-1; 			for($i = 4; $i < $size; $i++) {
				if ($upper)
					$a[strtoupper($data[2])][] = strtoupper($data[$i].'='.$data[++$i]);
				else
					$a[$data[2]][] = $data[$i].'='.$data[++$i];
			}
		}
		return $a;
	}

	function _query($sql,$inputarr=false)
	{
		if (! $this->_bindInputArray) {
						return ADODB_postgres64::_query($sql, $inputarr);
		}

		$this->_pnum = 0;
		$this->_errorMsg = false;
				if ($inputarr) {
			$sqlarr = explode('?',trim($sql));
			$sql = '';
			$i = 1;
			$last = sizeof($sqlarr)-1;
			foreach($sqlarr as $v) {
				if ($last < $i) $sql .= $v;
				else $sql .= $v.' $'.$i;
				$i++;
			}

			$rez = pg_query_params($this->_connectionID,$sql, $inputarr);
		} else {
			$rez = pg_query($this->_connectionID,$sql);
		}
				if ($rez && pg_num_fields($rez) <= 0) {
			if (is_resource($this->_resultid) && get_resource_type($this->_resultid) === 'pgsql result') {
				pg_free_result($this->_resultid);
			}
			$this->_resultid = $rez;
			return true;
		}
		return $rez;
	}

								function GetCharSet()
	{
				$this->charSet = @pg_client_encoding($this->_connectionID);
		if (!$this->charSet) {
			return false;
		} else {
			return $this->charSet;
		}
	}

		function SetCharSet($charset_name)
	{
		$this->GetCharSet();
		if ($this->charSet !== $charset_name) {
			$if = pg_set_client_encoding($this->_connectionID, $charset_name);
			if ($if == "0" & $this->GetCharSet() == $charset_name) {
				return true;
			} else return false;
		} else return true;
	}

}



class ADORecordSet_postgres7 extends ADORecordSet_postgres64{

	var $databaseType = "postgres7";


	function __construct($queryID, $mode=false)
	{
		parent::__construct($queryID, $mode);
	}

		function MoveNext()
	{
		if (!$this->EOF) {
			$this->_currentRow++;
			if ($this->_numOfRows < 0 || $this->_numOfRows > $this->_currentRow) {
				$this->fields = @pg_fetch_array($this->_queryID,$this->_currentRow,$this->fetchMode);

				if (is_array($this->fields)) {
					if ($this->fields && isset($this->_blobArr)) $this->_fixblobs();
					return true;
				}
			}
			$this->fields = false;
			$this->EOF = true;
		}
		return false;
	}

}

class ADORecordSet_assoc_postgres7 extends ADORecordSet_postgres64{

	var $databaseType = "postgres7";


	function __construct($queryID, $mode=false)
	{
		parent::__construct($queryID, $mode);
	}

	function _fetch()
	{
		if ($this->_currentRow >= $this->_numOfRows && $this->_numOfRows >= 0) {
			return false;
		}

		$this->fields = @pg_fetch_array($this->_queryID,$this->_currentRow,$this->fetchMode);

		if ($this->fields) {
			if (isset($this->_blobArr)) $this->_fixblobs();
			$this->_updatefields();
		}

		return (is_array($this->fields));
	}

	function MoveNext()
	{
		if (!$this->EOF) {
			$this->_currentRow++;
			if ($this->_numOfRows < 0 || $this->_numOfRows > $this->_currentRow) {
				$this->fields = @pg_fetch_array($this->_queryID,$this->_currentRow,$this->fetchMode);

				if (is_array($this->fields)) {
					if ($this->fields) {
						if (isset($this->_blobArr)) $this->_fixblobs();

						$this->_updatefields();
					}
					return true;
				}
			}


			$this->fields = false;
			$this->EOF = true;
		}
		return false;
	}
}
