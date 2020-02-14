<?php
if (!defined('ADODB_DIR')) die();

global $ADODB_INCLUDED_LIB;
$ADODB_INCLUDED_LIB = 1;



function adodb_strip_order_by($sql)
{
	$rez = preg_match('/(\sORDER\s+BY\s(?:[^)](?!LIMIT))*)/is', $sql, $arr);
	if ($arr)
		if (strpos($arr[1], '(') !== false) {
			$at = strpos($sql, $arr[1]);
			$cntin = 0;
			for ($i=$at, $max=strlen($sql); $i < $max; $i++) {
				$ch = $sql[$i];
				if ($ch == '(') {
					$cntin += 1;
				} elseif($ch == ')') {
					$cntin -= 1;
					if ($cntin < 0) {
						break;
					}
				}
			}
			$sql = substr($sql,0,$at).substr($sql,$i);
		} else {
			$sql = str_replace($arr[1], '', $sql);
		}
	return $sql;
}

if (false) {
	$sql = 'select * from (select a from b order by a(b),b(c) desc)';
	$sql = '(select * from abc order by 1)';
	die(adodb_strip_order_by($sql));
}

function adodb_probetypes(&$array,&$types,$probe=8)
{
	$types = array();
	if ($probe > sizeof($array)) $max = sizeof($array);
	else $max = $probe;


	for ($j=0;$j < $max; $j++) {
		$row = $array[$j];
		if (!$row) break;
		$i = -1;
		foreach($row as $v) {
			$i += 1;

			if (isset($types[$i]) && $types[$i]=='C') continue;

						$v = trim($v);

			if (!preg_match('/^[+-]{0,1}[0-9\.]+$/',$v)) {
				$types[$i] = 'C'; 
				continue;
			}
			if ($j == 0) {
																if (strlen($v) == 0) $types[$i] = 'C';
				if (strpos($v,'.') !== false) $types[$i] = 'N';
				else  $types[$i] = 'I';
				continue;
			}

			if (strpos($v,'.') !== false) $types[$i] = 'N';

		}
	}

}

function  adodb_transpose(&$arr, &$newarr, &$hdr, &$fobjs)
{
	$oldX = sizeof(reset($arr));
	$oldY = sizeof($arr);

	if ($hdr) {
		$startx = 1;
		$hdr = array('Fields');
		for ($y = 0; $y < $oldY; $y++) {
			$hdr[] = $arr[$y][0];
		}
	} else
		$startx = 0;

	for ($x = $startx; $x < $oldX; $x++) {
		if ($fobjs) {
			$o = $fobjs[$x];
			$newarr[] = array($o->name);
		} else
			$newarr[] = array();

		for ($y = 0; $y < $oldY; $y++) {
			$newarr[$x-$startx][] = $arr[$y][$x];
		}
	}
}

function _array_change_key_case($an_array)
{
	if (is_array($an_array)) {
		$new_array = array();
		foreach($an_array as $key=>$value)
			$new_array[strtoupper($key)] = $value;

	   	return $new_array;
   }

	return $an_array;
}

function _adodb_replace(&$zthis, $table, $fieldArray, $keyCol, $autoQuote, $has_autoinc)
{
		if (count($fieldArray) == 0) return 0;
		$first = true;
		$uSet = '';

		if (!is_array($keyCol)) {
			$keyCol = array($keyCol);
		}
		foreach($fieldArray as $k => $v) {
			if ($v === null) {
				$v = 'NULL';
				$fieldArray[$k] = $v;
			} else if ($autoQuote &&  strcasecmp($v,$zthis->null2null)!=0) {
				$v = $zthis->qstr($v);
				$fieldArray[$k] = $v;
			}
			if (in_array($k,$keyCol)) continue; 
			if ($first) {
				$first = false;
				$uSet = "$k=$v";
			} else
				$uSet .= ",$k=$v";
		}

		$where = false;
		foreach ($keyCol as $v) {
			if (isset($fieldArray[$v])) {
				if ($where) $where .= ' and '.$v.'='.$fieldArray[$v];
				else $where = $v.'='.$fieldArray[$v];
			}
		}

		if ($uSet && $where) {
			$update = "UPDATE $table SET $uSet WHERE $where";

			$rs = $zthis->Execute($update);


			if ($rs) {
				if ($zthis->poorAffectedRows) {
				
					if ($zthis->ErrorNo()<>0) return 0;

								
					$cnt = $zthis->GetOne("select count(*) from $table where $where");
					if ($cnt > 0) return 1; 				} else {
					if (($zthis->Affected_Rows()>0)) return 1;
				}
			} else
				return 0;
		}

			$first = true;
		foreach($fieldArray as $k => $v) {
			if ($has_autoinc && in_array($k,$keyCol)) continue; 
			if ($first) {
				$first = false;
				$iCols = "$k";
				$iVals = "$v";
			} else {
				$iCols .= ",$k";
				$iVals .= ",$v";
			}
		}
		$insert = "INSERT INTO $table ($iCols) VALUES ($iVals)";
		$rs = $zthis->Execute($insert);
		return ($rs) ? 2 : 0;
}

