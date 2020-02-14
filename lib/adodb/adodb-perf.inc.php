<?php


if (!defined('ADODB_DIR')) include_once(dirname(__FILE__).'/adodb.inc.php');
include_once(ADODB_DIR.'/tohtml.inc.php');

define( 'ADODB_OPT_HIGH', 2);
define( 'ADODB_OPT_LOW', 1);

global $ADODB_PERF_MIN;
$ADODB_PERF_MIN = 0.05; 

function adodb_getmem()
{
	if (function_exists('memory_get_usage'))
		return (integer) ((memory_get_usage()+512)/1024);

	$pid = getmypid();

	if ( strncmp(strtoupper(PHP_OS),'WIN',3)==0) {
		$output = array();

		exec('tasklist /FI "PID eq ' . $pid. '" /FO LIST', $output);
		return substr($output[5], strpos($output[5], ':') + 1);
	}

	
	exec("ps --pid $pid --no-headers -o%mem,size", $output);
	if (sizeof($output) == 0) return 0;

	$memarr = explode(' ',$output[0]);
	if (sizeof($memarr)>=2) return (integer) $memarr[1];

	return 0;
}

function adodb_round($n,$prec)
{
	return number_format($n, $prec, '.', '');
}


function adodb_microtime()
{
	return microtime(true);
}


function adodb_log_sql(&$connx,$sql,$inputarr)
{
    $perf_table = adodb_perf::table();
	$connx->fnExecute = false;
	$a0 = microtime(true);
	$rs = $connx->Execute($sql,$inputarr);
	$a1 = microtime(true);

	if (!empty($connx->_logsql) && (empty($connx->_logsqlErrors) || !$rs)) {
	global $ADODB_LOG_CONN;

		if (!empty($ADODB_LOG_CONN)) {
			$conn = $ADODB_LOG_CONN;
			if ($conn->databaseType != $connx->databaseType)
				$prefix = '/*dbx='.$connx->databaseType .'*/ ';
			else
				$prefix = '';
		} else {
			$conn = $connx;
			$prefix = '';
		}

		$conn->_logsql = false; 		$dbT = $conn->databaseType;

		$time = $a1 - $a0;

		if (!$rs) {
			$errM = $connx->ErrorMsg();
			$errN = $connx->ErrorNo();
			$conn->lastInsID = 0;
			$tracer = substr('ERROR: '.htmlspecialchars($errM),0,250);
		} else {
			$tracer = '';
			$errM = '';
			$errN = 0;
			$dbg = $conn->debug;
			$conn->debug = false;
			if (!is_object($rs) || $rs->dataProvider == 'empty')
				$conn->_affected = $conn->affected_rows(true);
			$conn->lastInsID = @$conn->Insert_ID();
			$conn->debug = $dbg;
		}
		if (isset($_SERVER['HTTP_HOST'])) {
			$tracer .= '<br>'.$_SERVER['HTTP_HOST'];
			if (isset($_SERVER['PHP_SELF'])) $tracer .= htmlspecialchars($_SERVER['PHP_SELF']);
		} else
			if (isset($_SERVER['PHP_SELF'])) $tracer .= '<br>'.htmlspecialchars($_SERVER['PHP_SELF']);
		
		$tracer = (string) substr($tracer,0,500);

		if (is_array($inputarr)) {
			if (is_array(reset($inputarr))) $params = 'Array sizeof='.sizeof($inputarr);
			else {
												$xar_params = $inputarr;
				foreach ($xar_params as $xar_param_key => $xar_param) {
					if (gettype($xar_param) == 'string')
					$xar_params[$xar_param_key] = '"' . $xar_param . '"';
				}
				$params = implode(', ', $xar_params);
				if (strlen($params) >= 3000) $params = substr($params, 0, 3000);
			}
		} else {
			$params = '';
		}

		if (is_array($sql)) $sql = $sql[0];
		if ($prefix) $sql = $prefix.$sql;
		$arr = array('b'=>strlen($sql).'.'.crc32($sql),
					'c'=>substr($sql,0,3900), 'd'=>$params,'e'=>$tracer,'f'=>adodb_round($time,6));
				$saved = $conn->debug;
		$conn->debug = 0;

		$d = $conn->sysTimeStamp;
		if (empty($d)) $d = date("'Y-m-d H:i:s'");
		if ($conn->dataProvider == 'oci8' && $dbT != 'oci8po') {
			$isql = "insert into $perf_table values($d,:b,:c,:d,:e,:f)";
		} else if ($dbT == 'odbc_mssql' || $dbT == 'informix' || strncmp($dbT,'odbtp',4)==0) {
			$timer = $arr['f'];
			if ($dbT == 'informix') $sql2 = substr($sql2,0,230);

			$sql1 = $conn->qstr($arr['b']);
			$sql2 = $conn->qstr($arr['c']);
			$params = $conn->qstr($arr['d']);
			$tracer = $conn->qstr($arr['e']);

			$isql = "insert into $perf_table (created,sql0,sql1,params,tracer,timer) values($d,$sql1,$sql2,$params,$tracer,$timer)";
			if ($dbT == 'informix') $isql = str_replace(chr(10),' ',$isql);
			$arr = false;
		} else {
			if ($dbT == 'db2') $arr['f'] = (float) $arr['f'];
			$isql = "insert into $perf_table (created,sql0,sql1,params,tracer,timer) values( $d,?,?,?,?,?)";
		}

		global $ADODB_PERF_MIN;
		if ($errN != 0 || $time >= $ADODB_PERF_MIN) {
			$ok = $conn->Execute($isql,$arr);
		} else
			$ok = true;

		$conn->debug = $saved;

		if ($ok) {
			$conn->_logsql = true;
		} else {
			$err2 = $conn->ErrorMsg();
			$conn->_logsql = true; 			$perf = NewPerfMonitor($conn);
			if ($perf) {
				if ($perf->CreateLogTable()) $ok = $conn->Execute($isql,$arr);
			} else {
				$ok = $conn->Execute("create table $perf_table (
				created varchar(50),
				sql0 varchar(250),
				sql1 varchar(4000),
				params varchar(3000),
				tracer varchar(500),
				timer decimal(16,6))");
			}
			if (!$ok) {
				ADOConnection::outp( "<p><b>LOGSQL Insert Failed</b>: $isql<br>$err2</p>");
				$conn->_logsql = false;
			}
		}
		$connx->_errorMsg = $errM;
		$connx->_errorCode = $errN;
	}
	$connx->fnExecute = 'adodb_log_sql';
	return $rs;
}




