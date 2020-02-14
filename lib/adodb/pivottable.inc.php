<?php




 function PivotTableSQL(&$db,$tables,$rowfields,$colfield, $where=false,
 	$aggfield = false,$sumlabel='Sum ',$aggfn ='SUM', $showcount = true)
 {
	if ($aggfield) $hidecnt = true;
	else $hidecnt = false;

	$iif = strpos($db->databaseType,'access') !== false;
		
	
 	if ($where) $where = "\nWHERE $where";
	if (!is_array($colfield)) $colarr = $db->GetCol("select distinct $colfield from $tables $where order by 1");
	if (!$aggfield) $hidecnt = false;

	$sel = "$rowfields, ";
	if (is_array($colfield)) {
		foreach ($colfield as $k => $v) {
			$k = trim($k);
			if (!$hidecnt) {
				$sel .= $iif ?
					"\n\t$aggfn(IIF($v,1,0)) AS \"$k\", "
					:
					"\n\t$aggfn(CASE WHEN $v THEN 1 ELSE 0 END) AS \"$k\", ";
			}
			if ($aggfield) {
				$sel .= $iif ?
					"\n\t$aggfn(IIF($v,$aggfield,0)) AS \"$sumlabel$k\", "
					:
					"\n\t$aggfn(CASE WHEN $v THEN $aggfield ELSE 0 END) AS \"$sumlabel$k\", ";
			}
		}
	} else {
		foreach ($colarr as $v) {
			if (!is_numeric($v)) $vq = $db->qstr($v);
			else $vq = $v;
			$v = trim($v);
			if (strlen($v) == 0	) $v = 'null';
			if (!$hidecnt) {
				$sel .= $iif ?
					"\n\t$aggfn(IIF($colfield=$vq,1,0)) AS \"$v\", "
					:
					"\n\t$aggfn(CASE WHEN $colfield=$vq THEN 1 ELSE 0 END) AS \"$v\", ";
			}
			if ($aggfield) {
				if ($hidecnt) $label = $v;
				else $label = "{$v}_$aggfield";
				$sel .= $iif ?
					"\n\t$aggfn(IIF($colfield=$vq,$aggfield,0)) AS \"$label\", "
					:
					"\n\t$aggfn(CASE WHEN $colfield=$vq THEN $aggfield ELSE 0 END) AS \"$label\", ";
			}
		}
	}
	if ($aggfield && $aggfield != '1'){
		$agg = "$aggfn($aggfield)";
		$sel .= "\n\t$agg as \"$sumlabel$aggfield\", ";
	}

	if ($showcount)
		$sel .= "\n\tSUM(1) as Total";
	else
		$sel = substr($sel,0,strlen($sel)-2);


		$rowfields = preg_replace('/ AS (\w+)/i', '', $rowfields);

	$sql = "SELECT $sel \nFROM $tables $where \nGROUP BY $rowfields";

	return $sql;
 }


if (0) {


 $sql = PivotTableSQL(
 	$gDB,  											 	'products p ,categories c ,suppliers s',  			'CompanyName,QuantityPerUnit',						'CategoryName',										'p.CategoryID = c.CategoryID and s.SupplierID= p.SupplierID' );
 print "<pre>$sql";
 $rs = $gDB->Execute($sql);
 rs2html($rs);



 $sql = PivotTableSQL(
 	$gDB,										 	'products p ,categories c ,suppliers s',		'CompanyName,QuantityPerUnit',																array(
' 0 ' => 'UnitsInStock <= 0',
"1 to 5" => '0 < UnitsInStock and UnitsInStock <= 5',
"6 to 10" => '5 < UnitsInStock and UnitsInStock <= 10',
"11 to 15"  => '10 < UnitsInStock and UnitsInStock <= 15',
"16+" =>'15 < UnitsInStock'
),
	' p.CategoryID = c.CategoryID and s.SupplierID= p.SupplierID', 	'UnitsInStock', 								'Sum'										);
 print "<pre>$sql";
 $rs = $gDB->Execute($sql);
 rs2html($rs);
 
}