function _adodb_getmenu(&$zthis, $name,$defstr='',$blank1stItem=true,$multiple=false,
			$size=0, $selectAttr='',$compareFields0=true)
{
	$hasvalue = false;

	if ($multiple or is_array($defstr)) {
		if ($size==0) $size=5;
		$attr = ' multiple size="'.$size.'"';
		if (!strpos($name,'[]')) $name .= '[]';
	} else if ($size) $attr = ' size="'.$size.'"';
	else $attr ='';

	$s = '<select name="'.$name.'"'.$attr.' '.$selectAttr.'>';
	if ($blank1stItem)
		if (is_string($blank1stItem))  {
			$barr = explode(':',$blank1stItem);
			if (sizeof($barr) == 1) $barr[] = '';
			$s .= "\n<option value=\"".$barr[0]."\">".$barr[1]."</option>";
		} else $s .= "\n<option></option>";

	if ($zthis->FieldCount() > 1) $hasvalue=true;
	else $compareFields0 = true;

	$value = '';
    $optgroup = null;
    $firstgroup = true;
    $fieldsize = $zthis->FieldCount();
	while(!$zthis->EOF) {
		$zval = rtrim(reset($zthis->fields));

		if ($blank1stItem && $zval=="") {
			$zthis->MoveNext();
			continue;
		}

        if ($fieldsize > 1) {
			if (isset($zthis->fields[1]))
				$zval2 = rtrim($zthis->fields[1]);
			else
				$zval2 = rtrim(next($zthis->fields));
		}
		$selected = ($compareFields0) ? $zval : $zval2;

        $group = '';
		if ($fieldsize > 2) {
            $group = rtrim($zthis->fields[2]);
        }

		if ($hasvalue)
			$value = " value='".htmlspecialchars($zval2)."'";

		if (is_array($defstr))  {

			if (in_array($selected,$defstr))
				$s .= "\n<option selected='selected'$value>".htmlspecialchars($zval).'</option>';
			else
				$s .= "\n<option".$value.'>'.htmlspecialchars($zval).'</option>';
		}
		else {
			if (strcasecmp($selected,$defstr)==0)
				$s .= "\n<option selected='selected'$value>".htmlspecialchars($zval).'</option>';
			else
				$s .= "\n<option".$value.'>'.htmlspecialchars($zval).'</option>';
		}
		$zthis->MoveNext();
	} 
        if($optgroup != null) {
        $s .= "\n</optgroup>";
	}
	return $s ."\n</select>\n";
}

