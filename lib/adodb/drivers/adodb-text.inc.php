<?php




if (!defined('ADODB_DIR')) die();

if (! defined("_ADODB_TEXT_LAYER")) {
 define("_ADODB_TEXT_LAYER", 1 );

function adodb_cmp($a, $b) {
	if ($a[0] == $b[0]) return 0;
	return ($a[0] < $b[0]) ? -1 : 1;
}
function adodb_cmpr($a, $b) {
	if ($a[0] == $b[0]) return 0;
	return ($a[0] > $b[0]) ? -1 : 1;
}
class ADODB_text extends ADOConnection {
	var $databaseType = 'text';

	var $_origarray; 	var $_types;
	var $_proberows = 8;
	var $_colnames;
	var $_skiprow1=false;
	var $readOnly = true;
	var $hasTransactions = false;

	var $_rezarray;
	var $_reznames;
	var $_reztypes;

	function __construct()
	{
	}

	function RSRecordCount()
	{
		if (!empty($this->_rezarray)) return sizeof($this->_rezarray);

		return sizeof($this->_origarray);
	}

	function _insertid()
	{
			return false;
	}

	function _affectedrows()
	{
			return false;
	}

			function PConnect(&$array, $types = false, $colnames = false)
	{
		return $this->Connect($array, $types, $colnames);
	}
			function Connect(&$array, $types = false, $colnames = false)
	{
		if (is_string($array) and $array === 'iluvphplens') return 'me2';

		if (!$array) {
			$this->_origarray = false;
			return true;
		}
		$row = $array[0];
		$cols = sizeof($row);


		if ($colnames) $this->_colnames = $colnames;
		else {
			$this->_colnames = $array[0];
			$this->_skiprow1 = true;
		}
		if (!$types) {
					$types = array();
			$firstrow = true;
			if ($this->_proberows > sizeof($array)) $max = sizeof($array);
			else $max = $this->_proberows;
			for ($j=($this->_skiprow1)?1:0;$j < $max; $j++) {
				$row = $array[$j];
				if (!$row) break;
				$i = -1;
				foreach($row as $v) {
					$i += 1;
										$v = trim($v);
	 				if (!preg_match('/^[+-]{0,1}[0-9\.]+$/',$v)) {
						$types[$i] = 'C'; 						continue;
					}
					if (isset($types[$i]) && $types[$i]=='C') continue;
					if ($firstrow) {
																										if (strlen($v) == 0) $types[0] = 'C';
						if (strpos($v,'.') !== false) $types[0] = 'N';
						else  $types[$i] = 'I';
						continue;
					}

					if (strpos($v,'.') !== false) $types[$i] = 'N';

				}
				$firstrow = false;
			}
		}
				$this->_origarray = $array;
		$this->_types = $types;
		return true;
	}



						function _query($sql,$input_arr,$eval=false)
	{
		if ($this->_origarray === false) return false;

		$eval = $this->evalAll;
		$usql = strtoupper(trim($sql));
		$usql = preg_replace("/[\t\n\r]/",' ',$usql);
		$usql = preg_replace('/ *BY/i',' BY',strtoupper($usql));

		$eregword ='([A-Z_0-9]*)';
				if ($eval) {
			$i = 0;
			foreach($this->_colnames as $n) {
				$n = strtoupper(trim($n));
				$eval = str_replace("\$$n","\$arr[$i]",$eval);

				$i += 1;
			}

			$i = 0;
			$eval = "\$rez=($eval);";
						$where_arr = array();

			reset($this->_origarray);
			while (list($k_arr,$arr) = each($this->_origarray)) {

				if ($i == 0 && $this->_skiprow1)
					$where_arr[] = $arr;
				else {
					eval($eval);
										if ($rez) $where_arr[] = $arr;
				}
				$i += 1;
			}
			$this->_rezarray = $where_arr;
		}else
			$where_arr = $this->_origarray;

						if (substr($usql,0,7) == 'SELECT ') {
			$at = strpos($usql,' FROM ');
			$sel = trim(substr($usql,7,$at-7));

			$distinct = false;
			if (substr($sel,0,8) == 'DISTINCT') {
				$distinct = true;
				$sel = trim(substr($sel,8,$at));
			}

												if (strpos(',',$sel)===false) {
				$colarr = array();

				preg_match("/$eregword/",$sel,$colarr);
				$col = $colarr[1];
				$i = 0;
				$n = '';
				reset($this->_colnames);
				while (list($k_n,$n) = each($this->_colnames)) {

					if ($col == strtoupper(trim($n))) break;
					$i += 1;
				}

				if ($n && $col) {
					$distarr = array();
					$projarray = array();
					$projtypes = array($this->_types[$i]);
					$projnames = array($n);

					reset($where_arr);
					while (list($k_a,$a) = each($where_arr)) {
						if ($i == 0 && $this->_skiprow1) {
							$projarray[] = array($n);
							continue;
						}

						if ($distinct) {
							$v = strtoupper($a[$i]);
							if (! $distarr[$v]) {
								$projarray[] = array($a[$i]);
								$distarr[$v] = 1;
							}
						} else
							$projarray[] = array($a[$i]);

					} 									}
			} 		}  
		if (empty($projarray)) {
			$projtypes = $this->_types;
			$projarray = $where_arr;
			$projnames = $this->_colnames;
		}
		$this->_rezarray = $projarray;
		$this->_reztypes = $projtypes;
		$this->_reznames = $projnames;


		$pos = strpos($usql,' ORDER BY ');
		if ($pos === false) return $this;
		$orderby = trim(substr($usql,$pos+10));

		preg_match("/$eregword/",$orderby,$arr);
		if (sizeof($arr) < 2) return $this; 		$col = $arr[1];
		$at = (integer) $col;
		if ($at == 0) {
			$i = 0;
			reset($projnames);
			while (list($k_n,$n) = each($projnames)) {
				if (strtoupper(trim($n)) == $col) {
					$at = $i+1;
					break;
				}
				$i += 1;
			}
		}

		if ($at <= 0 || $at > sizeof($projarray[0])) return $this; 		$at -= 1;

				$sorta = array();
		$t = $projtypes[$at];
		$num = ($t == 'I' || $t == 'N');
		for ($i=($this->_skiprow1)?1:0, $max = sizeof($projarray); $i < $max; $i++) {
			$row = $projarray[$i];
			$val = ($num)?(float)$row[$at]:$row[$at];
			$sorta[]=array($val,$i);
		}

				$orderby = substr($orderby,strlen($col)+1);
		$arr == array();
		preg_match('/([A-Z_0-9]*)/i',$orderby,$arr);

		if (trim($arr[1]) == 'DESC') $sortf = 'adodb_cmpr';
		else $sortf = 'adodb_cmp';

				usort($sorta, $sortf);

				$arr2 = array();
		if ($this->_skiprow1) $arr2[] = $projarray[0];
		foreach($sorta as $v) {
			$arr2[] = $projarray[$v[1]];
		}

		$this->_rezarray = $arr2;
		return $this;
	}

	
	function ErrorMsg()
	{
			return '';
	}

	
	function ErrorNo()
	{
		return 0;
	}

		function _close()
	{
	}


}




class ADORecordSet_text extends ADORecordSet_array
{

	var $databaseType = "text";

	function __construct(&$conn,$mode=false)
	{
		parent::__construct();
		$this->InitArray($conn->_rezarray,$conn->_reztypes,$conn->_reznames);
		$conn->_rezarray = false;
	}

} 

} 