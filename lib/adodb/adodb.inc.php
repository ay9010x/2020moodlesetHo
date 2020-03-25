<?php




if (!defined('_ADODB_LAYER')) {
	define('_ADODB_LAYER',1);

			

	
	if (!defined('ADODB_DIR')) {
		define('ADODB_DIR',dirname(__FILE__));
	}

			
	GLOBAL
		$ADODB_vers,				$ADODB_COUNTRECS,			$ADODB_CACHE_DIR,			$ADODB_CACHE,
		$ADODB_CACHE_CLASS,
		$ADODB_EXTENSION,   		$ADODB_COMPAT_FETCH, 		$ADODB_FETCH_MODE,			$ADODB_GETONE_EOF,
		$ADODB_QUOTE_FIELDNAMES; 
			
	$ADODB_EXTENSION = defined('ADODB_EXTENSION');

								
		define('ADODB_FORCE_IGNORE',0);
		define('ADODB_FORCE_NULL',1);
		define('ADODB_FORCE_EMPTY',2);
		define('ADODB_FORCE_VALUE',3);
	

	if (!$ADODB_EXTENSION || ADODB_EXTENSION < 4.0) {

		define('ADODB_BAD_RS','<p>Bad $rs in %s. Connection or SQL invalid. Try using $connection->debug=true;</p>');

			define('ADODB_TABLE_REGEX','([]0-9a-z_\:\"\`\.\@\[-]*)');

			if (!defined('ADODB_PREFETCH_ROWS')) {
			define('ADODB_PREFETCH_ROWS',10);
		}


	
		define('ADODB_FETCH_DEFAULT', 0);
		define('ADODB_FETCH_NUM', 1);
		define('ADODB_FETCH_ASSOC', 2);
		define('ADODB_FETCH_BOTH', 3);

	
		define('ADODB_ASSOC_CASE_LOWER', 0);
		define('ADODB_ASSOC_CASE_UPPER', 1);
		define('ADODB_ASSOC_CASE_NATIVE', 2);


		if (!defined('TIMESTAMP_FIRST_YEAR')) {
			define('TIMESTAMP_FIRST_YEAR',100);
		}

		
		define('DB_AUTOQUERY_INSERT', 1);
		define('DB_AUTOQUERY_UPDATE', 2);


				$_adodb_ver = (float) PHP_VERSION;
		if ($_adodb_ver >= 5.2) {
			define('ADODB_PHPVER',0x5200);
		} else if ($_adodb_ver >= 5.0) {
			define('ADODB_PHPVER',0x5000);
		} else {
			die("PHP5 or later required. You are running ".PHP_VERSION);
		}
		unset($_adodb_ver);
	}


	
	function ADODB_str_replace($src, $dest, $data) {
		if (ADODB_PHPVER >= 0x4050) {
			return str_replace($src,$dest,$data);
		}

		$s = reset($src);
		$d = reset($dest);
		while ($s !== false) {
			$data = str_replace($s,$d,$data);
			$s = next($src);
			$d = next($dest);
		}
		return $data;
	}

	function ADODB_Setup() {
	GLOBAL
		$ADODB_vers,				$ADODB_COUNTRECS,			$ADODB_CACHE_DIR,			$ADODB_FETCH_MODE,
		$ADODB_CACHE,
		$ADODB_CACHE_CLASS,
		$ADODB_FORCE_TYPE,
		$ADODB_GETONE_EOF,
		$ADODB_QUOTE_FIELDNAMES;

		if (empty($ADODB_CACHE_CLASS)) {
			$ADODB_CACHE_CLASS =  'ADODB_Cache_File' ;
		}
		$ADODB_FETCH_MODE = ADODB_FETCH_DEFAULT;
		$ADODB_FORCE_TYPE = ADODB_FORCE_VALUE;
		$ADODB_GETONE_EOF = null;

		if (!isset($ADODB_CACHE_DIR)) {
			$ADODB_CACHE_DIR = '/tmp'; 		} else {
						if (strpos($ADODB_CACHE_DIR,'://') !== false) {
				die("Illegal path http:// or ftp://");
			}
		}


								
		
		$ADODB_vers = 'v5.20.3  01-Jan-2016';

		
		if (!isset($ADODB_COUNTRECS)) {
			$ADODB_COUNTRECS = true;
		}
	}


			
	ADODB_Setup();

				
	class ADOFieldObject {
		var $name = '';
		var $max_length=0;
		var $type="";

	}


	function _adodb_safedate($s) {
		return str_replace(array("'", '\\'), '', $s);
	}

			function _adodb_safedateq($s) {
		$len = strlen($s);
		if ($s[0] !== "'") {
			$s2 = "'".$s[0];
		} else {
			$s2 = "'";
		}
		for($i=1; $i<$len; $i++) {
			$ch = $s[$i];
			if ($ch === '\\') {
				$s2 .= "'";
				break;
			} elseif ($ch === "'") {
				$s2 .= $ch;
				break;
			}

			$s2 .= $ch;
		}

		return strlen($s2) == 0 ? 'null' : $s2;
	}


	
	function ADODB_TransMonitor($dbms, $fn, $errno, $errmsg, $p1, $p2, &$thisConnection) {
				$thisConnection->_transOK = false;
		if ($thisConnection->_oldRaiseFn) {
			$fn = $thisConnection->_oldRaiseFn;
			$fn($dbms, $fn, $errno, $errmsg, $p1, $p2,$thisConnection);
		}
	}

			class ADODB_Cache_File {

		var $createdir = true; 
		function __construct() {
			global $ADODB_INCLUDED_CSV;
			if (empty($ADODB_INCLUDED_CSV)) {
				include_once(ADODB_DIR.'/adodb-csvlib.inc.php');
			}
		}

				function writecache($filename, $contents,  $debug, $secs2cache) {
			return adodb_write_file($filename, $contents,$debug);
		}

				function &readcache($filename, &$err, $secs2cache, $rsClass) {
			$rs = csv2rs($filename,$err,$secs2cache,$rsClass);
			return $rs;
		}

				function flushall($debug=false) {
			global $ADODB_CACHE_DIR;

			$rez = false;

			if (strlen($ADODB_CACHE_DIR) > 1) {
				$rez = $this->_dirFlush($ADODB_CACHE_DIR);
				if ($debug) {
					ADOConnection::outp( "flushall: $ADODB_CACHE_DIR<br><pre>\n". $rez."</pre>");
				}
			}
			return $rez;
		}

				function flushcache($f, $debug=false) {
			if (!@unlink($f)) {
				if ($debug) {
					ADOConnection::outp( "flushcache: failed for $f");
				}
			}
		}

		function getdirname($hash) {
			global $ADODB_CACHE_DIR;
			if (!isset($this->notSafeMode)) {
				$this->notSafeMode = !ini_get('safe_mode');
			}
			return ($this->notSafeMode) ? $ADODB_CACHE_DIR.'/'.substr($hash,0,2) : $ADODB_CACHE_DIR;
		}

				function createdir($hash, $debug) {
			global $ADODB_CACHE_PERMS;

			$dir = $this->getdirname($hash);
			if ($this->notSafeMode && !file_exists($dir)) {
				$oldu = umask(0);
				if (!@mkdir($dir, empty($ADODB_CACHE_PERMS) ? 0771 : $ADODB_CACHE_PERMS)) {
					if(!is_dir($dir) && $debug) {
						ADOConnection::outp("Cannot create $dir");
					}
				}
				umask($oldu);
			}

			return $dir;
		}

		
		function _dirFlush($dir, $kill_top_level = false) {
			if(!$dh = @opendir($dir)) return;

			while (($obj = readdir($dh))) {
				if($obj=='.' || $obj=='..') continue;
				$f = $dir.'/'.$obj;

				if (strpos($obj,'.cache')) {
					@unlink($f);
				}
				if (is_dir($f)) {
					$this->_dirFlush($f, true);
				}
			}
			if ($kill_top_level === true) {
				@rmdir($dir);
			}
			return true;
		}
	}

			
	
	abstract class ADOConnection {
				var $dataProvider = 'native';
	var $databaseType = '';			var $database = '';				var $host = '';					var $user = '';					var $password = '';				var $debug = false;				var $maxblobsize = 262144;		var $concat_operator = '+'; 	var $substr = 'substr';			var $length = 'length';			var $random = 'rand()';			var $upperCase = 'upper';			var $fmtDate = "'Y-m-d'";		var $fmtTimeStamp = "'Y-m-d, h:i:s A'"; 	var $true = '1';				var $false = '0';				var $replaceQuote = "\\'";		var $nameQuote = '"';			var $charSet=false;				var $metaDatabasesSQL = '';
	var $metaTablesSQL = '';
	var $uniqueOrderBy = false; 	var $emptyDate = '&nbsp;';
	var $emptyTimeStamp = '&nbsp;';
	var $lastInsID = false;
		var $hasInsertID = false;			var $hasAffectedRows = false;		var $hasTop = false;				var $hasLimit = false;				var $readOnly = false;				var $hasMoveFirst = false;			var $hasGenID = false;				var $hasTransactions = true;			var $genID = 0;						var $raiseErrorFn = false;			var $isoDates = false;				var $cacheSecs = 3600;			
		var $memCache = false; 	var $memCacheHost; 	var $memCachePort = 11211; 	var $memCacheCompress = false; 
	var $sysDate = false; 	var $sysTimeStamp = false; 	var $sysUTimeStamp = false; 	var $arrayClass = 'ADORecordSet_array'; 
	var $noNullStrings = false; 	var $numCacheHits = 0;
	var $numCacheMisses = 0;
	var $pageExecuteCountRows = true;
	var $uniqueSort = false; 	var $leftOuter = false; 	var $rightOuter = false; 	var $ansiOuter = false; 	var $autoRollback = false; 	var $poorAffectedRows = false; 
	var $fnExecute = false;
	var $fnCacheExecute = false;
	var $blobEncodeType = false; 	var $rsPrefix = "ADORecordSet_";

	var $autoCommit = true;			var $transOff = 0;				var $transCnt = 0;			
	var $fetchMode=false;

	var $null2null = 'null'; 	var $bulkBind = false; 				var $_oldRaiseFn =  false;
	var $_transOK = null;
	var $_connectionID	= false;		var $_errorMsg = false;											var $_errorCode = false;		var $_queryID = false;		
	var $_isPersistentConnection = false;		var $_bindInputArray = false; 	var $_evalAll = false;
	var $_affected = false;
	var $_logsql = false;
	var $_transmode = ''; 
	
	protected $connectionParameters = array();

	
	final public function setConnectionParameter($parameter,$value)
	{

		$this->connectionParameters[$parameter] = $value;

	}

	static function Version() {
		global $ADODB_vers;

				$regex = '^[vV]?(\d+\.\d+\.\d+'         			. '(?:-(?:'                         			. 'dev|'                            			. '(?:(?:alpha|beta|rc)(?:\.\d+))'  			. '))?)(?:\s|$)';                   
		if (!preg_match("/$regex/", $ADODB_vers, $matches)) {
									self::outp("Invalid version number: '$ADODB_vers'", 'Version');
			$regex = '^[vV]?(.*?)(?:\s|$)';
			preg_match("/$regex/", $ADODB_vers, $matches);
		}
		return $matches[1];
	}

	
	function ServerInfo() {
		return array('description' => '', 'version' => '');
	}

	function IsConnected() {
		return !empty($this->_connectionID);
	}

	function _findvers($str) {
		if (preg_match('/([0-9]+\.([0-9\.])+)/',$str, $arr)) {
			return $arr[1];
		} else {
			return '';
		}
	}

	
	static function outp($msg,$newline=true) {
		global $ADODB_FLUSH,$ADODB_OUTP;

		if (defined('ADODB_OUTP')) {
			$fn = ADODB_OUTP;
			$fn($msg,$newline);
			return;
		} else if (isset($ADODB_OUTP)) {
			$fn = $ADODB_OUTP;
			$fn($msg,$newline);
			return;
		}

		if ($newline) {
			$msg .= "<br>\n";
		}

		if (isset($_SERVER['HTTP_USER_AGENT']) || !$newline) {
			echo $msg;
		} else {
			echo strip_tags($msg);
		}


		if (!empty($ADODB_FLUSH) && ob_get_length() !== false) {
			flush(); 		}

	}

	function Time() {
		$rs = $this->_Execute("select $this->sysTimeStamp");
		if ($rs && !$rs->EOF) {
			return $this->UnixTimeStamp(reset($rs->fields));
		}

		return false;
	}

	
	function Connect($argHostname = "", $argUsername = "", $argPassword = "", $argDatabaseName = "", $forceNew = false) {
		if ($argHostname != "") {
			$this->host = $argHostname;
		}
		if ( strpos($this->host, ':') > 0 && isset($this->port) ) {
			list($this->host, $this->port) = explode(":", $this->host, 2);
        	}
		if ($argUsername != "") {
			$this->user = $argUsername;
		}
		if ($argPassword != "") {
			$this->password = 'not stored'; 		}
		if ($argDatabaseName != "") {
			$this->database = $argDatabaseName;
		}

		$this->_isPersistentConnection = false;

		if ($forceNew) {
			if ($rez=$this->_nconnect($this->host, $this->user, $argPassword, $this->database)) {
				return true;
			}
		} else {
			if ($rez=$this->_connect($this->host, $this->user, $argPassword, $this->database)) {
				return true;
			}
		}
		if (isset($rez)) {
			$err = $this->ErrorMsg();
			if (empty($err)) {
				$err = "Connection error to server '$argHostname' with user '$argUsername'";
			}
			$ret = false;
		} else {
			$err = "Missing extension for ".$this->dataProvider;
			$ret = 0;
		}
		if ($fn = $this->raiseErrorFn) {
			$fn($this->databaseType,'CONNECT',$this->ErrorNo(),$err,$this->host,$this->database,$this);
		}

		$this->_connectionID = false;
		if ($this->debug) {
			ADOConnection::outp( $this->host.': '.$err);
		}
		return $ret;
	}

	function _nconnect($argHostname, $argUsername, $argPassword, $argDatabaseName) {
		return $this->_connect($argHostname, $argUsername, $argPassword, $argDatabaseName);
	}


	
	function NConnect($argHostname = "", $argUsername = "", $argPassword = "", $argDatabaseName = "") {
		return $this->Connect($argHostname, $argUsername, $argPassword, $argDatabaseName, true);
	}

	
	function PConnect($argHostname = "", $argUsername = "", $argPassword = "", $argDatabaseName = "") {

		if (defined('ADODB_NEVER_PERSIST')) {
			return $this->Connect($argHostname,$argUsername,$argPassword,$argDatabaseName);
		}

		if ($argHostname != "") {
			$this->host = $argHostname;
		}
		if ( strpos($this->host, ':') > 0 && isset($this->port) ) {
			list($this->host, $this->port) = explode(":", $this->host, 2);
	        }
		if ($argUsername != "") {
			$this->user = $argUsername;
		}
		if ($argPassword != "") {
			$this->password = 'not stored';
		}
		if ($argDatabaseName != "") {
			$this->database = $argDatabaseName;
		}

		$this->_isPersistentConnection = true;

		if ($rez = $this->_pconnect($this->host, $this->user, $argPassword, $this->database)) {
			return true;
		}
		if (isset($rez)) {
			$err = $this->ErrorMsg();
			if (empty($err)) {
				$err = "Connection error to server '$argHostname' with user '$argUsername'";
			}
			$ret = false;
		} else {
			$err = "Missing extension for ".$this->dataProvider;
			$ret = 0;
		}
		if ($fn = $this->raiseErrorFn) {
			$fn($this->databaseType,'PCONNECT',$this->ErrorNo(),$err,$this->host,$this->database,$this);
		}

		$this->_connectionID = false;
		if ($this->debug) {
			ADOConnection::outp( $this->host.': '.$err);
		}
		return $ret;
	}

	function outp_throw($msg,$src='WARN',$sql='') {
		if (defined('ADODB_ERROR_HANDLER') &&  ADODB_ERROR_HANDLER == 'adodb_throw') {
			adodb_throw($this->databaseType,$src,-9999,$msg,$sql,false,$this);
			return;
		}
		ADOConnection::outp($msg);
	}

		function _CreateCache() {
		global $ADODB_CACHE, $ADODB_CACHE_CLASS;

		if ($this->memCache) {
			global $ADODB_INCLUDED_MEMCACHE;

			if (empty($ADODB_INCLUDED_MEMCACHE)) {
				include_once(ADODB_DIR.'/adodb-memcache.lib.inc.php');
			}
			$ADODB_CACHE = new ADODB_Cache_MemCache($this);
		} else {
			$ADODB_CACHE = new $ADODB_CACHE_CLASS($this);
		}
	}

		function SQLDate($fmt, $col=false) {
		if (!$col) {
			$col = $this->sysDate;
		}
		return $col; 	}

	
	function Prepare($sql) {
		return $sql;
	}

	
	function PrepareSP($sql,$param=true) {
		return $this->Prepare($sql,$param);
	}

	
	function Quote($s) {
		return $this->qstr($s,false);
	}

	
	function QMagic($s) {
		return $this->qstr($s,get_magic_quotes_gpc());
	}

	function q(&$s) {
								$s = $this->qstr($s,false);
	}

	
	function ErrorNative() {
		return $this->ErrorNo();
	}


	
	function nextId($seq_name) {
		return $this->GenID($seq_name);
	}

	
	function RowLock($table,$where,$col='1 as adodbignore') {
		return false;
	}

	function CommitLock($table) {
		return $this->CommitTrans();
	}

	function RollbackLock($table) {
		return $this->RollbackTrans();
	}

	
	function SetFetchMode($mode) {
		$old = $this->fetchMode;
		$this->fetchMode = $mode;

		if ($old === false) {
			global $ADODB_FETCH_MODE;
			return $ADODB_FETCH_MODE;
		}
		return $old;
	}


	
	function Query($sql, $inputarr=false) {
		$rs = $this->Execute($sql, $inputarr);
		if (!$rs && defined('ADODB_PEAR')) {
			return ADODB_PEAR_Error();
		}
		return $rs;
	}


	
	function LimitQuery($sql, $offset, $count, $params=false) {
		$rs = $this->SelectLimit($sql, $count, $offset, $params);
		if (!$rs && defined('ADODB_PEAR')) {
			return ADODB_PEAR_Error();
		}
		return $rs;
	}


	
	function Disconnect() {
		return $this->Close();
	}

	
	function Param($name,$type='C') {
		return '?';
	}

	
	function InParameter(&$stmt,&$var,$name,$maxLen=4000,$type=false) {
		return $this->Parameter($stmt,$var,$name,false,$maxLen,$type);
	}

	
	function OutParameter(&$stmt,&$var,$name,$maxLen=4000,$type=false) {
		return $this->Parameter($stmt,$var,$name,true,$maxLen,$type);

	}


	
	function Parameter(&$stmt,&$var,$name,$isOutput=false,$maxLen=4000,$type=false) {
		return false;
	}


	function IgnoreErrors($saveErrs=false) {
		if (!$saveErrs) {
			$saveErrs = array($this->raiseErrorFn,$this->_transOK);
			$this->raiseErrorFn = false;
			return $saveErrs;
		} else {
			$this->raiseErrorFn = $saveErrs[0];
			$this->_transOK = $saveErrs[1];
		}
	}

	
	function StartTrans($errfn = 'ADODB_TransMonitor') {
		if ($this->transOff > 0) {
			$this->transOff += 1;
			return true;
		}

		$this->_oldRaiseFn = $this->raiseErrorFn;
		$this->raiseErrorFn = $errfn;
		$this->_transOK = true;

		if ($this->debug && $this->transCnt > 0) {
			ADOConnection::outp("Bad Transaction: StartTrans called within BeginTrans");
		}
		$ok = $this->BeginTrans();
		$this->transOff = 1;
		return $ok;
	}


	
	function CompleteTrans($autoComplete = true) {
		if ($this->transOff > 1) {
			$this->transOff -= 1;
			return true;
		}
		$this->raiseErrorFn = $this->_oldRaiseFn;

		$this->transOff = 0;
		if ($this->_transOK && $autoComplete) {
			if (!$this->CommitTrans()) {
				$this->_transOK = false;
				if ($this->debug) {
					ADOConnection::outp("Smart Commit failed");
				}
			} else {
				if ($this->debug) {
					ADOConnection::outp("Smart Commit occurred");
				}
			}
		} else {
			$this->_transOK = false;
			$this->RollbackTrans();
			if ($this->debug) {
				ADOCOnnection::outp("Smart Rollback occurred");
			}
		}

		return $this->_transOK;
	}

	
	function FailTrans() {
		if ($this->debug)
			if ($this->transOff == 0) {
				ADOConnection::outp("FailTrans outside StartTrans/CompleteTrans");
			} else {
				ADOConnection::outp("FailTrans was called");
				adodb_backtrace();
			}
		$this->_transOK = false;
	}

	
	function HasFailedTrans() {
		if ($this->transOff > 0) {
			return $this->_transOK == false;
		}
		return false;
	}

	
	function Execute($sql,$inputarr=false) {
		if ($this->fnExecute) {
			$fn = $this->fnExecute;
			$ret = $fn($this,$sql,$inputarr);
			if (isset($ret)) {
				return $ret;
			}
		}
		if ($inputarr !== false) {
			if (!is_array($inputarr)) {
				$inputarr = array($inputarr);
			}

			$element0 = reset($inputarr);
						$array_2d = $this->bulkBind && is_array($element0) && !is_object(reset($element0));

						unset($element0);

			if (!is_array($sql) && !$this->_bindInputArray) {
								$sqlarr = explode('?',$sql);
				$nparams = sizeof($sqlarr)-1;

												if ($nparams != count($inputarr)) {
					$this->outp_throw(
						"Input array has " . count($inputarr) .
						" params, does not match query: '" . htmlspecialchars($sql) . "'",
						'Execute'
					);
					return false;
				}

				if (!$array_2d) {
					$inputarr = array($inputarr);
				}

				foreach($inputarr as $arr) {
					$sql = ''; $i = 0;
										while(list(, $v) = each($arr)) {
						$sql .= $sqlarr[$i];
																		$typ = gettype($v);
						if ($typ == 'string') {
														$sql .= $this->qstr($v);
						} else if ($typ == 'double') {
							$sql .= str_replace(',','.',$v); 						} else if ($typ == 'boolean') {
							$sql .= $v ? $this->true : $this->false;
						} else if ($typ == 'object') {
							if (method_exists($v, '__toString')) {
								$sql .= $this->qstr($v->__toString());
							} else {
								$sql .= $this->qstr((string) $v);
							}
						} else if ($v === null) {
							$sql .= 'NULL';
						} else {
							$sql .= $v;
						}
						$i += 1;

						if ($i == $nparams) {
							break;
						}
					} 					if (isset($sqlarr[$i])) {
						$sql .= $sqlarr[$i];
						if ($i+1 != sizeof($sqlarr)) {
							$this->outp_throw( "Input Array does not match ?: ".htmlspecialchars($sql),'Execute');
						}
					} else if ($i != sizeof($sqlarr)) {
						$this->outp_throw( "Input array does not match ?: ".htmlspecialchars($sql),'Execute');
					}

					$ret = $this->_Execute($sql);
					if (!$ret) {
						return $ret;
					}
				}
			} else {
				if ($array_2d) {
					if (is_string($sql)) {
						$stmt = $this->Prepare($sql);
					} else {
						$stmt = $sql;
					}

					foreach($inputarr as $arr) {
						$ret = $this->_Execute($stmt,$arr);
						if (!$ret) {
							return $ret;
						}
					}
				} else {
					$ret = $this->_Execute($sql,$inputarr);
				}
			}
		} else {
			$ret = $this->_Execute($sql,false);
		}

		return $ret;
	}

	function _Execute($sql,$inputarr=false) {
						if( is_string($sql) ) {
									$sql = ADODB_str_replace( '_ADODB_COUNT', '', $sql );
		}

		if ($this->debug) {
			global $ADODB_INCLUDED_LIB;
			if (empty($ADODB_INCLUDED_LIB)) {
				include(ADODB_DIR.'/adodb-lib.inc.php');
			}
			$this->_queryID = _adodb_debug_execute($this, $sql,$inputarr);
		} else {
			$this->_queryID = @$this->_query($sql,$inputarr);
		}

						
				if ($this->_queryID === false) {
			if ($this->debug == 99) {
				adodb_backtrace(true,5);
			}
			$fn = $this->raiseErrorFn;
			if ($fn) {
				$fn($this->databaseType,'EXECUTE',$this->ErrorNo(),$this->ErrorMsg(),$sql,$inputarr,$this);
			}
			return false;
		}

				if ($this->_queryID === true) {
			$rsclass = $this->rsPrefix.'empty';
			$rs = (class_exists($rsclass)) ? new $rsclass():  new ADORecordSet_empty();

			return $rs;
		}

				$rsclass = $this->rsPrefix.$this->databaseType;
		$rs = new $rsclass($this->_queryID,$this->fetchMode);
		$rs->connection = $this; 		$rs->Init();
		if (is_array($sql)) {
			$rs->sql = $sql[0];
		} else {
			$rs->sql = $sql;
		}
		if ($rs->_numOfRows <= 0) {
			global $ADODB_COUNTRECS;
			if ($ADODB_COUNTRECS) {
				if (!$rs->EOF) {
					$rs = $this->_rs2rs($rs,-1,-1,!is_array($sql));
					$rs->_queryID = $this->_queryID;
				} else
					$rs->_numOfRows = 0;
			}
		}
		return $rs;
	}

	function CreateSequence($seqname='adodbseq',$startID=1) {
		if (empty($this->_genSeqSQL)) {
			return false;
		}
		return $this->Execute(sprintf($this->_genSeqSQL,$seqname,$startID));
	}

	function DropSequence($seqname='adodbseq') {
		if (empty($this->_dropSeqSQL)) {
			return false;
		}
		return $this->Execute(sprintf($this->_dropSeqSQL,$seqname));
	}

	
	function GenID($seqname='adodbseq',$startID=1) {
		if (!$this->hasGenID) {
			return 0; 		}

		$getnext = sprintf($this->_genIDSQL,$seqname);

		$holdtransOK = $this->_transOK;

		$save_handler = $this->raiseErrorFn;
		$this->raiseErrorFn = '';
		@($rs = $this->Execute($getnext));
		$this->raiseErrorFn = $save_handler;

		if (!$rs) {
			$this->_transOK = $holdtransOK; 			$createseq = $this->Execute(sprintf($this->_genSeqSQL,$seqname,$startID));
			$rs = $this->Execute($getnext);
		}
		if ($rs && !$rs->EOF) {
			$this->genID = reset($rs->fields);
		} else {
			$this->genID = 0; 		}

		if ($rs) {
			$rs->Close();
		}

		return $this->genID;
	}

	
	function Insert_ID($table='',$column='') {
		if ($this->_logsql && $this->lastInsID) {
			return $this->lastInsID;
		}
		if ($this->hasInsertID) {
			return $this->_insertid($table,$column);
		}
		if ($this->debug) {
			ADOConnection::outp( '<p>Insert_ID error</p>');
			adodb_backtrace();
		}
		return false;
	}


	
	function PO_Insert_ID($table="", $id="") {
		if ($this->hasInsertID){
			return $this->Insert_ID($table,$id);
		} else {
			return $this->GetOne("SELECT MAX($id) FROM $table");
		}
	}

	
	function Affected_Rows() {
		if ($this->hasAffectedRows) {
			if ($this->fnExecute === 'adodb_log_sql') {
				if ($this->_logsql && $this->_affected !== false) {
					return $this->_affected;
				}
			}
			$val = $this->_affectedrows();
			return ($val < 0) ? false : $val;
		}

		if ($this->debug) {
			ADOConnection::outp( '<p>Affected_Rows error</p>',false);
		}
		return false;
	}


	
	function ErrorMsg() {
		if ($this->_errorMsg) {
			return '!! '.strtoupper($this->dataProvider.' '.$this->databaseType).': '.$this->_errorMsg;
		} else {
			return '';
		}
	}


	
	function ErrorNo() {
		return ($this->_errorMsg) ? -1 : 0;
	}

	function MetaError($err=false) {
		include_once(ADODB_DIR."/adodb-error.inc.php");
		if ($err === false) {
			$err = $this->ErrorNo();
		}
		return adodb_error($this->dataProvider,$this->databaseType,$err);
	}

	function MetaErrorMsg($errno) {
		include_once(ADODB_DIR."/adodb-error.inc.php");
		return adodb_errormsg($errno);
	}

	
	function MetaPrimaryKeys($table, $owner=false) {
			$p = array();
		$objs = $this->MetaColumns($table);
		if ($objs) {
			foreach($objs as $v) {
				if (!empty($v->primary_key)) {
					$p[] = $v->name;
				}
			}
		}
		if (sizeof($p)) {
			return $p;
		}
		if (function_exists('ADODB_VIEW_PRIMARYKEYS')) {
			return ADODB_VIEW_PRIMARYKEYS($this->databaseType, $this->database, $table, $owner);
		}
		return false;
	}

	
	function MetaForeignKeys($table, $owner=false, $upper=false) {
		return false;
	}
	
	function SelectDB($dbName) {return false;}


	
	function SelectLimit($sql,$nrows=-1,$offset=-1, $inputarr=false,$secs2cache=0) {
		if ($this->hasTop && $nrows > 0) {
									$ismssql = (strpos($this->databaseType,'mssql') !== false);
			if ($ismssql) {
				$isaccess = false;
			} else {
				$isaccess = (strpos($this->databaseType,'access') !== false);
			}

			if ($offset <= 0) {
										if ($isaccess) {
						$sql = preg_replace(
						'/(^\s*select\s+(distinctrow|distinct)?)/i','\\1 '.$this->hasTop.' '.((integer)$nrows).' ',$sql);

						if ($secs2cache != 0) {
							$ret = $this->CacheExecute($secs2cache, $sql,$inputarr);
						} else {
							$ret = $this->Execute($sql,$inputarr);
						}
						return $ret; 					} else if ($ismssql){
						$sql = preg_replace(
						'/(^\s*select\s+(distinctrow|distinct)?)/i','\\1 '.$this->hasTop.' '.((integer)$nrows).' ',$sql);
					} else {
						$sql = preg_replace(
						'/(^\s*select\s)/i','\\1 '.$this->hasTop.' '.((integer)$nrows).' ',$sql);
					}
			} else {
				$nn = $nrows + $offset;
				if ($isaccess || $ismssql) {
					$sql = preg_replace(
					'/(^\s*select\s+(distinctrow|distinct)?)/i','\\1 '.$this->hasTop.' '.$nn.' ',$sql);
				} else {
					$sql = preg_replace(
					'/(^\s*select\s)/i','\\1 '.$this->hasTop.' '.$nn.' ',$sql);
				}
			}
		}

						global $ADODB_COUNTRECS;

		$savec = $ADODB_COUNTRECS;
		$ADODB_COUNTRECS = false;


		if ($secs2cache != 0) {
			$rs = $this->CacheExecute($secs2cache,$sql,$inputarr);
		} else {
			$rs = $this->Execute($sql,$inputarr);
		}

		$ADODB_COUNTRECS = $savec;
		if ($rs && !$rs->EOF) {
			$rs = $this->_rs2rs($rs,$nrows,$offset);
		}
				return $rs;
	}

	
	function SerializableRS(&$rs) {
		$rs2 = $this->_rs2rs($rs);
		$ignore = false;
		$rs2->connection = $ignore;

		return $rs2;
	}

	
	function &_rs2rs(&$rs,$nrows=-1,$offset=-1,$close=true) {
		if (! $rs) {
			return false;
		}
		$dbtype = $rs->databaseType;
		if (!$dbtype) {
			$rs = $rs;  			return $rs;
		}
		if (($dbtype == 'array' || $dbtype == 'csv') && $nrows == -1 && $offset == -1) {
			$rs->MoveFirst();
			$rs = $rs; 			return $rs;
		}
		$flds = array();
		for ($i=0, $max=$rs->FieldCount(); $i < $max; $i++) {
			$flds[] = $rs->FetchField($i);
		}

		$arr = $rs->GetArrayLimit($nrows,$offset);
				if ($close) {
			$rs->Close();
		}

		$arrayClass = $this->arrayClass;

		$rs2 = new $arrayClass();
		$rs2->connection = $this;
		$rs2->sql = $rs->sql;
		$rs2->dataProvider = $this->dataProvider;
		$rs2->InitArrayFields($arr,$flds);
		$rs2->fetchMode = isset($rs->adodbFetchMode) ? $rs->adodbFetchMode : $rs->fetchMode;
		return $rs2;
	}

	
	function GetAll($sql, $inputarr=false) {
		$arr = $this->GetArray($sql,$inputarr);
		return $arr;
	}

	function GetAssoc($sql, $inputarr=false,$force_array = false, $first2cols = false) {
		$rs = $this->Execute($sql, $inputarr);
		if (!$rs) {
			return false;
		}
		$arr = $rs->GetAssoc($force_array,$first2cols);
		return $arr;
	}

	function CacheGetAssoc($secs2cache, $sql=false, $inputarr=false,$force_array = false, $first2cols = false) {
		if (!is_numeric($secs2cache)) {
			$first2cols = $force_array;
			$force_array = $inputarr;
		}
		$rs = $this->CacheExecute($secs2cache, $sql, $inputarr);
		if (!$rs) {
			return false;
		}
		$arr = $rs->GetAssoc($force_array,$first2cols);
		return $arr;
	}

	
	function GetOne($sql,$inputarr=false) {
		global $ADODB_COUNTRECS,$ADODB_GETONE_EOF;

		$crecs = $ADODB_COUNTRECS;
		$ADODB_COUNTRECS = false;

		$ret = false;
		$rs = $this->Execute($sql,$inputarr);
		if ($rs) {
			if ($rs->EOF) {
				$ret = $ADODB_GETONE_EOF;
			} else {
				$ret = reset($rs->fields);
			}

			$rs->Close();
		}
		$ADODB_COUNTRECS = $crecs;
		return $ret;
	}

		function GetMedian($table, $field,$where = '') {
		$total = $this->GetOne("select count(*) from $table $where");
		if (!$total) {
			return false;
		}

		$midrow = (integer) ($total/2);
		$rs = $this->SelectLimit("select $field from $table $where order by 1",1,$midrow);
		if ($rs && !$rs->EOF) {
			return reset($rs->fields);
		}
		return false;
	}


	function CacheGetOne($secs2cache,$sql=false,$inputarr=false) {
		global $ADODB_GETONE_EOF;

		$ret = false;
		$rs = $this->CacheExecute($secs2cache,$sql,$inputarr);
		if ($rs) {
			if ($rs->EOF) {
				$ret = $ADODB_GETONE_EOF;
			} else {
				$ret = reset($rs->fields);
			}
			$rs->Close();
		}

		return $ret;
	}

	function GetCol($sql, $inputarr = false, $trim = false) {

		$rs = $this->Execute($sql, $inputarr);
		if ($rs) {
			$rv = array();
			if ($trim) {
				while (!$rs->EOF) {
					$rv[] = trim(reset($rs->fields));
					$rs->MoveNext();
				}
			} else {
				while (!$rs->EOF) {
					$rv[] = reset($rs->fields);
					$rs->MoveNext();
				}
			}
			$rs->Close();
		} else {
			$rv = false;
		}
		return $rv;
	}

	function CacheGetCol($secs, $sql = false, $inputarr = false,$trim=false) {
		$rs = $this->CacheExecute($secs, $sql, $inputarr);
		if ($rs) {
			$rv = array();
			if ($trim) {
				while (!$rs->EOF) {
					$rv[] = trim(reset($rs->fields));
					$rs->MoveNext();
				}
			} else {
				while (!$rs->EOF) {
					$rv[] = reset($rs->fields);
					$rs->MoveNext();
				}
			}
			$rs->Close();
		} else
			$rv = false;

		return $rv;
	}

	function Transpose(&$rs,$addfieldnames=true) {
		$rs2 = $this->_rs2rs($rs);
		if (!$rs2) {
			return false;
		}

		$rs2->_transpose($addfieldnames);
		return $rs2;
	}

	
	function OffsetDate($dayFraction,$date=false) {
		if (!$date) {
			$date = $this->sysDate;
		}
		return  '('.$date.'+'.$dayFraction.')';
	}


	
	function GetArray($sql,$inputarr=false) {
		global $ADODB_COUNTRECS;

		$savec = $ADODB_COUNTRECS;
		$ADODB_COUNTRECS = false;
		$rs = $this->Execute($sql,$inputarr);
		$ADODB_COUNTRECS = $savec;
		if (!$rs)
			if (defined('ADODB_PEAR')) {
				$cls = ADODB_PEAR_Error();
				return $cls;
			} else {
				return false;
			}
		$arr = $rs->GetArray();
		$rs->Close();
		return $arr;
	}

	function CacheGetAll($secs2cache,$sql=false,$inputarr=false) {
		$arr = $this->CacheGetArray($secs2cache,$sql,$inputarr);
		return $arr;
	}

	function CacheGetArray($secs2cache,$sql=false,$inputarr=false) {
		global $ADODB_COUNTRECS;

		$savec = $ADODB_COUNTRECS;
		$ADODB_COUNTRECS = false;
		$rs = $this->CacheExecute($secs2cache,$sql,$inputarr);
		$ADODB_COUNTRECS = $savec;

		if (!$rs)
			if (defined('ADODB_PEAR')) {
				$cls = ADODB_PEAR_Error();
				return $cls;
			} else {
				return false;
			}
		$arr = $rs->GetArray();
		$rs->Close();
		return $arr;
	}

	function GetRandRow($sql, $arr= false) {
		$rezarr = $this->GetAll($sql, $arr);
		$sz = sizeof($rezarr);
		return $rezarr[abs(rand()) % $sz];
	}

	
	function GetRow($sql,$inputarr=false) {
		global $ADODB_COUNTRECS;

		$crecs = $ADODB_COUNTRECS;
		$ADODB_COUNTRECS = false;

		$rs = $this->Execute($sql,$inputarr);

		$ADODB_COUNTRECS = $crecs;
		if ($rs) {
			if (!$rs->EOF) {
				$arr = $rs->fields;
			} else {
				$arr = array();
			}
			$rs->Close();
			return $arr;
		}

		return false;
	}

	function CacheGetRow($secs2cache,$sql=false,$inputarr=false) {
		$rs = $this->CacheExecute($secs2cache,$sql,$inputarr);
		if ($rs) {
			if (!$rs->EOF) {
				$arr = $rs->fields;
			} else {
				$arr = array();
			}

			$rs->Close();
			return $arr;
		}
		return false;
	}

	

	function Replace($table, $fieldArray, $keyCol, $autoQuote=false, $has_autoinc=false) {
		global $ADODB_INCLUDED_LIB;
		if (empty($ADODB_INCLUDED_LIB)) {
			include(ADODB_DIR.'/adodb-lib.inc.php');
		}

		return _adodb_replace($this, $table, $fieldArray, $keyCol, $autoQuote, $has_autoinc);
	}


	
	function CacheSelectLimit($secs2cache,$sql,$nrows=-1,$offset=-1,$inputarr=false) {
		if (!is_numeric($secs2cache)) {
			if ($sql === false) {
				$sql = -1;
			}
			if ($offset == -1) {
				$offset = false;
			}
															$rs = $this->SelectLimit($secs2cache,$sql,$nrows,$offset,$this->cacheSecs);
		} else {
			if ($sql === false) {
				$this->outp_throw("Warning: \$sql missing from CacheSelectLimit()",'CacheSelectLimit');
			}
			$rs = $this->SelectLimit($sql,$nrows,$offset,$inputarr,$secs2cache);
		}
		return $rs;
	}

	
	function CacheFlush($sql=false,$inputarr=false) {
		global $ADODB_CACHE_DIR, $ADODB_CACHE;

				if (empty($ADODB_CACHE)) {
			$this->_CreateCache();
		}

		if (!$sql) {
			$ADODB_CACHE->flushall($this->debug);
			return;
		}

		$f = $this->_gencachename($sql.serialize($inputarr),false);
		return $ADODB_CACHE->flushcache($f, $this->debug);
	}


	
	function _gencachename($sql,$createdir) {
		global $ADODB_CACHE, $ADODB_CACHE_DIR;

		if ($this->fetchMode === false) {
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;
		} else {
			$mode = $this->fetchMode;
		}
		$m = md5($sql.$this->databaseType.$this->database.$this->user.$mode);
		if (!$ADODB_CACHE->createdir) {
			return $m;
		}
		if (!$createdir) {
			$dir = $ADODB_CACHE->getdirname($m);
		} else {
			$dir = $ADODB_CACHE->createdir($m, $this->debug);
		}

		return $dir.'/adodb_'.$m.'.cache';
	}


	
	function CacheExecute($secs2cache,$sql=false,$inputarr=false) {
		global $ADODB_CACHE;

		if (empty($ADODB_CACHE)) {
			$this->_CreateCache();
		}

		if (!is_numeric($secs2cache)) {
			$inputarr = $sql;
			$sql = $secs2cache;
			$secs2cache = $this->cacheSecs;
		}

		if (is_array($sql)) {
			$sqlparam = $sql;
			$sql = $sql[0];
		} else
			$sqlparam = $sql;


		$md5file = $this->_gencachename($sql.serialize($inputarr),true);
		$err = '';

		if ($secs2cache > 0){
			$rs = $ADODB_CACHE->readcache($md5file,$err,$secs2cache,$this->arrayClass);
			$this->numCacheHits += 1;
		} else {
			$err='Timeout 1';
			$rs = false;
			$this->numCacheMisses += 1;
		}

		if (!$rs) {
					if ($this->debug) {
				if (get_magic_quotes_runtime() && !$this->memCache) {
					ADOConnection::outp("Please disable magic_quotes_runtime - it corrupts cache files :(");
				}
				if ($this->debug !== -1) {
					ADOConnection::outp( " $md5file cache failure: $err (this is a notice and not an error)");
				}
			}

			$rs = $this->Execute($sqlparam,$inputarr);

			if ($rs) {
				$eof = $rs->EOF;
				$rs = $this->_rs2rs($rs); 				$rs->timeCreated = time(); 				$txt = _rs2serialize($rs,false,$sql); 
				$ok = $ADODB_CACHE->writecache($md5file,$txt,$this->debug, $secs2cache);
				if (!$ok) {
					if ($ok === false) {
						$em = 'Cache write error';
						$en = -32000;

						if ($fn = $this->raiseErrorFn) {
							$fn($this->databaseType,'CacheExecute', $en, $em, $md5file,$sql,$this);
						}
					} else {
						$em = 'Cache file locked warning';
						$en = -32001;
											}

					if ($this->debug) {
						ADOConnection::outp( " ".$em);
					}
				}
				if ($rs->EOF && !$eof) {
					$rs->MoveFirst();
										$rs->connection = $this; 				}

			} else if (!$this->memCache) {
				$ADODB_CACHE->flushcache($md5file);
			}
		} else {
			$this->_errorMsg = '';
			$this->_errorCode = 0;

			if ($this->fnCacheExecute) {
				$fn = $this->fnCacheExecute;
				$fn($this, $secs2cache, $sql, $inputarr);
			}
						$rs->connection = $this; 			if ($this->debug){
				if ($this->debug == 99) {
					adodb_backtrace();
				}
				$inBrowser = isset($_SERVER['HTTP_USER_AGENT']);
				$ttl = $rs->timeCreated + $secs2cache - time();
				$s = is_array($sql) ? $sql[0] : $sql;
				if ($inBrowser) {
					$s = '<i>'.htmlspecialchars($s).'</i>';
				}

				ADOConnection::outp( " $md5file reloaded, ttl=$ttl [ $s ]");
			}
		}
		return $rs;
	}


	
	function AutoExecute($table, $fields_values, $mode = 'INSERT', $where = false, $forceUpdate = true, $magicq = false) {
		if ($where === false && ($mode == 'UPDATE' || $mode == 2 ) ) {
			$this->outp_throw('AutoExecute: Illegal mode=UPDATE with empty WHERE clause', 'AutoExecute');
			return false;
		}

		$sql = "SELECT * FROM $table";
		$rs = $this->SelectLimit($sql, 1);
		if (!$rs) {
			return false; 		}

		$rs->tableName = $table;
		if ($where !== false) {
			$sql .= " WHERE $where";
		}
		$rs->sql = $sql;

		switch($mode) {
			case 'UPDATE':
			case DB_AUTOQUERY_UPDATE:
				$sql = $this->GetUpdateSQL($rs, $fields_values, $forceUpdate, $magicq);
				break;
			case 'INSERT':
			case DB_AUTOQUERY_INSERT:
				$sql = $this->GetInsertSQL($rs, $fields_values, $magicq);
				break;
			default:
				$this->outp_throw("AutoExecute: Unknown mode=$mode", 'AutoExecute');
				return false;
		}
		return $sql && $this->Execute($sql);
	}


	
	function GetUpdateSQL(&$rs, $arrFields,$forceUpdate=false,$magicq=false,$force=null) {
		global $ADODB_INCLUDED_LIB;

								if (!isset($force)) {
			global $ADODB_FORCE_TYPE;
			$force = $ADODB_FORCE_TYPE;
		}
		
		if (empty($ADODB_INCLUDED_LIB)) {
			include(ADODB_DIR.'/adodb-lib.inc.php');
		}
		return _adodb_getupdatesql($this,$rs,$arrFields,$forceUpdate,$magicq,$force);
	}

	
	function GetInsertSQL(&$rs, $arrFields,$magicq=false,$force=null) {
		global $ADODB_INCLUDED_LIB;
		if (!isset($force)) {
			global $ADODB_FORCE_TYPE;
			$force = $ADODB_FORCE_TYPE;
		}
		if (empty($ADODB_INCLUDED_LIB)) {
			include(ADODB_DIR.'/adodb-lib.inc.php');
		}
		return _adodb_getinsertsql($this,$rs,$arrFields,$magicq,$force);
	}


	
	function UpdateBlob($table,$column,$val,$where,$blobtype='BLOB') {
		return $this->Execute("UPDATE $table SET $column=? WHERE $where",array($val)) != false;
	}

	
	function UpdateBlobFile($table,$column,$path,$where,$blobtype='BLOB') {
		$fd = fopen($path,'rb');
		if ($fd === false) {
			return false;
		}
		$val = fread($fd,filesize($path));
		fclose($fd);
		return $this->UpdateBlob($table,$column,$val,$where,$blobtype);
	}

	function BlobDecode($blob) {
		return $blob;
	}

	function BlobEncode($blob) {
		return $blob;
	}

	function GetCharSet() {
		return $this->charSet;
	}

	function SetCharSet($charset) {
		$this->charSet = $charset;
		return true;
	}

	function IfNull( $field, $ifNull ) {
		return " CASE WHEN $field is null THEN $ifNull ELSE $field END ";
	}

	function LogSQL($enable=true) {
		include_once(ADODB_DIR.'/adodb-perf.inc.php');

		if ($enable) {
			$this->fnExecute = 'adodb_log_sql';
		} else {
			$this->fnExecute = false;
		}

		$old = $this->_logsql;
		$this->_logsql = $enable;
		if ($enable && !$old) {
			$this->_affected = false;
		}
		return $old;
	}

	
	function UpdateClob($table,$column,$val,$where) {
		return $this->UpdateBlob($table,$column,$val,$where,'CLOB');
	}

			function MetaType($t,$len=-1,$fieldobj=false) {

		if (empty($this->_metars)) {
			$rsclass = $this->rsPrefix.$this->databaseType;
			$this->_metars = new $rsclass(false,$this->fetchMode);
			$this->_metars->connection = $this;
		}
		return $this->_metars->MetaType($t,$len,$fieldobj);
	}


	
	function SetDateLocale($locale = 'En') {
		$this->locale = $locale;
		switch (strtoupper($locale))
		{
			case 'EN':
				$this->fmtDate="'Y-m-d'";
				$this->fmtTimeStamp = "'Y-m-d H:i:s'";
				break;

			case 'US':
				$this->fmtDate = "'m-d-Y'";
				$this->fmtTimeStamp = "'m-d-Y H:i:s'";
				break;

			case 'PT_BR':
			case 'NL':
			case 'FR':
			case 'RO':
			case 'IT':
				$this->fmtDate="'d-m-Y'";
				$this->fmtTimeStamp = "'d-m-Y H:i:s'";
				break;

			case 'GE':
				$this->fmtDate="'d.m.Y'";
				$this->fmtTimeStamp = "'d.m.Y H:i:s'";
				break;

			default:
				$this->fmtDate="'Y-m-d'";
				$this->fmtTimeStamp = "'Y-m-d H:i:s'";
				break;
		}
	}

	
	function GetActiveRecordsClass(
			$class, $table,$whereOrderBy=false,$bindarr=false, $primkeyArr=false,
			$extra=array(),
			$relations=array())
	{
		global $_ADODB_ACTIVE_DBS;
						if (!isset($_ADODB_ACTIVE_DBS)) {
			include_once(ADODB_DIR.'/adodb-active-record.inc.php');
		}
		return adodb_GetActiveRecordsClass($this, $class, $table, $whereOrderBy, $bindarr, $primkeyArr, $extra, $relations);
	}

	function GetActiveRecords($table,$where=false,$bindarr=false,$primkeyArr=false) {
		$arr = $this->GetActiveRecordsClass('ADODB_Active_Record', $table, $where, $bindarr, $primkeyArr);
		return $arr;
	}

	
	function Close() {
		$rez = $this->_close();
		$this->_connectionID = false;
		return $rez;
	}

	
	function BeginTrans() {
		if ($this->debug) {
			ADOConnection::outp("BeginTrans: Transactions not supported for this driver");
		}
		return false;
	}

	
	function SetTransactionMode( $transaction_mode ) {
		$transaction_mode = $this->MetaTransaction($transaction_mode, $this->dataProvider);
		$this->_transmode  = $transaction_mode;
	}

	function MetaTransaction($mode,$db) {
		$mode = strtoupper($mode);
		$mode = str_replace('ISOLATION LEVEL ','',$mode);

		switch($mode) {

		case 'READ UNCOMMITTED':
			switch($db) {
			case 'oci8':
			case 'oracle':
				return 'ISOLATION LEVEL READ COMMITTED';
			default:
				return 'ISOLATION LEVEL READ UNCOMMITTED';
			}
			break;

		case 'READ COMMITTED':
				return 'ISOLATION LEVEL READ COMMITTED';
			break;

		case 'REPEATABLE READ':
			switch($db) {
			case 'oci8':
			case 'oracle':
				return 'ISOLATION LEVEL SERIALIZABLE';
			default:
				return 'ISOLATION LEVEL REPEATABLE READ';
			}
			break;

		case 'SERIALIZABLE':
				return 'ISOLATION LEVEL SERIALIZABLE';
			break;

		default:
			return $mode;
		}
	}

	
	function CommitTrans($ok=true) {
		return true;
	}


	
	function RollbackTrans() {
		return false;
	}


	
	function MetaDatabases() {
		global $ADODB_FETCH_MODE;

		if ($this->metaDatabasesSQL) {
			$save = $ADODB_FETCH_MODE;
			$ADODB_FETCH_MODE = ADODB_FETCH_NUM;

			if ($this->fetchMode !== false) {
				$savem = $this->SetFetchMode(false);
			}

			$arr = $this->GetCol($this->metaDatabasesSQL);
			if (isset($savem)) {
				$this->SetFetchMode($savem);
			}
			$ADODB_FETCH_MODE = $save;

			return $arr;
		}

		return false;
	}

	
	function MetaProcedures($procedureNamePattern = null, $catalog  = null, $schemaPattern  = null) {
		return false;
	}


	
	function MetaTables($ttype=false,$showSchema=false,$mask=false) {
		global $ADODB_FETCH_MODE;

		if ($mask) {
			return false;
		}
		if ($this->metaTablesSQL) {
			$save = $ADODB_FETCH_MODE;
			$ADODB_FETCH_MODE = ADODB_FETCH_NUM;

			if ($this->fetchMode !== false) {
				$savem = $this->SetFetchMode(false);
			}

			$rs = $this->Execute($this->metaTablesSQL);
			if (isset($savem)) {
				$this->SetFetchMode($savem);
			}
			$ADODB_FETCH_MODE = $save;

			if ($rs === false) {
				return false;
			}
			$arr = $rs->GetArray();
			$arr2 = array();

			if ($hast = ($ttype && isset($arr[0][1]))) {
				$showt = strncmp($ttype,'T',1);
			}

			for ($i=0; $i < sizeof($arr); $i++) {
				if ($hast) {
					if ($showt == 0) {
						if (strncmp($arr[$i][1],'T',1) == 0) {
							$arr2[] = trim($arr[$i][0]);
						}
					} else {
						if (strncmp($arr[$i][1],'V',1) == 0) {
							$arr2[] = trim($arr[$i][0]);
						}
					}
				} else
					$arr2[] = trim($arr[$i][0]);
			}
			$rs->Close();
			return $arr2;
		}
		return false;
	}


	function _findschema(&$table,&$schema) {
		if (!$schema && ($at = strpos($table,'.')) !== false) {
			$schema = substr($table,0,$at);
			$table = substr($table,$at+1);
		}
	}

	
	function MetaColumns($table,$normalize=true) {
		global $ADODB_FETCH_MODE;

		if (!empty($this->metaColumnsSQL)) {
			$schema = false;
			$this->_findschema($table,$schema);

			$save = $ADODB_FETCH_MODE;
			$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
			if ($this->fetchMode !== false) {
				$savem = $this->SetFetchMode(false);
			}
			$rs = $this->Execute(sprintf($this->metaColumnsSQL,($normalize)?strtoupper($table):$table));
			if (isset($savem)) {
				$this->SetFetchMode($savem);
			}
			$ADODB_FETCH_MODE = $save;
			if ($rs === false || $rs->EOF) {
				return false;
			}

			$retarr = array();
			while (!$rs->EOF) { 				$fld = new ADOFieldObject();
				$fld->name = $rs->fields[0];
				$fld->type = $rs->fields[1];
				if (isset($rs->fields[3]) && $rs->fields[3]) {
					if ($rs->fields[3]>0) {
						$fld->max_length = $rs->fields[3];
					}
					$fld->scale = $rs->fields[4];
					if ($fld->scale>0) {
						$fld->max_length += 1;
					}
				} else {
					$fld->max_length = $rs->fields[2];
				}

				if ($ADODB_FETCH_MODE == ADODB_FETCH_NUM) {
					$retarr[] = $fld;
				} else {
					$retarr[strtoupper($fld->name)] = $fld;
				}
				$rs->MoveNext();
			}
			$rs->Close();
			return $retarr;
		}
		return false;
	}

	
	function MetaIndexes($table, $primary = false, $owner = false) {
		return false;
	}

	
	function MetaColumnNames($table, $numIndexes=false,$useattnum=false ) {
		$objarr = $this->MetaColumns($table);
		if (!is_array($objarr)) {
			return false;
		}
		$arr = array();
		if ($numIndexes) {
			$i = 0;
			if ($useattnum) {
				foreach($objarr as $v)
					$arr[$v->attnum] = $v->name;

			} else
				foreach($objarr as $v) $arr[$i++] = $v->name;
		} else
			foreach($objarr as $v) $arr[strtoupper($v->name)] = $v->name;

		return $arr;
	}

	
	function Concat() {
		$arr = func_get_args();
		return implode($this->concat_operator, $arr);
	}


	
	function DBDate($d, $isfld=false) {
		if (empty($d) && $d !== 0) {
			return 'null';
		}
		if ($isfld) {
			return $d;
		}
		if (is_object($d)) {
			return $d->format($this->fmtDate);
		}

		if (is_string($d) && !is_numeric($d)) {
			if ($d === 'null') {
				return $d;
			}
			if (strncmp($d,"'",1) === 0) {
				$d = _adodb_safedateq($d);
				return $d;
			}
			if ($this->isoDates) {
				return "'$d'";
			}
			$d = ADOConnection::UnixDate($d);
		}

		return adodb_date($this->fmtDate,$d);
	}

	function BindDate($d) {
		$d = $this->DBDate($d);
		if (strncmp($d,"'",1)) {
			return $d;
		}

		return substr($d,1,strlen($d)-2);
	}

	function BindTimeStamp($d) {
		$d = $this->DBTimeStamp($d);
		if (strncmp($d,"'",1)) {
			return $d;
		}

		return substr($d,1,strlen($d)-2);
	}


	
	function DBTimeStamp($ts,$isfld=false) {
		if (empty($ts) && $ts !== 0) {
			return 'null';
		}
		if ($isfld) {
			return $ts;
		}
		if (is_object($ts)) {
			return $ts->format($this->fmtTimeStamp);
		}

				if (!is_string($ts) || (is_numeric($ts) && strlen($ts)<14)) {
			return adodb_date($this->fmtTimeStamp,$ts);
		}

		if ($ts === 'null') {
			return $ts;
		}
		if ($this->isoDates && strlen($ts) !== 14) {
			$ts = _adodb_safedate($ts);
			return "'$ts'";
		}
		$ts = ADOConnection::UnixTimeStamp($ts);
		return adodb_date($this->fmtTimeStamp,$ts);
	}

	
	static function UnixDate($v) {
		if (is_object($v)) {
							return adodb_mktime($v->hour,$v->minute,$v->second,$v->month,$v->day, $v->year);
		}

		if (is_numeric($v) && strlen($v) !== 8) {
			return $v;
		}
		if (!preg_match( "|^([0-9]{4})[-/\.]?([0-9]{1,2})[-/\.]?([0-9]{1,2})|", $v, $rr)) {
			return false;
		}

		if ($rr[1] <= TIMESTAMP_FIRST_YEAR) {
			return 0;
		}

				return @adodb_mktime(0,0,0,$rr[2],$rr[3],$rr[1]);
	}


	
	static function UnixTimeStamp($v) {
		if (is_object($v)) {
							return adodb_mktime($v->hour,$v->minute,$v->second,$v->month,$v->day, $v->year);
		}

		if (!preg_match(
			"|^([0-9]{4})[-/\.]?([0-9]{1,2})[-/\.]?([0-9]{1,2})[ ,-]*(([0-9]{1,2}):?([0-9]{1,2}):?([0-9\.]{1,4}))?|",
			($v), $rr)) return false;

		if ($rr[1] <= TIMESTAMP_FIRST_YEAR && $rr[2]<= 1) {
			return 0;
		}

				if (!isset($rr[5])) {
			return  adodb_mktime(0,0,0,$rr[2],$rr[3],$rr[1]);
		}
		return @adodb_mktime($rr[5],$rr[6],$rr[7],$rr[2],$rr[3],$rr[1]);
	}

	
	function UserDate($v,$fmt='Y-m-d',$gmt=false) {
		$tt = $this->UnixDate($v);

				if (($tt === false || $tt == -1) && $v != false) {
			return $v;
		} else if ($tt == 0) {
			return $this->emptyDate;
		} else if ($tt == -1) {
					}

		return ($gmt) ? adodb_gmdate($fmt,$tt) : adodb_date($fmt,$tt);

	}

	
	function UserTimeStamp($v,$fmt='Y-m-d H:i:s',$gmt=false) {
		if (!isset($v)) {
			return $this->emptyTimeStamp;
		}
				if (is_numeric($v) && strlen($v)<14) {
			return ($gmt) ? adodb_gmdate($fmt,$v) : adodb_date($fmt,$v);
		}
		$tt = $this->UnixTimeStamp($v);
				if (($tt === false || $tt == -1) && $v != false) {
			return $v;
		}
		if ($tt == 0) {
			return $this->emptyTimeStamp;
		}
		return ($gmt) ? adodb_gmdate($fmt,$tt) : adodb_date($fmt,$tt);
	}

	function escape($s,$magic_quotes=false) {
		return $this->addq($s,$magic_quotes);
	}

	
	function addq($s,$magic_quotes=false) {
		if (!$magic_quotes) {
			if ($this->replaceQuote[0] == '\\') {
								$s = adodb_str_replace(array('\\',"\0"),array('\\\\',"\\\0"),$s);
							}
			return  str_replace("'",$this->replaceQuote,$s);
		}

				$s = str_replace('\\"','"',$s);

		if ($this->replaceQuote == "\\'" || ini_get('magic_quotes_sybase')) {
						return $s;
		} else {
						$s = str_replace('\\\\','\\',$s);
			return str_replace("\\'",$this->replaceQuote,$s);
		}
	}

	
	function qstr($s,$magic_quotes=false) {
		if (!$magic_quotes) {
			if ($this->replaceQuote[0] == '\\'){
								$s = adodb_str_replace(array('\\',"\0"),array('\\\\',"\\\0"),$s);
							}
			return  "'".str_replace("'",$this->replaceQuote,$s)."'";
		}

				$s = str_replace('\\"','"',$s);

		if ($this->replaceQuote == "\\'" || ini_get('magic_quotes_sybase')) {
						return "'$s'";
		} else {
						$s = str_replace('\\\\','\\',$s);
			return "'".str_replace("\\'",$this->replaceQuote,$s)."'";
		}
	}


	
	function PageExecute($sql, $nrows, $page, $inputarr=false, $secs2cache=0) {
		global $ADODB_INCLUDED_LIB;
		if (empty($ADODB_INCLUDED_LIB)) {
			include(ADODB_DIR.'/adodb-lib.inc.php');
		}
		if ($this->pageExecuteCountRows) {
			$rs = _adodb_pageexecute_all_rows($this, $sql, $nrows, $page, $inputarr, $secs2cache);
		} else {
			$rs = _adodb_pageexecute_no_last_page($this, $sql, $nrows, $page, $inputarr, $secs2cache);
		}
		return $rs;
	}


	
	function CachePageExecute($secs2cache, $sql, $nrows, $page,$inputarr=false) {
		
		$rs = $this->PageExecute($sql,$nrows,$page,$inputarr,$secs2cache);
		return $rs;
	}

} 


			
	
	class ADOFetchObj {
	};

			
	class ADODB_Iterator_empty implements Iterator {

		private $rs;

		function __construct($rs) {
			$this->rs = $rs;
		}

		function rewind() {}

		function valid() {
			return !$this->rs->EOF;
		}

		function key() {
			return false;
		}

		function current() {
			return false;
		}

		function next() {}

		function __call($func, $params) {
			return call_user_func_array(array($this->rs, $func), $params);
		}

		function hasMore() {
			return false;
		}

	}


	
	class ADORecordSet_empty implements IteratorAggregate
	{
		var $dataProvider = 'empty';
		var $databaseType = false;
		var $EOF = true;
		var $_numOfRows = 0;
		var $fields = false;
		var $connection = false;

		function RowCount() {
			return 0;
		}

		function RecordCount() {
			return 0;
		}

		function PO_RecordCount() {
			return 0;
		}

		function Close() {
			return true;
		}

		function FetchRow() {
			return false;
		}

		function FieldCount() {
			return 0;
		}

		function Init() {}

		function getIterator() {
			return new ADODB_Iterator_empty($this);
		}

		function GetAssoc() {
			return array();
		}

		function GetArray() {
			return array();
		}

		function GetAll() {
			return array();
		}

		function GetArrayLimit() {
			return array();
		}

		function GetRows() {
			return array();
		}

		function GetRowAssoc() {
			return array();
		}

		function MaxRecordCount() {
			return 0;
		}

		function NumRows() {
			return 0;
		}

		function NumCols() {
			return 0;
		}
	}

				if (!defined('ADODB_DATE_VERSION')) {
		include(ADODB_DIR.'/adodb-time.inc.php');
	}

			
	class ADODB_Iterator implements Iterator {

		private $rs;

		function __construct($rs) {
			$this->rs = $rs;
		}

		function rewind() {
			$this->rs->MoveFirst();
		}

		function valid() {
			return !$this->rs->EOF;
		}

		function key() {
			return $this->rs->_currentRow;
		}

		function current() {
			return $this->rs->fields;
		}

		function next() {
			$this->rs->MoveNext();
		}

		function __call($func, $params) {
			return call_user_func_array(array($this->rs, $func), $params);
		}

		function hasMore() {
			return !$this->rs->EOF;
		}

	}


	
	class ADORecordSet implements IteratorAggregate {

	
	var $dataProvider = "native";
	var $fields = false;		var $blobSize = 100;									var $canSeek = false;		var $sql;					var $EOF = false;		
	var $emptyTimeStamp = '&nbsp;'; 	var $emptyDate = '&nbsp;'; 	var $debug = false;
	var $timeCreated=0;		
	var $bind = false;			var $fetchMode;				var $connection = false; 
	
	var $_numOfRows = -1;	
	var $_numOfFields = -1;	
	var $_queryID = -1;		
	var $_currentRow = -1;	
	var $_closed = false;	
	var $_inited = false;	
	var $_obj;				
	var $_names;			

	var $_currentPage = -1;	
	var $_atFirstPage = false;	
	var $_atLastPage = false;	
	var $_lastPageNo = -1;
	var $_maxRecordCount = 0;
	var $datetime = false;

	
	function __construct($queryID) {
		$this->_queryID = $queryID;
	}

	function __destruct() {
		@$this->Close();
	}

	function getIterator() {
		return new ADODB_Iterator($this);
	}

	
	function __toString() {
		include_once(ADODB_DIR.'/toexport.inc.php');
		return _adodb_export($this,',',',',false,true);
	}

	function Init() {
		if ($this->_inited) {
			return;
		}
		$this->_inited = true;
		if ($this->_queryID) {
			@$this->_initrs();
		} else {
			$this->_numOfRows = 0;
			$this->_numOfFields = 0;
		}
		if ($this->_numOfRows != 0 && $this->_numOfFields && $this->_currentRow == -1) {
			$this->_currentRow = 0;
			if ($this->EOF = ($this->_fetch() === false)) {
				$this->_numOfRows = 0; 			}
		} else {
			$this->EOF = true;
		}
	}


	
	function GetMenu($name,$defstr='',$blank1stItem=true,$multiple=false,
			$size=0, $selectAttr='',$compareFields0=true)
	{
		global $ADODB_INCLUDED_LIB;
		if (empty($ADODB_INCLUDED_LIB)) {
			include(ADODB_DIR.'/adodb-lib.inc.php');
		}
		return _adodb_getmenu($this, $name,$defstr,$blank1stItem,$multiple,
			$size, $selectAttr,$compareFields0);
	}



	
	function GetMenu2($name,$defstr='',$blank1stItem=true,$multiple=false,$size=0, $selectAttr='') {
		return $this->GetMenu($name,$defstr,$blank1stItem,$multiple,
			$size, $selectAttr,false);
	}

	
	function GetMenu3($name,$defstr='',$blank1stItem=true,$multiple=false,
			$size=0, $selectAttr='')
	{
		global $ADODB_INCLUDED_LIB;
		if (empty($ADODB_INCLUDED_LIB)) {
			include(ADODB_DIR.'/adodb-lib.inc.php');
		}
		return _adodb_getmenu_gp($this, $name,$defstr,$blank1stItem,$multiple,
			$size, $selectAttr,false);
	}

	
	function GetArray($nRows = -1) {
		global $ADODB_EXTENSION; if ($ADODB_EXTENSION) {
		$results = adodb_getall($this,$nRows);
		return $results;
	}
		$results = array();
		$cnt = 0;
		while (!$this->EOF && $nRows != $cnt) {
			$results[] = $this->fields;
			$this->MoveNext();
			$cnt++;
		}
		return $results;
	}

	function GetAll($nRows = -1) {
		$arr = $this->GetArray($nRows);
		return $arr;
	}

	
	function NextRecordSet() {
		return false;
	}

	
	function GetArrayLimit($nrows,$offset=-1) {
		if ($offset <= 0) {
			$arr = $this->GetArray($nrows);
			return $arr;
		}

		$this->Move($offset);

		$results = array();
		$cnt = 0;
		while (!$this->EOF && $nrows != $cnt) {
			$results[$cnt++] = $this->fields;
			$this->MoveNext();
		}

		return $results;
	}


	
	function GetRows($nRows = -1) {
		$arr = $this->GetArray($nRows);
		return $arr;
	}

	
	function GetAssoc($force_array = false, $first2cols = false) {
		global $ADODB_EXTENSION;

		$cols = $this->_numOfFields;
		if ($cols < 2) {
			return false;
		}

				if (!$this->fields) {
			return array();
		}

				$numIndex = array_keys($this->fields) == range(0, count($this->fields) - 1);

		$results = array();

		if (!$first2cols && ($cols > 2 || $force_array)) {
			if ($ADODB_EXTENSION) {
				if ($numIndex) {
					while (!$this->EOF) {
						$results[trim($this->fields[0])] = array_slice($this->fields, 1);
						adodb_movenext($this);
					}
				} else {
					while (!$this->EOF) {
											$keys = array_slice(array_keys($this->fields), 1);
						$sliced_array = array();

						foreach($keys as $key) {
							$sliced_array[$key] = $this->fields[$key];
						}

						$results[trim(reset($this->fields))] = $sliced_array;
						adodb_movenext($this);
					}
				}
			} else {
				if ($numIndex) {
					while (!$this->EOF) {
						$results[trim($this->fields[0])] = array_slice($this->fields, 1);
						$this->MoveNext();
					}
				} else {
					while (!$this->EOF) {
											$keys = array_slice(array_keys($this->fields), 1);
						$sliced_array = array();

						foreach($keys as $key) {
							$sliced_array[$key] = $this->fields[$key];
						}

						$results[trim(reset($this->fields))] = $sliced_array;
						$this->MoveNext();
					}
				}
			}
		} else {
			if ($ADODB_EXTENSION) {
								if ($numIndex) {
					while (!$this->EOF) {
											$results[trim(($this->fields[0]))] = $this->fields[1];
						adodb_movenext($this);
					}
				} else {
					while (!$this->EOF) {
											$v1 = trim(reset($this->fields));
						$v2 = ''.next($this->fields);
						$results[$v1] = $v2;
						adodb_movenext($this);
					}
				}
			} else {
				if ($numIndex) {
					while (!$this->EOF) {
											$results[trim(($this->fields[0]))] = $this->fields[1];
						$this->MoveNext();
					}
				} else {
					while (!$this->EOF) {
											$v1 = trim(reset($this->fields));
						$v2 = ''.next($this->fields);
						$results[$v1] = $v2;
						$this->MoveNext();
					}
				}
			}
		}

		$ref = $results; 		return $ref;
	}


	
	function UserTimeStamp($v,$fmt='Y-m-d H:i:s') {
		if (is_numeric($v) && strlen($v)<14) {
			return adodb_date($fmt,$v);
		}
		$tt = $this->UnixTimeStamp($v);
				if (($tt === false || $tt == -1) && $v != false) {
			return $v;
		}
		if ($tt === 0) {
			return $this->emptyTimeStamp;
		}
		return adodb_date($fmt,$tt);
	}


	
	function UserDate($v,$fmt='Y-m-d') {
		$tt = $this->UnixDate($v);
				if (($tt === false || $tt == -1) && $v != false) {
			return $v;
		} else if ($tt == 0) {
			return $this->emptyDate;
		} else if ($tt == -1) {
					}
		return adodb_date($fmt,$tt);
	}


	
	static function UnixDate($v) {
		return ADOConnection::UnixDate($v);
	}


	
	static function UnixTimeStamp($v) {
		return ADOConnection::UnixTimeStamp($v);
	}


	
	function Free() {
		return $this->Close();
	}


	
	function NumRows() {
		return $this->_numOfRows;
	}


	
	function NumCols() {
		return $this->_numOfFields;
	}

	
	function FetchRow() {
		if ($this->EOF) {
			return false;
		}
		$arr = $this->fields;
		$this->_currentRow++;
		if (!$this->_fetch()) {
			$this->EOF = true;
		}
		return $arr;
	}


	
	function FetchInto(&$arr) {
		if ($this->EOF) {
			return (defined('PEAR_ERROR_RETURN')) ? new PEAR_Error('EOF',-1): false;
		}
		$arr = $this->fields;
		$this->MoveNext();
		return 1; 	}


	
	function MoveFirst() {
		if ($this->_currentRow == 0) {
			return true;
		}
		return $this->Move(0);
	}


	
	function MoveLast() {
		if ($this->_numOfRows >= 0) {
			return $this->Move($this->_numOfRows-1);
		}
		if ($this->EOF) {
			return false;
		}
		while (!$this->EOF) {
			$f = $this->fields;
			$this->MoveNext();
		}
		$this->fields = $f;
		$this->EOF = false;
		return true;
	}


	
	function MoveNext() {
		if (!$this->EOF) {
			$this->_currentRow++;
			if ($this->_fetch()) {
				return true;
			}
		}
		$this->EOF = true;
		
		return false;
	}


	
	function Move($rowNumber = 0) {
		$this->EOF = false;
		if ($rowNumber == $this->_currentRow) {
			return true;
		}
		if ($rowNumber >= $this->_numOfRows) {
			if ($this->_numOfRows != -1) {
				$rowNumber = $this->_numOfRows-2;
			}
		}

		if ($rowNumber < 0) {
			$this->EOF = true;
			return false;
		}

		if ($this->canSeek) {
			if ($this->_seek($rowNumber)) {
				$this->_currentRow = $rowNumber;
				if ($this->_fetch()) {
					return true;
				}
			} else {
				$this->EOF = true;
				return false;
			}
		} else {
			if ($rowNumber < $this->_currentRow) {
				return false;
			}
			global $ADODB_EXTENSION;
			if ($ADODB_EXTENSION) {
				while (!$this->EOF && $this->_currentRow < $rowNumber) {
					adodb_movenext($this);
				}
			} else {
				while (! $this->EOF && $this->_currentRow < $rowNumber) {
					$this->_currentRow++;

					if (!$this->_fetch()) {
						$this->EOF = true;
					}
				}
			}
			return !($this->EOF);
		}

		$this->fields = false;
		$this->EOF = true;
		return false;
	}


	
	function Fields($colname) {
		return $this->fields[$colname];
	}

	
	protected function AssocCaseConvertFunction($case = ADODB_ASSOC_CASE) {
		switch($case) {
			case ADODB_ASSOC_CASE_UPPER:
				return 'strtoupper';
			case ADODB_ASSOC_CASE_LOWER:
				return 'strtolower';
			case ADODB_ASSOC_CASE_NATIVE:
			default:
				return false;
		}
	}

	
	function GetAssocKeys($upper = ADODB_ASSOC_CASE) {
		if ($this->bind) {
			return;
		}
		$this->bind = array();

				$fn_change_case = $this->AssocCaseConvertFunction($upper);

				for ($i=0; $i < $this->_numOfFields; $i++) {
			$o = $this->FetchField($i);

						if(is_numeric($o->name)) {
								$key = $i;
			}
			elseif( $fn_change_case ) {
								$key = $fn_change_case($o->name);
			}
			else {
				$key = $o->name;
			}

			$this->bind[$key] = $i;
		}
	}

	
	function GetRowAssoc($upper = ADODB_ASSOC_CASE) {
		$record = array();
		$this->GetAssocKeys($upper);

		foreach($this->bind as $k => $v) {
			if( array_key_exists( $v, $this->fields ) ) {
				$record[$k] = $this->fields[$v];
			} elseif( array_key_exists( $k, $this->fields ) ) {
				$record[$k] = $this->fields[$k];
			} else {
								$record[$k] = null;
			}
		}
		return $record;
	}

	
	function Close() {
								if (!$this->_closed) {
			$this->_closed = true;
			return $this->_close();
		} else
			return true;
	}

	
	function RecordCount() {
		return $this->_numOfRows;
	}


	
	function MaxRecordCount() {
		return ($this->_maxRecordCount) ? $this->_maxRecordCount : $this->RecordCount();
	}

	
	function RowCount() {
		return $this->_numOfRows;
	}


	 
	function PO_RecordCount($table="", $condition="") {

		$lnumrows = $this->_numOfRows;
				if ($lnumrows == -1 && $this->connection) {
			IF ($table) {
				if ($condition) {
					$condition = " WHERE " . $condition;
				}
				$resultrows = $this->connection->Execute("SELECT COUNT(*) FROM $table $condition");
				if ($resultrows) {
					$lnumrows = reset($resultrows->fields);
				}
			}
		}
		return $lnumrows;
	}


	
	function CurrentRow() {
		return $this->_currentRow;
	}

	
	function AbsolutePosition() {
		return $this->_currentRow;
	}

	
	function FieldCount() {
		return $this->_numOfFields;
	}


	
	function FetchField($fieldoffset = -1) {
		
		return false;
	}

	
	function FieldTypesArray() {
		$arr = array();
		for ($i=0, $max=$this->_numOfFields; $i < $max; $i++)
			$arr[] = $this->FetchField($i);
		return $arr;
	}

	
	function FetchObj() {
		$o = $this->FetchObject(false);
		return $o;
	}

	
	function FetchObject($isupper=true) {
		if (empty($this->_obj)) {
			$this->_obj = new ADOFetchObj();
			$this->_names = array();
			for ($i=0; $i <$this->_numOfFields; $i++) {
				$f = $this->FetchField($i);
				$this->_names[] = $f->name;
			}
		}
		$i = 0;
		if (PHP_VERSION >= 5) {
			$o = clone($this->_obj);
		} else {
			$o = $this->_obj;
		}

		for ($i=0; $i <$this->_numOfFields; $i++) {
			$name = $this->_names[$i];
			if ($isupper) {
				$n = strtoupper($name);
			} else {
				$n = $name;
			}

			$o->$n = $this->Fields($name);
		}
		return $o;
	}

	
	function FetchNextObj() {
		$o = $this->FetchNextObject(false);
		return $o;
	}


	
	function FetchNextObject($isupper=true) {
		$o = false;
		if ($this->_numOfRows != 0 && !$this->EOF) {
			$o = $this->FetchObject($isupper);
			$this->_currentRow++;
			if ($this->_fetch()) {
				return $o;
			}
		}
		$this->EOF = true;
		return $o;
	}

	
	function MetaType($t,$len=-1,$fieldobj=false) {
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
		}

				static $typeMap = array(
			'VARCHAR' => 'C',
			'VARCHAR2' => 'C',
			'CHAR' => 'C',
			'C' => 'C',
			'STRING' => 'C',
			'NCHAR' => 'C',
			'NVARCHAR' => 'C',
			'VARYING' => 'C',
			'BPCHAR' => 'C',
			'CHARACTER' => 'C',
			'INTERVAL' => 'C',  			'MACADDR' => 'C', 			'VAR_STRING' => 'C', 						'LONGCHAR' => 'X',
			'TEXT' => 'X',
			'NTEXT' => 'X',
			'M' => 'X',
			'X' => 'X',
			'CLOB' => 'X',
			'NCLOB' => 'X',
			'LVARCHAR' => 'X',
						'BLOB' => 'B',
			'IMAGE' => 'B',
			'BINARY' => 'B',
			'VARBINARY' => 'B',
			'LONGBINARY' => 'B',
			'B' => 'B',
						'YEAR' => 'D', 			'DATE' => 'D',
			'D' => 'D',
						'UNIQUEIDENTIFIER' => 'C', 						'SMALLDATETIME' => 'T',
			'TIME' => 'T',
			'TIMESTAMP' => 'T',
			'DATETIME' => 'T',
			'DATETIME2' => 'T',
			'TIMESTAMPTZ' => 'T',
			'T' => 'T',
			'TIMESTAMP WITHOUT TIME ZONE' => 'T', 						'BOOL' => 'L',
			'BOOLEAN' => 'L',
			'BIT' => 'L',
			'L' => 'L',
						'COUNTER' => 'R',
			'R' => 'R',
			'SERIAL' => 'R', 			'INT IDENTITY' => 'R',
						'INT' => 'I',
			'INT2' => 'I',
			'INT4' => 'I',
			'INT8' => 'I',
			'INTEGER' => 'I',
			'INTEGER UNSIGNED' => 'I',
			'SHORT' => 'I',
			'TINYINT' => 'I',
			'SMALLINT' => 'I',
			'I' => 'I',
						'LONG' => 'N', 			'BIGINT' => 'N', 			'DECIMAL' => 'N',
			'DEC' => 'N',
			'REAL' => 'N',
			'DOUBLE' => 'N',
			'DOUBLE PRECISION' => 'N',
			'SMALLFLOAT' => 'N',
			'FLOAT' => 'N',
			'NUMBER' => 'N',
			'NUM' => 'N',
			'NUMERIC' => 'N',
			'MONEY' => 'N',

						'SQLINT' => 'I',
			'SQLSERIAL' => 'I',
			'SQLSMINT' => 'I',
			'SQLSMFLOAT' => 'N',
			'SQLFLOAT' => 'N',
			'SQLMONEY' => 'N',
			'SQLDECIMAL' => 'N',
			'SQLDATE' => 'D',
			'SQLVCHAR' => 'C',
			'SQLCHAR' => 'C',
			'SQLDTIME' => 'T',
			'SQLINTERVAL' => 'N',
			'SQLBYTES' => 'B',
			'SQLTEXT' => 'X',
						"SQLINT8" => 'I8',
			"SQLSERIAL8" => 'I8',
			"SQLNCHAR" => 'C',
			"SQLNVCHAR" => 'C',
			"SQLLVARCHAR" => 'X',
			"SQLBOOL" => 'L'
		);

		$tmap = false;
		$t = strtoupper($t);
		$tmap = (isset($typeMap[$t])) ? $typeMap[$t] : 'N';
		switch ($tmap) {
			case 'C':
								if ($this->blobSize >= 0) {
					if ($len > $this->blobSize) {
						return 'X';
					}
				} else if ($len > 250) {
					return 'X';
				}
				return 'C';

			case 'I':
				if (!empty($fieldobj->primary_key)) {
					return 'R';
				}
				return 'I';

			case false:
				return 'N';

			case 'B':
				if (isset($fieldobj->binary)) {
					return ($fieldobj->binary) ? 'B' : 'X';
				}
				return 'B';

			case 'D':
				if (!empty($this->connection) && !empty($this->connection->datetime)) {
					return 'T';
				}
				return 'D';

			default:
				if ($t == 'LONG' && $this->dataProvider == 'oci8') {
					return 'B';
				}
				return $tmap;
		}
	}

	
	protected function _updatefields()
	{
		if( empty($this->fields)) {
			return;
		}

				$fn_change_case = $this->AssocCaseConvertFunction();
		if(!$fn_change_case) {
						return;
		}

		$arr = array();

				foreach($this->fields as $k => $v) {
			if (!is_integer($k)) {
				$k = $fn_change_case($k);
			}
			$arr[$k] = $v;
		}
		$this->fields = $arr;
	}

	function _close() {}

	
	function AbsolutePage($page=-1) {
		if ($page != -1) {
			$this->_currentPage = $page;
		}
		return $this->_currentPage;
	}

	
	function AtFirstPage($status=false) {
		if ($status != false) {
			$this->_atFirstPage = $status;
		}
		return $this->_atFirstPage;
	}

	function LastPageNo($page = false) {
		if ($page != false) {
			$this->_lastPageNo = $page;
		}
		return $this->_lastPageNo;
	}

	
	function AtLastPage($status=false) {
		if ($status != false) {
			$this->_atLastPage = $status;
		}
		return $this->_atLastPage;
	}

} 
			
	
	class ADORecordSet_array extends ADORecordSet
	{
		var $databaseType = 'array';

		var $_array;			var $_types;			var $_colnames;			var $_skiprow1;			var $_fieldobjects; 		var $canSeek = true;
		var $affectedrows = false;
		var $insertid = false;
		var $sql = '';
		var $compat = false;

		
		function __construct($fakeid=1) {
			global $ADODB_FETCH_MODE,$ADODB_COMPAT_FETCH;

						$this->compat = !empty($ADODB_COMPAT_FETCH);
			parent::__construct($fakeid); 			$this->fetchMode = $ADODB_FETCH_MODE;
		}

		function _transpose($addfieldnames=true) {
			global $ADODB_INCLUDED_LIB;

			if (empty($ADODB_INCLUDED_LIB)) {
				include(ADODB_DIR.'/adodb-lib.inc.php');
			}
			$hdr = true;

			$fobjs = $addfieldnames ? $this->_fieldobjects : false;
			adodb_transpose($this->_array, $newarr, $hdr, $fobjs);
			
			$this->_skiprow1 = false;
			$this->_array = $newarr;
			$this->_colnames = $hdr;

			adodb_probetypes($newarr,$this->_types);

			$this->_fieldobjects = array();

			foreach($hdr as $k => $name) {
				$f = new ADOFieldObject();
				$f->name = $name;
				$f->type = $this->_types[$k];
				$f->max_length = -1;
				$this->_fieldobjects[] = $f;
			}
			$this->fields = reset($this->_array);

			$this->_initrs();

		}

		
		function InitArray($array,$typearr,$colnames=false) {
			$this->_array = $array;
			$this->_types = $typearr;
			if ($colnames) {
				$this->_skiprow1 = false;
				$this->_colnames = $colnames;
			} else {
				$this->_skiprow1 = true;
				$this->_colnames = $array[0];
			}
			$this->Init();
		}
		
		function InitArrayFields(&$array,&$fieldarr) {
			$this->_array = $array;
			$this->_skiprow1= false;
			if ($fieldarr) {
				$this->_fieldobjects = $fieldarr;
			}
			$this->Init();
		}

		function GetArray($nRows=-1) {
			if ($nRows == -1 && $this->_currentRow <= 0 && !$this->_skiprow1) {
				return $this->_array;
			} else {
				$arr = ADORecordSet::GetArray($nRows);
				return $arr;
			}
		}

		function _initrs() {
			$this->_numOfRows =  sizeof($this->_array);
			if ($this->_skiprow1) {
				$this->_numOfRows -= 1;
			}

			$this->_numOfFields = (isset($this->_fieldobjects))
				? sizeof($this->_fieldobjects)
				: sizeof($this->_types);
		}

		
		function Fields($colname) {
			$mode = isset($this->adodbFetchMode) ? $this->adodbFetchMode : $this->fetchMode;

			if ($mode & ADODB_FETCH_ASSOC) {
				if (!isset($this->fields[$colname]) && !is_null($this->fields[$colname])) {
					$colname = strtolower($colname);
				}
				return $this->fields[$colname];
			}
			if (!$this->bind) {
				$this->bind = array();
				for ($i=0; $i < $this->_numOfFields; $i++) {
					$o = $this->FetchField($i);
					$this->bind[strtoupper($o->name)] = $i;
				}
			}
			return $this->fields[$this->bind[strtoupper($colname)]];
		}

		function FetchField($fieldOffset = -1) {
			if (isset($this->_fieldobjects)) {
				return $this->_fieldobjects[$fieldOffset];
			}
			$o =  new ADOFieldObject();
			$o->name = $this->_colnames[$fieldOffset];
			$o->type =  $this->_types[$fieldOffset];
			$o->max_length = -1; 
			return $o;
		}

		function _seek($row) {
			if (sizeof($this->_array) && 0 <= $row && $row < $this->_numOfRows) {
				$this->_currentRow = $row;
				if ($this->_skiprow1) {
					$row += 1;
				}
				$this->fields = $this->_array[$row];
				return true;
			}
			return false;
		}

		function MoveNext() {
			if (!$this->EOF) {
				$this->_currentRow++;

				$pos = $this->_currentRow;

				if ($this->_numOfRows <= $pos) {
					if (!$this->compat) {
						$this->fields = false;
					}
				} else {
					if ($this->_skiprow1) {
						$pos += 1;
					}
					$this->fields = $this->_array[$pos];
					return true;
				}
				$this->EOF = true;
			}

			return false;
		}

		function _fetch() {
			$pos = $this->_currentRow;

			if ($this->_numOfRows <= $pos) {
				if (!$this->compat) {
					$this->fields = false;
				}
				return false;
			}
			if ($this->_skiprow1) {
				$pos += 1;
			}
			$this->fields = $this->_array[$pos];
			return true;
		}

		function _close() {
			return true;
		}

	} 
			
	
	function ADOLoadDB($dbType) {
		return ADOLoadCode($dbType);
	}

	
	function ADOLoadCode($dbType) {
		global $ADODB_LASTDB;

		if (!$dbType) {
			return false;
		}
		$db = strtolower($dbType);
		switch ($db) {
			case 'ado':
				if (PHP_VERSION >= 5) {
					$db = 'ado5';
				}
				$class = 'ado';
				break;

			case 'ifx':
			case 'maxsql':
				$class = $db = 'mysqlt';
				break;

			case 'pgsql':
			case 'postgres':
				$class = $db = 'postgres8';
				break;

			default:
				$class = $db; break;
		}

		$file = ADODB_DIR."/drivers/adodb-".$db.".inc.php";
		@include_once($file);
		$ADODB_LASTDB = $class;
		if (class_exists("ADODB_" . $class)) {
			return $class;
		}

				if (!file_exists($file)) {
			ADOConnection::outp("Missing file: $file");
		} else {
			ADOConnection::outp("Syntax error in file: $file");
		}
		return false;
	}

	
	function NewADOConnection($db='') {
		$tmp = ADONewConnection($db);
		return $tmp;
	}

	
	function ADONewConnection($db='') {
		global $ADODB_NEWCONNECTION, $ADODB_LASTDB;

		if (!defined('ADODB_ASSOC_CASE')) {
			define('ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_NATIVE);
		}
		$errorfn = (defined('ADODB_ERROR_HANDLER')) ? ADODB_ERROR_HANDLER : false;
		if (($at = strpos($db,'://')) !== FALSE) {
			$origdsn = $db;
			$fakedsn = 'fake'.substr($origdsn,$at);
			if (($at2 = strpos($origdsn,'@/')) !== FALSE) {
								$fakedsn = str_replace('@/','@adodb-fakehost/',$fakedsn);
			}

			if ((strpos($origdsn, 'sqlite')) !== FALSE && stripos($origdsn, '%2F') === FALSE) {
																				list($scheme, $path) = explode('://', $origdsn);
				$dsna['scheme'] = $scheme;
				if ($qmark = strpos($path,'?')) {
					$dsn['query'] = substr($path,$qmark+1);
					$path = substr($path,0,$qmark);
				}
				$dsna['path'] = '/' . urlencode($path);
			} else
				$dsna = @parse_url($fakedsn);

			if (!$dsna) {
				return false;
			}
			$dsna['scheme'] = substr($origdsn,0,$at);
			if ($at2 !== FALSE) {
				$dsna['host'] = '';
			}

			if (strncmp($origdsn,'pdo',3) == 0) {
				$sch = explode('_',$dsna['scheme']);
				if (sizeof($sch)>1) {
					$dsna['host'] = isset($dsna['host']) ? rawurldecode($dsna['host']) : '';
					if ($sch[1] == 'sqlite') {
						$dsna['host'] = rawurlencode($sch[1].':'.rawurldecode($dsna['host']));
					} else {
						$dsna['host'] = rawurlencode($sch[1].':host='.rawurldecode($dsna['host']));
					}
					$dsna['scheme'] = 'pdo';
				}
			}

			$db = @$dsna['scheme'];
			if (!$db) {
				return false;
			}
			$dsna['host'] = isset($dsna['host']) ? rawurldecode($dsna['host']) : '';
			$dsna['user'] = isset($dsna['user']) ? rawurldecode($dsna['user']) : '';
			$dsna['pass'] = isset($dsna['pass']) ? rawurldecode($dsna['pass']) : '';
			$dsna['path'] = isset($dsna['path']) ? rawurldecode(substr($dsna['path'],1)) : ''; 
			if (isset($dsna['query'])) {
				$opt1 = explode('&',$dsna['query']);
				foreach($opt1 as $k => $v) {
					$arr = explode('=',$v);
					$opt[$arr[0]] = isset($arr[1]) ? rawurldecode($arr[1]) : 1;
				}
			} else {
				$opt = array();
			}
		}
	
		if (!empty($ADODB_NEWCONNECTION)) {
			$obj = $ADODB_NEWCONNECTION($db);

		}

		if(empty($obj)) {

			if (!isset($ADODB_LASTDB)) {
				$ADODB_LASTDB = '';
			}
			if (empty($db)) {
				$db = $ADODB_LASTDB;
			}
			if ($db != $ADODB_LASTDB) {
				$db = ADOLoadCode($db);
			}

			if (!$db) {
				if (isset($origdsn)) {
					$db = $origdsn;
				}
				if ($errorfn) {
										$ignore = false;
					$errorfn('ADONewConnection', 'ADONewConnection', -998,
							"could not load the database driver for '$db'",
							$db,false,$ignore);
				} else {
					ADOConnection::outp( "<p>ADONewConnection: Unable to load database driver '$db'</p>",false);
				}
				return false;
			}

			$cls = 'ADODB_'.$db;
			if (!class_exists($cls)) {
				adodb_backtrace();
				return false;
			}

			$obj = new $cls();
		}

				if ($obj) {
			if ($errorfn) {
				$obj->raiseErrorFn = $errorfn;
			}
			if (isset($dsna)) {
				if (isset($dsna['port'])) {
					$obj->port = $dsna['port'];
				}
				foreach($opt as $k => $v) {
					switch(strtolower($k)) {
					case 'new':
										$nconnect = true; $persist = true; break;
					case 'persist':
					case 'persistent':	$persist = $v; break;
					case 'debug':		$obj->debug = (integer) $v; break;
										case 'role':		$obj->role = $v; break;
					case 'dialect':	$obj->dialect = (integer) $v; break;
					case 'charset':		$obj->charset = $v; $obj->charSet=$v; break;
					case 'buffers':		$obj->buffers = $v; break;
					case 'fetchmode':   $obj->SetFetchMode($v); break;
										case 'charpage':	$obj->charPage = $v; break;
										case 'clientflags': $obj->clientFlags = $v; break;
										case 'port': $obj->port = $v; break;
										case 'socket': $obj->socket = $v; break;
										case 'nls_date_format': $obj->NLS_DATE_FORMAT = $v; break;
					case 'cachesecs': $obj->cacheSecs = $v; break;
					case 'memcache':
						$varr = explode(':',$v);
						$vlen = sizeof($varr);
						if ($vlen == 0) {
							break;
						}
						$obj->memCache = true;
						$obj->memCacheHost = explode(',',$varr[0]);
						if ($vlen == 1) {
							break;
						}
						$obj->memCachePort = $varr[1];
						if ($vlen == 2) {
							break;
						}
						$obj->memCacheCompress = $varr[2] ?  true : false;
						break;
					}
				}
				if (empty($persist)) {
					$ok = $obj->Connect($dsna['host'], $dsna['user'], $dsna['pass'], $dsna['path']);
				} else if (empty($nconnect)) {
					$ok = $obj->PConnect($dsna['host'], $dsna['user'], $dsna['pass'], $dsna['path']);
				} else {
					$ok = $obj->NConnect($dsna['host'], $dsna['user'], $dsna['pass'], $dsna['path']);
				}

				if (!$ok) {
					return false;
				}
			}
		}
		return $obj;
	}



		function _adodb_getdriver($provider,$drivername,$perf=false) {
		switch ($provider) {
			case 'odbtp':
				if (strncmp('odbtp_',$drivername,6)==0) {
					return substr($drivername,6);
				}
			case 'odbc' :
				if (strncmp('odbc_',$drivername,5)==0) {
					return substr($drivername,5);
				}
			case 'ado'  :
				if (strncmp('ado_',$drivername,4)==0) {
					return substr($drivername,4);
				}
			case 'native':
				break;
			default:
				return $provider;
		}

		switch($drivername) {
			case 'mysqlt':
			case 'mysqli':
				$drivername='mysql';
				break;
			case 'postgres7':
			case 'postgres8':
				$drivername = 'postgres';
				break;
			case 'firebird15':
				$drivername = 'firebird';
				break;
			case 'oracle':
				$drivername = 'oci8';
				break;
			case 'access':
				if ($perf) {
					$drivername = '';
				}
				break;
			case 'db2'   :
			case 'sapdb' :
				break;
			default:
				$drivername = 'generic';
				break;
		}
		return $drivername;
	}

	function NewPerfMonitor(&$conn) {
		$drivername = _adodb_getdriver($conn->dataProvider,$conn->databaseType,true);
		if (!$drivername || $drivername == 'generic') {
			return false;
		}
		include_once(ADODB_DIR.'/adodb-perf.inc.php');
		@include_once(ADODB_DIR."/perf/perf-$drivername.inc.php");
		$class = "Perf_$drivername";
		if (!class_exists($class)) {
			return false;
		}
		$perf = new $class($conn);

		return $perf;
	}

	function NewDataDictionary(&$conn,$drivername=false) {
		if (!$drivername) {
			$drivername = _adodb_getdriver($conn->dataProvider,$conn->databaseType);
		}

		include_once(ADODB_DIR.'/adodb-lib.inc.php');
		include_once(ADODB_DIR.'/adodb-datadict.inc.php');
		$path = ADODB_DIR."/datadict/datadict-$drivername.inc.php";

		if (!file_exists($path)) {
			ADOConnection::outp("Dictionary driver '$path' not available");
			return false;
		}
		include_once($path);
		$class = "ADODB2_$drivername";
		$dict = new $class();
		$dict->dataProvider = $conn->dataProvider;
		$dict->connection = $conn;
		$dict->upperName = strtoupper($drivername);
		$dict->quote = $conn->nameQuote;
		if (!empty($conn->_connectionID)) {
			$dict->serverInfo = $conn->ServerInfo();
		}

		return $dict;
	}



	
	function adodb_pr($var,$as_string=false) {
		if ($as_string) {
			ob_start();
		}

		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			echo " <pre>\n";print_r($var);echo "</pre>\n";
		} else {
			print_r($var);
		}

		if ($as_string) {
			$s = ob_get_contents();
			ob_end_clean();
			return $s;
		}
	}

	
	function adodb_backtrace($printOrArr=true,$levels=9999,$ishtml=null) {
		global $ADODB_INCLUDED_LIB;
		if (empty($ADODB_INCLUDED_LIB)) {
			include(ADODB_DIR.'/adodb-lib.inc.php');
		}
		return _adodb_backtrace($printOrArr,$levels,0,$ishtml);
	}

}