function _adodb_getmenu_gp(&$zthis, $name,$defstr='',$blank1stItem=true,$multiple=false,
			$size=0, $selectAttr='',$compareFields0=true)
{
	$hasvalue = false;

	if ($multiple or is_array($defstr)) {
		if ($size==0) $size=5;
		$attr = ' multiple size="'.$size.'"';
		if (!strpos($name,'[]')) $name .= '[]';
	} else if ($size) $attr = ' size="'.$size.'"';
	else $attr ='';

	$s = '<select name="'.$name.'"'.$attr.' '.$selectAttr.'>';
	if ($blank1stItem)
		if (is_string($blank1stItem))  {
			$barr = explode(':',$blank1stItem);
			if (sizeof($barr) == 1) $barr[] = '';
			$s .= "\n<option value=\"".$barr[0]."\">".$barr[1]."</option>";
		} else $s .= "\n<option></option>";

	if ($zthis->FieldCount() > 1) $hasvalue=true;
	else $compareFields0 = true;

	$value = '';
    $optgroup = null;
    $firstgroup = true;
    $fieldsize = sizeof($zthis->fields);
	while(!$zthis->EOF) {
		$zval = rtrim(reset($zthis->fields));

		if ($blank1stItem && $zval=="") {
			$zthis->MoveNext();
			continue;
		}

        if ($fieldsize > 1) {
			if (isset($zthis->fields[1]))
				$zval2 = rtrim($zthis->fields[1]);
			else
				$zval2 = rtrim(next($zthis->fields));
		}
		$selected = ($compareFields0) ? $zval : $zval2;

        $group = '';
		if (isset($zthis->fields[2])) {
            $group = rtrim($zthis->fields[2]);
        }

        if ($optgroup != $group) {
            $optgroup = $group;
            if ($firstgroup) {
                $firstgroup = false;
                $s .="\n<optgroup label='". htmlspecialchars($group) ."'>";
            } else {
                $s .="\n</optgroup>";
                $s .="\n<optgroup label='". htmlspecialchars($group) ."'>";
            }
		}

		if ($hasvalue)
			$value = " value='".htmlspecialchars($zval2)."'";

		if (is_array($defstr))  {

			if (in_array($selected,$defstr))
				$s .= "\n<option selected='selected'$value>".htmlspecialchars($zval).'</option>';
			else
				$s .= "\n<option".$value.'>'.htmlspecialchars($zval).'</option>';
		}
		else {
			if (strcasecmp($selected,$defstr)==0)
				$s .= "\n<option selected='selected'$value>".htmlspecialchars($zval).'</option>';
			else
				$s .= "\n<option".$value.'>'.htmlspecialchars($zval).'</option>';
		}
		$zthis->MoveNext();
	} 
        if($optgroup != null) {
        $s .= "\n</optgroup>";
	}
	return $s ."\n</select>\n";
}



function _adodb_getcount(&$zthis, $sql,$inputarr=false,$secs2cache=0)
{
	$qryRecs = 0;

	 if (!empty($zthis->_nestedSQL) || preg_match("/^\s*SELECT\s+DISTINCT/is", $sql) ||
	 	preg_match('/\s+GROUP\s+BY\s+/is',$sql) ||
		preg_match('/\s+UNION\s+/is',$sql)) {

		$rewritesql = adodb_strip_order_by($sql);

						if ($zthis->dataProvider == 'oci8') {
						if (preg_match('#/\\*+.*?\\*\\/#', $sql, $hint)) {
				$rewritesql = "SELECT ".$hint[0]." COUNT(*) FROM (".$rewritesql.")";
			} else
				$rewritesql = "SELECT COUNT(*) FROM (".$rewritesql.")";

		} else if (strncmp($zthis->databaseType,'postgres',8) == 0 || strncmp($zthis->databaseType,'mysql',5) == 0)  {
			$rewritesql = "SELECT COUNT(*) FROM ($rewritesql) _ADODB_ALIAS_";
		} else {
			$rewritesql = "SELECT COUNT(*) FROM ($rewritesql)";
		}
	} else {
				if ( strpos($sql, '_ADODB_COUNT') !== FALSE ) {
			$rewritesql = preg_replace('/^\s*?SELECT\s+_ADODB_COUNT(.*)_ADODB_COUNT\s/is','SELECT COUNT(*) ',$sql);
		} else {
			$rewritesql = preg_replace('/^\s*?SELECT\s.*?\s+(.*?)\s+FROM\s/is','SELECT COUNT(*) FROM ',$sql);
		}
								$rewritesql = adodb_strip_order_by($rewritesql);
	}

	if (isset($rewritesql) && $rewritesql != $sql) {
		if (preg_match('/\sLIMIT\s+[0-9]+/i',$sql,$limitarr)) $rewritesql .= $limitarr[0];

		if ($secs2cache) {
									$qryRecs = $zthis->CacheGetOne($secs2cache/2,$rewritesql,$inputarr);

		} else {
			$qryRecs = $zthis->GetOne($rewritesql,$inputarr);
	  	}
		if ($qryRecs !== false) return $qryRecs;
	}
		

		if (preg_match('/\s*UNION\s*/is', $sql)) $rewritesql = $sql;
	else $rewritesql = $rewritesql = adodb_strip_order_by($sql);

	if (preg_match('/\sLIMIT\s+[0-9]+/i',$sql,$limitarr)) $rewritesql .= $limitarr[0];

	if ($secs2cache) {
		$rstest = $zthis->CacheExecute($secs2cache,$rewritesql,$inputarr);
		if (!$rstest) $rstest = $zthis->CacheExecute($secs2cache,$sql,$inputarr);
	} else {
		$rstest = $zthis->Execute($rewritesql,$inputarr);
		if (!$rstest) $rstest = $zthis->Execute($sql,$inputarr);
	}
	if ($rstest) {
	  		$qryRecs = $rstest->RecordCount();
		if ($qryRecs == -1) {
		global $ADODB_EXTENSION;
					if ($ADODB_EXTENSION) {
				while(!$rstest->EOF) {
					adodb_movenext($rstest);
				}
			} else {
				while(!$rstest->EOF) {
					$rstest->MoveNext();
				}
			}
			$qryRecs = $rstest->_currentRow;
		}
		$rstest->Close();
		if ($qryRecs == -1) return 0;
	}
	return $qryRecs;
}