class adodb_perf {
	var $conn;
	var $color = '#F0F0F0';
	var $table = '<table border=1 bgcolor=white>';
	var $titles = '<tr><td><b>Parameter</b></td><td><b>Value</b></td><td><b>Description</b></td></tr>';
	var $warnRatio = 90;
	var $tablesSQL = false;
	var $cliFormat = "%32s => %s \r\n";
	var $sql1 = 'sql1';  	var $explain = true;
	var $helpurl = '<a href="http://adodb.sourceforge.net/docs-adodb.htm#logsql">LogSQL help</a>';
	var $createTableSQL = false;
	var $maxLength = 2000;

        static function table($newtable = false)
    {
        static $_table;

        if (!empty($newtable))  $_table = $newtable;
		if (empty($_table)) $_table = 'adodb_logsql';
        return $_table;
    }

		function _CPULoad()
	{

						if (strncmp(PHP_OS,'WIN',3)==0) {
			if (PHP_VERSION == '5.0.0') return false;
			if (PHP_VERSION == '5.0.1') return false;
			if (PHP_VERSION == '5.0.2') return false;
			if (PHP_VERSION == '5.0.3') return false;
			if (PHP_VERSION == '4.3.10') return false; 
			static $FAIL = false;
			if ($FAIL) return false;

			$objName = "winmgmts:{impersonationLevel=impersonate}!\\\\.\\root\\CIMV2";
			$myQuery = "SELECT * FROM Win32_PerfFormattedData_PerfOS_Processor WHERE Name = '_Total'";

			try {
				@$objWMIService = new COM($objName);
				if (!$objWMIService) {
					$FAIL = true;
					return false;
				}

				$info[0] = -1;
				$info[1] = 0;
				$info[2] = 0;
				$info[3] = 0;
				foreach($objWMIService->ExecQuery($myQuery) as $objItem)  {
						$info[0] = $objItem->PercentProcessorTime();
				}

			} catch(Exception $e) {
				$FAIL = true;
				echo $e->getMessage();
				return false;
			}

			return $info;
		}

				$statfile = '/proc/stat';
		if (!file_exists($statfile)) return false;

		$fd = fopen($statfile,"r");
		if (!$fd) return false;

		$statinfo = explode("\n",fgets($fd, 1024));
		fclose($fd);
		foreach($statinfo as $line) {
			$info = explode(" ",$line);
			if($info[0]=="cpu") {
				array_shift($info);  				if(!$info[0]) array_shift($info); 				return $info;
			}
		}

		return false;

	}

	
	function MemInfo()
	{
		
	}


	
	var $_lastLoad;
	function CPULoad()
	{
		$info = $this->_CPULoad();
		if (!$info) return false;

		if (strncmp(PHP_OS,'WIN',3)==0) {
			return (integer) $info[0];
		}else {
			if (empty($this->_lastLoad)) {
				sleep(1);
				$this->_lastLoad = $info;
				$info = $this->_CPULoad();
			}

			$last = $this->_lastLoad;
			$this->_lastLoad = $info;

			$d_user = $info[0] - $last[0];
			$d_nice = $info[1] - $last[1];
			$d_system = $info[2] - $last[2];
			$d_idle = $info[3] - $last[3];

			
			$total=$d_user+$d_nice+$d_system+$d_idle;
			if ($total<1) $total=1;
			return 100*($d_user+$d_nice+$d_system)/$total;
		}
	}

