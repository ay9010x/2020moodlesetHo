<?php

if (!defined('ADODB_DIR')) die();

global $ADODB_INCLUDED_CSV;
$ADODB_INCLUDED_CSV = 1;



	
	function _rs2serialize(&$rs,$conn=false,$sql='')
	{
		$max = ($rs) ? $rs->FieldCount() : 0;

		if ($sql) $sql = urlencode($sql);
		
		if ($max <= 0 || $rs->dataProvider == 'empty') { 			if (is_object($conn)) {
				$sql .= ','.$conn->Affected_Rows();
				$sql .= ','.$conn->Insert_ID();
			} else
				$sql .= ',,';

			$text = "====-1,0,$sql\n";
			return $text;
		}
		$tt = ($rs->timeCreated) ? $rs->timeCreated : time();

				$line = "====1,$tt,$sql\n";

		if ($rs->databaseType == 'array') {
			$rows = $rs->_array;
		} else {
			$rows = array();
			while (!$rs->EOF) {
				$rows[] = $rs->fields;
				$rs->MoveNext();
			}
		}

		for($i=0; $i < $max; $i++) {
			$o = $rs->FetchField($i);
			$flds[] = $o;
		}

		$savefetch = isset($rs->adodbFetchMode) ? $rs->adodbFetchMode : $rs->fetchMode;
		$class = $rs->connection->arrayClass;
		$rs2 = new $class();
		$rs2->timeCreated = $rs->timeCreated; 		$rs2->sql = $rs->sql;
		$rs2->oldProvider = $rs->dataProvider;
		$rs2->InitArrayFields($rows,$flds);
		$rs2->fetchMode = $savefetch;
		return $line.serialize($rs2);
	}



	function csv2rs($url,&$err,$timeout=0, $rsclass='ADORecordSet_array')
	{
		$false = false;
		$err = false;
		$fp = @fopen($url,'rb');
		if (!$fp) {
			$err = $url.' file/URL not found';
			return $false;
		}
		@flock($fp, LOCK_SH);
		$arr = array();
		$ttl = 0;

		if ($meta = fgetcsv($fp, 32000, ",")) {
						if (strncmp($meta[0],'****',4) === 0) {
				$err = trim(substr($meta[0],4,1024));
				fclose($fp);
				return $false;
			}
									
			if (strncmp($meta[0], '====',4) === 0) {

				if ($meta[0] == "====-1") {
					if (sizeof($meta) < 5) {
						$err = "Corrupt first line for format -1";
						fclose($fp);
						return $false;
					}
					fclose($fp);

					if ($timeout > 0) {
						$err = " Illegal Timeout $timeout ";
						return $false;
					}

					$rs = new $rsclass($val=true);
					$rs->fields = array();
					$rs->timeCreated = $meta[1];
					$rs->EOF = true;
					$rs->_numOfFields = 0;
					$rs->sql = urldecode($meta[2]);
					$rs->affectedrows = (integer)$meta[3];
					$rs->insertid = $meta[4];
					return $rs;
				}
																												if (sizeof($meta) > 1) {
					if($timeout >0){
						$tdiff = (integer)( $meta[1]+$timeout - time());
						if ($tdiff <= 2) {
							switch($tdiff) {
							case 4:
							case 3:
								if ((rand() & 31) == 0) {
									fclose($fp);
									$err = "Timeout 3";
									return $false;
								}
								break;
							case 2:
								if ((rand() & 15) == 0) {
									fclose($fp);
									$err = "Timeout 2";
									return $false;
								}
								break;
							case 1:
								if ((rand() & 3) == 0) {
									fclose($fp);
									$err = "Timeout 1";
									return $false;
								}
								break;
							default:
								fclose($fp);
								$err = "Timeout 0";
								return $false;
							} 
						} 					}					$ttl = $meta[1];
				}
												if ($meta[0] === '====1') {
										$MAXSIZE = 128000;

					$text = fread($fp,$MAXSIZE);
					if (strlen($text)) {
						while ($txt = fread($fp,$MAXSIZE)) {
							$text .= $txt;
						}
					}
					fclose($fp);
					$rs = unserialize($text);
					if (is_object($rs)) $rs->timeCreated = $ttl;
					else {
						$err = "Unable to unserialize recordset";
											}
					return $rs;
				}

				$meta = false;
				$meta = fgetcsv($fp, 32000, ",");
				if (!$meta) {
					fclose($fp);
					$err = "Unexpected EOF 1";
					return $false;
				}
			}

						$flds = array();
			foreach($meta as $o) {
				$o2 = explode(':',$o);
				if (sizeof($o2)!=3) {
					$arr[] = $meta;
					$flds = false;
					break;
				}
				$fld = new ADOFieldObject();
				$fld->name = urldecode($o2[0]);
				$fld->type = $o2[1];
				$fld->max_length = $o2[2];
				$flds[] = $fld;
			}
		} else {
			fclose($fp);
			$err = "Recordset had unexpected EOF 2";
			return $false;
		}

				$MAXSIZE = 128000;

		$text = '';
		while ($txt = fread($fp,$MAXSIZE)) {
			$text .= $txt;
		}

		fclose($fp);
		@$arr = unserialize($text);
				if (!is_array($arr)) {
			$err = "Recordset had unexpected EOF (in serialized recordset)";
			if (get_magic_quotes_runtime()) $err .= ". Magic Quotes Runtime should be disabled!";
			return $false;
		}
		$rs = new $rsclass();
		$rs->timeCreated = $ttl;
		$rs->InitArrayFields($arr,$flds);
		return $rs;
	}


	
	function adodb_write_file($filename, $contents,$debug=false)
	{
													if (strncmp(PHP_OS,'WIN',3) === 0) {
						$mtime = substr(str_replace(' ','_',microtime()),2);
						$tmpname = $filename.uniqid($mtime).getmypid();
			if (!($fd = @fopen($tmpname,'w'))) return false;
			if (fwrite($fd,$contents)) $ok = true;
			else $ok = false;
			fclose($fd);

			if ($ok) {
				@chmod($tmpname,0644);
								@unlink($filename);
				if (!@rename($tmpname,$filename)) {
					@unlink($tmpname);
					$ok = 0;
				}
				if (!$ok) {
					if ($debug) ADOConnection::outp( " Rename $tmpname ".($ok? 'ok' : 'failed'));
				}
			}
			return $ok;
		}
		if (!($fd = @fopen($filename, 'a'))) return false;
		if (flock($fd, LOCK_EX) && ftruncate($fd, 0)) {
			if (fwrite( $fd, $contents )) $ok = true;
			else $ok = false;
			fclose($fd);
			@chmod($filename,0644);
		}else {
			fclose($fd);
			if ($debug)ADOConnection::outp( " Failed acquiring lock for $filename<br>\n");
			$ok = false;
		}

		return $ok;
	}