function _adodb_pageexecute_all_rows(&$zthis, $sql, $nrows, $page,
						$inputarr=false, $secs2cache=0)
{
	$atfirstpage = false;
	$atlastpage = false;
	$lastpageno=1;

			if (!isset($nrows) || $nrows <= 0) $nrows = 10;

	$qryRecs = false; 
	$qryRecs = _adodb_getcount($zthis,$sql,$inputarr,$secs2cache);
	$lastpageno = (int) ceil($qryRecs / $nrows);
	$zthis->_maxRecordCount = $qryRecs;



				if ($page >= $lastpageno) {
		$page = $lastpageno;
		$atlastpage = true;
	}

		if (empty($page) || $page <= 1) {
		$page = 1;
		$atfirstpage = true;
	}

		$offset = $nrows * ($page-1);
	if ($secs2cache > 0)
		$rsreturn = $zthis->CacheSelectLimit($secs2cache, $sql, $nrows, $offset, $inputarr);
	else
		$rsreturn = $zthis->SelectLimit($sql, $nrows, $offset, $inputarr, $secs2cache);


		if ($rsreturn) {
		$rsreturn->_maxRecordCount = $qryRecs;
		$rsreturn->rowsPerPage = $nrows;
		$rsreturn->AbsolutePage($page);
		$rsreturn->AtFirstPage($atfirstpage);
		$rsreturn->AtLastPage($atlastpage);
		$rsreturn->LastPageNo($lastpageno);
	}
	return $rsreturn;
}

function _adodb_pageexecute_no_last_page(&$zthis, $sql, $nrows, $page, $inputarr=false, $secs2cache=0)
{

	$atfirstpage = false;
	$atlastpage = false;

	if (!isset($page) || $page <= 1) {
				$page = 1;
		$atfirstpage = true;
	}
	if ($nrows <= 0) {
				$nrows = 10;
	}

	$pagecounteroffset = ($page * $nrows) - $nrows;

						$test_nrows = $nrows + 1;
	if ($secs2cache > 0) {
		$rsreturn = $zthis->CacheSelectLimit($secs2cache, $sql, $nrows, $pagecounteroffset, $inputarr);
	} else {
		$rsreturn = $zthis->SelectLimit($sql, $test_nrows, $pagecounteroffset, $inputarr, $secs2cache);
	}

		if ( $rsreturn->_numOfRows == $test_nrows ) {
						$rsreturn->_numOfRows = ( $rsreturn->_numOfRows - 1 );
	} elseif ( $rsreturn->_numOfRows == 0 && $page > 1 ) {
								$pagecounter = $page + 1;
		$pagecounteroffset = ($pagecounter * $nrows) - $nrows;

		$rstest = $rsreturn;
		if ($rstest) {
			while ($rstest && $rstest->EOF && $pagecounter > 0) {
				$atlastpage = true;
				$pagecounter--;
				$pagecounteroffset = $nrows * ($pagecounter - 1);
				$rstest->Close();
				if ($secs2cache>0) {
					$rstest = $zthis->CacheSelectLimit($secs2cache, $sql, $nrows, $pagecounteroffset, $inputarr);
				}
				else {
					$rstest = $zthis->SelectLimit($sql, $nrows, $pagecounteroffset, $inputarr, $secs2cache);
				}
			}
			if ($rstest) $rstest->Close();
		}
		if ($atlastpage) {
						$page = $pagecounter;
			if ($page == 1) {
												$atfirstpage = true;
			}
		}
				$offset = $nrows * ($page-1);
		if ($secs2cache > 0) {
			$rsreturn = $zthis->CacheSelectLimit($secs2cache, $sql, $nrows, $offset, $inputarr);
		}
		else {
			$rsreturn = $zthis->SelectLimit($sql, $nrows, $offset, $inputarr, $secs2cache);
		}
	} elseif ( $rsreturn->_numOfRows < $test_nrows ) {
				$atlastpage = true;
	}

		if ($rsreturn) {
		$rsreturn->rowsPerPage = $nrows;
		$rsreturn->AbsolutePage($page);
		$rsreturn->AtFirstPage($atfirstpage);
		$rsreturn->AtLastPage($atlastpage);
	}
	return $rsreturn;
}

