<?php



function RSFilter($rs,$fn)
{
	if ($rs->databaseType != 'array') {
		if (!$rs->connection) return false;

		$rs = $rs->connection->_rs2rs($rs);
	}
	$rows = $rs->RecordCount();
	for ($i=0; $i < $rows; $i++) {
		if (is_array ($fn)) {
        	$obj = $fn[0];
        	$method = $fn[1];
        	$obj->$method ($rs->_array[$i],$rs);
      } else {
			$fn($rs->_array[$i],$rs);
      }

	}
	if (!$rs->EOF) {
		$rs->_currentRow = 0;
		$rs->fields = $rs->_array[0];
	}

	return $rs;
}
