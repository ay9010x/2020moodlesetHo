<?php


	define('TAB', chr(9));
	define('LF', chr(10));
	define('CR', chr(13));
	define('CRLF', CR . LF);


final class t3lib_div {

			const SYSLOG_SEVERITY_INFO = 0;
	const SYSLOG_SEVERITY_NOTICE = 1;
	const SYSLOG_SEVERITY_WARNING = 2;
	const SYSLOG_SEVERITY_ERROR = 3;
	const SYSLOG_SEVERITY_FATAL = 4;

	const ENV_TRUSTED_HOSTS_PATTERN_ALLOW_ALL = '.*';
	const ENV_TRUSTED_HOSTS_PATTERN_SERVER_NAME = 'SERVER_NAME';

	
	static protected $allowHostHeaderValue = FALSE;

	
	protected static $singletonInstances = array();

	
	protected static $nonSingletonInstances = array();

	
	protected static $finalClassNameRegister = array();

	

	
	public static function _GP($var) {
		if (empty($var)) {
			return;
		}
		$value = isset($_POST[$var]) ? $_POST[$var] : $_GET[$var];
		if (isset($value)) {
			if (is_array($value)) {
				self::stripSlashesOnArray($value);
			} else {
				$value = stripslashes($value);
			}
		}
		return $value;
	}

	
	public static function _GPmerged($parameter) {
		$postParameter = (isset($_POST[$parameter]) && is_array($_POST[$parameter])) ? $_POST[$parameter] : array();
		$getParameter = (isset($_GET[$parameter]) && is_array($_GET[$parameter])) ? $_GET[$parameter] : array();

		$mergedParameters = self::array_merge_recursive_overrule($getParameter, $postParameter);
		self::stripSlashesOnArray($mergedParameters);

		return $mergedParameters;
	}

	
	public static function _GET($var = NULL) {
		$value = ($var === NULL) ? $_GET : (empty($var) ? NULL : $_GET[$var]);
		if (isset($value)) { 			if (is_array($value)) {
				self::stripSlashesOnArray($value);
			} else {
				$value = stripslashes($value);
			}
		}
		return $value;
	}

	
	public static function _POST($var = NULL) {
		$value = ($var === NULL) ? $_POST : (empty($var) ? NULL : $_POST[$var]);
		if (isset($value)) { 			if (is_array($value)) {
				self::stripSlashesOnArray($value);
			} else {
				$value = stripslashes($value);
			}
		}
		return $value;
	}

	
	public static function _GETset($inputGet, $key = '') {
								if (is_array($inputGet)) {
			self::addSlashesOnArray($inputGet);
		} else {
			$inputGet = addslashes($inputGet);
		}

		if ($key != '') {
			if (strpos($key, '|') !== FALSE) {
				$pieces = explode('|', $key);
				$newGet = array();
				$pointer =& $newGet;
				foreach ($pieces as $piece) {
					$pointer =& $pointer[$piece];
				}
				$pointer = $inputGet;
				$mergedGet = self::array_merge_recursive_overrule(
					$_GET, $newGet
				);

				$_GET = $mergedGet;
				$GLOBALS['HTTP_GET_VARS'] = $mergedGet;
			} else {
				$_GET[$key] = $inputGet;
				$GLOBALS['HTTP_GET_VARS'][$key] = $inputGet;
			}
		} elseif (is_array($inputGet)) {
			$_GET = $inputGet;
			$GLOBALS['HTTP_GET_VARS'] = $inputGet;
		}
	}

	
	public static function removeXSS($string) {
		require_once(PATH_typo3 . 'contrib/RemoveXSS/RemoveXSS.php');
		$string = RemoveXSS::process($string);
		return $string;
	}


	


	
	public static function gif_compress($theFile, $type) {
		$gfxConf = $GLOBALS['TYPO3_CONF_VARS']['GFX'];
		$returnCode = '';
		if ($gfxConf['gif_compress'] && strtolower(substr($theFile, -4, 4)) == '.gif') { 			if (($type == 'IM' || !$type) && $gfxConf['im'] && $gfxConf['im_path_lzw']) { 									$temporaryName  =  dirname($theFile) . '/' . md5(uniqid()) . '.gif';
									if (@rename($theFile, $temporaryName)) {
					$cmd = self::imageMagickCommand('convert', '"' . $temporaryName . '" "' . $theFile . '"', $gfxConf['im_path_lzw']);
					t3lib_utility_Command::exec($cmd);
					unlink($temporaryName);
				}

				$returnCode = 'IM';
				if (@is_file($theFile)) {
					self::fixPermissions($theFile);
				}
			} elseif (($type == 'GD' || !$type) && $gfxConf['gdlib'] && !$gfxConf['gdlib_png']) { 				$tempImage = imageCreateFromGif($theFile);
				imageGif($tempImage, $theFile);
				imageDestroy($tempImage);
				$returnCode = 'GD';
				if (@is_file($theFile)) {
					self::fixPermissions($theFile);
				}
			}
		}
		return $returnCode;
	}

	
	public static function png_to_gif_by_imagemagick($theFile) {
		if ($GLOBALS['TYPO3_CONF_VARS']['FE']['png_to_gif']
				&& $GLOBALS['TYPO3_CONF_VARS']['GFX']['im']
				&& $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']
				&& strtolower(substr($theFile, -4, 4)) == '.png'
				&& @is_file($theFile)) { 			$newFile = substr($theFile, 0, -4) . '.gif';
			$cmd = self::imageMagickCommand('convert', '"' . $theFile . '" "' . $newFile . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
			t3lib_utility_Command::exec($cmd);
			$theFile = $newFile;
			if (@is_file($newFile)) {
				self::fixPermissions($newFile);
			}
										}
		return $theFile;
	}

	
	public static function read_png_gif($theFile, $output_png = FALSE) {
		if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im'] && @is_file($theFile)) {
			$ext = strtolower(substr($theFile, -4, 4));
			if (
				((string) $ext == '.png' && $output_png) ||
				((string) $ext == '.gif' && !$output_png)
			) {
				return $theFile;
			} else {
				$newFile = PATH_site . 'typo3temp/readPG_' . md5($theFile . '|' . filemtime($theFile)) . ($output_png ? '.png' : '.gif');
				$cmd = self::imageMagickCommand('convert', '"' . $theFile . '" "' . $newFile . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path']);
				t3lib_utility_Command::exec($cmd);
				if (@is_file($newFile)) {
					self::fixPermissions($newFile);
					return $newFile;
				}
			}
		}
	}


	

	
	public static function fixed_lgd_cs($string, $chars, $appendString = '...') {
		if (is_object($GLOBALS['LANG'])) {
			return $GLOBALS['LANG']->csConvObj->crop($GLOBALS['LANG']->charSet, $string, $chars, $appendString);
		} elseif (is_object($GLOBALS['TSFE'])) {
			$charSet = ($GLOBALS['TSFE']->renderCharset != '' ? $GLOBALS['TSFE']->renderCharset : $GLOBALS['TSFE']->defaultCharSet);
			return $GLOBALS['TSFE']->csConvObj->crop($charSet, $string, $chars, $appendString);
		} else {
							$csConvObj = self::makeInstance('t3lib_cs');
			return $csConvObj->crop('utf-8', $string, $chars, $appendString);
		}
	}

	
	public static function breakLinesForEmail($str, $newlineChar = LF, $lineWidth = 76) {
		self::logDeprecatedFunction();
		return t3lib_utility_Mail::breakLinesForEmail($str, $newlineChar, $lineWidth);
	}

	
	public static function cmpIP($baseIP, $list) {
		$list = trim($list);
		if ($list === '') {
			return FALSE;
		} elseif ($list === '*') {
			return TRUE;
		}
		if (strpos($baseIP, ':') !== FALSE && self::validIPv6($baseIP)) {
			return self::cmpIPv6($baseIP, $list);
		} else {
			return self::cmpIPv4($baseIP, $list);
		}
	}

	
	public static function cmpIPv4($baseIP, $list) {
		$IPpartsReq = explode('.', $baseIP);
		if (count($IPpartsReq) == 4) {
			$values = self::trimExplode(',', $list, 1);

			foreach ($values as $test) {
				$testList = explode('/', $test);
				if (count($testList) == 2) {
					list($test, $mask) = $testList;
				} else {
					$mask = FALSE;
				}

				if (intval($mask)) {
											$lnet = ip2long($test);
					$lip = ip2long($baseIP);
					$binnet = str_pad(decbin($lnet), 32, '0', STR_PAD_LEFT);
					$firstpart = substr($binnet, 0, $mask);
					$binip = str_pad(decbin($lip), 32, '0', STR_PAD_LEFT);
					$firstip = substr($binip, 0, $mask);
					$yes = (strcmp($firstpart, $firstip) == 0);
				} else {
											$IPparts = explode('.', $test);
					$yes = 1;
					foreach ($IPparts as $index => $val) {
						$val = trim($val);
						if (($val !== '*') && ($IPpartsReq[$index] !== $val)) {
							$yes = 0;
						}
					}
				}
				if ($yes) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	
	public static function cmpIPv6($baseIP, $list) {
		$success = FALSE; 		$baseIP = self::normalizeIPv6($baseIP);

		$values = self::trimExplode(',', $list, 1);
		foreach ($values as $test) {
			$testList = explode('/', $test);
			if (count($testList) == 2) {
				list($test, $mask) = $testList;
			} else {
				$mask = FALSE;
			}

			if (self::validIPv6($test)) {
				$test = self::normalizeIPv6($test);
				$maskInt = intval($mask) ? intval($mask) : 128;
				if ($mask === '0') { 					$success = TRUE;
				} elseif ($maskInt == 128) {
					$success = ($test === $baseIP);
				} else {
					$testBin = self::IPv6Hex2Bin($test);
					$baseIPBin = self::IPv6Hex2Bin($baseIP);
					$success = TRUE;

										$maskIntModulo = $maskInt % 8;
					$numFullCharactersUntilBoundary = intval($maskInt / 8);

					if (substr($testBin, 0, $numFullCharactersUntilBoundary) !== substr($baseIPBin, 0, $numFullCharactersUntilBoundary)) {
						$success = FALSE;
					} elseif ($maskIntModulo > 0) {
												$testLastBits = str_pad(decbin(ord(substr($testBin, $numFullCharactersUntilBoundary, 1))), 8, '0', STR_PAD_LEFT);
						$baseIPLastBits = str_pad(decbin(ord(substr($baseIPBin, $numFullCharactersUntilBoundary, 1))), 8, '0', STR_PAD_LEFT);
						if (strncmp($testLastBits, $baseIPLastBits, $maskIntModulo) != 0) {
							$success = FALSE;
						}
					}
				}
			}
			if ($success) {
				return TRUE;
			}
		}
		return FALSE;
	}

	
	public static function IPv6Hex2Bin($hex) {
					if (defined('AF_INET6')) {
			$bin = inet_pton($hex);
		} else {
			$hex = self::normalizeIPv6($hex);
			$hex = str_replace(':', '', $hex); 			$bin = pack("H*" , $hex);
		}
		return $bin;
	}

	
	public static function IPv6Bin2Hex($bin) {
					if (defined('AF_INET6')) {
			$hex = inet_ntop($bin);
		} else {
			$hex = unpack("H*" , $bin);
			$hex = chunk_split($hex[1], 4, ':');
							$hex = substr($hex, 0, -1);
											$hex = self::compressIPv6($hex);
		}
		return $hex;

	}

	
	public static function normalizeIPv6($address) {
		$normalizedAddress = '';
		$stageOneAddress = '';

					$address = strtolower($address);

					if (strlen($address) == 39) {
							return $address;
		}

		$chunks = explode('::', $address); 		if (count($chunks) == 2) {
			$chunksLeft = explode(':', $chunks[0]);
			$chunksRight = explode(':', $chunks[1]);
			$left = count($chunksLeft);
			$right = count($chunksRight);

							if ($left == 1 && strlen($chunksLeft[0]) == 0) {
				$left = 0;
			}

			$hiddenBlocks = 8 - ($left + $right);
			$hiddenPart = '';
			$h = 0;
			while ($h < $hiddenBlocks) {
				$hiddenPart .= '0000:';
				$h++;
			}

			if ($left == 0) {
				$stageOneAddress = $hiddenPart . $chunks[1];
			} else {
				$stageOneAddress = $chunks[0] . ':' . $hiddenPart . $chunks[1];
			}
		} else {
			$stageOneAddress = $address;
		}

					$blocks = explode(':', $stageOneAddress);
		$divCounter = 0;
		foreach ($blocks as $block) {
			$tmpBlock = '';
			$i = 0;
			$hiddenZeros = 4 - strlen($block);
			while ($i < $hiddenZeros) {
				$tmpBlock .= '0';
				$i++;
			}
			$normalizedAddress .= $tmpBlock . $block;
			if ($divCounter < 7) {
				$normalizedAddress .= ':';
				$divCounter++;
			}
		}
		return $normalizedAddress;
	}


	
	public static function compressIPv6($address) {
					if (defined('AF_INET6')) {
			$bin = inet_pton($address);
			$address = inet_ntop($bin);
		} else {
			$address = self::normalizeIPv6($address);

											$address .= ':';

															for ($counter = 8; $counter > 1; $counter--) {
				$search = str_repeat('0000:', $counter);
				if (($pos = strpos($address, $search)) !== FALSE) {
					$address = substr($address, 0, $pos) . ':' . substr($address, $pos + ($counter*5));
					break;
				}
			}

							$address = preg_replace('/^0{1,3}/', '', $address);
							$address = preg_replace('/:0{1,3}/', ':', $address);

							$address = substr($address, 0, -1);
		}
		return $address;
	}

	
	public static function validIP($ip) {
		return (filter_var($ip, FILTER_VALIDATE_IP) !== FALSE);
	}

	
	public static function validIPv4($ip) {
		return (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== FALSE);
	}

	
	public static function validIPv6($ip) {
		return (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== FALSE);
	}

	
	public static function cmpFQDN($baseHost, $list) {
		$baseHost = trim($baseHost);
		if (empty($baseHost)) {
			return FALSE;
		}
		if (self::validIPv4($baseHost) || self::validIPv6($baseHost)) {
															$baseHostName = gethostbyaddr($baseHost);
			if ($baseHostName === $baseHost) {
									return FALSE;
			}
		} else {
			$baseHostName = $baseHost;
		}
		$baseHostNameParts = explode('.', $baseHostName);

		$values = self::trimExplode(',', $list, 1);

		foreach ($values as $test) {
			$hostNameParts = explode('.', $test);

							if (count($hostNameParts) > count($baseHostNameParts)) {
				continue;
			}

			$yes = TRUE;
			foreach ($hostNameParts as $index => $val) {
				$val = trim($val);
				if ($val === '*') {
						
					$wildcardStart = $index + 1;
											if ($wildcardStart < count($hostNameParts)) {
						$wildcardMatched = FALSE;
						$tempHostName = implode('.', array_slice($hostNameParts, $index + 1));
						while (($wildcardStart < count($baseHostNameParts)) && (!$wildcardMatched)) {
							$tempBaseHostName = implode('.', array_slice($baseHostNameParts, $wildcardStart));
							$wildcardMatched = self::cmpFQDN($tempBaseHostName, $tempHostName);
							$wildcardStart++;
						}
						if ($wildcardMatched) {
															return TRUE;
						} else {
							$yes = FALSE;
						}
					}
				} elseif ($baseHostNameParts[$index] !== $val) {
											$yes = FALSE;
				}
			}
			if ($yes) {
				return TRUE;
			}
		}
		return FALSE;
	}

	
	public static function isOnCurrentHost($url) {
		return (stripos($url . '/', self::getIndpEnv('TYPO3_REQUEST_HOST') . '/') === 0);
	}

	
	public static function inList($list, $item) {
		return (strpos(',' . $list . ',', ',' . $item . ',') !== FALSE ? TRUE : FALSE);
	}

	
	public static function rmFromList($element, $list) {
		$items = explode(',', $list);
		foreach ($items as $k => $v) {
			if ($v == $element) {
				unset($items[$k]);
			}
		}
		return implode(',', $items);
	}

	
	public static function expandList($list) {
		$items = explode(',', $list);
		$list = array();
		foreach ($items as $item) {
			$range = explode('-', $item);
			if (isset($range[1])) {
				$runAwayBrake = 1000;
				for ($n = $range[0]; $n <= $range[1]; $n++) {
					$list[] = $n;

					$runAwayBrake--;
					if ($runAwayBrake <= 0) {
						break;
					}
				}
			} else {
				$list[] = $item;
			}
		}
		return implode(',', $list);
	}

	
	public static function intInRange($theInt, $min, $max = 2000000000, $zeroValue = 0) {
		self::logDeprecatedFunction();
		return t3lib_utility_Math::forceIntegerInRange($theInt, $min, $max, $zeroValue);
	}

	
	public static function intval_positive($theInt) {
		self::logDeprecatedFunction();
		return t3lib_utility_Math::convertToPositiveInteger($theInt);
	}

	
	public static function int_from_ver($verNumberStr) {
					if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4007000) {
			self::logDeprecatedFunction();
		}
		return t3lib_utility_VersionNumber::convertVersionNumberToInteger($verNumberStr);
	}

	
	public static function compat_version($verNumberStr) {
		$currVersionStr = $GLOBALS['TYPO3_CONF_VARS']['SYS']['compat_version'] ? $GLOBALS['TYPO3_CONF_VARS']['SYS']['compat_version'] : TYPO3_branch;

		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger($currVersionStr) < t3lib_utility_VersionNumber::convertVersionNumberToInteger($verNumberStr)) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	
	public static function md5int($str) {
		return hexdec(substr(md5($str), 0, 7));
	}

	
	public static function shortMD5($input, $len = 10) {
		return substr(md5($input), 0, $len);
	}

	
	public static function hmac($input, $additionalSecret = '') {
		$hashAlgorithm = 'sha1';
		$hashBlocksize = 64;
		$hmac = '';
		$secret = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] . $additionalSecret;
		if (extension_loaded('hash') && function_exists('hash_hmac') && function_exists('hash_algos') && in_array($hashAlgorithm, hash_algos())) {
			$hmac = hash_hmac($hashAlgorithm, $input, $secret);
		} else {
							$opad = str_repeat(chr(0x5C), $hashBlocksize);
							$ipad = str_repeat(chr(0x36), $hashBlocksize);
			if (strlen($secret) > $hashBlocksize) {
									$key = str_pad(pack('H*', call_user_func($hashAlgorithm, $secret)), $hashBlocksize, chr(0));
			} else {
									$key = str_pad($secret, $hashBlocksize, chr(0));
			}
			$hmac = call_user_func($hashAlgorithm, ($key ^ $opad) . pack('H*', call_user_func($hashAlgorithm, ($key ^ $ipad) . $input)));
		}
		return $hmac;
	}

	
	public static function uniqueList($in_list, $secondParameter = NULL) {
		if (is_array($in_list)) {
			throw new InvalidArgumentException(
				'TYPO3 Fatal Error: t3lib_div::uniqueList() does NOT support array arguments anymore! Only string comma lists!',
				1270853885
			);
		}
		if (isset($secondParameter)) {
			throw new InvalidArgumentException(
				'TYPO3 Fatal Error: t3lib_div::uniqueList() does NOT support more than a single argument value anymore. You have specified more than one!',
				1270853886
			);
		}

		return implode(',', array_unique(self::trimExplode(',', $in_list, 1)));
	}

	
	public static function split_fileref($fileref) {
		$reg = array();
		if (preg_match('/(.*\/)(.*)$/', $fileref, $reg)) {
			$info['path'] = $reg[1];
			$info['file'] = $reg[2];
		} else {
			$info['path'] = '';
			$info['file'] = $fileref;
		}

		$reg = '';
		if (!is_dir($fileref) && preg_match('/(.*)\.([^\.]*$)/', $info['file'], $reg)) {
			$info['filebody'] = $reg[1];
			$info['fileext'] = strtolower($reg[2]);
			$info['realFileext'] = $reg[2];
		} else {
			$info['filebody'] = $info['file'];
			$info['fileext'] = '';
		}
		reset($info);
		return $info;
	}

	
	public static function dirname($path) {
		$p = self::revExplode('/', $path, 2);
		return count($p) == 2 ? $p[0] : '';
	}

	
	public static function modifyHTMLColor($color, $R, $G, $B) {
					$nR = t3lib_utility_Math::forceIntegerInRange(hexdec(substr($color, 1, 2)) + $R, 0, 255);
		$nG = t3lib_utility_Math::forceIntegerInRange(hexdec(substr($color, 3, 2)) + $G, 0, 255);
		$nB = t3lib_utility_Math::forceIntegerInRange(hexdec(substr($color, 5, 2)) + $B, 0, 255);
		return '#' .
				substr('0' . dechex($nR), -2) .
				substr('0' . dechex($nG), -2) .
				substr('0' . dechex($nB), -2);
	}

	
	public static function modifyHTMLColorAll($color, $all) {
		return self::modifyHTMLColor($color, $all, $all, $all);
	}

	
	public static function testInt($var) {
		self::logDeprecatedFunction();

		return t3lib_utility_Math::canBeInterpretedAsInteger($var);
	}

	
	public static function isFirstPartOfStr($str, $partStr) {
		return $partStr != '' && strpos((string) $str, (string) $partStr, 0) === 0;
	}

	
	public static function formatSize($sizeInBytes, $labels = '') {

					if (strlen($labels) == 0) {
			$labels = ' | K| M| G';
		} else {
			$labels = str_replace('"', '', $labels);
		}
		$labelArr = explode('|', $labels);

					if ($sizeInBytes > 900) {
			if ($sizeInBytes > 900000000) { 				$val = $sizeInBytes / (1024 * 1024 * 1024);
				return number_format($val, (($val < 20) ? 1 : 0), '.', '') . $labelArr[3];
			}
			elseif ($sizeInBytes > 900000) { 				$val = $sizeInBytes / (1024 * 1024);
				return number_format($val, (($val < 20) ? 1 : 0), '.', '') . $labelArr[2];
			} else { 				$val = $sizeInBytes / (1024);
				return number_format($val, (($val < 20) ? 1 : 0), '.', '') . $labelArr[1];
			}
		} else { 			return $sizeInBytes . $labelArr[0];
		}
	}

	
	public static function convertMicrotime($microtime) {
		$parts = explode(' ', $microtime);
		return round(($parts[0] + $parts[1]) * 1000);
	}

	
	public static function splitCalc($string, $operators) {
		$res = Array();
		$sign = '+';
		while ($string) {
			$valueLen = strcspn($string, $operators);
			$value = substr($string, 0, $valueLen);
			$res[] = Array($sign, trim($value));
			$sign = substr($string, $valueLen, 1);
			$string = substr($string, $valueLen + 1);
		}
		reset($res);
		return $res;
	}

	
	public static function calcPriority($string) {
		self::logDeprecatedFunction();

		return t3lib_utility_Math::calculateWithPriorityToAdditionAndSubtraction($string);
	}

	
	public static function calcParenthesis($string) {
		self::logDeprecatedFunction();

		return t3lib_utility_Math::calculateWithParentheses($string);
	}

	
	public static function htmlspecialchars_decode($value) {
		$value = str_replace('&gt;', '>', $value);
		$value = str_replace('&lt;', '<', $value);
		$value = str_replace('&quot;', '"', $value);
		$value = str_replace('&amp;', '&', $value);
		return $value;
	}

	
	public static function deHSCentities($str) {
		return preg_replace('/&amp;([#[:alnum:]]*;)/', '&\1', $str);
	}

	
	public static function slashJS($string, $extended = FALSE, $char = "'") {
		if ($extended) {
			$string = str_replace("\\", "\\\\", $string);
		}
		return str_replace($char, "\\" . $char, $string);
	}

	
	public static function rawUrlEncodeJS($str) {
		return str_replace('%20', ' ', rawurlencode($str));
	}

	
	public static function rawUrlEncodeFP($str) {
		return str_replace('%2F', '/', rawurlencode($str));
	}

	
	public static function validEmail($email) {
								if (strlen($email) > 320) {
			return FALSE;
		}
		require_once(PATH_typo3 . 'contrib/idna/idna_convert.class.php');
		$IDN = new idna_convert(array('idn_version' => 2008));

		return (filter_var($IDN->encode($email), FILTER_VALIDATE_EMAIL) !== FALSE);
	}

	
	public static function isBrokenEmailEnvironment() {
		return TYPO3_OS == 'WIN' || (FALSE !== strpos(ini_get('sendmail_path'), 'mini_sendmail'));
	}

	
	public static function normalizeMailAddress($address) {
		if (self::isBrokenEmailEnvironment() && FALSE !== ($pos1 = strrpos($address, '<'))) {
			$pos2 = strpos($address, '>', $pos1);
			$address = substr($address, $pos1 + 1, ($pos2 ? $pos2 : strlen($address)) - $pos1 - 1);
		}
		return $address;
	}

	
	public static function formatForTextarea($content) {
		return LF . htmlspecialchars($content);
	}

	
	public static function strtoupper($str) {
		return strtr((string) $str, 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
	}

	
	public static function strtolower($str) {
		return strtr((string) $str, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
	}

	
	public static function generateRandomBytes($bytesToReturn) {
					static $bytes = '';
		$bytesToGenerate = max(4096, $bytesToReturn);

					if (!isset($bytes{$bytesToReturn - 1})) {
			if (TYPO3_OS === 'WIN') {
														$bytes .= self::generateRandomBytesMcrypt($bytesToGenerate, MCRYPT_RAND);
			} else {
									$bytes .= self::generateRandomBytesOpenSsl($bytesToGenerate);

				if (!isset($bytes{$bytesToReturn - 1})) {
					$bytes .= self::generateRandomBytesMcrypt($bytesToGenerate, MCRYPT_DEV_URANDOM);
				}

									if (!isset($bytes{$bytesToReturn - 1})) {
					$bytes .= self::generateRandomBytesUrandom($bytesToGenerate);
				}
			}

							if (!isset($bytes{$bytesToReturn - 1})) {
				$bytes .= self::generateRandomBytesFallback($bytesToReturn);
			}
		}

					$output = substr($bytes, 0, $bytesToReturn);
		$bytes = substr($bytes, $bytesToReturn);

		return $output;
	}

	
	protected static function generateRandomBytesOpenSsl($bytesToGenerate) {
		if (!function_exists('openssl_random_pseudo_bytes')) {
			return '';
		}
		$isStrong = NULL;
		return (string) openssl_random_pseudo_bytes($bytesToGenerate, $isStrong);
	}

	
	protected static function generateRandomBytesMcrypt($bytesToGenerate, $randomSource) {
		if (!function_exists('mcrypt_create_iv')) {
			return '';
		}
		return (string) @mcrypt_create_iv($bytesToGenerate, $randomSource);
	}

	
	protected static function generateRandomBytesUrandom($bytesToGenerate) {
		$bytes = '';
		$fh = @fopen('/dev/urandom', 'rb');
		if ($fh) {
															$bytes = fread($fh, $bytesToGenerate);
			fclose($fh);
		}

		return $bytes;
	}

	
	protected static function generateRandomBytesFallback($bytesToReturn) {
		$bytes = '';
					$randomState = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] . base_convert(memory_get_usage() % pow(10, 6), 10, 2) . microtime() . uniqid('') . getmypid();
		while (!isset($bytes{$bytesToReturn - 1})) {
			$randomState = sha1(microtime() . mt_rand() . $randomState);
			$bytes .= sha1(mt_rand() . $randomState, TRUE);
		}
		return $bytes;
	}

	
	public static function getRandomHexString($count) {
		return substr(bin2hex(self::generateRandomBytes(intval(($count + 1) / 2))), 0, $count);
	}

	
	public static function underscoredToUpperCamelCase($string) {
		$upperCamelCase = str_replace(' ', '', ucwords(str_replace('_', ' ', self::strtolower($string))));
		return $upperCamelCase;
	}

	
	public static function underscoredToLowerCamelCase($string) {
		$upperCamelCase = str_replace(' ', '', ucwords(str_replace('_', ' ', self::strtolower($string))));
		$lowerCamelCase = self::lcfirst($upperCamelCase);
		return $lowerCamelCase;
	}

	
	public static function camelCaseToLowerCaseUnderscored($string) {
		return self::strtolower(preg_replace('/(?<=\w)([A-Z])/', '_\\1', $string));
	}

	
	public static function lcfirst($string) {
		return self::strtolower(substr($string, 0, 1)) . substr($string, 1);
	}

	
	public static function isValidUrl($url) {
		require_once(PATH_typo3 . 'contrib/idna/idna_convert.class.php');
		$IDN = new idna_convert(array('idn_version' => 2008));

		return (filter_var($IDN->encode($url), FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) !== FALSE);
	}


	

	
	public static function inArray(array $in_array, $item) {
		foreach ($in_array as $val) {
			if (!is_array($val) && !strcmp($val, $item)) {
				return TRUE;
			}
		}
		return FALSE;
	}

	
	public static function intExplode($delimiter, $string, $onlyNonEmptyValues = FALSE, $limit = 0) {
		$explodedValues = self::trimExplode($delimiter, $string, $onlyNonEmptyValues, $limit);
		return array_map('intval', $explodedValues);
	}

	
	public static function revExplode($delimiter, $string, $count = 0) {
		$explodedValues = explode($delimiter, strrev($string), $count);
		$explodedValues = array_map('strrev', $explodedValues);
		return array_reverse($explodedValues);
	}

	
	public static function trimExplode($delim, $string, $removeEmptyValues = FALSE, $limit = 0) {
		$explodedValues = explode($delim, $string);

		$result = array_map('trim', $explodedValues);

		if ($removeEmptyValues) {
			$temp = array();
			foreach ($result as $value) {
				if ($value !== '') {
					$temp[] = $value;
				}
			}
			$result = $temp;
		}

		if ($limit != 0) {
			if ($limit < 0) {
				$result = array_slice($result, 0, $limit);
			} elseif (count($result) > $limit) {
				$lastElements = array_slice($result, $limit - 1);
				$result = array_slice($result, 0, $limit - 1);
				$result[] = implode($delim, $lastElements);
			}
		}

		return $result;
	}

	
	public static function removeArrayEntryByValue(array $array, $cmpValue) {
		foreach ($array as $k => $v) {
			if (is_array($v)) {
				$array[$k] = self::removeArrayEntryByValue($v, $cmpValue);
			} elseif (!strcmp($v, $cmpValue)) {
				unset($array[$k]);
			}
		}
		return $array;
	}

	
	public static function keepItemsInArray(array $array, $keepItems, $getValueFunc = NULL) {
		if ($array) {
							if (is_string($keepItems)) {
				$keepItems = self::trimExplode(',', $keepItems);
			}
							if (!is_string($getValueFunc)) {
				$getValueFunc = NULL;
			}
							if (is_array($keepItems) && count($keepItems)) {
				foreach ($array as $key => $value) {
											$keepValue = (isset($getValueFunc) ? $getValueFunc($value) : $value);
					if (!in_array($keepValue, $keepItems)) {
						unset($array[$key]);
					}
				}
			}
		}
		return $array;
	}

	
	public static function implodeArrayForUrl($name, array $theArray, $str = '', $skipBlank = FALSE, $rawurlencodeParamName = FALSE) {
		foreach ($theArray as $Akey => $AVal) {
			$thisKeyName = $name ? $name . '[' . $Akey . ']' : $Akey;
			if (is_array($AVal)) {
				$str = self::implodeArrayForUrl($thisKeyName, $AVal, $str, $skipBlank, $rawurlencodeParamName);
			} else {
				if (!$skipBlank || strcmp($AVal, '')) {
					$str .= '&' . ($rawurlencodeParamName ? rawurlencode($thisKeyName) : $thisKeyName) .
							'=' . rawurlencode($AVal);
				}
			}
		}
		return $str;
	}

	
	public static function explodeUrl2Array($string, $multidim = FALSE) {
		$output = array();
		if ($multidim) {
			parse_str($string, $output);
		} else {
			$p = explode('&', $string);
			foreach ($p as $v) {
				if (strlen($v)) {
					list($pK, $pV) = explode('=', $v, 2);
					$output[rawurldecode($pK)] = rawurldecode($pV);
				}
			}
		}
		return $output;
	}

	
	public static function compileSelectedGetVarsFromArray($varList, array $getArray, $GPvarAlt = TRUE) {
		$keys = self::trimExplode(',', $varList, 1);
		$outArr = array();
		foreach ($keys as $v) {
			if (isset($getArray[$v])) {
				$outArr[$v] = $getArray[$v];
			} elseif ($GPvarAlt) {
				$outArr[$v] = self::_GP($v);
			}
		}
		return $outArr;
	}

	
	public static function addSlashesOnArray(array &$theArray) {
		foreach ($theArray as &$value) {
			if (is_array($value)) {
				self::addSlashesOnArray($value);
			} else {
				$value = addslashes($value);
			}
		}
		unset($value);
		reset($theArray);
	}

	
	public static function stripSlashesOnArray(array &$theArray) {
		foreach ($theArray as &$value) {
			if (is_array($value)) {
				self::stripSlashesOnArray($value);
			} else {
				$value = stripslashes($value);
			}
		}
		unset($value);
		reset($theArray);
	}

	
	public static function slashArray(array $arr, $cmd) {
		if ($cmd == 'strip') {
			self::stripSlashesOnArray($arr);
		}
		if ($cmd == 'add') {
			self::addSlashesOnArray($arr);
		}
		return $arr;
	}

	
	public static function remapArrayKeys(&$array, $mappingTable) {
		if (is_array($mappingTable)) {
			foreach ($mappingTable as $old => $new) {
				if ($new && isset($array[$old])) {
					$array[$new] = $array[$old];
					unset ($array[$old]);
				}
			}
		}
	}


	
	public static function array_merge_recursive_overrule(array $arr0, array $arr1, $notAddKeys = FALSE, $includeEmptyValues = TRUE, $enableUnsetFeature = TRUE) {
		foreach ($arr1 as $key => $val) {
			if ($enableUnsetFeature && $val === '__UNSET') {
				unset($arr0[$key]);
				continue;
			}
			if (is_array($arr0[$key])) {
				if (is_array($arr1[$key])) {
					$arr0[$key] = self::array_merge_recursive_overrule(
						$arr0[$key],
						$arr1[$key],
						$notAddKeys,
						$includeEmptyValues,
						$enableUnsetFeature
					);
				}
			} elseif (
				(!$notAddKeys || isset($arr0[$key])) &&
				($includeEmptyValues || $val)
			) {
				$arr0[$key] = $val;
			}
		}

		reset($arr0);
		return $arr0;
	}

	
	public static function array_merge(array $arr1, array $arr2) {
		return $arr2 + $arr1;
	}

	
	public static function arrayDiffAssocRecursive(array $array1, array $array2) {
		$differenceArray = array();
		foreach ($array1 as $key => $value) {
			if (!array_key_exists($key, $array2)) {
				$differenceArray[$key] = $value;
			} elseif (is_array($value)) {
				if (is_array($array2[$key])) {
					$differenceArray[$key] = self::arrayDiffAssocRecursive($value, $array2[$key]);
				}
			}
		}

		return $differenceArray;
	}

	
	public static function csvValues(array $row, $delim = ',', $quote = '"') {
		$out = array();
		foreach ($row as $value) {
			$out[] = str_replace($quote, $quote . $quote, $value);
		}
		$str = $quote . implode($quote . $delim . $quote, $out) . $quote;
		return $str;
	}

	
	public static function removeDotsFromTS(array $ts) {
		$out = array();
		foreach ($ts as $key => $value) {
			if (is_array($value)) {
				$key = rtrim($key, '.');
				$out[$key] = self::removeDotsFromTS($value);
			} else {
				$out[$key] = $value;
			}
		}
		return $out;
	}

	
	public static function naturalKeySortRecursive(&$array) {
		if (!is_array($array)) {
			return FALSE;
		}
		uksort($array, 'strnatcasecmp');
		foreach ($array as $key => $value) {
			self::naturalKeySortRecursive($array[$key]);
		}
		return TRUE;
	}


	

	
	public static function get_tag_attributes($tag) {
		$components = self::split_tag_attributes($tag);
		$name = ''; 		$valuemode = FALSE;
		$attributes = array();
		foreach ($components as $key => $val) {
			if ($val != '=') { 				if ($valuemode) {
					if ($name) {
						$attributes[$name] = $val;
						$name = '';
					}
				} else {
					if ($key = strtolower(preg_replace('/[^[:alnum:]_\:\-]/', '', $val))) {
						$attributes[$key] = '';
						$name = $key;
					}
				}
				$valuemode = FALSE;
			} else {
				$valuemode = TRUE;
			}
		}
		return $attributes;
	}

	
	public static function split_tag_attributes($tag) {
		$tag_tmp = trim(preg_replace('/^<[^[:space:]]*/', '', trim($tag)));
					$tag_tmp = trim(rtrim($tag_tmp, '>'));

		$value = array();
		while (strcmp($tag_tmp, '')) { 			$firstChar = substr($tag_tmp, 0, 1);
			if (!strcmp($firstChar, '"') || !strcmp($firstChar, "'")) {
				$reg = explode($firstChar, $tag_tmp, 3);
				$value[] = $reg[1];
				$tag_tmp = trim($reg[2]);
			} elseif (!strcmp($firstChar, '=')) {
				$value[] = '=';
				$tag_tmp = trim(substr($tag_tmp, 1)); 			} else {
									$reg = preg_split('/[[:space:]=]/', $tag_tmp, 2);
				$value[] = trim($reg[0]);
				$tag_tmp = trim(substr($tag_tmp, strlen($reg[0]), 1) . $reg[1]);
			}
		}
		reset($value);
		return $value;
	}

	
	public static function implodeAttributes(array $arr, $xhtmlSafe = FALSE, $dontOmitBlankAttribs = FALSE) {
		if ($xhtmlSafe) {
			$newArr = array();
			foreach ($arr as $p => $v) {
				if (!isset($newArr[strtolower($p)])) {
					$newArr[strtolower($p)] = htmlspecialchars($v);
				}
			}
			$arr = $newArr;
		}
		$list = array();
		foreach ($arr as $p => $v) {
			if (strcmp($v, '') || $dontOmitBlankAttribs) {
				$list[] = $p . '="' . $v . '"';
			}
		}
		return implode(' ', $list);
	}

	
	public static function wrapJS($string, $linebreak = TRUE) {
		if (trim($string)) {
							$cr = $linebreak ? LF : '';

							$string = preg_replace('/^\n+/', '', $string);
							$match = array();
			if (preg_match('/^(\t+)/', $string, $match)) {
				$string = str_replace($match[1], TAB, $string);
			}
			$string = $cr . '<script type="text/javascript">
/*<![CDATA[*/
' . $string . '
/*]]>*/
</script>' . $cr;
		}
		return trim($string);
	}


	
	public static function xml2tree($string, $depth = 999) {
		$parser = xml_parser_create();
		$vals = array();
		$index = array();

		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 0);
		xml_parse_into_struct($parser, $string, $vals, $index);

		if (xml_get_error_code($parser)) {
			return 'Line ' . xml_get_current_line_number($parser) . ': ' . xml_error_string(xml_get_error_code($parser));
		}
		xml_parser_free($parser);

		$stack = array(array());
		$stacktop = 0;
		$startPoint = 0;

		$tagi = array();
		foreach ($vals as $key => $val) {
			$type = $val['type'];

							if ($type == 'open' || $type == 'complete') {
				$stack[$stacktop++] = $tagi;

				if ($depth == $stacktop) {
					$startPoint = $key;
				}

				$tagi = array('tag' => $val['tag']);

				if (isset($val['attributes'])) {
					$tagi['attrs'] = $val['attributes'];
				}
				if (isset($val['value'])) {
					$tagi['values'][] = $val['value'];
				}
			}
							if ($type == 'complete' || $type == 'close') {
				$oldtagi = $tagi;
				$tagi = $stack[--$stacktop];
				$oldtag = $oldtagi['tag'];
				unset($oldtagi['tag']);

				if ($depth == ($stacktop + 1)) {
					if ($key - $startPoint > 0) {
						$partArray = array_slice(
							$vals,
								$startPoint + 1,
								$key - $startPoint - 1
						);
						$oldtagi['XMLvalue'] = self::xmlRecompileFromStructValArray($partArray);
					} else {
						$oldtagi['XMLvalue'] = $oldtagi['values'][0];
					}
				}

				$tagi['ch'][$oldtag][] = $oldtagi;
				unset($oldtagi);
			}
							if ($type == 'cdata') {
				$tagi['values'][] = $val['value'];
			}
		}
		return $tagi['ch'];
	}

	
	public static function array2xml_cs(array $array, $docTag = 'phparray', array $options = array(), $charset = '') {

					$charset = $charset ? $charset : 'utf-8';

					return '<?xml version="1.0" encoding="' . htmlspecialchars($charset) . '" standalone="yes" ?>' . LF .
				self::array2xml($array, '', 0, $docTag, 0, $options);
	}

	
	public static function array2xml(array $array, $NSprefix = '', $level = 0, $docTag = 'phparray', $spaceInd = 0, array $options = array(), array $stackData = array()) {
					$binaryChars = chr(0) . chr(1) . chr(2) . chr(3) . chr(4) . chr(5) . chr(6) . chr(7) . chr(8) .
				chr(11) . chr(12) . chr(14) . chr(15) . chr(16) . chr(17) . chr(18) . chr(19) .
				chr(20) . chr(21) . chr(22) . chr(23) . chr(24) . chr(25) . chr(26) . chr(27) . chr(28) . chr(29) .
				chr(30) . chr(31);
					$indentChar = $spaceInd ? ' ' : TAB;
		$indentN = $spaceInd > 0 ? $spaceInd : 1;
		$nl = ($spaceInd >= 0 ? LF : '');

					$output = '';

					foreach ($array as $k => $v) {
			$attr = '';
			$tagName = $k;

							if (isset($options['grandParentTagMap'][$stackData['grandParentTagName'] . '/' . $stackData['parentTagName']])) { 				$attr .= ' index="' . htmlspecialchars($tagName) . '"';
				$tagName = (string) $options['grandParentTagMap'][$stackData['grandParentTagName'] . '/' . $stackData['parentTagName']];
			} elseif (isset($options['parentTagMap'][$stackData['parentTagName'] . ':_IS_NUM']) && t3lib_utility_Math::canBeInterpretedAsInteger($tagName)) { 				$attr .= ' index="' . htmlspecialchars($tagName) . '"';
				$tagName = (string) $options['parentTagMap'][$stackData['parentTagName'] . ':_IS_NUM'];
			} elseif (isset($options['parentTagMap'][$stackData['parentTagName'] . ':' . $tagName])) { 				$attr .= ' index="' . htmlspecialchars($tagName) . '"';
				$tagName = (string) $options['parentTagMap'][$stackData['parentTagName'] . ':' . $tagName];
			} elseif (isset($options['parentTagMap'][$stackData['parentTagName']])) { 				$attr .= ' index="' . htmlspecialchars($tagName) . '"';
				$tagName = (string) $options['parentTagMap'][$stackData['parentTagName']];
			} elseif (!strcmp(intval($tagName), $tagName)) { 				if ($options['useNindex']) { 					$tagName = 'n' . $tagName;
				} else { 					$attr .= ' index="' . $tagName . '"';
					$tagName = $options['useIndexTagForNum'] ? $options['useIndexTagForNum'] : 'numIndex';
				}
			} elseif ($options['useIndexTagForAssoc']) { 				$attr .= ' index="' . htmlspecialchars($tagName) . '"';
				$tagName = $options['useIndexTagForAssoc'];
			}

							$tagName = substr(preg_replace('/[^[:alnum:]_-]/', '', $tagName), 0, 100);

							if (is_array($v)) {

									if ($options['alt_options'][$stackData['path'] . '/' . $tagName]) {
					$subOptions = $options['alt_options'][$stackData['path'] . '/' . $tagName];
					$clearStackPath = $subOptions['clearStackPath'];
				} else {
					$subOptions = $options;
					$clearStackPath = FALSE;
				}

				$content = $nl .
						self::array2xml(
							$v,
							$NSprefix,
								$level + 1,
							'',
							$spaceInd,
							$subOptions,
							array(
								'parentTagName' => $tagName,
								'grandParentTagName' => $stackData['parentTagName'],
								'path' => $clearStackPath ? '' : $stackData['path'] . '/' . $tagName,
							)
						) .
						($spaceInd >= 0 ? str_pad('', ($level + 1) * $indentN, $indentChar) : '');
				if ((int) $options['disableTypeAttrib'] != 2) { 					$attr .= ' type="array"';
				}
			} else { 
									$vLen = strlen($v); 				if ($vLen && strcspn($v, $binaryChars) != $vLen) { 											$content = $nl . chunk_split(base64_encode($v));
					$attr .= ' base64="1"';
				} else {
											$content = htmlspecialchars($v);
					$dType = gettype($v);
					if ($dType == 'string') {
						if ($options['useCDATA'] && $content != $v) {
							$content = '<![CDATA[' . $v . ']]>';
						}
					} elseif (!$options['disableTypeAttrib']) {
						$attr .= ' type="' . $dType . '"';
					}
				}
			}

							$output .= ($spaceInd >= 0 ? str_pad('', ($level + 1) * $indentN, $indentChar) : '') . '<' . $NSprefix . $tagName . $attr . '>' . $content . '</' . $NSprefix . $tagName . '>' . $nl;
		}

					if (!$level) {
			$output =
					'<' . $docTag . '>' . $nl .
							$output .
							'</' . $docTag . '>';
		}

		return $output;
	}

	
	public static function xml2array($string, $NSprefix = '', $reportDocTag = FALSE) {
		static $firstLevelCache = array();

		$identifier = md5($string . $NSprefix . ($reportDocTag ? '1' : '0'));

					if (!empty($firstLevelCache[$identifier])) {
			$array = $firstLevelCache[$identifier];
		} else {
							$cacheContent = t3lib_pageSelect::getHash($identifier, 0);
			$array = unserialize($cacheContent);

			if ($array === FALSE) {
				$array = self::xml2arrayProcess($string, $NSprefix, $reportDocTag);
				t3lib_pageSelect::storeHash($identifier, serialize($array), 'ident_xml2array');
			}
							$firstLevelCache[$identifier] = $array;
		}
		return $array;
	}

	
	protected static function xml2arrayProcess($string, $NSprefix = '', $reportDocTag = FALSE) {
					$parser = xml_parser_create();
		$vals = array();
		$index = array();

		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 0);

					$match = array();
		preg_match('/^[[:space:]]*<\?xml[^>]*encoding[[:space:]]*=[[:space:]]*"([^"]*)"/', substr($string, 0, 200), $match);
		$theCharset = $match[1] ? $match[1] : 'utf-8';
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, $theCharset); 
					xml_parse_into_struct($parser, $string, $vals, $index);

					if (xml_get_error_code($parser)) {
			return 'Line ' . xml_get_current_line_number($parser) . ': ' . xml_error_string(xml_get_error_code($parser));
		}
		xml_parser_free($parser);

					$stack = array(array());
		$stacktop = 0;
		$current = array();
		$tagName = '';
		$documentTag = '';

					foreach ($vals as $key => $val) {

							$tagName = $val['tag'];
			if (!$documentTag) {
				$documentTag = $tagName;
			}

							$tagName = ($NSprefix && substr($tagName, 0, strlen($NSprefix)) == $NSprefix) ? substr($tagName, strlen($NSprefix)) : $tagName;

							$testNtag = substr($tagName, 1); 			$tagName = (substr($tagName, 0, 1) == 'n' && !strcmp(intval($testNtag), $testNtag)) ? intval($testNtag) : $tagName;

							if (strlen($val['attributes']['index'])) {
				$tagName = $val['attributes']['index'];
			}

							switch ($val['type']) {
				case 'open': 					$current[$tagName] = array(); 					$stack[$stacktop++] = $current;
					$current = array();
					break;
				case 'close': 					$oldCurrent = $current;
					$current = $stack[--$stacktop];
					end($current); 					$current[key($current)] = $oldCurrent;
					unset($oldCurrent);
					break;
				case 'complete': 					if ($val['attributes']['base64']) {
						$current[$tagName] = base64_decode($val['value']);
					} else {
						$current[$tagName] = (string) $val['value']; 
													switch ((string) $val['attributes']['type']) {
							case 'integer':
								$current[$tagName] = (integer) $current[$tagName];
								break;
							case 'double':
								$current[$tagName] = (double) $current[$tagName];
								break;
							case 'boolean':
								$current[$tagName] = (bool) $current[$tagName];
								break;
							case 'array':
								$current[$tagName] = array(); 								break;
						}
					}
					break;
			}
		}

		if ($reportDocTag) {
			$current[$tagName]['_DOCUMENT_TAG'] = $documentTag;
		}

					return $current[$tagName];
	}

	
	public static function xmlRecompileFromStructValArray(array $vals) {
		$XMLcontent = '';

		foreach ($vals as $val) {
			$type = $val['type'];

							if ($type == 'open' || $type == 'complete') {
				$XMLcontent .= '<' . $val['tag'];
				if (isset($val['attributes'])) {
					foreach ($val['attributes'] as $k => $v) {
						$XMLcontent .= ' ' . $k . '="' . htmlspecialchars($v) . '"';
					}
				}
				if ($type == 'complete') {
					if (isset($val['value'])) {
						$XMLcontent .= '>' . htmlspecialchars($val['value']) . '</' . $val['tag'] . '>';
					} else {
						$XMLcontent .= '/>';
					}
				} else {
					$XMLcontent .= '>';
				}

				if ($type == 'open' && isset($val['value'])) {
					$XMLcontent .= htmlspecialchars($val['value']);
				}
			}
							if ($type == 'close') {
				$XMLcontent .= '</' . $val['tag'] . '>';
			}
							if ($type == 'cdata') {
				$XMLcontent .= htmlspecialchars($val['value']);
			}
		}

		return $XMLcontent;
	}

	
	public static function xmlGetHeaderAttribs($xmlData) {
		$match = array();
		if (preg_match('/^\s*<\?xml([^>]*)\?\>/', $xmlData, $match)) {
			return self::get_tag_attributes($match[1]);
		}
	}

	
	public static function minifyJavaScript($script, &$error = '') {
		require_once(PATH_typo3 . 'contrib/jsmin/jsmin.php');
		try {
			$error = '';
			$script = trim(JSMin::minify(str_replace(CR, '', $script)));
		}
		catch (JSMinException $e) {
			$error = 'Error while minifying JavaScript: ' . $e->getMessage();
			self::devLog($error, 't3lib_div', 2,
				array('JavaScript' => $script, 'Stack trace' => $e->getTrace()));
		}
		return $script;
	}


	

	
	public static function getUrl($url, $includeHeader = 0, $requestHeaders = FALSE, &$report = NULL) {
		$content = FALSE;

		if (isset($report)) {
			$report['error'] = 0;
			$report['message'] = '';
		}

					if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlUse'] == '1' && preg_match('/^(?:http|ftp)s?|s(?:ftp|cp):/', $url)) {
			if (isset($report)) {
				$report['lib'] = 'cURL';
			}

							if (!function_exists('curl_init') || !($ch = curl_init())) {
				if (isset($report)) {
					$report['error'] = -1;
					$report['message'] = 'Couldn\'t initialize cURL.';
				}
				return FALSE;
			}

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, $includeHeader ? 1 : 0);
			curl_setopt($ch, CURLOPT_NOBODY, $includeHeader == 2 ? 1 : 0);
			curl_setopt($ch, CURLOPT_HTTPGET, $includeHeader == 2 ? 'HEAD' : 'GET');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, max(0, intval($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlTimeout'])));

			$followLocation = @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

			if (is_array($requestHeaders)) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
			}

							if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer']) {
				curl_setopt($ch, CURLOPT_PROXY, $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer']);

				if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyTunnel']) {
					curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyTunnel']);
				}
				if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyUserPass']) {
					curl_setopt($ch, CURLOPT_PROXYUSERPWD, $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyUserPass']);
				}
			}
			$content = curl_exec($ch);
			if (isset($report)) {
				if ($content === FALSE) {
					$report['error'] = curl_errno($ch);
					$report['message'] = curl_error($ch);
				} else {
					$curlInfo = curl_getinfo($ch);
											if (!$followLocation && $curlInfo['status'] >= 300 && $curlInfo['status'] < 400) {
						$report['error'] = -1;
						$report['message'] = 'Couldn\'t follow location redirect (PHP configuration option open_basedir is in effect).';
					} elseif ($includeHeader) {
													$report['http_code'] = $curlInfo['http_code'];
						$report['content_type'] = $curlInfo['content_type'];
					}
				}
			}
			curl_close($ch);

		} elseif ($includeHeader) {
			if (isset($report)) {
				$report['lib'] = 'socket';
			}
			$parsedURL = parse_url($url);
			if (!preg_match('/^https?/', $parsedURL['scheme'])) {
				if (isset($report)) {
					$report['error'] = -1;
					$report['message'] = 'Reading headers is not allowed for this protocol.';
				}
				return FALSE;
			}
			$port = intval($parsedURL['port']);
			if ($port < 1) {
				if ($parsedURL['scheme'] == 'http') {
					$port = ($port > 0 ? $port : 80);
					$scheme = '';
				} else {
					$port = ($port > 0 ? $port : 443);
					$scheme = 'ssl://';
				}
			}
			$errno = 0;
							$fp = @fsockopen($scheme . $parsedURL['host'], $port, $errno, $errstr, 2.0);
			if (!$fp || $errno > 0) {
				if (isset($report)) {
					$report['error'] = $errno ? $errno : -1;
					$report['message'] = $errno ? ($errstr ? $errstr : 'Socket error.') : 'Socket initialization error.';
				}
				return FALSE;
			}
			$method = ($includeHeader == 2) ? 'HEAD' : 'GET';
			$msg = $method . ' ' . (isset($parsedURL['path']) ? $parsedURL['path'] : '/') .
					($parsedURL['query'] ? '?' . $parsedURL['query'] : '') .
					' HTTP/1.0' . CRLF . 'Host: ' .
					$parsedURL['host'] . "\r\nConnection: close\r\n";
			if (is_array($requestHeaders)) {
				$msg .= implode(CRLF, $requestHeaders) . CRLF;
			}
			$msg .= CRLF;

			fputs($fp, $msg);
			while (!feof($fp)) {
				$line = fgets($fp, 2048);
				if (isset($report)) {
					if (preg_match('|^HTTP/\d\.\d +(\d+)|', $line, $status)) {
						$report['http_code'] = $status[1];
					}
					elseif (preg_match('/^Content-Type: *(.*)/i', $line, $type)) {
						$report['content_type'] = $type[1];
					}
				}
				$content .= $line;
				if (!strlen(trim($line))) {
					break; 				}
			}
			if ($includeHeader != 2) {
				$content .= stream_get_contents($fp);
			}
			fclose($fp);

		} elseif (is_array($requestHeaders)) {
			if (isset($report)) {
				$report['lib'] = 'file/context';
			}
			$parsedURL = parse_url($url);
			if (!preg_match('/^https?/', $parsedURL['scheme'])) {
				if (isset($report)) {
					$report['error'] = -1;
					$report['message'] = 'Sending request headers is not allowed for this protocol.';
				}
				return FALSE;
			}
			$ctx = stream_context_create(array(
				'http' => array(
					'header' => implode(CRLF, $requestHeaders)
				)
			)
			);

			$content = @file_get_contents($url, FALSE, $ctx);

			if ($content === FALSE && isset($report)) {
				$report['error'] = -1;
				$report['message'] = 'Couldn\'t get URL: ' . implode(LF, $http_response_header);
			}
		} else {
			if (isset($report)) {
				$report['lib'] = 'file';
			}

			$content = @file_get_contents($url);

			if ($content === FALSE && isset($report)) {
				$report['error'] = -1;
				$report['message'] = 'Couldn\'t get URL: ' . implode(LF, $http_response_header);
			}
		}

		return $content;
	}

	
	public static function writeFile($file, $content) {
		if (!@is_file($file)) {
			$changePermissions = TRUE;
		}

		if ($fd = fopen($file, 'wb')) {
			$res = fwrite($fd, $content);
			fclose($fd);

			if ($res === FALSE) {
				return FALSE;
			}

			if ($changePermissions) { 				self::fixPermissions($file);
			}

			return TRUE;
		}

		return FALSE;
	}

	
	public static function fixPermissions($path, $recursive = FALSE) {
		if (TYPO3_OS != 'WIN') {
			$result = FALSE;

							if (!self::isAbsPath($path)) {
				$path = self::getFileAbsFileName($path, FALSE);
			}

			if (self::isAllowedAbsPath($path)) {
				if (@is_file($path)) {
											$result = @chmod($path, octdec($GLOBALS['TYPO3_CONF_VARS']['BE']['fileCreateMask']));
				} elseif (@is_dir($path)) {
											$result = @chmod($path, octdec($GLOBALS['TYPO3_CONF_VARS']['BE']['folderCreateMask']));
				}

									if ($GLOBALS['TYPO3_CONF_VARS']['BE']['createGroup']) {
											$changeGroupResult = @chgrp($path, $GLOBALS['TYPO3_CONF_VARS']['BE']['createGroup']);
					$result = $changeGroupResult ? $result : FALSE;
				}

									if ($recursive && @is_dir($path)) {
					$handle = opendir($path);
					while (($file = readdir($handle)) !== FALSE) {
						$recursionResult = NULL;
						if ($file !== '.' && $file !== '..') {
							if (@is_file($path . '/' . $file)) {
								$recursionResult = self::fixPermissions($path . '/' . $file);
							} elseif (@is_dir($path . '/' . $file)) {
								$recursionResult = self::fixPermissions($path . '/' . $file, TRUE);
							}
							if (isset($recursionResult) && !$recursionResult) {
								$result = FALSE;
							}
						}
					}
					closedir($handle);
				}
			}
		} else {
			$result = TRUE;
		}
		return $result;
	}

	
	public static function writeFileToTypo3tempDir($filepath, $content) {

					$fI = pathinfo($filepath);
		$fI['dirname'] .= '/';

					if (self::validPathStr($filepath) && $fI['basename'] && strlen($fI['basename']) < 60) {
			if (defined('PATH_site')) {
				$dirName = PATH_site . 'typo3temp/'; 				if (@is_dir($dirName)) {
					if (self::isFirstPartOfStr($fI['dirname'], $dirName)) {

													$subdir = substr($fI['dirname'], strlen($dirName));
						if ($subdir) {
							if (preg_match('/^[[:alnum:]_]+\/$/', $subdir) || preg_match('/^[[:alnum:]_]+\/[[:alnum:]_]+\/$/', $subdir)) {
								$dirName .= $subdir;
								if (!@is_dir($dirName)) {
									self::mkdir_deep(PATH_site . 'typo3temp/', $subdir);
								}
							} else {
								return 'Subdir, "' . $subdir . '", was NOT on the form "[[:alnum:]_]/" or  "[[:alnum:]_]/[[:alnum:]_]/"';
							}
						}
													if (@is_dir($dirName)) {
							if ($filepath == $dirName . $fI['basename']) {
								self::writeFile($filepath, $content);
								if (!@is_file($filepath)) {
									return 'The file was not written to the disk. Please, check that you have write permissions to the typo3temp/ directory.';
								}
							} else {
								return 'Calculated filelocation didn\'t match input $filepath!';
							}
						} else {
							return '"' . $dirName . '" is not a directory!';
						}
					} else {
						return '"' . $fI['dirname'] . '" was not within directory PATH_site + "typo3temp/"';
					}
				} else {
					return 'PATH_site + "typo3temp/" was not a directory!';
				}
			} else {
				return 'PATH_site constant was NOT defined!';
			}
		} else {
			return 'Input filepath "' . $filepath . '" was generally invalid!';
		}
	}

	
	public static function mkdir($newFolder) {
		$result = @mkdir($newFolder, octdec($GLOBALS['TYPO3_CONF_VARS']['BE']['folderCreateMask']));
		if ($result) {
			self::fixPermissions($newFolder);
		}
		return $result;
	}

	
	public static function mkdir_deep($directory, $deepDirectory = '') {
		if (!is_string($directory)) {
			throw new \InvalidArgumentException(
				'The specified directory is of type "' . gettype($directory) . '" but a string is expected.',
				1303662955
			);
		}
		if (!is_string($deepDirectory)) {
			throw new \InvalidArgumentException(
				'The specified directory is of type "' . gettype($deepDirectory) . '" but a string is expected.',
				1303662956
			);
		}

		$fullPath = $directory . $deepDirectory;
		if (!is_dir($fullPath) && strlen($fullPath) > 0) {
			$firstCreatedPath = self::createDirectoryPath($fullPath);
			if ($firstCreatedPath !== '') {
				self::fixPermissions($firstCreatedPath, TRUE);
			}
		}
	}

	
	protected static function createDirectoryPath($fullDirectoryPath) {
		$currentPath = $fullDirectoryPath;
		$firstCreatedPath = '';
		$permissionMask = octdec($GLOBALS['TYPO3_CONF_VARS']['BE']['folderCreateMask']);
		if (!@is_dir($currentPath)) {
			do {
				$firstCreatedPath = $currentPath;
				$separatorPosition = strrpos($currentPath, DIRECTORY_SEPARATOR);
				$currentPath = substr($currentPath, 0, $separatorPosition);
			} while (!is_dir($currentPath) && $separatorPosition !== FALSE);

			$result = @mkdir($fullDirectoryPath, $permissionMask, TRUE);
			if (!$result) {
				throw new \RuntimeException('Could not create directory "' . $fullDirectoryPath . '"!', 1170251400);
			}
		}
		return $firstCreatedPath;
	}

	
	public static function rmdir($path, $removeNonEmpty = FALSE) {
		$OK = FALSE;
		$path = preg_replace('|/$|', '', $path); 
		if (file_exists($path)) {
			$OK = TRUE;

			if (is_dir($path)) {
				if ($removeNonEmpty == TRUE && $handle = opendir($path)) {
					while ($OK && FALSE !== ($file = readdir($handle))) {
						if ($file == '.' || $file == '..') {
							continue;
						}
						$OK = self::rmdir($path . '/' . $file, $removeNonEmpty);
					}
					closedir($handle);
				}
				if ($OK) {
					$OK = rmdir($path);
				}

			} else { 				$OK = unlink($path);
			}

			clearstatcache();
		}

		return $OK;
	}

	
	public static function get_dirs($path) {
		if ($path) {
			if (is_dir($path)) {
				$dir = scandir($path);
				$dirs = array();
				foreach ($dir as $entry) {
					if (is_dir($path . '/' . $entry) && $entry != '..' && $entry != '.') {
						$dirs[] = $entry;
					}
				}
			} else {
				$dirs = 'error';
			}
		}
		return $dirs;
	}

	
	public static function getFilesInDir($path, $extensionList = '', $prependPath = FALSE, $order = '', $excludePattern = '') {

					$filearray = array();
		$sortarray = array();
		$path = rtrim($path, '/');

					if (@is_dir($path)) {
			$extensionList = strtolower($extensionList);
			$d = dir($path);
			if (is_object($d)) {
				while ($entry = $d->read()) {
					if (@is_file($path . '/' . $entry)) {
						$fI = pathinfo($entry);
						$key = md5($path . '/' . $entry); 						if ((!strlen($extensionList) || self::inList($extensionList, strtolower($fI['extension']))) && (!strlen($excludePattern) || !preg_match('/^' . $excludePattern . '$/', $entry))) {
							$filearray[$key] = ($prependPath ? $path . '/' : '') . $entry;
							if ($order == 'mtime') {
								$sortarray[$key] = filemtime($path . '/' . $entry);
							}
							elseif ($order) {
								$sortarray[$key] = strtolower($entry);
							}
						}
					}
				}
				$d->close();
			} else {
				return 'error opening path: "' . $path . '"';
			}
		}

					if ($order) {
			asort($sortarray);
			$newArr = array();
			foreach ($sortarray as $k => $v) {
				$newArr[$k] = $filearray[$k];
			}
			$filearray = $newArr;
		}

					reset($filearray);
		return $filearray;
	}

	
	public static function getAllFilesAndFoldersInPath(array $fileArr, $path, $extList = '', $regDirs = FALSE, $recursivityLevels = 99, $excludePattern = '') {
		if ($regDirs) {
			$fileArr[] = $path;
		}
		$fileArr = array_merge($fileArr, self::getFilesInDir($path, $extList, 1, 1, $excludePattern));

		$dirs = self::get_dirs($path);
		if (is_array($dirs) && $recursivityLevels > 0) {
			foreach ($dirs as $subdirs) {
				if ((string) $subdirs != '' && (!strlen($excludePattern) || !preg_match('/^' . $excludePattern . '$/', $subdirs))) {
					$fileArr = self::getAllFilesAndFoldersInPath($fileArr, $path . $subdirs . '/', $extList, $regDirs, $recursivityLevels - 1, $excludePattern);
				}
			}
		}
		return $fileArr;
	}

	
	public static function removePrefixPathFromList(array $fileArr, $prefixToRemove) {
		foreach ($fileArr as $k => &$absFileRef) {
			if (self::isFirstPartOfStr($absFileRef, $prefixToRemove)) {
				$absFileRef = substr($absFileRef, strlen($prefixToRemove));
			} else {
				return 'ERROR: One or more of the files was NOT prefixed with the prefix-path!';
			}
		}
		unset($absFileRef);
		return $fileArr;
	}

	
	public static function fixWindowsFilePath($theFile) {
		return str_replace('//', '/', str_replace('\\', '/', $theFile));
	}

	
	public static function resolveBackPath($pathStr) {
		$parts = explode('/', $pathStr);
		$output = array();
		$c = 0;
		foreach ($parts as $pV) {
			if ($pV == '..') {
				if ($c) {
					array_pop($output);
					$c--;
				} else {
					$output[] = $pV;
				}
			} else {
				$c++;
				$output[] = $pV;
			}
		}
		return implode('/', $output);
	}

	
	public static function locationHeaderUrl($path) {
		$uI = parse_url($path);
		if (substr($path, 0, 1) == '/') { 			$path = self::getIndpEnv('TYPO3_REQUEST_HOST') . $path;
		} elseif (!$uI['scheme']) { 			$path = self::getIndpEnv('TYPO3_REQUEST_DIR') . $path;
		}
		return $path;
	}

	
	public static function getMaxUploadFileSize($localLimit = 0) {
					$t3Limit = (intval($localLimit > 0 ? $localLimit : $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize']));
					$t3Limit = $t3Limit * 1024;

					$phpUploadLimit = self::getBytesFromSizeMeasurement(ini_get('upload_max_filesize'));
					$phpPostLimit = self::getBytesFromSizeMeasurement(ini_get('post_max_size'));
								$phpUploadLimit = ($phpPostLimit < $phpUploadLimit ? $phpPostLimit : $phpUploadLimit);

					return floor($phpUploadLimit < $t3Limit ? $phpUploadLimit : $t3Limit) / 1024;
	}

	
	public static function getBytesFromSizeMeasurement($measurement) {
		$bytes = doubleval($measurement);
		if (stripos($measurement, 'G')) {
			$bytes *= 1024 * 1024 * 1024;
		} elseif (stripos($measurement, 'M')) {
			$bytes *= 1024 * 1024;
		} elseif (stripos($measurement, 'K')) {
			$bytes *= 1024;
		}
		return $bytes;
	}

	
	public static function getMaximumPathLength() {
		return PHP_MAXPATHLEN;
	}


	
	public static function createVersionNumberedFilename($file, $forceQueryString = FALSE) {
		$lookupFile = explode('?', $file);
		$path = self::resolveBackPath(self::dirname(PATH_thisScript) . '/' . $lookupFile[0]);

		if (TYPO3_MODE == 'FE') {
			$mode = strtolower($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['versionNumberInFilename']);
			if ($mode === 'embed') {
				$mode = TRUE;
			} else {
				if ($mode === 'querystring') {
					$mode = FALSE;
				} else {
					$doNothing = TRUE;
				}
			}
		} else {
			$mode = $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['versionNumberInFilename'];
		}

		if (!file_exists($path) || $doNothing) {
							$fullName = $file;

		} else {
			if (!$mode || $forceQueryString) {
														if ($lookupFile[1]) {
					$separator = '&';
				} else {
					$separator = '?';
				}
				$fullName = $file . $separator . filemtime($path);

			} else {
									$name = explode('.', $lookupFile[0]);
				$extension = array_pop($name);

				array_push($name, filemtime($path), $extension);
				$fullName = implode('.', $name);
									$fullName .= $lookupFile[1] ? '?' . $lookupFile[1] : '';
			}
		}

		return $fullName;
	}

	

	
	public static function getThisUrl() {
		$p = parse_url(self::getIndpEnv('TYPO3_REQUEST_SCRIPT')); 		$dir = self::dirname($p['path']) . '/'; 		$url = str_replace('//', '/', $p['host'] . ($p['port'] ? ':' . $p['port'] : '') . $dir);
		return $url;
	}

	
	public static function linkThisScript(array $getParams = array()) {
		$parts = self::getIndpEnv('SCRIPT_NAME');
		$params = self::_GET();

		foreach ($getParams as $key => $value) {
			if ($value !== '') {
				$params[$key] = $value;
			} else {
				unset($params[$key]);
			}
		}

		$pString = self::implodeArrayForUrl('', $params);

		return $pString ? $parts . '?' . preg_replace('/^&/', '', $pString) : $parts;
	}

	
	public static function linkThisUrl($url, array $getParams = array()) {
		$parts = parse_url($url);
		$getP = array();
		if ($parts['query']) {
			parse_str($parts['query'], $getP);
		}
		$getP = self::array_merge_recursive_overrule($getP, $getParams);
		$uP = explode('?', $url);

		$params = self::implodeArrayForUrl('', $getP);
		$outurl = $uP[0] . ($params ? '?' . substr($params, 1) : '');

		return $outurl;
	}

	
	public static function getIndpEnv($getEnvName) {
		

		$retVal = '';

		switch ((string) $getEnvName) {
			case 'SCRIPT_NAME':
				$retVal = (PHP_SAPI == 'fpm-fcgi' || PHP_SAPI == 'cgi' || PHP_SAPI == 'cgi-fcgi') &&
						($_SERVER['ORIG_PATH_INFO'] ? $_SERVER['ORIG_PATH_INFO'] : $_SERVER['PATH_INFO']) ?
						($_SERVER['ORIG_PATH_INFO'] ? $_SERVER['ORIG_PATH_INFO'] : $_SERVER['PATH_INFO']) :
						($_SERVER['ORIG_SCRIPT_NAME'] ? $_SERVER['ORIG_SCRIPT_NAME'] : $_SERVER['SCRIPT_NAME']);
									if (self::cmpIP($_SERVER['REMOTE_ADDR'], $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyIP'])) {
					if (self::getIndpEnv('TYPO3_SSL') && $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyPrefixSSL']) {
						$retVal = $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyPrefixSSL'] . $retVal;
					} elseif ($GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyPrefix']) {
						$retVal = $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyPrefix'] . $retVal;
					}
				}
				break;
			case 'SCRIPT_FILENAME':
				$retVal = str_replace('//', '/', str_replace('\\', '/',
					(PHP_SAPI == 'fpm-fcgi' || PHP_SAPI == 'cgi' || PHP_SAPI == 'isapi' || PHP_SAPI == 'cgi-fcgi') &&
							($_SERVER['ORIG_PATH_TRANSLATED'] ? $_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED']) ?
							($_SERVER['ORIG_PATH_TRANSLATED'] ? $_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED']) :
							($_SERVER['ORIG_SCRIPT_FILENAME'] ? $_SERVER['ORIG_SCRIPT_FILENAME'] : $_SERVER['SCRIPT_FILENAME'])));

				break;
			case 'REQUEST_URI':
									if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['requestURIvar']) { 					list($v, $n) = explode('|', $GLOBALS['TYPO3_CONF_VARS']['SYS']['requestURIvar']);
					$retVal = $GLOBALS[$v][$n];
				} elseif (!$_SERVER['REQUEST_URI']) { 					$retVal = '/' . ltrim(self::getIndpEnv('SCRIPT_NAME'), '/') .
							($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');
				} else {
					$retVal = $_SERVER['REQUEST_URI'];
				}
									if (self::cmpIP($_SERVER['REMOTE_ADDR'], $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyIP'])) {
					if (self::getIndpEnv('TYPO3_SSL') && $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyPrefixSSL']) {
						$retVal = $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyPrefixSSL'] . $retVal;
					} elseif ($GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyPrefix']) {
						$retVal = $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyPrefix'] . $retVal;
					}
				}
				break;
			case 'PATH_INFO':
																								if (PHP_SAPI != 'cgi' && PHP_SAPI != 'cgi-fcgi' && PHP_SAPI != 'fpm-fcgi') {
					$retVal = $_SERVER['PATH_INFO'];
				}
				break;
			case 'TYPO3_REV_PROXY':
				$retVal = self::cmpIP($_SERVER['REMOTE_ADDR'], $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyIP']);
				break;
			case 'REMOTE_ADDR':
				$retVal = $_SERVER['REMOTE_ADDR'];
				if (self::cmpIP($_SERVER['REMOTE_ADDR'], $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyIP'])) {
					$ip = self::trimExplode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
											if (count($ip)) {
						switch ($GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyHeaderMultiValue']) {
							case 'last':
								$ip = array_pop($ip);
								break;
							case 'first':
								$ip = array_shift($ip);
								break;
							case 'none':
							default:
								$ip = '';
								break;
						}
					}
					if (self::validIP($ip)) {
						$retVal = $ip;
					}
				}
				break;
			case 'HTTP_HOST':
				$retVal = $_SERVER['HTTP_HOST'];
				if (self::cmpIP($_SERVER['REMOTE_ADDR'], $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyIP'])) {
					$host = self::trimExplode(',', $_SERVER['HTTP_X_FORWARDED_HOST']);
											if (count($host)) {
						switch ($GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyHeaderMultiValue']) {
							case 'last':
								$host = array_pop($host);
								break;
							case 'first':
								$host = array_shift($host);
								break;
							case 'none':
							default:
								$host = '';
								break;
						}
					}
					if ($host) {
						$retVal = $host;
					}
				}
				if (!self::isAllowedHostHeaderValue($retVal)) {
					throw new UnexpectedValueException(
						'The current host header value does not match the configured trusted hosts pattern! Check the pattern defined in $GLOBALS[\'TYPO3_CONF_VARS\'][\'SYS\'][\'trustedHostsPattern\'] and adapt it, if you want to allow the current host header \'' . $retVal . '\' for your installation.',
						1396795884
					);
				}
				break;
							case 'HTTP_REFERER':
			case 'HTTP_USER_AGENT':
			case 'HTTP_ACCEPT_ENCODING':
			case 'HTTP_ACCEPT_LANGUAGE':
			case 'REMOTE_HOST':
			case 'QUERY_STRING':
				$retVal = $_SERVER[$getEnvName];
				break;
			case 'TYPO3_DOCUMENT_ROOT':
																								$SFN = self::getIndpEnv('SCRIPT_FILENAME');
				$SN_A = explode('/', strrev(self::getIndpEnv('SCRIPT_NAME')));
				$SFN_A = explode('/', strrev($SFN));
				$acc = array();
				foreach ($SN_A as $kk => $vv) {
					if (!strcmp($SFN_A[$kk], $vv)) {
						$acc[] = $vv;
					} else {
						break;
					}
				}
				$commonEnd = strrev(implode('/', $acc));
				if (strcmp($commonEnd, '')) {
					$DR = substr($SFN, 0, -(strlen($commonEnd) + 1));
				}
				$retVal = $DR;
				break;
			case 'TYPO3_HOST_ONLY':
				$httpHost = self::getIndpEnv('HTTP_HOST');
				$httpHostBracketPosition = strpos($httpHost, ']');
				$httpHostParts = explode(':', $httpHost);
				$retVal = ($httpHostBracketPosition !== FALSE) ? substr($httpHost, 0, ($httpHostBracketPosition + 1)) : array_shift($httpHostParts);
				break;
			case 'TYPO3_PORT':
				$httpHost = self::getIndpEnv('HTTP_HOST');
				$httpHostOnly = self::getIndpEnv('TYPO3_HOST_ONLY');
				$retVal = (strlen($httpHost) > strlen($httpHostOnly)) ? substr($httpHost, strlen($httpHostOnly) + 1) : '';
				break;
			case 'TYPO3_REQUEST_HOST':
				$retVal = (self::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://') .
						self::getIndpEnv('HTTP_HOST');
				break;
			case 'TYPO3_REQUEST_URL':
				$retVal = self::getIndpEnv('TYPO3_REQUEST_HOST') . self::getIndpEnv('REQUEST_URI');
				break;
			case 'TYPO3_REQUEST_SCRIPT':
				$retVal = self::getIndpEnv('TYPO3_REQUEST_HOST') . self::getIndpEnv('SCRIPT_NAME');
				break;
			case 'TYPO3_REQUEST_DIR':
				$retVal = self::getIndpEnv('TYPO3_REQUEST_HOST') . self::dirname(self::getIndpEnv('SCRIPT_NAME')) . '/';
				break;
			case 'TYPO3_SITE_URL':
				if (defined('PATH_thisScript') && defined('PATH_site')) {
					$lPath = substr(dirname(PATH_thisScript), strlen(PATH_site)) . '/';
					$url = self::getIndpEnv('TYPO3_REQUEST_DIR');
					$siteUrl = substr($url, 0, -strlen($lPath));
					if (substr($siteUrl, -1) != '/') {
						$siteUrl .= '/';
					}
					$retVal = $siteUrl;
				}
				break;
			case 'TYPO3_SITE_PATH':
				$retVal = substr(self::getIndpEnv('TYPO3_SITE_URL'), strlen(self::getIndpEnv('TYPO3_REQUEST_HOST')));
				break;
			case 'TYPO3_SITE_SCRIPT':
				$retVal = substr(self::getIndpEnv('TYPO3_REQUEST_URL'), strlen(self::getIndpEnv('TYPO3_SITE_URL')));
				break;
			case 'TYPO3_SSL':
				$proxySSL = trim($GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxySSL']);
				if ($proxySSL == '*') {
					$proxySSL = $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyIP'];
				}
				if (self::cmpIP(self::getIndpEnv('REMOTE_ADDR'), $proxySSL)) {
					$retVal = TRUE;
				} else {
					$retVal = $_SERVER['SSL_SESSION_ID'] || !strcasecmp($_SERVER['HTTPS'], 'on') || !strcmp($_SERVER['HTTPS'], '1') ? TRUE : FALSE; 				}
				break;
			case '_ARRAY':
				$out = array();
									$envTestVars = self::trimExplode(',', '
					HTTP_HOST,
					TYPO3_HOST_ONLY,
					TYPO3_PORT,
					PATH_INFO,
					QUERY_STRING,
					REQUEST_URI,
					HTTP_REFERER,
					TYPO3_REQUEST_HOST,
					TYPO3_REQUEST_URL,
					TYPO3_REQUEST_SCRIPT,
					TYPO3_REQUEST_DIR,
					TYPO3_SITE_URL,
					TYPO3_SITE_SCRIPT,
					TYPO3_SSL,
					TYPO3_REV_PROXY,
					SCRIPT_NAME,
					TYPO3_DOCUMENT_ROOT,
					SCRIPT_FILENAME,
					REMOTE_ADDR,
					REMOTE_HOST,
					HTTP_USER_AGENT,
					HTTP_ACCEPT_LANGUAGE', 1);
				foreach ($envTestVars as $v) {
					$out[$v] = self::getIndpEnv($v);
				}
				reset($out);
				$retVal = $out;
				break;
		}
		return $retVal;
	}

	
	static public function isAllowedHostHeaderValue($hostHeaderValue) {
		if (self::$allowHostHeaderValue === TRUE) {
			return TRUE;
		}

								if (defined('TYPO3_REQUESTTYPE') && (TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL) || (TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_CLI)) {
			return self::$allowHostHeaderValue = TRUE;
		}

				if (empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'])) {
			return FALSE;
		}

		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] === self::ENV_TRUSTED_HOSTS_PATTERN_ALLOW_ALL) {
			self::$allowHostHeaderValue = TRUE;
		} elseif ($GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] === self::ENV_TRUSTED_HOSTS_PATTERN_SERVER_NAME) {
									$defaultPort = self::getIndpEnv('TYPO3_SSL') ? '443' : '80';
			$parsedHostValue = parse_url('http://' . $hostHeaderValue);
			if (isset($parsedHostValue['port'])) {
				self::$allowHostHeaderValue = ($parsedHostValue['host'] === $_SERVER['SERVER_NAME'] && (string)$parsedHostValue['port'] === $_SERVER['SERVER_PORT']);
			} else {
				self::$allowHostHeaderValue = ($hostHeaderValue === $_SERVER['SERVER_NAME'] && $defaultPort === $_SERVER['SERVER_PORT']);
			}
		} else {
									self::$allowHostHeaderValue = (bool)preg_match('/^' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] . '$/', $hostHeaderValue);
		}

		return self::$allowHostHeaderValue;
	}

	
	public static function milliseconds() {
		return round(microtime(TRUE) * 1000);
	}

	
	public static function clientInfo($useragent = '') {
		if (!$useragent) {
			$useragent = self::getIndpEnv('HTTP_USER_AGENT');
		}

		$bInfo = array();
					if (strpos($useragent, 'Konqueror') !== FALSE) {
			$bInfo['BROWSER'] = 'konqu';
		} elseif (strpos($useragent, 'Opera') !== FALSE) {
			$bInfo['BROWSER'] = 'opera';
		} elseif (strpos($useragent, 'MSIE') !== FALSE) {
			$bInfo['BROWSER'] = 'msie';
		} elseif (strpos($useragent, 'Mozilla') !== FALSE) {
			$bInfo['BROWSER'] = 'net';
		} elseif (strpos($useragent, 'Flash') !== FALSE) {
			$bInfo['BROWSER'] = 'flash';
		}
		if ($bInfo['BROWSER']) {
							switch ($bInfo['BROWSER']) {
				case 'net':
					$bInfo['VERSION'] = doubleval(substr($useragent, 8));
					if (strpos($useragent, 'Netscape6/') !== FALSE) {
						$bInfo['VERSION'] = doubleval(substr(strstr($useragent, 'Netscape6/'), 10));
					} 					if (strpos($useragent, 'Netscape/6') !== FALSE) {
						$bInfo['VERSION'] = doubleval(substr(strstr($useragent, 'Netscape/6'), 10));
					}
					if (strpos($useragent, 'Netscape/7') !== FALSE) {
						$bInfo['VERSION'] = doubleval(substr(strstr($useragent, 'Netscape/7'), 9));
					}
					break;
				case 'msie':
					$tmp = strstr($useragent, 'MSIE');
					$bInfo['VERSION'] = doubleval(preg_replace('/^[^0-9]*/', '', substr($tmp, 4)));
					break;
				case 'opera':
					$tmp = strstr($useragent, 'Opera');
					$bInfo['VERSION'] = doubleval(preg_replace('/^[^0-9]*/', '', substr($tmp, 5)));
					break;
				case 'konqu':
					$tmp = strstr($useragent, 'Konqueror/');
					$bInfo['VERSION'] = doubleval(substr($tmp, 10));
					break;
			}
						if (strpos($useragent, 'Win') !== FALSE) {
				$bInfo['SYSTEM'] = 'win';
			} elseif (strpos($useragent, 'Mac') !== FALSE) {
				$bInfo['SYSTEM'] = 'mac';
			} elseif (strpos($useragent, 'Linux') !== FALSE || strpos($useragent, 'X11') !== FALSE || strpos($useragent, 'SGI') !== FALSE || strpos($useragent, ' SunOS ') !== FALSE || strpos($useragent, ' HP-UX ') !== FALSE) {
				$bInfo['SYSTEM'] = 'unix';
			}
		}
				$bInfo['FORMSTYLE'] = ($bInfo['BROWSER'] == 'msie' || ($bInfo['BROWSER'] == 'net' && $bInfo['VERSION'] >= 5) || $bInfo['BROWSER'] == 'opera' || $bInfo['BROWSER'] == 'konqu');

		return $bInfo;
	}

	
	public static function getHostname($requestHost = TRUE) {
		$host = '';
								if ($requestHost && (!defined('TYPO3_cliMode') || !TYPO3_cliMode)) {
			$host = self::getIndpEnv('HTTP_HOST');
		}
		if (!$host) {
							$host = @php_uname('n');
							if (strpos($host, ' ')) {
				$host = '';
			}
		}
					if ($host && strpos($host, '.') === FALSE) {
			$ip = gethostbyname($host);
							if ($ip != $host) {
				$fqdn = gethostbyaddr($ip);
				if ($ip != $fqdn) {
					$host = $fqdn;
				}
			}
		}
		if (!$host) {
			$host = 'localhost.localdomain';
		}

		return $host;
	}


	

	
	public static function getFileAbsFileName($filename, $onlyRelative = TRUE, $relToTYPO3_mainDir = FALSE) {
		if (!strcmp($filename, '')) {
			return '';
		}

		if ($relToTYPO3_mainDir) {
			if (!defined('PATH_typo3')) {
				return '';
			}
			$relPathPrefix = PATH_typo3;
		} else {
			$relPathPrefix = PATH_site;
		}
		if (substr($filename, 0, 4) == 'EXT:') { 			list($extKey, $local) = explode('/', substr($filename, 4), 2);
			$filename = '';
			if (strcmp($extKey, '') && t3lib_extMgm::isLoaded($extKey) && strcmp($local, '')) {
				$filename = t3lib_extMgm::extPath($extKey) . $local;
			}
		} elseif (!self::isAbsPath($filename)) { 			$filename = $relPathPrefix . $filename;
		} elseif ($onlyRelative && !self::isFirstPartOfStr($filename, $relPathPrefix)) { 			$filename = '';
		}
		if (strcmp($filename, '') && self::validPathStr($filename)) { 			return $filename;
		}
	}

	
	public static function validPathStr($theFile) {
		if (strpos($theFile, '//') === FALSE && strpos($theFile, '\\') === FALSE && !preg_match('#(?:^\.\.|/\.\./|[[:cntrl:]])#u', $theFile)) {
			return TRUE;
		}

		return FALSE;
	}

	
	public static function isAbsPath($path) {
					if (TYPO3_OS === 'WIN' && substr($path, 1, 2) === ':/') {
			return TRUE;
		}

					return (substr($path, 0, 1) === '/');
	}

	
	public static function isAllowedAbsPath($path) {
		if (self::isAbsPath($path) &&
				self::validPathStr($path) &&
				(self::isFirstPartOfStr($path, PATH_site)
						||
						($GLOBALS['TYPO3_CONF_VARS']['BE']['lockRootPath'] && self::isFirstPartOfStr($path, $GLOBALS['TYPO3_CONF_VARS']['BE']['lockRootPath']))
				)
		) {
			return TRUE;
		}
	}

	
	public static function verifyFilenameAgainstDenyPattern($filename) {
					if (preg_match('/[[:cntrl:]]/', $filename)) {
			return FALSE;
		}

		if (strcmp($filename, '') && strcmp($GLOBALS['TYPO3_CONF_VARS']['BE']['fileDenyPattern'], '')) {
			$result = preg_match('/' . $GLOBALS['TYPO3_CONF_VARS']['BE']['fileDenyPattern'] . '/i', $filename);
			if ($result) {
				return FALSE;
			} 		}
		return TRUE;
	}

	
	public static function sanitizeLocalUrl($url = '') {
		$sanitizedUrl = '';
		$decodedUrl = rawurldecode($url);

		if (!empty($url) && self::removeXSS($decodedUrl) === $decodedUrl) {
			$testAbsoluteUrl = self::resolveBackPath($decodedUrl);
			$testRelativeUrl = self::resolveBackPath(
				self::dirname(self::getIndpEnv('SCRIPT_NAME')) . '/' . $decodedUrl
			);

							if (self::isValidUrl($decodedUrl)) {
				if (self::isOnCurrentHost($decodedUrl) && strpos($decodedUrl, self::getIndpEnv('TYPO3_SITE_URL')) === 0) {
					$sanitizedUrl = $url;
				}
							} elseif (self::isAbsPath($decodedUrl) && self::isAllowedAbsPath($decodedUrl)) {
				$sanitizedUrl = $url;
							} elseif (strpos($testAbsoluteUrl, self::getIndpEnv('TYPO3_SITE_PATH')) === 0 && substr($decodedUrl, 0, 1) === '/') {
				$sanitizedUrl = $url;
							} elseif (strpos($testRelativeUrl, self::getIndpEnv('TYPO3_SITE_PATH')) === 0 && substr($decodedUrl, 0, 1) !== '/') {
				$sanitizedUrl = $url;
			}
		}

		if (!empty($url) && empty($sanitizedUrl)) {
			self::sysLog('The URL "' . $url . '" is not considered to be local and was denied.', 'Core', self::SYSLOG_SEVERITY_NOTICE);
		}

		return $sanitizedUrl;
	}

	
	public static function upload_copy_move($source, $destination) {
		if (is_uploaded_file($source)) {
			$uploaded = TRUE;
							$uploadedResult = move_uploaded_file($source, $destination);
		} else {
			$uploaded = FALSE;
			@copy($source, $destination);
		}

		self::fixPermissions($destination); 
					return $uploaded ? $uploadedResult : FALSE;
	}

	
	public static function upload_to_tempfile($uploadedFileName) {
		if (is_uploaded_file($uploadedFileName)) {
			$tempFile = self::tempnam('upload_temp_');
			move_uploaded_file($uploadedFileName, $tempFile);
			return @is_file($tempFile) ? $tempFile : '';
		}
	}

	
	public static function unlink_tempfile($uploadedTempFileName) {
		if ($uploadedTempFileName) {
			$uploadedTempFileName = self::fixWindowsFilePath($uploadedTempFileName);
			if (self::validPathStr($uploadedTempFileName) && self::isFirstPartOfStr($uploadedTempFileName, PATH_site . 'typo3temp/') && @is_file($uploadedTempFileName)) {
				if (unlink($uploadedTempFileName)) {
					return TRUE;
				}
			}
		}
	}

	
	public static function tempnam($filePrefix) {
		return tempnam(PATH_site . 'typo3temp/', $filePrefix);
	}

	
	public static function stdAuthCode($uid_or_record, $fields = '', $codeLength = 8) {

		if (is_array($uid_or_record)) {
			$recCopy_temp = array();
			if ($fields) {
				$fieldArr = self::trimExplode(',', $fields, 1);
				foreach ($fieldArr as $k => $v) {
					$recCopy_temp[$k] = $uid_or_record[$v];
				}
			} else {
				$recCopy_temp = $uid_or_record;
			}
			$preKey = implode('|', $recCopy_temp);
		} else {
			$preKey = $uid_or_record;
		}

		$authCode = $preKey . '||' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
		$authCode = substr(md5($authCode), 0, $codeLength);
		return $authCode;
	}

	
	public static function cHashParams($addQueryParams) {
		t3lib_div::logDeprecatedFunction();
		$params = explode('&', substr($addQueryParams, 1)); 		
		$cacheHash = t3lib_div::makeInstance('t3lib_cacheHash');
		$pA = $cacheHash->getRelevantParameters($addQueryParams);

					if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['cHashParamsHook'])) {
			$cHashParamsHook =& $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['cHashParamsHook'];
			if (is_array($cHashParamsHook)) {
				$hookParameters = array(
					'addQueryParams' => &$addQueryParams,
					'params' => &$params,
					'pA' => &$pA,
				);
				$hookReference = NULL;
				foreach ($cHashParamsHook as $hookFunction) {
					self::callUserFunction($hookFunction, $hookParameters, $hookReference);
				}
			}
		}

		return $pA;
	}

	
	public static function generateCHash($addQueryParams) {
		t3lib_div::logDeprecatedFunction();
		
		$cacheHash = t3lib_div::makeInstance('t3lib_cacheHash');
		return $cacheHash->generateForParameters($addQueryParams);
	}

	
	public static function calculateCHash($params) {
		t3lib_div::logDeprecatedFunction();
		
		$cacheHash = t3lib_div::makeInstance('t3lib_cacheHash');
		return $cacheHash->calculateCacheHash($params);
	}

	
	public static function hideIfNotTranslated($l18n_cfg_fieldValue) {
		if ($GLOBALS['TYPO3_CONF_VARS']['FE']['hidePagesIfNotTranslatedByDefault']) {
			return $l18n_cfg_fieldValue & 2 ? FALSE : TRUE;
		} else {
			return $l18n_cfg_fieldValue & 2 ? TRUE : FALSE;
		}
	}

	
	public static function hideIfDefaultLanguage($localizationConfiguration) {
		return ($localizationConfiguration & 1);
	}

	
	public static function readLLfile($fileRef, $langKey, $charset = '', $errorMode = 0) {
		
		$languageFactory = t3lib_div::makeInstance('t3lib_l10n_Factory');
		return $languageFactory->getParsedData($fileRef, $langKey, $charset, $errorMode);
	}

	
	public static function readLLPHPfile($fileRef, $langKey, $charset = '') {
		t3lib_div::logDeprecatedFunction();

		if (is_object($GLOBALS['LANG'])) {
			$csConvObj = $GLOBALS['LANG']->csConvObj;
		} elseif (is_object($GLOBALS['TSFE'])) {
			$csConvObj = $GLOBALS['TSFE']->csConvObj;
		} else {
			$csConvObj = self::makeInstance('t3lib_cs');
		}

		if (@is_file($fileRef) && $langKey) {

							$sourceCharset = $csConvObj->parse_charset($csConvObj->charSetArray[$langKey] ? $csConvObj->charSetArray[$langKey] : 'utf-8');
			if ($charset) {
				$targetCharset = $csConvObj->parse_charset($charset);
			} else {
				$targetCharset = 'utf-8';
			}

							$hashSource = substr($fileRef, strlen(PATH_site)) . '|' . date('d-m-Y H:i:s', filemtime($fileRef)) . '|version=2.3';
			$cacheFileName = PATH_site . 'typo3temp/llxml/' .
					substr(basename($fileRef), 10, 15) .
					'_' . self::shortMD5($hashSource) . '.' . $langKey . '.' . $targetCharset . '.cache';
							if (!@is_file($cacheFileName)) { 				$LOCAL_LANG = NULL;
									include($fileRef);
				if (!is_array($LOCAL_LANG)) {
					$fileName = substr($fileRef, strlen(PATH_site));
					throw new RuntimeException(
						'TYPO3 Fatal Error: "' . $fileName . '" is no TYPO3 language file!',
						1270853900
					);
				}

														if (is_array($LOCAL_LANG['default']) && $targetCharset != 'utf-8') {
					foreach ($LOCAL_LANG['default'] as &$labelValue) {
						$labelValue = $csConvObj->conv($labelValue, 'utf-8', $targetCharset);
					}
					unset($labelValue);
				}

				if ($langKey != 'default' && is_array($LOCAL_LANG[$langKey]) && $sourceCharset != $targetCharset) {
					foreach ($LOCAL_LANG[$langKey] as &$labelValue) {
						$labelValue = $csConvObj->conv($labelValue, $sourceCharset, $targetCharset);
					}
					unset($labelValue);
				}

									$serContent = array('origFile' => $hashSource, 'LOCAL_LANG' => array('default' => $LOCAL_LANG['default'], $langKey => $LOCAL_LANG[$langKey]));
				$res = self::writeFileToTypo3tempDir($cacheFileName, serialize($serContent));
				if ($res) {
					throw new RuntimeException(
						'TYPO3 Fatal Error: "' . $res,
						1270853901
					);
				}
			} else {
									$serContent = unserialize(self::getUrl($cacheFileName));
				$LOCAL_LANG = $serContent['LOCAL_LANG'];
			}

			return $LOCAL_LANG;
		}
	}

	
	public static function readLLXMLfile($fileRef, $langKey, $charset = '') {
		t3lib_div::logDeprecatedFunction();

		if (is_object($GLOBALS['LANG'])) {
			$csConvObj = $GLOBALS['LANG']->csConvObj;
		} elseif (is_object($GLOBALS['TSFE'])) {
			$csConvObj = $GLOBALS['TSFE']->csConvObj;
		} else {
			$csConvObj = self::makeInstance('t3lib_cs');
		}

		$LOCAL_LANG = NULL;
		if (@is_file($fileRef) && $langKey) {

							if ($charset) {
				$targetCharset = $csConvObj->parse_charset($charset);
			} else {
				$targetCharset = 'utf-8';
			}

							$hashSource = substr($fileRef, strlen(PATH_site)) . '|' . date('d-m-Y H:i:s', filemtime($fileRef)) . '|version=2.3';
			$cacheFileName = PATH_site . 'typo3temp/llxml/' .
					substr(basename($fileRef), 10, 15) .
					'_' . self::shortMD5($hashSource) . '.' . $langKey . '.' . $targetCharset . '.cache';

							if (!@is_file($cacheFileName)) { 
									$xmlString = self::getUrl($fileRef);
				$xmlContent = self::xml2array($xmlString);
				if (!is_array($xmlContent)) {
					$fileName = substr($fileRef, strlen(PATH_site));
					throw new RuntimeException(
						'TYPO3 Fatal Error: The file "' . $fileName . '" is no TYPO3 language file!',
						1270853902
					);
				}

									$LOCAL_LANG = array();
				$LOCAL_LANG['default'] = $xmlContent['data']['default'];

																			if (is_array($LOCAL_LANG['default']) && $targetCharset != 'utf-8') {
					foreach ($LOCAL_LANG['default'] as &$labelValue) {
						$labelValue = $csConvObj->utf8_decode($labelValue, $targetCharset);
					}
					unset($labelValue);
				}

														if ($langKey != 'default') {

											$LOCAL_LANG[$langKey] = self::llXmlAutoFileName($fileRef, $langKey);
					$localized_file = self::getFileAbsFileName($LOCAL_LANG[$langKey]);
					if (!@is_file($localized_file) && isset($xmlContent['data'][$langKey])) {
						$LOCAL_LANG[$langKey] = $xmlContent['data'][$langKey];
					}

											if (is_array($LOCAL_LANG[$langKey]) && $targetCharset != 'utf-8') {
						foreach ($LOCAL_LANG[$langKey] as &$labelValue) {
							$labelValue = $csConvObj->utf8_decode($labelValue, $targetCharset);
						}
						unset($labelValue);
					}
				}

									$serContent = array('origFile' => $hashSource, 'LOCAL_LANG' => array('default' => $LOCAL_LANG['default'], $langKey => $LOCAL_LANG[$langKey]));
				$res = self::writeFileToTypo3tempDir($cacheFileName, serialize($serContent));
				if ($res) {
					throw new RuntimeException(
						'TYPO3 Fatal Error: ' . $res,
						1270853903
					);
				}
			} else {
									$serContent = unserialize(self::getUrl($cacheFileName));
				$LOCAL_LANG = $serContent['LOCAL_LANG'];
			}

							if ($langKey != 'default' && is_string($LOCAL_LANG[$langKey]) && strlen($LOCAL_LANG[$langKey])) {

									$localized_file = self::getFileAbsFileName($LOCAL_LANG[$langKey]);
				if ($localized_file && @is_file($localized_file)) {

											$hashSource = substr($localized_file, strlen(PATH_site)) . '|' . date('d-m-Y H:i:s', filemtime($localized_file)) . '|version=2.3';
					$cacheFileName = PATH_site . 'typo3temp/llxml/EXT_' .
							substr(basename($localized_file), 10, 15) .
							'_' . self::shortMD5($hashSource) . '.' . $langKey . '.' . $targetCharset . '.cache';

											if (!@is_file($cacheFileName)) { 
													$local_xmlString = self::getUrl($localized_file);
						$local_xmlContent = self::xml2array($local_xmlString);
						if (!is_array($local_xmlContent)) {
							$fileName = substr($localized_file, strlen(PATH_site));
							throw new RuntimeException(
								'TYPO3 Fatal Error: The file "' . $fileName . '" is no TYPO3 language file!',
								1270853904
							);
						}
						$LOCAL_LANG[$langKey] = is_array($local_xmlContent['data'][$langKey]) ? $local_xmlContent['data'][$langKey] : array();

													if (is_array($LOCAL_LANG[$langKey]) && $targetCharset != 'utf-8') {
							foreach ($LOCAL_LANG[$langKey] as &$labelValue) {
								$labelValue = $csConvObj->utf8_decode($labelValue, $targetCharset);
							}
							unset($labelValue);
						}

													$serContent = array('extlang' => $langKey, 'origFile' => $hashSource, 'EXT_DATA' => $LOCAL_LANG[$langKey]);
						$res = self::writeFileToTypo3tempDir($cacheFileName, serialize($serContent));
						if ($res) {
							throw new RuntimeException(
								'TYPO3 Fatal Error: ' . $res,
								1270853905
							);
						}
					} else {
													$serContent = unserialize(self::getUrl($cacheFileName));
						$LOCAL_LANG[$langKey] = $serContent['EXT_DATA'];
					}
				} else {
					$LOCAL_LANG[$langKey] = array();
				}
			}

							foreach ($LOCAL_LANG as &$keysLabels) {
				foreach ($keysLabels as &$label) {
					$label = array(0 => array(
						'target' => $label,
					));
				}
				unset($label);
			}
			unset($keysLabels);

			return $LOCAL_LANG;
		}
	}

	
	public static function llXmlAutoFileName($fileRef, $language, $sameLocation = FALSE) {
		if ($sameLocation) {
			$location = 'EXT:';
		} else {
			$location = 'typo3conf/l10n/' . $language . '/'; 		}

					if (self::isFirstPartOfStr($fileRef, PATH_typo3 . 'sysext/')) { 			$validatedPrefix = PATH_typo3 . 'sysext/';
					} elseif (self::isFirstPartOfStr($fileRef, PATH_typo3 . 'ext/')) { 			$validatedPrefix = PATH_typo3 . 'ext/';
		} elseif (self::isFirstPartOfStr($fileRef, PATH_typo3conf . 'ext/')) { 			$validatedPrefix = PATH_typo3conf . 'ext/';
		} elseif (self::isFirstPartOfStr($fileRef, PATH_site . 'typo3_src/tests/')) { 			$validatedPrefix = PATH_site . 'typo3_src/tests/';
			$location = $validatedPrefix;
		} else {
			$validatedPrefix = '';
		}

		if ($validatedPrefix) {

							list($file_extKey, $file_extPath) = explode('/', substr($fileRef, strlen($validatedPrefix)), 2);
			$temp = self::revExplode('/', $file_extPath, 2);
			if (count($temp) == 1) {
				array_unshift($temp, '');
			} 			list($file_extPath, $file_fileName) = $temp;

							if (substr($file_fileName, 0, strlen($language) + 1) === $language . '.') {
				return $fileRef;
			}

							return $location .
					$file_extKey . '/' .
					($file_extPath ? $file_extPath . '/' : '') .
					$language . '.' . $file_fileName;
		} else {
			return NULL;
		}
	}


	
	public static function loadTCA($table) {
					global $TCA;
		if (isset($TCA[$table])) {
			$tca = &$TCA[$table];
			if (!$tca['columns']) {
				$dcf = $tca['ctrl']['dynamicConfigFile'];
				if ($dcf) {
					if (!strcmp(substr($dcf, 0, 6), 'T3LIB:')) {
						include(PATH_t3lib . 'stddb/' . substr($dcf, 6));
					} elseif (self::isAbsPath($dcf) && @is_file($dcf)) { 						include($dcf);
					} else {
						include(PATH_typo3conf . $dcf);
					}
				}
			}
		}
	}

	
	public static function resolveSheetDefInDS($dataStructArray, $sheet = 'sDEF') {
		if (!is_array($dataStructArray)) {
			return 'Data structure must be an array';
		}

		if (is_array($dataStructArray['sheets'])) {
			$singleSheet = FALSE;
			if (!isset($dataStructArray['sheets'][$sheet])) {
				$sheet = 'sDEF';
			}
			$dataStruct = $dataStructArray['sheets'][$sheet];

							if ($dataStruct && !is_array($dataStruct)) {
				$file = self::getFileAbsFileName($dataStruct);
				if ($file && @is_file($file)) {
					$dataStruct = self::xml2array(self::getUrl($file));
				}
			}
		} else {
			$singleSheet = TRUE;
			$dataStruct = $dataStructArray;
			if (isset($dataStruct['meta'])) {
				unset($dataStruct['meta']);
			} 			$sheet = 'sDEF'; 		}
		return array($dataStruct, $sheet, $singleSheet);
	}

	
	public static function resolveAllSheetsInDS(array $dataStructArray) {
		if (is_array($dataStructArray['sheets'])) {
			$out = array('sheets' => array());
			foreach ($dataStructArray['sheets'] as $sheetId => $sDat) {
				list($ds, $aS) = self::resolveSheetDefInDS($dataStructArray, $sheetId);
				if ($sheetId == $aS) {
					$out['sheets'][$aS] = $ds;
				}
			}
		} else {
			list($ds) = self::resolveSheetDefInDS($dataStructArray);
			$out = array('sheets' => array('sDEF' => $ds));
		}
		return $out;
	}

	
	public static function callUserFunction($funcName, &$params, &$ref, $checkPrefix = 'user_', $errorMode = 0) {
		$content = FALSE;

					if (is_array($GLOBALS['T3_VAR']['callUserFunction'][$funcName])) {
			return call_user_func_array(
				array(&$GLOBALS['T3_VAR']['callUserFunction'][$funcName]['obj'],
					$GLOBALS['T3_VAR']['callUserFunction'][$funcName]['method']),
				array(&$params, &$ref)
			);
		}

					if (strpos($funcName, ':') !== FALSE) {
			list($file, $funcRef) = self::revExplode(':', $funcName, 2);
			$requireFile = self::getFileAbsFileName($file);
			if ($requireFile) {
				self::requireOnce($requireFile);
			}
		} else {
			$funcRef = $funcName;
		}

					if (substr($funcRef, 0, 1) == '&') {
			$funcRef = substr($funcRef, 1);
			$storePersistentObject = TRUE;
		} else {
			$storePersistentObject = FALSE;
		}

					if ($checkPrefix && !self::hasValidClassPrefix($funcRef, array($checkPrefix))) {
			$errorMsg = "Function/class '$funcRef' was not prepended with '$checkPrefix'";
			if ($errorMode == 2) {
				throw new InvalidArgumentException($errorMsg, 1294585864);
			} elseif (!$errorMode) {
				debug($errorMsg, 't3lib_div::callUserFunction');
			}
			return FALSE;
		}

					$parts = explode('->', $funcRef);
		if (count($parts) == 2) { 
							if (class_exists($parts[0])) {

									if ($storePersistentObject) { 					if (!is_object($GLOBALS['T3_VAR']['callUserFunction_classPool'][$parts[0]])) {
						$GLOBALS['T3_VAR']['callUserFunction_classPool'][$parts[0]] = self::makeInstance($parts[0]);
					}
					$classObj = $GLOBALS['T3_VAR']['callUserFunction_classPool'][$parts[0]];
				} else { 					$classObj = self::makeInstance($parts[0]);
				}

				if (method_exists($classObj, $parts[1])) {

											if ($storePersistentObject) {
						$GLOBALS['T3_VAR']['callUserFunction'][$funcName] = array(
							'method' => $parts[1],
							'obj' => &$classObj
						);
					}
											$content = call_user_func_array(
						array(&$classObj, $parts[1]),
						array(&$params, &$ref)
					);
				} else {
					$errorMsg = "No method name '" . $parts[1] . "' in class " . $parts[0];
					if ($errorMode == 2) {
						throw new InvalidArgumentException($errorMsg, 1294585865);
					} elseif (!$errorMode) {
						debug($errorMsg, 't3lib_div::callUserFunction');
					}
				}
			} else {
				$errorMsg = 'No class named ' . $parts[0];
				if ($errorMode == 2) {
					throw new InvalidArgumentException($errorMsg, 1294585866);
				} elseif (!$errorMode) {
					debug($errorMsg, 't3lib_div::callUserFunction');
				}
			}
		} else { 			if (function_exists($funcRef)) {
				$content = call_user_func_array($funcRef, array(&$params, &$ref));
			} else {
				$errorMsg = 'No function named: ' . $funcRef;
				if ($errorMode == 2) {
					throw new InvalidArgumentException($errorMsg, 1294585867);
				} elseif (!$errorMode) {
					debug($errorMsg, 't3lib_div::callUserFunction');
				}
			}
		}
		return $content;
	}

	
	public static function getUserObj($classRef, $checkPrefix = 'user_', $silent = FALSE) {
					if (is_object($GLOBALS['T3_VAR']['getUserObj'][$classRef])) {
			return $GLOBALS['T3_VAR']['getUserObj'][$classRef];
		} else {

							if (strpos($classRef, ':') !== FALSE) {
				list($file, $class) = self::revExplode(':', $classRef, 2);
				$requireFile = self::getFileAbsFileName($file);
				if ($requireFile) {
					self::requireOnce($requireFile);
				}
			} else {
				$class = $classRef;
			}

							if (substr($class, 0, 1) == '&') {
				$class = substr($class, 1);
				$storePersistentObject = TRUE;
			} else {
				$storePersistentObject = FALSE;
			}

							if ($checkPrefix && !self::hasValidClassPrefix($class, array($checkPrefix))) {
				if (!$silent) {
					debug("Class '" . $class . "' was not prepended with '" . $checkPrefix . "'", 't3lib_div::getUserObj');
				}
				return FALSE;
			}

							if (class_exists($class)) {
				$classObj = self::makeInstance($class);

									if ($storePersistentObject) {
					$GLOBALS['T3_VAR']['getUserObj'][$classRef] = $classObj;
				}

				return $classObj;
			} else {
				if (!$silent) {
					debug("<strong>ERROR:</strong> No class named: " . $class, 't3lib_div::getUserObj');
				}
			}
		}
	}

	
	public static function hasValidClassPrefix($classRef, array $additionalPrefixes = array()) {
		if (empty($classRef)) {
			return FALSE;
		}
		if (!is_string($classRef)) {
			throw new InvalidArgumentException('$classRef has to be of type string', 1313917992);
		}
		$hasValidPrefix = FALSE;
		$validPrefixes = self::getValidClassPrefixes();
		$classRef = trim($classRef);

		if (count($additionalPrefixes)) {
			$validPrefixes = array_merge($validPrefixes, $additionalPrefixes);
		}
		foreach ($validPrefixes as $prefixToCheck) {
			if (self::isFirstPartOfStr($classRef, $prefixToCheck) || $prefixToCheck === '') {
				$hasValidPrefix = TRUE;
				break;
			}
		}

		return $hasValidPrefix;
	}

	
	public static function getValidClassPrefixes() {
		$validPrefixes = array('tx_', 'Tx_', 'user_', 'User_');
		if (
			isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['additionalAllowedClassPrefixes'])
			&& is_string($GLOBALS['TYPO3_CONF_VARS']['SYS']['additionalAllowedClassPrefixes'])
		) {
			$validPrefixes = array_merge(
				$validPrefixes,
				t3lib_div::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['SYS']['additionalAllowedClassPrefixes'])
			);
		}
		return $validPrefixes;
	}

	
	public static function makeInstance($className) {
		if (!is_string($className) || empty($className)) {
			throw new InvalidArgumentException('$className must be a non empty string.', 1288965219);
		}

								if (!isset(self::$finalClassNameRegister[$className])) {
			self::$finalClassNameRegister[$className] = self::getClassName($className);
		}
		$finalClassName = self::$finalClassNameRegister[$className];

					if (isset(self::$singletonInstances[$finalClassName])) {
			return self::$singletonInstances[$finalClassName];
		}

					if (isset(self::$nonSingletonInstances[$finalClassName])
			&& !empty(self::$nonSingletonInstances[$finalClassName])
		) {
			return array_shift(self::$nonSingletonInstances[$finalClassName]);
		}

					$instance = static::instantiateClass($finalClassName, func_get_args());

					if ($instance instanceof t3lib_Singleton) {
			self::$singletonInstances[$finalClassName] = $instance;
		}

		return $instance;
	}

	
	protected static function instantiateClass($className, $arguments) {
		switch (count($arguments)) {
			case 1:
				$instance = new $className();
				break;
			case 2:
				$instance = new $className($arguments[1]);
				break;
			case 3:
				$instance = new $className($arguments[1], $arguments[2]);
				break;
			case 4:
				$instance = new $className($arguments[1], $arguments[2], $arguments[3]);
				break;
			case 5:
				$instance = new $className($arguments[1], $arguments[2], $arguments[3], $arguments[4]);
				break;
			case 6:
				$instance = new $className($arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5]);
				break;
			case 7:
				$instance = new $className($arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5], $arguments[6]);
				break;
			case 8:
				$instance = new $className($arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5], $arguments[6], $arguments[7]);
				break;
			case 9:
				$instance = new $className($arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5], $arguments[6], $arguments[7], $arguments[8]);
				break;
			default:
																$class = new ReflectionClass($className);
				array_shift($arguments);
				$instance = $class->newInstanceArgs($arguments);
				return $instance;
		}
		return $instance;
	}

	
	protected static function getClassName($className) {
		if (class_exists($className)) {
			while (class_exists('ux_' . $className, FALSE)) {
				$className = 'ux_' . $className;
			}
		}

		return $className;
	}

	
	public static function setSingletonInstance($className, t3lib_Singleton $instance) {
		self::checkInstanceClassName($className, $instance);
		self::$singletonInstances[$className] = $instance;
	}

	
	public static function addInstance($className, $instance) {
		self::checkInstanceClassName($className, $instance);

		if ($instance instanceof t3lib_Singleton) {
			throw new InvalidArgumentException(
				'$instance must not be an instance of t3lib_Singleton. ' .
					'For setting singletons, please use setSingletonInstance.',
				1288969325
			);
		}

		if (!isset(self::$nonSingletonInstances[$className])) {
			self::$nonSingletonInstances[$className] = array();
		}
		self::$nonSingletonInstances[$className][] = $instance;
	}

	
	protected static function checkInstanceClassName($className, $instance) {
		if ($className === '') {
			throw new InvalidArgumentException('$className must not be empty.', 1288967479);
		}
		if (!($instance instanceof $className)) {
			throw new InvalidArgumentException(
				'$instance must be an instance of ' . $className . ', but actually is an instance of ' . get_class($instance) . '.',
				1288967686
			);
		}
	}

	
	public static function purgeInstances() {
		self::$singletonInstances = array();
		self::$nonSingletonInstances = array();
	}

	
	public static function makeInstanceService($serviceType, $serviceSubType = '', $excludeServiceKeys = array()) {
		$error = FALSE;

		if (!is_array($excludeServiceKeys)) {
			$excludeServiceKeys = self::trimExplode(',', $excludeServiceKeys, 1);
		}

		$requestInfo = array(
			'requestedServiceType' => $serviceType,
			'requestedServiceSubType' => $serviceSubType,
			'requestedExcludeServiceKeys' => $excludeServiceKeys,
		);

		while ($info = t3lib_extMgm::findService($serviceType, $serviceSubType, $excludeServiceKeys)) {

							$info = array_merge($info, $requestInfo);

							if (is_object($GLOBALS['T3_VAR']['makeInstanceService'][$info['className']])) {

									$GLOBALS['T3_VAR']['makeInstanceService'][$info['className']]->info = $info;

									$GLOBALS['T3_VAR']['makeInstanceService'][$info['className']]->reset();
				return $GLOBALS['T3_VAR']['makeInstanceService'][$info['className']];

							} else {
				$requireFile = self::getFileAbsFileName($info['classFile']);
				if (@is_file($requireFile)) {
					self::requireOnce($requireFile);
					$obj = self::makeInstance($info['className']);
					if (is_object($obj)) {
						if (!@is_callable(array($obj, 'init'))) {
															die ('Broken service:' . t3lib_utility_Debug::viewArray($info));
						}
						$obj->info = $info;
						if ($obj->init()) { 
															$GLOBALS['T3_VAR']['makeInstanceService'][$info['className']] = $obj;

															register_shutdown_function(array(&$obj, '__destruct'));

							return $obj; 						}
						$error = $obj->getLastErrorArray();
						unset($obj);
					}
				}
			}
							t3lib_extMgm::deactivateService($info['serviceType'], $info['serviceKey']);
		}
		return $error;
	}

	
	public static function requireOnce($requireFile) {
					global $T3_SERVICES, $T3_VAR, $TYPO3_CONF_VARS;

		require_once ($requireFile);
	}

	
	public static function requireFile($requireFile) {
					global $T3_SERVICES, $T3_VAR, $TYPO3_CONF_VARS;
		require $requireFile;
	}

	
	public static function plainMailEncoded($email, $subject, $message, $headers = '', $encoding = 'quoted-printable', $charset = '', $dontEncodeHeader = FALSE) {
		if (!$charset) {
			$charset = 'utf-8';
		}

		$email = self::normalizeMailAddress($email);
		if (!$dontEncodeHeader) {
							$newHeaders = array();
			foreach (explode(LF, $headers) as $line) { 				$parts = explode(': ', $line, 2); 				if (count($parts) == 2) {
					if (0 == strcasecmp($parts[0], 'from')) {
						$parts[1] = self::normalizeMailAddress($parts[1]);
					}
					$parts[1] = self::encodeHeader($parts[1], $encoding, $charset);
					$newHeaders[] = implode(': ', $parts);
				} else {
					$newHeaders[] = $line; 				}
			}
			$headers = implode(LF, $newHeaders);
			unset($newHeaders);

			$email = self::encodeHeader($email, $encoding, $charset); 			$subject = self::encodeHeader($subject, $encoding, $charset);
		}

		switch ((string) $encoding) {
			case 'base64':
				$headers = trim($headers) . LF .
						'Mime-Version: 1.0' . LF .
						'Content-Type: text/plain; charset="' . $charset . '"' . LF .
						'Content-Transfer-Encoding: base64';

				$message = trim(chunk_split(base64_encode($message . LF))) . LF; 				break;
			case '8bit':
				$headers = trim($headers) . LF .
						'Mime-Version: 1.0' . LF .
						'Content-Type: text/plain; charset=' . $charset . LF .
						'Content-Transfer-Encoding: 8bit';
				break;
			case 'quoted-printable':
			default:
				$headers = trim($headers) . LF .
						'Mime-Version: 1.0' . LF .
						'Content-Type: text/plain; charset=' . $charset . LF .
						'Content-Transfer-Encoding: quoted-printable';

				$message = self::quoted_printable($message);
				break;
		}

											$headers = trim(implode(LF, self::trimExplode(LF, $headers, TRUE))); 
		return t3lib_utility_Mail::mail($email, $subject, $message, $headers);
	}

	
	public static function quoted_printable($string, $maxlen = 76) {
					$string = str_replace(CRLF, LF, $string); 		$string = str_replace(CR, LF, $string); 
		$linebreak = LF; 		if (TYPO3_OS == 'WIN') {
			$linebreak = CRLF; 		}

		$newString = '';
		$theLines = explode(LF, $string); 		foreach ($theLines as $val) {
			$newVal = '';
			$theValLen = strlen($val);
			$len = 0;
			for ($index = 0; $index < $theValLen; $index++) { 				$char = substr($val, $index, 1);
				$ordVal = ord($char);
				if ($len > ($maxlen - 4) || ($len > ($maxlen - 14) && $ordVal == 32)) {
					$newVal .= '=' . $linebreak; 					$len = 0; 				}
				if (($ordVal >= 33 && $ordVal <= 60) || ($ordVal >= 62 && $ordVal <= 126) || $ordVal == 9 || $ordVal == 32) {
					$newVal .= $char; 					$len++;
				} else {
					$newVal .= sprintf('=%02X', $ordVal); 					$len += 3;
				}
			}
			$newVal = preg_replace('/' . chr(32) . '$/', '=20', $newVal); 			$newVal = preg_replace('/' . TAB . '$/', '=09', $newVal); 			$newString .= $newVal . $linebreak;
		}
		return preg_replace('/' . $linebreak . '$/', '', $newString); 	}

	
	public static function encodeHeader($line, $enc = 'quoted-printable', $charset = 'utf-8') {
					if (strpos($line, '###') !== FALSE) {
			return $line;
		}
					if (!preg_match('/[^' . chr(32) . '-' . chr(127) . ']/', $line)) {
			return $line;
		}
					$line = preg_replace('/([^ ]+@[^ ]+)/', '###$1###', $line);

		$matches = preg_split('/(.?###.+###.?|\(|\))/', $line, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($matches as $part) {
			$oldPart = $part;
			$partWasQuoted = ($part{0} == '"');
			$part = trim($part, '"');
			switch ((string) $enc) {
				case 'base64':
					$part = '=?' . $charset . '?B?' . base64_encode($part) . '?=';
					break;
				case 'quoted-printable':
				default:
					$qpValue = self::quoted_printable($part, 1000);
					if ($part != $qpValue) {
																											$search = array(' ', '?');
						$replace = array('_', '=3F');
						$qpValue = str_replace($search, $replace, $qpValue);
						$part = '=?' . $charset . '?Q?' . $qpValue . '?=';
					}
					break;
			}
			if ($partWasQuoted) {
				$part = '"' . $part . '"';
			}
			$line = str_replace($oldPart, $part, $line);
		}
		$line = preg_replace('/###(.+?)###/', '$1', $line); 
		return $line;
	}

	
	public static function substUrlsInPlainText($message, $urlmode = '76', $index_script_url = '') {
		$lengthLimit = FALSE;

		switch ((string) $urlmode) {
			case '':
				$lengthLimit = FALSE;
				break;
			case 'all':
				$lengthLimit = 0;
				break;
			case '76':
			default:
				$lengthLimit = (int) $urlmode;
		}

		if ($lengthLimit === FALSE) {
							$messageSubstituted = $message;
		} else {
			$messageSubstituted = preg_replace(
				'/(http|https):\/\/.+(?=[\]\.\?]*([\! \'"()<>]+|$))/eiU',
				'self::makeRedirectUrl("\\0",' . $lengthLimit . ',"' . $index_script_url . '")',
				$message
			);
		}

		return $messageSubstituted;
	}

	
	public static function makeRedirectUrl($inUrl, $l = 0, $index_script_url = '') {
		if (strlen($inUrl) > $l) {
			$md5 = substr(md5($inUrl), 0, 20);
			$count = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
				'*',
				'cache_md5params',
					'md5hash=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($md5, 'cache_md5params')
			);
			if (!$count) {
				$insertFields = array(
					'md5hash' => $md5,
					'tstamp' => $GLOBALS['EXEC_TIME'],
					'type' => 2,
					'params' => $inUrl
				);

				$GLOBALS['TYPO3_DB']->exec_INSERTquery('cache_md5params', $insertFields);
			}
			$inUrl = ($index_script_url ? $index_script_url : self::getIndpEnv('TYPO3_REQUEST_DIR') . 'index.php') .
					'?RDCT=' . $md5;
		}

		return $inUrl;
	}

	
	public static function freetypeDpiComp($font_size) {
		$dpi = intval($GLOBALS['TYPO3_CONF_VARS']['GFX']['TTFdpi']);
		if ($dpi != 72) {
			$font_size = $font_size / $dpi * 72;
		}
		return $font_size;
	}

	
	public static function initSysLog() {
								if (defined('TYPO3_cliMode') && TYPO3_cliMode) {
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLogHost'] = self::getHostname($requestHost = FALSE) . ':' . PATH_site;
		}
					else {
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLogHost'] = self::getIndpEnv('TYPO3_SITE_URL');
		}

					if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'])) {
			$params = array('initLog' => TRUE);
			$fakeThis = FALSE;
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'] as $hookMethod) {
				self::callUserFunction($hookMethod, $params, $fakeThis);
			}
		}

					foreach (explode(';', $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'], 2) as $log) {
			list($type, $destination) = explode(',', $log, 3);

			if ($type == 'syslog') {
				if (TYPO3_OS == 'WIN') {
					$facility = LOG_USER;
				} else {
					$facility = constant('LOG_' . strtoupper($destination));
				}
				openlog($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLogHost'], LOG_ODELAY, $facility);
			}
		}

		$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLogLevel'] = t3lib_utility_Math::forceIntegerInRange($GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLogLevel'], 0, 4);
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLogInit'] = TRUE;
	}

	
	public static function sysLog($msg, $extKey, $severity = 0) {
		$severity = t3lib_utility_Math::forceIntegerInRange($severity, 0, 4);

					if (intval($GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLogLevel']) > $severity) {
			return;
		}

					if (!$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLogInit']) {
			self::initSysLog();
		}

					if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog']) &&
				is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'])) {
			$params = array('msg' => $msg, 'extKey' => $extKey, 'backTrace' => debug_backtrace(), 'severity' => $severity);
			$fakeThis = FALSE;
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'] as $hookMethod) {
				self::callUserFunction($hookMethod, $params, $fakeThis);
			}
		}

					if (!$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog']) {
			return;
		}

		$dateFormat = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'];
		$timeFormat = $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'];

					foreach (explode(';', $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'], 2) as $log) {
			list($type, $destination, $level) = explode(',', $log, 4);

							if (intval($level) > $severity) {
				continue;
			}

			$msgLine = ' - ' . $extKey . ': ' . $msg;

							if ($type == 'file') {
				$lockObject = t3lib_div::makeInstance('t3lib_lock', $destination, $GLOBALS['TYPO3_CONF_VARS']['SYS']['lockingMode']);
				
				$lockObject->setEnableLogging(FALSE);
				$lockObject->acquire();
				$file = fopen($destination, 'a');
				if ($file) {
					fwrite($file, date($dateFormat . ' ' . $timeFormat) . $msgLine . LF);
					fclose($file);
					self::fixPermissions($destination);
				}
				$lockObject->release();
			}
							elseif ($type == 'mail') {
				list($to, $from) = explode('/', $destination);
				if (!t3lib_div::validEmail($from)) {
					$from = t3lib_utility_Mail::getSystemFrom();
				}
				
				$mail = t3lib_div::makeInstance('t3lib_mail_Message');
				$mail->setTo($to)
						->setFrom($from)
						->setSubject('Warning - error in TYPO3 installation')
						->setBody('Host: ' . $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLogHost'] . LF .
								'Extension: ' . $extKey . LF .
								'Severity: ' . $severity . LF .
								LF . $msg
				);
				$mail->send();
			}
							elseif ($type == 'error_log') {
				error_log($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLogHost'] . $msgLine, 0);
			}
							elseif ($type == 'syslog') {
				$priority = array(LOG_INFO, LOG_NOTICE, LOG_WARNING, LOG_ERR, LOG_CRIT);
				syslog($priority[(int) $severity], $msgLine);
			}
		}
	}

	
	public static function devLog($msg, $extKey, $severity = 0, $dataVar = FALSE) {
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['devLog'])) {
			$params = array('msg' => $msg, 'extKey' => $extKey, 'severity' => $severity, 'dataVar' => $dataVar);
			$fakeThis = FALSE;
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['devLog'] as $hookMethod) {
				self::callUserFunction($hookMethod, $params, $fakeThis);
			}
		}
	}

	
	public static function deprecationLog($msg) {
		if (!$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog']) {
			return;
		}

		$log = $GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'];
		$date = date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'] . ': ');

					if ($log === TRUE || $log == '1') {
			$log = 'file';
		}

		if (stripos($log, 'file') !== FALSE) {
							if (class_exists('t3lib_lock') === FALSE) {
				require_once PATH_t3lib . 'class.t3lib_lock.php';
			}
							$destination = self::getDeprecationLogFileName();
			$lockObject = t3lib_div::makeInstance('t3lib_lock', $destination, $GLOBALS['TYPO3_CONF_VARS']['SYS']['lockingMode']);
			
			$lockObject->setEnableLogging(FALSE);
			$lockObject->acquire();
			$file = @fopen($destination, 'a');
			if ($file) {
				@fwrite($file, $date . $msg . LF);
				@fclose($file);
				self::fixPermissions($destination);
			}
			$lockObject->release();
		}

		if (stripos($log, 'devlog') !== FALSE) {
							self::devLog($msg, 'Core', self::SYSLOG_SEVERITY_WARNING);
		}

					if (stripos($log, 'console') !== FALSE && isset($GLOBALS['BE_USER']->user['uid'])) {
			t3lib_utility_Debug::debug($msg, $date, 'Deprecation Log');
		}
	}

	
	public static function getDeprecationLogFileName() {
		return PATH_typo3conf .
				'deprecation_' .
				self::shortMD5(
					PATH_site . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']
				) .
				'.log';
	}

	
	public static function logDeprecatedFunction() {
		if (!$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog']) {
			return;
		}

														require_once __DIR__.'/class.t3lib_utility_debug.php';

		$trail = debug_backtrace();

		if ($trail[1]['type']) {
			$function = new ReflectionMethod($trail[1]['class'], $trail[1]['function']);
		} else {
			$function = new ReflectionFunction($trail[1]['function']);
		}

		$msg = '';
		if (preg_match('/@deprecated\s+(.*)/', $function->getDocComment(), $match)) {
			$msg = $match[1];
		}

					$errorMsg = 'Function ' . $trail[1]['function'];
		if ($trail[1]['class']) {
			$errorMsg .= ' of class ' . $trail[1]['class'];
		}
		$errorMsg .= ' is deprecated (called from ' . $trail[1]['file'] . '#' . $trail[1]['line'] . ', defined in ' . $function->getFileName() . '#' . $function->getStartLine() . ')';

					$logMsg = $trail[1]['class'] . $trail[1]['type'] . $trail[1]['function'];
		$logMsg .= '() - ' . $msg.' - ' . t3lib_utility_Debug::debugTrail();
		$logMsg .= ' (' . substr($function->getFileName(), strlen(PATH_site)) . '#' . $function->getStartLine() . ')';
		self::deprecationLog($logMsg);
	}

	
	public static function arrayToLogString(array $arr, $valueList = array(), $valueLength = 20) {
		$str = '';
		if (!is_array($valueList)) {
			$valueList = self::trimExplode(',', $valueList, 1);
		}
		$valListCnt = count($valueList);
		foreach ($arr as $key => $value) {
			if (!$valListCnt || in_array($key, $valueList)) {
				$str .= (string) $key . trim(': ' . self::fixed_lgd_cs(str_replace(LF, '|', (string) $value), $valueLength)) . '; ';
			}
		}
		return $str;
	}

	
	public static function imageMagickCommand($command, $parameters, $path = '') {
		return t3lib_utility_Command::imageMagickCommand($command, $parameters, $path);
	}

	
	public static function unQuoteFilenames($parameters, $unQuote = FALSE) {
		$paramsArr = explode(' ', trim($parameters));

		$quoteActive = -1; 		foreach ($paramsArr as $k => $v) {
			if ($quoteActive > -1) {
				$paramsArr[$quoteActive] .= ' ' . $v;
				unset($paramsArr[$k]);
				if (substr($v, -1) === $paramsArr[$quoteActive][0]) {
					$quoteActive = -1;
				}
			} elseif (!trim($v)) {
				unset($paramsArr[$k]); 
			} elseif (preg_match('/^(["\'])/', $v) && substr($v, -1) !== $v[0]) {
				$quoteActive = $k;
			}
		}

		if ($unQuote) {
			foreach ($paramsArr as $key => &$val) {
				$val = preg_replace('/(^"|"$)/', '', $val);
				$val = preg_replace('/(^\'|\'$)/', '', $val);

			}
			unset($val);
		}
					return array_values($paramsArr);
	}


	
	public static function quoteJSvalue($value) {
		$escapedValue = t3lib_div::makeInstance('t3lib_codec_JavaScriptEncoder')->encode($value);
		return '\'' . $escapedValue . '\'';
	}


	
	public static function cleanOutputBuffers() {
		while (ob_end_clean()) {
			;
		}
		header('Content-Encoding: None', TRUE);
	}


	
	public static function flushOutputBuffers() {
		$obContent = '';

		while ($content = ob_get_clean()) {
			$obContent .= $content;
		}

					if (!headers_sent()) {
			$headersList = headers_list();
			foreach ($headersList as $header) {
									list($key, $value) = self::trimExplode(':', $header, TRUE);
									if (strtolower($key) === 'content-encoding' && strtolower($value) !== 'none') {
					header('Content-Encoding: None');
					break;
				}
			}
		}
		echo $obContent;
	}
}

?>