function _adodb_getupdatesql(&$zthis,&$rs, $arrFields,$forceUpdate=false,$magicq=false,$force=2)
{
	global $ADODB_QUOTE_FIELDNAMES;

		if (!$rs) {
			printf(ADODB_BAD_RS,'GetUpdateSQL');
			return false;
		}

		$fieldUpdatedCount = 0;
		$arrFields = _array_change_key_case($arrFields);

		$hasnumeric = isset($rs->fields[0]);
		$setFields = '';

				for ($i=0, $max=$rs->FieldCount(); $i < $max; $i++) {
						$field = $rs->FetchField($i);

									$upperfname = strtoupper($field->name);
			if (adodb_key_exists($upperfname,$arrFields,$force)) {

																
				if ($hasnumeric) $val = $rs->fields[$i];
				else if (isset($rs->fields[$upperfname])) $val = $rs->fields[$upperfname];
				else if (isset($rs->fields[$field->name])) $val =  $rs->fields[$field->name];
				else if (isset($rs->fields[strtolower($upperfname)])) $val =  $rs->fields[strtolower($upperfname)];
				else $val = '';


				if ($forceUpdate || strcmp($val, $arrFields[$upperfname])) {
										$fieldUpdatedCount++;

															$type = $rs->MetaType($field->type);


					if ($type == 'null') {
						$type = 'C';
					}

					if ((strpos($upperfname,' ') !== false) || ($ADODB_QUOTE_FIELDNAMES)) {
						switch ($ADODB_QUOTE_FIELDNAMES) {
						case 'LOWER':
							$fnameq = $zthis->nameQuote.strtolower($field->name).$zthis->nameQuote;break;
						case 'NATIVE':
							$fnameq = $zthis->nameQuote.$field->name.$zthis->nameQuote;break;
						case 'UPPER':
						default:
							$fnameq = $zthis->nameQuote.$upperfname.$zthis->nameQuote;break;
						}
					} else
						$fnameq = $upperfname;

                                if (is_null($arrFields[$upperfname])
					|| (empty($arrFields[$upperfname]) && strlen($arrFields[$upperfname]) == 0)
                    || $arrFields[$upperfname] === $zthis->null2null
                    )
                {
                    switch ($force) {

                                                                        
                        case 1:
                                                        $setFields .= $field->name . " = null, ";
                        break;

                        case 2:
                                                        $arrFields[$upperfname] = "";
                            $setFields .= _adodb_column_sql($zthis, 'U', $type, $upperfname, $fnameq,$arrFields, $magicq);
                        break;
						default:
                        case 3:
                                                        if (is_null($arrFields[$upperfname]) || $arrFields[$upperfname] === $zthis->null2null) {
                                $setFields .= $field->name . " = null, ";
                            } else {
                                $setFields .= _adodb_column_sql($zthis, 'U', $type, $upperfname, $fnameq,$arrFields, $magicq);
                            }
                        break;
                    }
                                } else {
																														$setFields .= _adodb_column_sql($zthis, 'U', $type, $upperfname, $fnameq,
														  $arrFields, $magicq);
					}
				}
			}
		}

				if ($fieldUpdatedCount > 0 || $forceUpdate) {
								if (!empty($rs->tableName)) $tableName = $rs->tableName;
			else {
				preg_match("/FROM\s+".ADODB_TABLE_REGEX."/is", $rs->sql, $tableName);
				$tableName = $tableName[1];
			}
									preg_match('/\sWHERE\s(.*)/is', $rs->sql, $whereClause);

			$discard = false;
						if ($whereClause) {
							if (preg_match('/\s(ORDER\s.*)/is', $whereClause[1], $discard));
				else if (preg_match('/\s(LIMIT\s.*)/is', $whereClause[1], $discard));
				else if (preg_match('/\s(FOR UPDATE.*)/is', $whereClause[1], $discard));
				else preg_match('/\s.*(\) WHERE .*)/is', $whereClause[1], $discard); 			} else
				$whereClause = array(false,false);

			if ($discard)
				$whereClause[1] = substr($whereClause[1], 0, strlen($whereClause[1]) - strlen($discard[1]));

			$sql = 'UPDATE '.$tableName.' SET '.substr($setFields, 0, -2);
			if (strlen($whereClause[1]) > 0)
				$sql .= ' WHERE '.$whereClause[1];

			return $sql;

		} else {
			return false;
	}
}