	function Tracer($sql)
	{
        $perf_table = adodb_perf::table();
		$saveE = $this->conn->fnExecute;
		$this->conn->fnExecute = false;

		global $ADODB_FETCH_MODE;
		$save = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		if ($this->conn->fetchMode !== false) $savem = $this->conn->SetFetchMode(false);

		$sqlq = $this->conn->qstr($sql);
		$arr = $this->conn->GetArray(
"select count(*),tracer
	from $perf_table where sql1=$sqlq
	group by tracer
	order by 1 desc");
		$s = '';
		if ($arr) {
			$s .= '<h3>Scripts Affected</h3>';
			foreach($arr as $k) {
				$s .= sprintf("%4d",$k[0]).' &nbsp; '.strip_tags($k[1]).'<br>';
			}
		}

		if (isset($savem)) $this->conn->SetFetchMode($savem);
		$ADODB_CACHE_MODE = $save;
		$this->conn->fnExecute = $saveE;
		return $s;
	}

	
	function Explain($sql,$partial=false)
	{
		return false;
	}

	function InvalidSQL($numsql = 10)
	{

		if (isset($_GET['sql'])) return;
		$s = '<h3>Invalid SQL</h3>';
		$saveE = $this->conn->fnExecute;
		$this->conn->fnExecute = false;
        $perf_table = adodb_perf::table();
		$rs = $this->conn->SelectLimit("select distinct count(*),sql1,tracer as error_msg from $perf_table where tracer like 'ERROR:%' group by sql1,tracer order by 1 desc",$numsql);		$this->conn->fnExecute = $saveE;
		if ($rs) {
			$s .= rs2html($rs,false,false,false,false);
		} else
			return "<p>$this->helpurl. ".$this->conn->ErrorMsg()."</p>";

		return $s;
	}


	
	function _SuspiciousSQL($numsql = 10)
	{
		global $ADODB_FETCH_MODE;

            $perf_table = adodb_perf::table();
			$saveE = $this->conn->fnExecute;
			$this->conn->fnExecute = false;

			if (isset($_GET['exps']) && isset($_GET['sql'])) {
				$partial = !empty($_GET['part']);
				echo "<a name=explain></a>".$this->Explain($_GET['sql'],$partial)."\n";
			}

			if (isset($_GET['sql'])) return;
			$sql1 = $this->sql1;

			$save = $ADODB_FETCH_MODE;
			$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
			if ($this->conn->fetchMode !== false) $savem = $this->conn->SetFetchMode(false);
						$rs = $this->conn->SelectLimit(
			"select avg(timer) as avg_timer,$sql1,count(*),max(timer) as max_timer,min(timer) as min_timer
				from $perf_table
				where {$this->conn->upperCase}({$this->conn->substr}(sql0,1,5)) not in ('DROP ','INSER','COMMI','CREAT')
				and (tracer is null or tracer not like 'ERROR:%')
				group by sql1
				order by 1 desc",$numsql);
			if (isset($savem)) $this->conn->SetFetchMode($savem);
			$ADODB_FETCH_MODE = $save;
			$this->conn->fnExecute = $saveE;

			if (!$rs) return "<p>$this->helpurl. ".$this->conn->ErrorMsg()."</p>";
			$s = "<h3>Suspicious SQL</h3>
<font size=1>The following SQL have high average execution times</font><br>
<table border=1 bgcolor=white><tr><td><b>Avg Time</b><td><b>Count</b><td><b>SQL</b><td><b>Max</b><td><b>Min</b></tr>\n";
			$max = $this->maxLength;
			while (!$rs->EOF) {
				$sql = $rs->fields[1];
				$raw = urlencode($sql);
				if (strlen($raw)>$max-100) {
					$sql2 = substr($sql,0,$max-500);
					$raw = urlencode($sql2).'&part='.crc32($sql);
				}
				$prefix = "<a target=sql".rand()." href=\"?hidem=1&exps=1&sql=".$raw."&x#explain\">";
				$suffix = "</a>";
				if ($this->explain == false || strlen($prefix)>$max) {
					$suffix = ' ... <i>String too long for GET parameter: '.strlen($prefix).'</i>';
					$prefix = '';
				}
				$s .= "<tr><td>".adodb_round($rs->fields[0],6)."<td align=right>".$rs->fields[2]."<td><font size=-1>".$prefix.htmlspecialchars($sql).$suffix."</font>".
					"<td>".$rs->fields[3]."<td>".$rs->fields[4]."</tr>";
				$rs->MoveNext();
			}
			return $s."</table>";

	}

	function CheckMemory()
	{
		return '';
	}


	function SuspiciousSQL($numsql=10)
	{
		return adodb_perf::_SuspiciousSQL($numsql);
	}

	function ExpensiveSQL($numsql=10)
	{
		return adodb_perf::_ExpensiveSQL($numsql);
	}


	
	function _ExpensiveSQL($numsql = 10)
	{
		global $ADODB_FETCH_MODE;

            $perf_table = adodb_perf::table();
			$saveE = $this->conn->fnExecute;
			$this->conn->fnExecute = false;

			if (isset($_GET['expe']) && isset($_GET['sql'])) {
				$partial = !empty($_GET['part']);
				echo "<a name=explain></a>".$this->Explain($_GET['sql'],$partial)."\n";
			}

			if (isset($_GET['sql'])) return;

			$sql1 = $this->sql1;
			$save = $ADODB_FETCH_MODE;
			$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
			if ($this->conn->fetchMode !== false) $savem = $this->conn->SetFetchMode(false);

			$rs = $this->conn->SelectLimit(
			"select sum(timer) as total,$sql1,count(*),max(timer) as max_timer,min(timer) as min_timer
				from $perf_table
				where {$this->conn->upperCase}({$this->conn->substr}(sql0,1,5))  not in ('DROP ','INSER','COMMI','CREAT')
				and (tracer is null or tracer not like 'ERROR:%')
				group by sql1
				having count(*)>1
				order by 1 desc",$numsql);
			if (isset($savem)) $this->conn->SetFetchMode($savem);
			$this->conn->fnExecute = $saveE;
			$ADODB_FETCH_MODE = $save;
			if (!$rs) return "<p>$this->helpurl. ".$this->conn->ErrorMsg()."</p>";
			$s = "<h3>Expensive SQL</h3>
<font size=1>Tuning the following SQL could reduce the server load substantially</font><br>
<table border=1 bgcolor=white><tr><td><b>Load</b><td><b>Count</b><td><b>SQL</b><td><b>Max</b><td><b>Min</b></tr>\n";
			$max = $this->maxLength;
			while (!$rs->EOF) {
				$sql = $rs->fields[1];
				$raw = urlencode($sql);
				if (strlen($raw)>$max-100) {
					$sql2 = substr($sql,0,$max-500);
					$raw = urlencode($sql2).'&part='.crc32($sql);
				}
				$prefix = "<a target=sqle".rand()." href=\"?hidem=1&expe=1&sql=".$raw."&x#explain\">";
				$suffix = "</a>";
				if($this->explain == false || strlen($prefix>$max)) {
					$prefix = '';
					$suffix = '';
				}
				$s .= "<tr><td>".adodb_round($rs->fields[0],6)."<td align=right>".$rs->fields[2]."<td><font size=-1>".$prefix.htmlspecialchars($sql).$suffix."</font>".
					"<td>".$rs->fields[3]."<td>".$rs->fields[4]."</tr>";
				$rs->MoveNext();
			}
			return $s."</table>";
	}

	
	function DBParameter($param)
	{
		if (empty($this->settings[$param])) return false;
		$sql = $this->settings[$param][1];
		return $this->_DBParameter($sql);
	}

	
	function PollParameters()
	{
		$arr[0] = (float)$this->DBParameter('data cache hit ratio');
		$arr[1] = (float)$this->DBParameter('data reads');
		$arr[2] = (float)$this->DBParameter('data writes');
		$arr[3] = (integer) $this->DBParameter('current connections');
		return $arr;
	}

	
	function _DBParameter($sql)
	{
		$savelog = $this->conn->LogSQL(false);
		if (is_array($sql)) {
		global $ADODB_FETCH_MODE;

			$sql1 = $sql[0];
			$key = $sql[1];
			if (sizeof($sql)>2) $pos = $sql[2];
			else $pos = 1;
			if (sizeof($sql)>3) $coef = $sql[3];
			else $coef = false;
			$ret = false;
			$save = $ADODB_FETCH_MODE;
			$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
			if ($this->conn->fetchMode !== false) $savem = $this->conn->SetFetchMode(false);

			$rs = $this->conn->Execute($sql1);

			if (isset($savem)) $this->conn->SetFetchMode($savem);
			$ADODB_FETCH_MODE = $save;
			if ($rs) {
				while (!$rs->EOF) {
					$keyf = reset($rs->fields);
					if (trim($keyf) == $key) {
						$ret = $rs->fields[$pos];
						if ($coef) $ret *= $coef;
						break;
					}
					$rs->MoveNext();
				}
				$rs->Close();
			}
			$this->conn->LogSQL($savelog);
			return $ret;
		} else {
			if (strncmp($sql,'=',1) == 0) {
				$fn = substr($sql,1);
				return $this->$fn();
			}
			$sql = str_replace('$DATABASE',$this->conn->database,$sql);
			$ret = $this->conn->GetOne($sql);
			$this->conn->LogSQL($savelog);

			return $ret;
		}
	}

	
	function WarnCacheRatio($val)
	{
		if ($val < $this->warnRatio)
			 return '<font color=red><b>Cache ratio should be at least '.$this->warnRatio.'%</b></font>';
		else return '';
	}

	function clearsql()
	{
		$perf_table = adodb_perf::table();
		$this->conn->Execute("delete from $perf_table where created<".$this->conn->sysTimeStamp);
	}
	
		


	function UI($pollsecs=5)
	{
	global $ADODB_LOG_CONN;

    $perf_table = adodb_perf::table();
	$conn = $this->conn;

	$app = $conn->host;
	if ($conn->host && $conn->database) $app .= ', db=';
	$app .= $conn->database;

	if ($app) $app .= ', ';
	$savelog = $this->conn->LogSQL(false);
	$info = $conn->ServerInfo();
	if (isset($_GET['clearsql'])) {
		$this->clearsql();
	}
	$this->conn->LogSQL($savelog);

	
	if (isset($_GET['sql']) && get_magic_quotes_gpc()) {
		$_GET['sql'] = $_GET['sql'] = str_replace(array("\\'",'\"'),array("'",'"'),$_GET['sql']);
	}

	if (!isset($_SESSION['ADODB_PERF_SQL'])) $nsql = $_SESSION['ADODB_PERF_SQL'] = 10;
	else  $nsql = $_SESSION['ADODB_PERF_SQL'];

	$app .= $info['description'];


	if (isset($_GET['do'])) $do = $_GET['do'];
	else if (isset($_POST['do'])) $do = $_POST['do'];
	 else if (isset($_GET['sql'])) $do = 'viewsql';
	 else $do = 'stats';

	if (isset($_GET['nsql'])) {
		if ($_GET['nsql'] > 0) $nsql = $_SESSION['ADODB_PERF_SQL'] = (integer) $_GET['nsql'];
	}
	echo "<title>ADOdb Performance Monitor on $app</title><body bgcolor=white>";
	if ($do == 'viewsql') $form = "<td><form># SQL:<input type=hidden value=viewsql name=do> <input type=text size=4 name=nsql value=$nsql><input type=submit value=Go></td></form>";
	else $form = "<td>&nbsp;</td>";

	$allowsql = !defined('ADODB_PERF_NO_RUN_SQL');
	global $ADODB_PERF_MIN;
	$app .= " (Min sql timing \$ADODB_PERF_MIN=$ADODB_PERF_MIN secs)";

	if  (empty($_GET['hidem']))
	echo "<table border=1 width=100% bgcolor=lightyellow><tr><td colspan=2>
	<b><a href=http://adodb.sourceforge.net/?perf=1>ADOdb</a> Performance Monitor</b> <font size=1>for $app</font></tr><tr><td>
	<a href=?do=stats><b>Performance Stats</b></a> &nbsp; <a href=?do=viewsql><b>View SQL</b></a>
	 &nbsp; <a href=?do=tables><b>View Tables</b></a> &nbsp; <a href=?do=poll><b>Poll Stats</b></a>",
	 $allowsql ? ' &nbsp; <a href=?do=dosql><b>Run SQL</b></a>' : '',
	 "$form",
	 "</tr></table>";


	 	switch ($do) {
		default:
		case 'stats':
			if (empty($ADODB_LOG_CONN))
				echo "<p>&nbsp; <a href=\"?do=viewsql&clearsql=1\">Clear SQL Log</a><br>";
			echo $this->HealthCheck();
						echo $this->CheckMemory();
			break;
		case 'poll':
			$self = htmlspecialchars($_SERVER['PHP_SELF']);
			echo "<iframe width=720 height=80%
				src=\"{$self}?do=poll2&hidem=1\"></iframe>";
			break;
		case 'poll2':
			echo "<pre>";
			$this->Poll($pollsecs);
			break;

		case 'dosql':
			if (!$allowsql) break;

			$this->DoSQLForm();
			break;
		case 'viewsql':
			if (empty($_GET['hidem']))
				echo "&nbsp; <a href=\"?do=viewsql&clearsql=1\">Clear SQL Log</a><br>";
			echo($this->SuspiciousSQL($nsql));
			echo($this->ExpensiveSQL($nsql));
			echo($this->InvalidSQL($nsql));
			break;
		case 'tables':
			echo $this->Tables(); break;
		}
		global $ADODB_vers;
		echo "<p><div align=center><font size=1>$ADODB_vers Sponsored by <a href=http://phplens.com/>phpLens</a></font></div>";
	}

	
	function Poll($secs=5)
	{
		$this->conn->fnExecute = false;
				if ($secs <= 1) $secs = 1;
		echo "Accumulating statistics, every $secs seconds...\n";flush();
		$arro = $this->PollParameters();
		$cnt = 0;
		set_time_limit(0);
		sleep($secs);
		while (1) {

			$arr = $this->PollParameters();

			$hits   = sprintf('%2.2f',$arr[0]);
			$reads  = sprintf('%12.4f',($arr[1]-$arro[1])/$secs);
			$writes = sprintf('%12.4f',($arr[2]-$arro[2])/$secs);
			$sess = sprintf('%5d',$arr[3]);

			$load = $this->CPULoad();
			if ($load !== false) {
				$oslabel = 'WS-CPU%';
				$osval = sprintf(" %2.1f  ",(float) $load);
			}else {
				$oslabel = '';
				$osval = '';
			}
			if ($cnt % 10 == 0) echo " Time   ".$oslabel."   Hit%   Sess           Reads/s          Writes/s\n";
			$cnt += 1;
			echo date('H:i:s').'  '.$osval."$hits  $sess $reads $writes\n";
			flush();

			if (connection_aborted()) return;

			sleep($secs);
			$arro = $arr;
		}
	}

	
	function HealthCheckCLI()
	{
		return $this->HealthCheck(true);
	}


	
	function HealthCheck($cli=false)
	{
		$saveE = $this->conn->fnExecute;
		$this->conn->fnExecute = false;
		if ($cli) $html = '';
		else $html = $this->table.'<tr><td colspan=3><h3>'.$this->conn->databaseType.'</h3></td></tr>'.$this->titles;

		$oldc = false;
		$bgc = '';
		foreach($this->settings as $name => $arr) {
			if ($arr === false) break;

			if (!is_string($name)) {
				if ($cli) $html .= " -- $arr -- \n";
				else $html .= "<tr bgcolor=$this->color><td colspan=3><i>$arr</i> &nbsp;</td></tr>";
				continue;
			}

			if (!is_array($arr)) break;
			$category = $arr[0];
			$how = $arr[1];
			if (sizeof($arr)>2) $desc = $arr[2];
			else $desc = ' &nbsp; ';


			if ($category == 'HIDE') continue;

			$val = $this->_DBParameter($how);

			if ($desc && strncmp($desc,"=",1) === 0) {
				$fn = substr($desc,1);
				$desc = $this->$fn($val);
			}

			if ($val === false) {
				$m = $this->conn->ErrorMsg();
				$val = "Error: $m";
			} else {
				if (is_numeric($val) && $val >= 256*1024) {
					if ($val % (1024*1024) == 0) {
						$val /= (1024*1024);
						$val .= 'M';
					} else if ($val % 1024 == 0) {
						$val /= 1024;
						$val .= 'K';
					}
									}
			}
			if ($category != $oldc) {
				$oldc = $category;
							}
			if (strlen($desc)==0) $desc = '&nbsp;';
			if (strlen($val)==0) $val = '&nbsp;';
			if ($cli) {
				$html  .= str_replace('&nbsp;','',sprintf($this->cliFormat,strip_tags($name),strip_tags($val),strip_tags($desc)));

			}else {
				$html .= "<tr$bgc><td>".$name.'</td><td>'.$val.'</td><td>'.$desc."</td></tr>\n";
			}
		}

		if (!$cli) $html .= "</table>\n";
		$this->conn->fnExecute = $saveE;

		return $html;
	}

	function Tables($orderby='1')
	{
		if (!$this->tablesSQL) return false;

		$savelog = $this->conn->LogSQL(false);
		$rs = $this->conn->Execute($this->tablesSQL.' order by '.$orderby);
		$this->conn->LogSQL($savelog);
		$html = rs2html($rs,false,false,false,false);
		return $html;
	}


	function CreateLogTable()
	{
		if (!$this->createTableSQL) return false;

		$table = $this->table();
		$sql = str_replace('adodb_logsql',$table,$this->createTableSQL);
		$savelog = $this->conn->LogSQL(false);
		$ok = $this->conn->Execute($sql);
		$this->conn->LogSQL($savelog);
		return ($ok) ? true : false;
	}

	function DoSQLForm()
	{


		$PHP_SELF = htmlspecialchars($_SERVER['PHP_SELF']);
		$sql = isset($_REQUEST['sql']) ? $_REQUEST['sql'] : '';

		if (isset($_SESSION['phplens_sqlrows'])) $rows = $_SESSION['phplens_sqlrows'];
		else $rows = 3;

		if (isset($_REQUEST['SMALLER'])) {
			$rows /= 2;
			if ($rows < 3) $rows = 3;
			$_SESSION['phplens_sqlrows'] = $rows;
		}
		if (isset($_REQUEST['BIGGER'])) {
			$rows *= 2;
			$_SESSION['phplens_sqlrows'] = $rows;
		}

?>

<form method="POST" action="<?php echo $PHP_SELF ?>">
<table><tr>
<td> Form size: <input type="submit" value=" &lt; " name="SMALLER"><input type="submit" value=" &gt; &gt; " name="BIGGER">
</td>
<td align=right>
<input type="submit" value=" Run SQL Below " name="RUN"><input type=hidden name=do value=dosql>
</td></tr>
  <tr>
  <td colspan=2><textarea rows=<?php print $rows; ?> name="sql" cols="80"><?php print htmlspecialchars($sql) ?></textarea>
  </td>
  </tr>
 </table>
</form>

<?php
		if (!isset($_REQUEST['sql'])) return;

		$sql = $this->undomq(trim($sql));
		if (substr($sql,strlen($sql)-1) === ';') {
			$print = true;
			$sqla = $this->SplitSQL($sql);
		} else  {
			$print = false;
			$sqla = array($sql);
		}
		foreach($sqla as $sqls) {

			if (!$sqls) continue;

			if ($print) {
				print "<p>".htmlspecialchars($sqls)."</p>";
				flush();
			}
			$savelog = $this->conn->LogSQL(false);
			$rs = $this->conn->Execute($sqls);
			$this->conn->LogSQL($savelog);
			if ($rs && is_object($rs) && !$rs->EOF) {
				rs2html($rs);
				while ($rs->NextRecordSet()) {
					print "<table width=98% bgcolor=#C0C0FF><tr><td>&nbsp;</td></tr></table>";
					rs2html($rs);
				}
			} else {
				$e1 = (integer) $this->conn->ErrorNo();
				$e2 = $this->conn->ErrorMsg();
				if (($e1) || ($e2)) {
					if (empty($e1)) $e1 = '-1'; 					print ' &nbsp; '.$e1.': '.$e2;
				} else {
					print "<p>No Recordset returned<br></p>";
				}
			}
		} 	}

	function SplitSQL($sql)
	{
		$arr = explode(';',$sql);
		return $arr;
	}

	function undomq($m)
	{
	if (get_magic_quotes_gpc()) {
				$m = str_replace('\\\\','\\',$m);
		$m = str_replace('\"','"',$m);
		$m = str_replace('\\\'','\'',$m);
	}
	return $m;
}


   

    
    function OptimizeTables()
    {
        $args = func_get_args();
        $numArgs = func_num_args();

        if ( $numArgs == 0) return false;

        $mode = ADODB_OPT_LOW;
        $lastArg = $args[ $numArgs - 1];
        if ( !is_string($lastArg)) {
            $mode = $lastArg;
            unset( $args[ $numArgs - 1]);
        }

        foreach( $args as $table) {
            $this->optimizeTable( $table, $mode);
        }
	}

    
    function OptimizeTable( $table, $mode = ADODB_OPT_LOW)
    {
        ADOConnection::outp( sprintf( "<p>%s: '%s' not implemented for driver '%s'</p>", __CLASS__, __FUNCTION__, $this->conn->databaseType));
        return false;
    }

    
    function optimizeDatabase()
    {
        $conn = $this->conn;
        if ( !$conn) return false;

        $tables = $conn->MetaTables( 'TABLES');
        if ( !$tables ) return false;

        foreach( $tables as $table) {
            if ( !$this->optimizeTable( $table)) {
                return false;
            }
        }

        return true;
    }
    }