function adodb_key_exists($key, &$arr,$force=2)
{
	if ($force<=0) {
				return (!empty($arr[$key])) || (isset($arr[$key]) && strlen($arr[$key])>0);
	}

	if (isset($arr[$key])) return true;
		if (ADODB_PHPVER >= 0x4010) return array_key_exists($key,$arr);
	return false;
}


function _adodb_getinsertsql(&$zthis,&$rs,$arrFields,$magicq=false,$force=2)
{
static $cacheRS = false;
static $cacheSig = 0;
static $cacheCols;
	global $ADODB_QUOTE_FIELDNAMES;

	$tableName = '';
	$values = '';
	$fields = '';
	$recordSet = null;
	$arrFields = _array_change_key_case($arrFields);
	$fieldInsertedCount = 0;

	if (is_string($rs)) {
						$tableName = $rs;

								$rsclass = $zthis->rsPrefix.$zthis->databaseType;
		$recordSet = new $rsclass(-1,$zthis->fetchMode);
		$recordSet->connection = $zthis;

		if (is_string($cacheRS) && $cacheRS == $rs) {
			$columns = $cacheCols;
		} else {
			$columns = $zthis->MetaColumns( $tableName );
			$cacheRS = $tableName;
			$cacheCols = $columns;
		}
	} else if (is_subclass_of($rs, 'adorecordset')) {
		if (isset($rs->insertSig) && is_integer($cacheRS) && $cacheRS == $rs->insertSig) {
			$columns = $cacheCols;
		} else {
			for ($i=0, $max=$rs->FieldCount(); $i < $max; $i++)
				$columns[] = $rs->FetchField($i);
			$cacheRS = $cacheSig;
			$cacheCols = $columns;
			$rs->insertSig = $cacheSig++;
		}
		$recordSet = $rs;

	} else {
		printf(ADODB_BAD_RS,'GetInsertSQL');
		return false;
	}

		foreach( $columns as $field ) {
		$upperfname = strtoupper($field->name);
		if (adodb_key_exists($upperfname,$arrFields,$force)) {
			$bad = false;
			if ((strpos($upperfname,' ') !== false) || ($ADODB_QUOTE_FIELDNAMES)) {
				switch ($ADODB_QUOTE_FIELDNAMES) {
				case 'LOWER':
					$fnameq = $zthis->nameQuote.strtolower($field->name).$zthis->nameQuote;break;
				case 'NATIVE':
					$fnameq = $zthis->nameQuote.$field->name.$zthis->nameQuote;break;
				case 'UPPER':
				default:
					$fnameq = $zthis->nameQuote.$upperfname.$zthis->nameQuote;break;
				}
			} else
				$fnameq = $upperfname;

			$type = $recordSet->MetaType($field->type);

            
            if (is_null($arrFields[$upperfname])
                || (empty($arrFields[$upperfname]) && strlen($arrFields[$upperfname]) == 0)
                || $arrFields[$upperfname] === $zthis->null2null
				)
               {
                    switch ($force) {

                        case 0: 							$bad = true;
							break;

                        case 1:
                            $values  .= "null, ";
                        break;

                        case 2:
                                                        $arrFields[$upperfname] = "";
                            $values .= _adodb_column_sql($zthis, 'I', $type, $upperfname, $fnameq,$arrFields, $magicq);
                        break;

						default:
                        case 3:
                            							if (is_null($arrFields[$upperfname]) || $arrFields[$upperfname] === $zthis->null2null) {
								$values  .= "null, ";
							} else {
                        		$values .= _adodb_column_sql($zthis, 'I', $type, $upperfname, $fnameq, $arrFields, $magicq);
             				}
              			break;
             		} 
            
			} else {
																				$values .= _adodb_column_sql($zthis, 'I', $type, $upperfname, $fnameq,
											   $arrFields, $magicq);
			}

			if ($bad) continue;
						$fieldInsertedCount++;


						$fields .= $fnameq . ", ";
		}
	}


		if ($fieldInsertedCount <= 0)  return false;

		if (!$tableName) {
		if (!empty($rs->tableName)) $tableName = $rs->tableName;
		else if (preg_match("/FROM\s+".ADODB_TABLE_REGEX."/is", $rs->sql, $tableName))
			$tableName = $tableName[1];
		else
			return false;
	}

			$fields = substr($fields, 0, -2);
	$values = substr($values, 0, -2);

		return 'INSERT INTO '.$tableName.' ( '.$fields.' ) VALUES ( '.$values.' )';
}



function _adodb_column_sql_oci8(&$zthis,$action, $type, $fname, $fnameq, $arrFields, $magicq)
{
    $sql = '';

            switch($type) {
    case 'B':
                
                        if (!empty($zthis->hasReturningInto)) {
            if ($action == 'I') {
                $sql = 'empty_blob(), ';
            } else {
                $sql = $fnameq. '=empty_blob(), ';
            }
                                                $zthis->_returningArray[$fname] = ':xx'.$fname.'xx';
        } else if (empty($arrFields[$fname])){
            if ($action == 'I') {
                $sql = 'empty_blob(), ';
            } else {
                $sql = $fnameq. '=empty_blob(), ';
            }
        } else {
                                    $sql = _adodb_column_sql($zthis, $action, $type, $fname, $fnameq, $arrFields, $magicq,false);
        }
        break;

    case "X":
                
                       if (!empty($zthis->hasReturningInto)) {
            if ($action == 'I') {
                $sql = ':xx'.$fname.'xx, ';
            } else {
                $sql = $fnameq.'=:xx'.$fname.'xx, ';
            }
                                                $zthis->_returningArray[$fname] = ':xx'.$fname.'xx';
        } else {
                                    $sql = _adodb_column_sql($zthis, $action, $type, $fname, $fnameq, $arrFields, $magicq,false);
        }
        break;

    default:
        $sql = _adodb_column_sql($zthis, $action, $type, $fname, $fnameq,  $arrFields, $magicq,false);
        break;
    }

    return $sql;
}

function _adodb_column_sql(&$zthis, $action, $type, $fname, $fnameq, $arrFields, $magicq, $recurse=true)
{

	if ($recurse) {
		switch($zthis->dataProvider)  {
		case 'postgres':
			if ($type == 'L') $type = 'C';
			break;
		case 'oci8':
			return _adodb_column_sql_oci8($zthis, $action, $type, $fname, $fnameq, $arrFields, $magicq);

		}
	}

	switch($type) {
		case "C":
		case "X":
		case 'B':
			$val = $zthis->qstr($arrFields[$fname],$magicq);
			break;

		case "D":
			$val = $zthis->DBDate($arrFields[$fname]);
			break;

		case "T":
			$val = $zthis->DBTimeStamp($arrFields[$fname]);
			break;

		case "N":
		    $val = $arrFields[$fname];
			if (!is_numeric($val)) $val = str_replace(',', '.', (float)$val);
		    break;

		case "I":
		case "R":
		    $val = $arrFields[$fname];
			if (!is_numeric($val)) $val = (integer) $val;
		    break;

		default:
			$val = str_replace(array("'"," ","("),"",$arrFields[$fname]); 			if (empty($val)) $val = '0';
			break;
	}

	if ($action == 'I') return $val . ", ";


	return $fnameq . "=" . $val  . ", ";

}



function _adodb_debug_execute(&$zthis, $sql, $inputarr)
{
	$ss = '';
	if ($inputarr) {
		foreach($inputarr as $kk=>$vv) {
			if (is_string($vv) && strlen($vv)>64) $vv = substr($vv,0,64).'...';
			if (is_null($vv)) $ss .= "($kk=>null) ";
			else $ss .= "($kk=>'$vv') ";
		}
		$ss = "[ $ss ]";
	}
	$sqlTxt = is_array($sql) ? $sql[0] : $sql;
	
		$inBrowser = isset($_SERVER['HTTP_USER_AGENT']);

	$dbt = $zthis->databaseType;
	if (isset($zthis->dsnType)) $dbt .= '-'.$zthis->dsnType;
	if ($inBrowser) {
		if ($ss) {
			$ss = '<code>'.htmlspecialchars($ss).'</code>';
		}
		if ($zthis->debug === -1)
			ADOConnection::outp( "<br>\n($dbt): ".htmlspecialchars($sqlTxt)." &nbsp; $ss\n<br>\n",false);
		else if ($zthis->debug !== -99)
			ADOConnection::outp( "<hr>\n($dbt): ".htmlspecialchars($sqlTxt)." &nbsp; $ss\n<hr>\n",false);
	} else {
		$ss = "\n   ".$ss;
		if ($zthis->debug !== -99)
			ADOConnection::outp("-----<hr>\n($dbt): ".$sqlTxt." $ss\n-----<hr>\n",false);
	}

	$qID = $zthis->_query($sql,$inputarr);

	
	if ($zthis->databaseType == 'mssql') {
	
		if($emsg = $zthis->ErrorMsg()) {
			if ($err = $zthis->ErrorNo()) {
				if ($zthis->debug === -99)
					ADOConnection::outp( "<hr>\n($dbt): ".htmlspecialchars($sqlTxt)." &nbsp; $ss\n<hr>\n",false);

				ADOConnection::outp($err.': '.$emsg);
			}
		}
	} else if (!$qID) {

		if ($zthis->debug === -99)
				if ($inBrowser) ADOConnection::outp( "<hr>\n($dbt): ".htmlspecialchars($sqlTxt)." &nbsp; $ss\n<hr>\n",false);
				else ADOConnection::outp("-----<hr>\n($dbt): ".$sqlTxt."$ss\n-----<hr>\n",false);

		ADOConnection::outp($zthis->ErrorNo() .': '. $zthis->ErrorMsg());
	}

	if ($zthis->debug === 99) _adodb_backtrace(true,9999,2);
	return $qID;
}

function _adodb_backtrace($printOrArr=true,$levels=9999,$skippy=0,$ishtml=null)
{
	if (!function_exists('debug_backtrace')) return '';

	if ($ishtml === null) $html =  (isset($_SERVER['HTTP_USER_AGENT']));
	else $html = $ishtml;

	$fmt =  ($html) ? "</font><font color=#808080 size=-1> %% line %4d, file: <a href=\"file:/%s\">%s</a></font>" : "%% line %4d, file: %s";

	$MAXSTRLEN = 128;

	$s = ($html) ? '<pre align=left>' : '';

	if (is_array($printOrArr)) $traceArr = $printOrArr;
	else $traceArr = debug_backtrace();
	array_shift($traceArr);
	array_shift($traceArr);
	$tabs = sizeof($traceArr)-2;

	foreach ($traceArr as $arr) {
		if ($skippy) {$skippy -= 1; continue;}
		$levels -= 1;
		if ($levels < 0) break;

		$args = array();
		for ($i=0; $i < $tabs; $i++) $s .=  ($html) ? ' &nbsp; ' : "\t";
		$tabs -= 1;
		if ($html) $s .= '<font face="Courier New,Courier">';
		if (isset($arr['class'])) $s .= $arr['class'].'.';
		if (isset($arr['args']))
		 foreach($arr['args'] as $v) {
			if (is_null($v)) $args[] = 'null';
			else if (is_array($v)) $args[] = 'Array['.sizeof($v).']';
			else if (is_object($v)) $args[] = 'Object:'.get_class($v);
			else if (is_bool($v)) $args[] = $v ? 'true' : 'false';
			else {
				$v = (string) @$v;
				$str = htmlspecialchars(str_replace(array("\r","\n"),' ',substr($v,0,$MAXSTRLEN)));
				if (strlen($v) > $MAXSTRLEN) $str .= '...';
				$args[] = $str;
			}
		}
		$s .= $arr['function'].'('.implode(', ',$args).')';


		$s .= @sprintf($fmt, $arr['line'],$arr['file'],basename($arr['file']));

		$s .= "\n";
	}
	if ($html) $s .= '</pre>';
	if ($printOrArr) print $s;

	return $s;
}

