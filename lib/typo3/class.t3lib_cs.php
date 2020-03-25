<?php






class t3lib_cs {

	
	protected $locales;

	var $noCharByteVal = 63; 
			var $parsedCharsets = array();

			var $caseFolding = array();

			var $toASCII = array();

			var $twoByteSets = array(
		'ucs-2' => 1, 	);

			var $fourByteSets = array(
		'ucs-4' => 1, 		'utf-32' => 1, 	);

			var $eucBasedSets = array(
		'gb2312' => 1, 		'big5' => 1, 		'euc-kr' => 1, 		'shift_jis' => 1, 	);

					var $synonyms = array(
		'us' => 'ascii',
		'us-ascii' => 'ascii',
		'cp819' => 'iso-8859-1',
		'ibm819' => 'iso-8859-1',
		'iso-ir-100' => 'iso-8859-1',
		'iso-ir-101' => 'iso-8859-2',
		'iso-ir-109' => 'iso-8859-3',
		'iso-ir-110' => 'iso-8859-4',
		'iso-ir-144' => 'iso-8859-5',
		'iso-ir-127' => 'iso-8859-6',
		'iso-ir-126' => 'iso-8859-7',
		'iso-ir-138' => 'iso-8859-8',
		'iso-ir-148' => 'iso-8859-9',
		'iso-ir-157' => 'iso-8859-10',
		'iso-ir-179' => 'iso-8859-13',
		'iso-ir-199' => 'iso-8859-14',
		'iso-ir-203' => 'iso-8859-15',
		'csisolatin1' => 'iso-8859-1',
		'csisolatin2' => 'iso-8859-2',
		'csisolatin3' => 'iso-8859-3',
		'csisolatin5' => 'iso-8859-9',
		'csisolatin8' => 'iso-8859-14',
		'csisolatin9' => 'iso-8859-15',
		'csisolatingreek' => 'iso-8859-7',
		'iso-celtic' => 'iso-8859-14',
		'latin1' => 'iso-8859-1',
		'latin2' => 'iso-8859-2',
		'latin3' => 'iso-8859-3',
		'latin5' => 'iso-8859-9',
		'latin6' => 'iso-8859-10',
		'latin8' => 'iso-8859-14',
		'latin9' => 'iso-8859-15',
		'l1' => 'iso-8859-1',
		'l2' => 'iso-8859-2',
		'l3' => 'iso-8859-3',
		'l5' => 'iso-8859-9',
		'l6' => 'iso-8859-10',
		'l8' => 'iso-8859-14',
		'l9' => 'iso-8859-15',
		'cyrillic' => 'iso-8859-5',
		'arabic' => 'iso-8859-6',
		'tis-620' => 'iso-8859-11',
		'win874' => 'windows-874',
		'win1250' => 'windows-1250',
		'win1251' => 'windows-1251',
		'win1252' => 'windows-1252',
		'win1253' => 'windows-1253',
		'win1254' => 'windows-1254',
		'win1255' => 'windows-1255',
		'win1256' => 'windows-1256',
		'win1257' => 'windows-1257',
		'win1258' => 'windows-1258',
		'cp1250' => 'windows-1250',
		'cp1251' => 'windows-1251',
		'cp1252' => 'windows-1252',
		'ms-ee' => 'windows-1250',
		'ms-ansi' => 'windows-1252',
		'ms-greek' => 'windows-1253',
		'ms-turk' => 'windows-1254',
		'winbaltrim' => 'windows-1257',
		'koi-8ru' => 'koi-8r',
		'koi8r' => 'koi-8r',
		'cp878' => 'koi-8r',
		'mac' => 'macroman',
		'macintosh' => 'macroman',
		'euc-cn' => 'gb2312',
		'x-euc-cn' => 'gb2312',
		'euccn' => 'gb2312',
		'cp936' => 'gb2312',
		'big-5' => 'big5',
		'cp950' => 'big5',
		'eucjp' => 'euc-jp',
		'sjis' => 'shift_jis',
		'shift-jis' => 'shift_jis',
		'cp932' => 'shift_jis',
		'cp949' => 'euc-kr',
		'utf7' => 'utf-7',
		'utf8' => 'utf-8',
		'utf16' => 'utf-16',
		'utf32' => 'utf-32',
		'utf8' => 'utf-8',
		'ucs2' => 'ucs-2',
		'ucs4' => 'ucs-4',
	);

			var $lang_to_script = array(
					'af' => 'west_european', 		'ar' => 'arabic',
		'bg' => 'cyrillic', 		'bs' => 'east_european', 		'cs' => 'east_european', 		'da' => 'west_european', 		'de' => 'west_european', 		'es' => 'west_european', 		'et' => 'estonian',
		'eo' => 'unicode', 		'eu' => 'west_european', 		'fa' => 'arabic', 		'fi' => 'west_european', 		'fo' => 'west_european', 		'fr' => 'west_european', 		'ga' => 'west_european', 		'gl' => 'west_european', 		'gr' => 'greek',
		'he' => 'hebrew', 		'hi' => 'unicode', 		'hr' => 'east_european', 		'hu' => 'east_european', 		'iw' => 'hebrew', 		'is' => 'west_european', 		'it' => 'west_european', 		'ja' => 'japanese',
		'ka' => 'unicode', 		'kl' => 'west_european', 		'km' => 'unicode', 		'ko' => 'korean',
		'lt' => 'lithuanian',
		'lv' => 'west_european', 		'nl' => 'west_european', 		'no' => 'west_european', 		'nb' => 'west_european', 		'nn' => 'west_european', 		'pl' => 'east_european', 		'pt' => 'west_european', 		'ro' => 'east_european', 		'ru' => 'cyrillic', 		'sk' => 'east_european', 		'sl' => 'east_european', 		'sr' => 'cyrillic', 		'sv' => 'west_european', 		'sq' => 'albanian', 		'th' => 'thai',
		'uk' => 'cyrillic', 		'vi' => 'vietnamese',
		'zh' => 'chinese',
								'afk'=> 'west_european', 		'ara' => 'arabic',
		'bgr' => 'cyrillic', 		'cat' => 'west_european', 		'chs' => 'simpl_chinese',
		'cht' => 'trad_chinese',
		'csy' => 'east_european', 		'dan' => 'west_european', 		'deu' => 'west_european', 		'dea' => 'west_european', 		'des' => 'west_european', 		'ena' => 'west_european', 		'enc' => 'west_european', 		'eng' => 'west_european', 		'enz' => 'west_european', 		'enu' => 'west_european', 		'euq' => 'west_european', 		'fos' => 'west_european', 		'far' => 'arabic', 		'fin' => 'west_european', 		'fra' => 'west_european', 		'frb' => 'west_european', 		'frc' => 'west_european', 		'frs' => 'west_european', 		'geo' => 'unicode', 		'glg' => 'west_european', 		'ell' => 'greek',
		'heb' => 'hebrew',
		'hin' => 'unicode', 		'hun' => 'east_european', 		'isl' => 'west_euorpean', 		'ita' => 'west_european', 		'its' => 'west_european', 		'jpn' => 'japanese',
		'khm' => 'unicode', 		'kor' => 'korean',
		'lth' => 'lithuanian',
		'lvi' => 'west_european', 		'msl' => 'west_european', 		'nlb' => 'west_european', 		'nld' => 'west_european', 		'nor' => 'west_european', 		'non' => 'west_european', 		'plk' => 'east_european', 		'ptg' => 'west_european', 		'ptb' => 'west_european', 		'rom' => 'east_european', 		'rus' => 'cyrillic', 		'slv' => 'east_european', 		'sky' => 'east_european', 		'srl' => 'east_european', 		'srb' => 'cyrillic', 		'esp' => 'west_european', 		'esm' => 'west_european', 		'esn' => 'west_european', 		'sve' => 'west_european', 		'sqi' => 'albanian', 		'tha' => 'thai',
		'trk' => 'turkish',
		'ukr' => 'cyrillic', 					'afrikaans' => 'west_european',
		'albanian' => 'albanian',
		'arabic' => 'arabic',
		'basque' => 'west_european',
		'bosnian' => 'east_european',
		'bulgarian' => 'east_european',
		'catalan' => 'west_european',
		'croatian' => 'east_european',
		'czech' => 'east_european',
		'danish' => 'west_european',
		'dutch' => 'west_european',
		'english' => 'west_european',
		'esperanto' => 'unicode',
		'estonian' => 'estonian',
		'faroese' => 'west_european',
		'farsi' => 'arabic',
		'finnish' => 'west_european',
		'french' => 'west_european',
		'galician' => 'west_european',
		'georgian' => 'unicode',
		'german' => 'west_european',
		'greek' => 'greek',
		'greenlandic' => 'west_european',
		'hebrew' => 'hebrew',
		'hindi' => 'unicode',
		'hungarian' => 'east_european',
		'icelandic' => 'west_european',
		'italian' => 'west_european',
		'khmer' => 'unicode',
		'latvian' => 'west_european',
		'lettish' => 'west_european',
		'lithuanian' => 'lithuanian',
		'malay' => 'west_european',
		'norwegian' => 'west_european',
		'persian' => 'arabic',
		'polish' => 'east_european',
		'portuguese' => 'west_european',
		'russian' => 'cyrillic',
		'romanian' => 'east_european',
		'serbian' => 'cyrillic',
		'slovak' => 'east_european',
		'slovenian' => 'east_european',
		'spanish' => 'west_european',
		'svedish' => 'west_european',
		'that' => 'thai',
		'turkish' => 'turkish',
		'ukrainian' => 'cyrillic',
	);

			var $script_to_charset_unix = array(
		'west_european' => 'iso-8859-1',
		'estonian' => 'iso-8859-1',
		'east_european' => 'iso-8859-2',
		'baltic' => 'iso-8859-4',
		'cyrillic' => 'iso-8859-5',
		'arabic' => 'iso-8859-6',
		'greek' => 'iso-8859-7',
		'hebrew' => 'iso-8859-8',
		'turkish' => 'iso-8859-9',
		'thai' => 'iso-8859-11', 		'lithuanian' => 'iso-8859-13',
		'chinese' => 'gb2312', 		'japanese' => 'euc-jp',
		'korean' => 'euc-kr',
		'simpl_chinese' => 'gb2312',
		'trad_chinese' => 'big5',
		'vietnamese' => '',
		'unicode' => 'utf-8',
		'albanian' => 'utf-8'
	);

			var $script_to_charset_windows = array(
		'east_european' => 'windows-1250',
		'cyrillic' => 'windows-1251',
		'west_european' => 'windows-1252',
		'greek' => 'windows-1253',
		'turkish' => 'windows-1254',
		'hebrew' => 'windows-1255',
		'arabic' => 'windows-1256',
		'baltic' => 'windows-1257',
		'estonian' => 'windows-1257',
		'lithuanian' => 'windows-1257',
		'vietnamese' => 'windows-1258',
		'thai' => 'cp874',
		'korean' => 'cp949',
		'chinese' => 'gb2312',
		'japanese' => 'shift_jis',
		'simpl_chinese' => 'gb2312',
		'trad_chinese' => 'big5',
		'albanian' => 'windows-1250',
		'unicode' => 'utf-8'
	);

			var $locale_to_charset = array(
		'japanese.euc' => 'euc-jp',
		'ja_jp.ujis' => 'euc-jp',
		'korean.euc' => 'euc-kr',
		'sr@Latn' => 'iso-8859-2',
		'zh_cn' => 'gb2312',
		'zh_hk' => 'big5',
		'zh_tw' => 'big5',
	);

					var $charSetArray = array(
		'af' => '',
		'ar' => 'iso-8859-6',
		'ba' => 'iso-8859-2',
		'bg' => 'windows-1251',
		'br' => '',
		'ca' => 'iso-8859-15',
		'ch' => 'gb2312',
		'cs' => 'windows-1250',
		'cz' => 'windows-1250',
		'da' => '',
		'de' => '',
		'dk' => '',
		'el' => 'iso-8859-7',
		'eo' => 'utf-8',
		'es' => '',
		'et' => 'iso-8859-4',
		'eu' => '',
		'fa' => 'utf-8',
		'fi' => '',
		'fo' => 'utf-8',
		'fr' => '',
		'fr_CA' => '',
		'ga' => '',
		'ge' => 'utf-8',
		'gl' => '',
		'gr' => 'iso-8859-7',
		'he' => 'utf-8',
		'hi' => 'utf-8',
		'hk' => 'big5',
		'hr' => 'windows-1250',
		'hu' => 'iso-8859-2',
		'is' => 'utf-8',
		'it' => '',
		'ja' => 'shift_jis',
		'jp' => 'shift_jis',
		'ka' => 'utf-8',
		'kl' => 'utf-8',
		'km' => 'utf-8',
		'ko' => 'euc-kr',
		'kr' => 'euc-kr',
		'lt' => 'windows-1257',
		'lv' => 'utf-8',
		'ms' => '',
		'my' => '',
		'nl' => '',
		'no' => '',
		'pl' => 'iso-8859-2',
		'pt' => '',
		'pt_BR' => '',
		'qc' => '',
		'ro' => 'iso-8859-2',
		'ru' => 'windows-1251',
		'se' => '',
		'si' => 'windows-1250',
		'sk' => 'windows-1250',
		'sl' => 'windows-1250',
		'sq' => 'utf-8',
		'sr' => 'utf-8',
		'sv' => '',
		'th' => 'iso-8859-11',
		'tr' => 'iso-8859-9',
		'ua' => 'windows-1251',
		'uk' => 'windows-1251',
		'vi' => 'utf-8',
		'vn' => 'utf-8',
		'zh' => 'big5',
	);

							var $isoArray = array(
		'ba' => 'bs',
		'br' => 'pt_BR',
		'ch' => 'zh_CN',
		'cz' => 'cs',
		'dk' => 'da',
		'si' => 'sl',
		'se' => 'sv',
		'gl' => 'kl',
		'gr' => 'el',
		'hk' => 'zh_HK',
		'kr' => 'ko',
		'ua' => 'uk',
		'jp' => 'ja',
		'qc' => 'fr_CA',
		'vn' => 'vi',
		'ge' => 'ka',
		'ga' => 'gl',
	);

	
	public function __construct() {
		$this->locales = t3lib_div::makeInstance('t3lib_l10n_Locales');
	}

	
	function parse_charset($charset) {
		$charset = trim(strtolower($charset));
		if (isset($this->synonyms[$charset])) {
			$charset = $this->synonyms[$charset];
		}

		return $charset;
	}

	
	function get_locale_charset($locale) {
		$locale = strtolower($locale);

					if (isset($this->locale_to_charset[$locale])) {
			return $this->locale_to_charset[$locale];
		}

					list($locale, $modifier) = explode('@', $locale);

					list($locale, $charset) = explode('.', $locale);
		if ($charset) {
			return $this->parse_charset($charset);
		}

					if ($modifier == 'euro') {
			return 'iso-8859-15';
		}

					list($language, $country) = explode('_', $locale);
		if (isset($this->lang_to_script[$language])) {
			$script = $this->lang_to_script[$language];
		}

		if (TYPO3_OS == 'WIN') {
			$cs = $this->script_to_charset_windows[$script] ? $this->script_to_charset_windows[$script] : 'windows-1252';
		} else {
			$cs = $this->script_to_charset_unix[$script] ? $this->script_to_charset_unix[$script] : 'utf-8';
		}

		return $cs;
	}


	

	
	function conv($str, $fromCS, $toCS, $useEntityForNoChar = 0) {
		if ($fromCS == $toCS) {
			return $str;
		}

					if ($toCS == 'utf-8' || !$useEntityForNoChar) {
			switch ($GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_convMethod']) {
				case 'mbstring':
					$conv_str = mb_convert_encoding($str, $toCS, $fromCS);
					if (FALSE !== $conv_str) {
						return $conv_str;
					} 					break;

				case 'iconv':
					$conv_str = iconv($fromCS, $toCS . '//TRANSLIT', $str);
					if (FALSE !== $conv_str) {
						return $conv_str;
					}
					break;

				case 'recode':
					$conv_str = recode_string($fromCS . '..' . $toCS, $str);
					if (FALSE !== $conv_str) {
						return $conv_str;
					}
					break;
			}
					}

		if ($fromCS != 'utf-8') {
			$str = $this->utf8_encode($str, $fromCS);
		}
		if ($toCS != 'utf-8') {
			$str = $this->utf8_decode($str, $toCS, $useEntityForNoChar);
		}
		return $str;
	}

	
	function convArray(&$array, $fromCS, $toCS, $useEntityForNoChar = 0) {
		foreach ($array as $key => $value) {
			if (is_array($array[$key])) {
				$this->convArray($array[$key], $fromCS, $toCS, $useEntityForNoChar);
			} elseif (is_string($array[$key])) {
				$array[$key] = $this->conv($array[$key], $fromCS, $toCS, $useEntityForNoChar);
			}
		}
	}

	
	function utf8_encode($str, $charset) {

		if ($charset === 'utf-8') {
			return $str;
		}

					if ($this->initCharset($charset)) { 			$strLen = strlen($str);
			$outStr = '';

			for ($a = 0; $a < $strLen; $a++) { 				$chr = substr($str, $a, 1);
				$ord = ord($chr);
				if (isset($this->twoByteSets[$charset])) { 					$ord2 = ord($str{$a + 1});
					$ord = $ord << 8 | $ord2; 
					if (isset($this->parsedCharsets[$charset]['local'][$ord])) { 						$outStr .= $this->parsedCharsets[$charset]['local'][$ord];
					} else {
						$outStr .= chr($this->noCharByteVal);
					} 					$a++;
				} elseif ($ord > 127) { 					if (isset($this->eucBasedSets[$charset])) { 						if ($charset != 'shift_jis' || ($ord < 0xA0 || $ord > 0xDF)) { 							$a++;
							$ord2 = ord(substr($str, $a, 1));
							$ord = $ord * 256 + $ord2;
						}
					}

					if (isset($this->parsedCharsets[$charset]['local'][$ord])) { 						$outStr .= $this->parsedCharsets[$charset]['local'][$ord];
					} else {
						$outStr .= chr($this->noCharByteVal);
					} 				} else {
					$outStr .= $chr;
				} 			}
			return $outStr;
		}
	}

	
	function utf8_decode($str, $charset, $useEntityForNoChar = 0) {

		if ($charset === 'utf-8') {
			return $str;
		}

					if ($this->initCharset($charset)) { 			$strLen = strlen($str);
			$outStr = '';
			$buf = '';
			for ($a = 0, $i = 0; $a < $strLen; $a++, $i++) { 				$chr = substr($str, $a, 1);
				$ord = ord($chr);
				if ($ord > 127) { 					if ($ord & 64) { 
						$buf = $chr; 						for ($b = 0; $b < 8; $b++) { 							$ord = $ord << 1; 							if ($ord & 128) { 								$a++; 								$buf .= substr($str, $a, 1); 							} else {
								break;
							}
						}

						if (isset($this->parsedCharsets[$charset]['utf8'][$buf])) { 							$mByte = $this->parsedCharsets[$charset]['utf8'][$buf]; 							if ($mByte > 255) { 								$outStr .= chr(($mByte >> 8) & 255) . chr($mByte & 255);
							} else {
								$outStr .= chr($mByte);
							}
						} elseif ($useEntityForNoChar) { 							$outStr .= '&#' . $this->utf8CharToUnumber($buf, 1) . ';';
						} else {
							$outStr .= chr($this->noCharByteVal);
						} 					} else {
						$outStr .= chr($this->noCharByteVal);
					} 				} else {
					$outStr .= $chr;
				} 			}
			return $outStr;
		}
	}

	
	function utf8_to_entities($str) {
		$strLen = strlen($str);
		$outStr = '';
		$buf = '';
		for ($a = 0; $a < $strLen; $a++) { 			$chr = substr($str, $a, 1);
			$ord = ord($chr);
			if ($ord > 127) { 				if ($ord & 64) { 					$buf = $chr; 					for ($b = 0; $b < 8; $b++) { 						$ord = $ord << 1; 						if ($ord & 128) { 							$a++; 							$buf .= substr($str, $a, 1); 						} else {
							break;
						}
					}

					$outStr .= '&#' . $this->utf8CharToUnumber($buf, 1) . ';';
				} else {
					$outStr .= chr($this->noCharByteVal);
				} 			} else {
				$outStr .= $chr;
			} 		}

		return $outStr;
	}

	
	function entities_to_utf8($str, $alsoStdHtmlEnt = FALSE) {
						$applyPhpCompatibilityFix = version_compare(phpversion(), '5.3.4', '<');

		if ($alsoStdHtmlEnt) {
			if ($applyPhpCompatibilityFix === TRUE) {
				$trans_tbl = array_flip(get_html_translation_table(HTML_ENTITIES, ENT_COMPAT));
			} else {
				$trans_tbl = array_flip(get_html_translation_table(HTML_ENTITIES, ENT_COMPAT, 'UTF-8'));
			}
		}

		$token = md5(microtime());
		$parts = explode($token, preg_replace('/(&([#[:alnum:]]*);)/', $token . '${2}' . $token, $str));
		foreach ($parts as $k => $v) {
							if ($k % 2 === 0) {
				continue;
			}

			$position = 0;
			if (substr($v, $position, 1) == '#') { 				$position++;
				if (substr($v, $position, 1) == 'x') {
					$v = hexdec(substr($v, ++$position));
				} else {
					$v = substr($v, $position);
				}
				$parts[$k] = $this->UnumberToChar($v);
			} elseif ($alsoStdHtmlEnt && isset($trans_tbl['&' . $v . ';'])) { 				$v = $trans_tbl['&' . $v . ';'];
				if ($applyPhpCompatibilityFix === TRUE) {
					$v = $this->utf8_encode($v, 'iso-8859-1');
				}
				$parts[$k] = $v;
			} else { 				$parts[$k] = '&' . $v . ';';
			}
		}

		return implode('', $parts);
	}

	
	function utf8_to_numberarray($str, $convEntities = 0, $retChar = 0) {
					if ($convEntities) {
			$str = $this->entities_to_utf8($str, 1);
		}
					$strLen = strlen($str);
		$outArr = array();
		$buf = '';
		for ($a = 0; $a < $strLen; $a++) { 			$chr = substr($str, $a, 1);
			$ord = ord($chr);
			if ($ord > 127) { 				if ($ord & 64) { 					$buf = $chr; 					for ($b = 0; $b < 8; $b++) { 						$ord = $ord << 1; 						if ($ord & 128) { 							$a++; 							$buf .= substr($str, $a, 1); 						} else {
							break;
						}
					}

					$outArr[] = $retChar ? $buf : $this->utf8CharToUnumber($buf);
				} else {
					$outArr[] = $retChar ? chr($this->noCharByteVal) : $this->noCharByteVal;
				} 			} else {
				$outArr[] = $retChar ? chr($ord) : $ord;
			} 		}

		return $outArr;
	}

	
	function UnumberToChar($cbyte) {
		$str = '';

		if ($cbyte < 0x80) {
			$str .= chr($cbyte);
		} else {
			if ($cbyte < 0x800) {
				$str .= chr(0xC0 | ($cbyte >> 6));
				$str .= chr(0x80 | ($cbyte & 0x3F));
			} else {
				if ($cbyte < 0x10000) {
					$str .= chr(0xE0 | ($cbyte >> 12));
					$str .= chr(0x80 | (($cbyte >> 6) & 0x3F));
					$str .= chr(0x80 | ($cbyte & 0x3F));
				} else {
					if ($cbyte < 0x200000) {
						$str .= chr(0xF0 | ($cbyte >> 18));
						$str .= chr(0x80 | (($cbyte >> 12) & 0x3F));
						$str .= chr(0x80 | (($cbyte >> 6) & 0x3F));
						$str .= chr(0x80 | ($cbyte & 0x3F));
					} else {
						if ($cbyte < 0x4000000) {
							$str .= chr(0xF8 | ($cbyte >> 24));
							$str .= chr(0x80 | (($cbyte >> 18) & 0x3F));
							$str .= chr(0x80 | (($cbyte >> 12) & 0x3F));
							$str .= chr(0x80 | (($cbyte >> 6) & 0x3F));
							$str .= chr(0x80 | ($cbyte & 0x3F));
						} else {
							if ($cbyte < 0x80000000) {
								$str .= chr(0xFC | ($cbyte >> 30));
								$str .= chr(0x80 | (($cbyte >> 24) & 0x3F));
								$str .= chr(0x80 | (($cbyte >> 18) & 0x3F));
								$str .= chr(0x80 | (($cbyte >> 12) & 0x3F));
								$str .= chr(0x80 | (($cbyte >> 6) & 0x3F));
								$str .= chr(0x80 | ($cbyte & 0x3F));
							} else { 								$str .= chr($this->noCharByteVal);
							}
						}
					}
				}
			}
		}
		return $str;
	}

	
	function utf8CharToUnumber($str, $hex = 0) {
		$ord = ord(substr($str, 0, 1)); 
		if (($ord & 192) == 192) { 			$binBuf = '';
			for ($b = 0; $b < 8; $b++) { 				$ord = $ord << 1; 				if ($ord & 128) { 					$binBuf .= substr('00000000' . decbin(ord(substr($str, $b + 1, 1))), -6);
				} else {
					break;
				}
			}
			$binBuf = substr('00000000' . decbin(ord(substr($str, 0, 1))), -(6 - $b)) . $binBuf;

			$int = bindec($binBuf);
		} else {
			$int = $ord;
		}

		return $hex ? 'x' . dechex($int) : $int;
	}


	

	
	function initCharset($charset) {
					if (!is_array($this->parsedCharsets[$charset])) {

							$charsetConvTableFile = PATH_t3lib . 'csconvtbl/' . $charset . '.tbl';

							if ($charset && t3lib_div::validPathStr($charsetConvTableFile) && @is_file($charsetConvTableFile)) {
														$cacheFile = t3lib_div::getFileAbsFileName('typo3temp/cs/charset_' . $charset . '.tbl');
				if ($cacheFile && @is_file($cacheFile)) {
					$this->parsedCharsets[$charset] = unserialize(t3lib_div::getUrl($cacheFile));
				} else {
											$lines = t3lib_div::trimExplode(LF, t3lib_div::getUrl($charsetConvTableFile), 1);
											$this->parsedCharsets[$charset] = array('local' => array(), 'utf8' => array());
											$detectedType = '';
					foreach ($lines as $value) {
						if (trim($value) && substr($value, 0, 1) != '#') { 
																							if (!$detectedType) {
								$detectedType = preg_match('/[[:space:]]*0x([[:alnum:]]*)[[:space:]]+0x([[:alnum:]]*)[[:space:]]+/', $value) ? 'whitespaced' : 'ms-token';
							}

							if ($detectedType == 'ms-token') {
								list($hexbyte, $utf8) = preg_split('/[=:]/', $value, 3);
							} elseif ($detectedType == 'whitespaced') {
								$regA = array();
								preg_match('/[[:space:]]*0x([[:alnum:]]*)[[:space:]]+0x([[:alnum:]]*)[[:space:]]+/', $value, $regA);
								$hexbyte = $regA[1];
								$utf8 = 'U+' . $regA[2];
							}
							$decval = hexdec(trim($hexbyte));
							if ($decval > 127) {
								$utf8decval = hexdec(substr(trim($utf8), 2));
								$this->parsedCharsets[$charset]['local'][$decval] = $this->UnumberToChar($utf8decval);
								$this->parsedCharsets[$charset]['utf8'][$this->parsedCharsets[$charset]['local'][$decval]] = $decval;
							}
						}
					}
					if ($cacheFile) {
						t3lib_div::writeFileToTypo3tempDir($cacheFile, serialize($this->parsedCharsets[$charset]));
					}
				}
				return 2;
			} else {
				return FALSE;
			}
		} else {
			return 1;
		}
	}

	
	function initUnicodeData($mode = NULL) {
					$cacheFileCase = t3lib_div::getFileAbsFileName('typo3temp/cs/cscase_utf-8.tbl');
		$cacheFileASCII = t3lib_div::getFileAbsFileName('typo3temp/cs/csascii_utf-8.tbl');

					switch ($mode) {
			case 'case':
				if (is_array($this->caseFolding['utf-8'])) {
					return 1;
				}

									if ($cacheFileCase && @is_file($cacheFileCase)) {
					$this->caseFolding['utf-8'] = unserialize(t3lib_div::getUrl($cacheFileCase));
					return 2;
				}
				break;

			case 'ascii':
				if (is_array($this->toASCII['utf-8'])) {
					return 1;
				}

									if ($cacheFileASCII && @is_file($cacheFileASCII)) {
					$this->toASCII['utf-8'] = unserialize(t3lib_div::getUrl($cacheFileASCII));
					return 2;
				}
				break;
		}

					$unicodeDataFile = PATH_t3lib . 'unidata/UnicodeData.txt';
		if (!(t3lib_div::validPathStr($unicodeDataFile) && @is_file($unicodeDataFile))) {
			return FALSE;
		}

		$fh = fopen($unicodeDataFile, 'rb');
		if (!$fh) {
			return FALSE;
		}

								$this->caseFolding['utf-8'] = array();
		$utf8CaseFolding =& $this->caseFolding['utf-8']; 		$utf8CaseFolding['toUpper'] = array();
		$utf8CaseFolding['toLower'] = array();
		$utf8CaseFolding['toTitle'] = array();

		$decomposition = array(); 		$mark = array(); 		$number = array(); 		$omit = array(); 
		while (!feof($fh)) {
			$line = fgets($fh, 4096);
							list($char, $name, $cat, , , $decomp, , , $num, , , , $upper, $lower, $title,) = explode(';', rtrim($line));

			$ord = hexdec($char);
			if ($ord > 0xFFFF) {
				break;
			} 
			$utf8_char = $this->UnumberToChar($ord);

			if ($upper) {
				$utf8CaseFolding['toUpper'][$utf8_char] = $this->UnumberToChar(hexdec($upper));
			}
			if ($lower) {
				$utf8CaseFolding['toLower'][$utf8_char] = $this->UnumberToChar(hexdec($lower));
			}
							if ($title && $title != $upper) {
				$utf8CaseFolding['toTitle'][$utf8_char] = $this->UnumberToChar(hexdec($title));
			}

			switch ($cat{0}) {
				case 'M': 					$mark["U+$char"] = 1;
					break;

				case 'N': 					if ($ord > 0x80 && $num != '') {
						$number["U+$char"] = $num;
					}
			}

							$match = array();
			if (preg_match('/^LATIN (SMALL|CAPITAL) LETTER ([A-Z]) WITH/', $name, $match) && !$decomp) {
				$c = ord($match[2]);
				if ($match[1] == 'SMALL') {
					$c += 32;
				}

				$decomposition["U+$char"] = array(dechex($c));
				continue;
			}

			$match = array();
			if (preg_match('/(<.*>)? *(.+)/', $decomp, $match)) {
				switch ($match[1]) {
					case '<circle>': 						$match[2] = '0028 ' . $match[2] . ' 0029';
						break;

					case '<square>': 						$match[2] = '005B ' . $match[2] . ' 005D';
						break;

					case '<compat>': 						if (preg_match('/^0020 /', $match[2])) {
							continue 2;
						}
						break;

											case '<initial>':
					case '<medial>':
					case '<final>':
					case '<isolated>':
					case '<vertical>':
						continue 2;
				}
				$decomposition["U+$char"] = explode(' ', $match[2]);
			}
		}
		fclose($fh);

					$specialCasingFile = PATH_t3lib . 'unidata/SpecialCasing.txt';
		if (t3lib_div::validPathStr($specialCasingFile) && @is_file($specialCasingFile)) {
			$fh = fopen($specialCasingFile, 'rb');
			if ($fh) {
				while (!feof($fh)) {
					$line = fgets($fh, 4096);
					if ($line{0} != '#' && trim($line) != '') {

						list($char, $lower, $title, $upper, $cond) = t3lib_div::trimExplode(';', $line);
						if ($cond == '' || $cond{0} == '#') {
							$utf8_char = $this->UnumberToChar(hexdec($char));
							if ($char != $lower) {
								$arr = explode(' ', $lower);
								for ($i = 0; isset($arr[$i]); $i++) {
									$arr[$i] = $this->UnumberToChar(hexdec($arr[$i]));
								}
								$utf8CaseFolding['toLower'][$utf8_char] = implode('', $arr);
							}
							if ($char != $title && $title != $upper) {
								$arr = explode(' ', $title);
								for ($i = 0; isset($arr[$i]); $i++) {
									$arr[$i] = $this->UnumberToChar(hexdec($arr[$i]));
								}
								$utf8CaseFolding['toTitle'][$utf8_char] = implode('', $arr);
							}
							if ($char != $upper) {
								$arr = explode(' ', $upper);
								for ($i = 0; isset($arr[$i]); $i++) {
									$arr[$i] = $this->UnumberToChar(hexdec($arr[$i]));
								}
								$utf8CaseFolding['toUpper'][$utf8_char] = implode('', $arr);
							}
						}
					}
				}
				fclose($fh);
			}
		}

					$customTranslitFile = PATH_t3lib . 'unidata/Translit.txt';
		if (t3lib_div::validPathStr($customTranslitFile) && @is_file($customTranslitFile)) {
			$fh = fopen($customTranslitFile, 'rb');
			if ($fh) {
				while (!feof($fh)) {
					$line = fgets($fh, 4096);
					if ($line{0} != '#' && trim($line) != '') {
						list($char, $translit) = t3lib_div::trimExplode(';', $line);
						if (!$translit) {
							$omit["U+$char"] = 1;
						}
						$decomposition["U+$char"] = explode(' ', $translit);

					}
				}
				fclose($fh);
			}
		}

					foreach ($decomposition as $from => $to) {
			$code_decomp = array();

			while ($code_value = array_shift($to)) {
				if (isset($decomposition["U+$code_value"])) { 					foreach (array_reverse($decomposition["U+$code_value"]) as $cv) {
						array_unshift($to, $cv);
					}
				} elseif (!isset($mark["U+$code_value"])) { 					array_push($code_decomp, $code_value);
				}
			}
			if (count($code_decomp) || isset($omit[$from])) {
				$decomposition[$from] = $code_decomp;
			} else {
				unset($decomposition[$from]);
			}
		}

					$this->toASCII['utf-8'] = array();
		$ascii =& $this->toASCII['utf-8'];

		foreach ($decomposition as $from => $to) {
			$code_decomp = array();
			while ($code_value = array_shift($to)) {
				$ord = hexdec($code_value);
				if ($ord > 127) {
					continue 2;
				} 				else
				{
					array_push($code_decomp, chr($ord));
				}
			}
			$ascii[$this->UnumberToChar(hexdec($from))] = join('', $code_decomp);
		}

					foreach ($number as $from => $to) {
			$utf8_char = $this->UnumberToChar(hexdec($from));
			if (!isset($ascii[$utf8_char])) {
				$ascii[$utf8_char] = $to;
			}
		}

		if ($cacheFileCase) {
			t3lib_div::writeFileToTypo3tempDir($cacheFileCase, serialize($utf8CaseFolding));
		}

		if ($cacheFileASCII) {
			t3lib_div::writeFileToTypo3tempDir($cacheFileASCII, serialize($ascii));
		}

		return 3;
	}

	
	function initCaseFolding($charset) {
					if (is_array($this->caseFolding[$charset])) {
			return 1;
		}

					$cacheFile = t3lib_div::getFileAbsFileName('typo3temp/cs/cscase_' . $charset . '.tbl');
		if ($cacheFile && @is_file($cacheFile)) {
			$this->caseFolding[$charset] = unserialize(t3lib_div::getUrl($cacheFile));
			return 2;
		}

					if (!$this->initCharset($charset)) {
			return FALSE;
		}

					if (!$this->initUnicodeData('case')) {
			return FALSE;
		}

		$nochar = chr($this->noCharByteVal);
		foreach ($this->parsedCharsets[$charset]['local'] as $ci => $utf8) {
							$c = $this->utf8_decode($utf8, $charset);

							$cc = $this->utf8_decode($this->caseFolding['utf-8']['toUpper'][$utf8], $charset);
			if ($cc != '' && $cc != $nochar) {
				$this->caseFolding[$charset]['toUpper'][$c] = $cc;
			}

							$cc = $this->utf8_decode($this->caseFolding['utf-8']['toLower'][$utf8], $charset);
			if ($cc != '' && $cc != $nochar) {
				$this->caseFolding[$charset]['toLower'][$c] = $cc;
			}

							$cc = $this->utf8_decode($this->caseFolding['utf-8']['toTitle'][$utf8], $charset);
			if ($cc != '' && $cc != $nochar) {
				$this->caseFolding[$charset]['toTitle'][$c] = $cc;
			}
		}

					for ($i = ord('a'); $i <= ord('z'); $i++) {
			$this->caseFolding[$charset]['toUpper'][chr($i)] = chr($i - 32);
		}
		for ($i = ord('A'); $i <= ord('Z'); $i++) {
			$this->caseFolding[$charset]['toLower'][chr($i)] = chr($i + 32);
		}

		if ($cacheFile) {
			t3lib_div::writeFileToTypo3tempDir($cacheFile, serialize($this->caseFolding[$charset]));
		}

		return 3;
	}

	
	function initToASCII($charset) {
					if (is_array($this->toASCII[$charset])) {
			return 1;
		}

					$cacheFile = t3lib_div::getFileAbsFileName('typo3temp/cs/csascii_' . $charset . '.tbl');
		if ($cacheFile && @is_file($cacheFile)) {
			$this->toASCII[$charset] = unserialize(t3lib_div::getUrl($cacheFile));
			return 2;
		}

					if (!$this->initCharset($charset)) {
			return FALSE;
		}

					if (!$this->initUnicodeData('ascii')) {
			return FALSE;
		}

		$nochar = chr($this->noCharByteVal);
		foreach ($this->parsedCharsets[$charset]['local'] as $ci => $utf8) {
							$c = $this->utf8_decode($utf8, $charset);

			if (isset($this->toASCII['utf-8'][$utf8])) {
				$this->toASCII[$charset][$c] = $this->toASCII['utf-8'][$utf8];
			}
		}

		if ($cacheFile) {
			t3lib_div::writeFileToTypo3tempDir($cacheFile, serialize($this->toASCII[$charset]));
		}

		return 3;
	}


	

	
	function substr($charset, $string, $start, $len = NULL) {
		if ($len === 0 || $string === '') {
			return '';
		}

		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] == 'mbstring') {
							if ($len == NULL) {
				$enc = mb_internal_encoding(); 				mb_internal_encoding($charset);
				$str = mb_substr($string, $start);
				mb_internal_encoding($enc); 
				return $str;
			}
			else {
				return mb_substr($string, $start, $len, $charset);
			}
		} elseif ($GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] == 'iconv') {
							if ($len == NULL) {
				$enc = iconv_get_encoding('internal_encoding'); 				iconv_set_encoding('internal_encoding', $charset);
				$str = iconv_substr($string, $start);
				iconv_set_encoding('internal_encoding', $enc); 
				return $str;
			}
			else {
				return iconv_substr($string, $start, $len, $charset);
			}
		} elseif ($charset == 'utf-8') {
			return $this->utf8_substr($string, $start, $len);
		} elseif ($this->eucBasedSets[$charset]) {
			return $this->euc_substr($string, $start, $charset, $len);
		} elseif ($this->twoByteSets[$charset]) {
			return substr($string, $start * 2, $len * 2);
		} elseif ($this->fourByteSets[$charset]) {
			return substr($string, $start * 4, $len * 4);
		}

					return $len === NULL ? substr($string, $start) : substr($string, $start, $len);
	}

	
	function strlen($charset, $string) {
		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] == 'mbstring') {
			return mb_strlen($string, $charset);
		} elseif ($GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] == 'iconv') {
			return iconv_strlen($string, $charset);
		} elseif ($charset == 'utf-8') {
			return $this->utf8_strlen($string);
		} elseif ($this->eucBasedSets[$charset]) {
			return $this->euc_strlen($string, $charset);
		} elseif ($this->twoByteSets[$charset]) {
			return strlen($string) / 2;
		} elseif ($this->fourByteSets[$charset]) {
			return strlen($string) / 4;
		}
					return strlen($string);
	}

	
	protected function cropMbstring($charset, $string, $len, $crop = '') {
		if (intval($len) === 0 || mb_strlen($string, $charset) <= abs($len)) {
			return $string;
		}

		if ($len > 0) {
			$string = mb_substr($string, 0, $len, $charset) . $crop;
		} else {
			$string = $crop . mb_substr($string, $len, mb_strlen($string, $charset), $charset);
		}

		return $string;
	}

	
	function crop($charset, $string, $len, $crop = '') {
		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] == 'mbstring') {
			return $this->cropMbstring($charset, $string, $len, $crop);
		}

		if (intval($len) == 0) {
			return $string;
		}

		if ($charset == 'utf-8') {
			$i = $this->utf8_char2byte_pos($string, $len);
		} elseif ($this->eucBasedSets[$charset]) {
			$i = $this->euc_char2byte_pos($string, $len, $charset);
		} else {
			if ($len > 0) {
				$i = $len;
			} else {
				$i = strlen($string) + $len;
				if ($i <= 0) {
					$i = FALSE;
				}
			}
		}

		if ($i === FALSE) { 			return $string;
		} else {
			if ($len > 0) {
				if (strlen($string{$i})) {
					return substr($string, 0, $i) . $crop;

				}
			} else {
				if (strlen($string{$i - 1})) {
					return $crop . substr($string, $i);
				}
			}

			
		}
		return $string;
	}

	
	function strtrunc($charset, $string, $len) {
		if ($len <= 0) {
			return '';
		}

		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] == 'mbstring') {
			return mb_strcut($string, 0, $len, $charset);
		} elseif ($charset == 'utf-8') {
			return $this->utf8_strtrunc($string, $len);
		} elseif ($this->eucBasedSets[$charset]) {
			return $this->euc_strtrunc($string, $len, $charset);
		} elseif ($this->twoByteSets[$charset]) {
			if ($len % 2) {
				$len--;
			} 		} elseif ($this->fourByteSets[$charset]) {
			$x = $len % 4;
			$len -= $x; 		}
					return substr($string, 0, $len);
	}

	
	function conv_case($charset, $string, $case) {
		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] == 'mbstring') {
			if ($case == 'toLower') {
				$string = mb_strtolower($string, $charset);
			} else {
				$string = mb_strtoupper($string, $charset);
			}
		} elseif ($charset == 'utf-8') {
			$string = $this->utf8_char_mapping($string, 'case', $case);
		} elseif (isset($this->eucBasedSets[$charset])) {
			$string = $this->euc_char_mapping($string, $charset, 'case', $case);
		} else {
							$string = $this->sb_char_mapping($string, $charset, 'case', $case);
		}

		return $string;
	}

	
	public function convCaseFirst($charset, $string, $case) {
		$firstChar = $this->substr($charset, $string, 0, 1);
		$firstChar = $this->conv_case($charset, $firstChar, $case);
		$remainder = $this->substr($charset, $string, 1);
		return $firstChar . $remainder;
	}

	
	function specCharsToASCII($charset, $string) {
		if ($charset == 'utf-8') {
			$string = $this->utf8_char_mapping($string, 'ascii');
		} elseif (isset($this->eucBasedSets[$charset])) {
			$string = $this->euc_char_mapping($string, $charset, 'ascii');
		} else {
							$string = $this->sb_char_mapping($string, $charset, 'ascii');
		}

		return $string;
	}


	
	public function getPreferredClientLanguage($languageCodesList) {
		$allLanguageCodes = array();
		$selectedLanguage = 'default';

					foreach ($this->charSetArray as $typo3Lang => $charSet) {
			$allLanguageCodes[$typo3Lang] = $typo3Lang;
		}

											foreach ($this->locales->getIsoMapping() as $typo3Lang => $isoLang) {
			$isoLang = join('-', explode('_', $isoLang));
			$allLanguageCodes[$typo3Lang] = $isoLang;
		}

					$allLanguageCodes = array_flip($allLanguageCodes);


		$preferredLanguages = t3lib_div::trimExplode(',', $languageCodesList);
					$sortedPreferredLanguages = array();
		foreach ($preferredLanguages as $preferredLanguage) {
			$quality = 1.0;
			if (strpos($preferredLanguage, ';q=') !== FALSE) {
				list($preferredLanguage, $quality) = explode(';q=', $preferredLanguage);
			}
			$sortedPreferredLanguages[$preferredLanguage] = $quality;
		}

					arsort($sortedPreferredLanguages, SORT_NUMERIC);
		foreach ($sortedPreferredLanguages as $preferredLanguage => $quality) {
			if (isset($allLanguageCodes[$preferredLanguage])) {
				$selectedLanguage = $allLanguageCodes[$preferredLanguage];
				break;
			}

							list($preferredLanguage, $preferredCountry) = explode('-', $preferredLanguage);
			if (isset($allLanguageCodes[$preferredLanguage])) {
				$selectedLanguage = $allLanguageCodes[$preferredLanguage];
				break;
			}
		}
		if (!$selectedLanguage || $selectedLanguage == 'en') {
			$selectedLanguage = 'default';
		}
		return $selectedLanguage;
	}


	

	
	function sb_char_mapping($str, $charset, $mode, $opt = '') {
		switch ($mode) {
			case 'case':
				if (!$this->initCaseFolding($charset)) {
					return $str;
				} 				$map =& $this->caseFolding[$charset][$opt];
				break;

			case 'ascii':
				if (!$this->initToASCII($charset)) {
					return $str;
				} 				$map =& $this->toASCII[$charset];
				break;

			default:
				return $str;
		}

		$out = '';
		for ($i = 0; strlen($str{$i}); $i++) {
			$c = $str{$i};
			if (isset($map[$c])) {
				$out .= $map[$c];
			} else {
				$out .= $c;
			}
		}

		return $out;
	}


	

	
	function utf8_substr($str, $start, $len = NULL) {
		if (!strcmp($len, '0')) {
			return '';
		}

		$byte_start = $this->utf8_char2byte_pos($str, $start);
		if ($byte_start === FALSE) {
			if ($start > 0) {
				return FALSE; 			} else {
				$start = 0;
			}
		}

		$str = substr($str, $byte_start);

		if ($len != NULL) {
			$byte_end = $this->utf8_char2byte_pos($str, $len);
			if ($byte_end === FALSE) 			{
				return $len < 0 ? '' : $str;
			} 			else
			{
				return substr($str, 0, $byte_end);
			}
		}
		else	{
			return $str;
		}
	}

	
	function utf8_strlen($str) {
		$n = 0;
		for ($i = 0; strlen($str{$i}); $i++) {
			$c = ord($str{$i});
			if (!($c & 0x80)) 			{
				$n++;
			}
			elseif (($c & 0xC0) == 0xC0) 			{
				$n++;
			}
		}
		return $n;
	}

	
	function utf8_strtrunc($str, $len) {
		$i = $len - 1;
		if (ord($str{$i}) & 0x80) { 			for (; $i > 0 && !(ord($str{$i}) & 0x40); $i--) {
								;
			}
			if ($i <= 0) {
				return '';
			} 			for ($bc = 0, $mbs = ord($str{$i}); $mbs & 0x80; $mbs = $mbs << 1) {
								$bc++;
			}
			if ($bc + $i > $len) {
				return substr($str, 0, $i);
			}
					}
		return substr($str, 0, $len);
	}

	
	function utf8_strpos($haystack, $needle, $offset = 0) {
		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] == 'mbstring') {
			return mb_strpos($haystack, $needle, $offset, 'utf-8');
		} elseif ($GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] == 'iconv') {
			return iconv_strpos($haystack, $needle, $offset, 'utf-8');
		}

		$byte_offset = $this->utf8_char2byte_pos($haystack, $offset);
		if ($byte_offset === FALSE) {
			return FALSE;
		} 
		$byte_pos = strpos($haystack, $needle, $byte_offset);
		if ($byte_pos === FALSE) {
			return FALSE;
		} 
		return $this->utf8_byte2char_pos($haystack, $byte_pos);
	}

	
	function utf8_strrpos($haystack, $needle) {
		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] == 'mbstring') {
			return mb_strrpos($haystack, $needle, 'utf-8');
		} elseif ($GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] == 'iconv') {
			return iconv_strrpos($haystack, $needle, 'utf-8');
		}

		$byte_pos = strrpos($haystack, $needle);
		if ($byte_pos === FALSE) {
			return FALSE;
		} 
		return $this->utf8_byte2char_pos($haystack, $byte_pos);
	}

	
	function utf8_char2byte_pos($str, $pos) {
		$n = 0; 		$p = abs($pos); 
		if ($pos >= 0) {
			$i = 0;
			$d = 1;
		} else {
			$i = strlen($str) - 1;
			$d = -1;
		}

		for (; strlen($str{$i}) && $n < $p; $i += $d) {
			$c = (int) ord($str{$i});
			if (!($c & 0x80)) 			{
				$n++;
			}
			elseif (($c & 0xC0) == 0xC0) 			{
				$n++;
			}
		}
		if (!strlen($str{$i})) {
			return FALSE;
		} 
		if ($pos >= 0) {
							while ((ord($str{$i}) & 0x80) && !(ord($str{$i}) & 0x40)) {
				$i++;
			}
		} else {
							$i++;
		}

		return $i;
	}

	
	function utf8_byte2char_pos($str, $pos) {
		$n = 0; 		for ($i = $pos; $i > 0; $i--) {
			$c = (int) ord($str{$i});
			if (!($c & 0x80)) 			{
				$n++;
			}
			elseif (($c & 0xC0) == 0xC0) 			{
				$n++;
			}
		}
		if (!strlen($str{$i})) {
			return FALSE;
		} 
		return $n;
	}

	
	function utf8_char_mapping($str, $mode, $opt = '') {
		if (!$this->initUnicodeData($mode)) {
			return $str;
		} 
		$out = '';
		switch ($mode) {
			case 'case':
				$map =& $this->caseFolding['utf-8'][$opt];
				break;

			case 'ascii':
				$map =& $this->toASCII['utf-8'];
				break;

			default:
				return $str;
		}

		for ($i = 0; strlen($str{$i}); $i++) {
			$c = ord($str{$i});
			if (!($c & 0x80)) 			{
				$mbc = $str{$i};
			}
			elseif (($c & 0xC0) == 0xC0) { 				for ($bc = 0; $c & 0x80; $c = $c << 1) {
					$bc++;
				} 				$mbc = substr($str, $i, $bc);
				$i += $bc - 1;
			}

			if (isset($map[$mbc])) {
				$out .= $map[$mbc];
			} else {
				$out .= $mbc;
			}
		}

		return $out;
	}


	

	
	function euc_strtrunc($str, $len, $charset) {
		$sjis = ($charset == 'shift_jis');
		for ($i = 0; strlen($str{$i}) && $i < $len; $i++) {
			$c = ord($str{$i});
			if ($sjis) {
				if (($c >= 0x80 && $c < 0xA0) || ($c >= 0xE0)) {
					$i++;
				} 			}
			else {
				if ($c >= 0x80) {
					$i++;
				} 			}
		}
		if (!strlen($str{$i})) {
			return $str;
		} 
		if ($i > $len) {
			return substr($str, 0, $len - 1); 		} else {
			return substr($str, 0, $len);
		}
	}

	
	function euc_substr($str, $start, $charset, $len = NULL) {
		$byte_start = $this->euc_char2byte_pos($str, $start, $charset);
		if ($byte_start === FALSE) {
			return FALSE;
		} 
		$str = substr($str, $byte_start);

		if ($len != NULL) {
			$byte_end = $this->euc_char2byte_pos($str, $len, $charset);
			if ($byte_end === FALSE) 			{
				return $str;
			}
			else
			{
				return substr($str, 0, $byte_end);
			}
		}
		else	{
			return $str;
		}
	}

	
	function euc_strlen($str, $charset) {
		$sjis = ($charset == 'shift_jis');
		$n = 0;
		for ($i = 0; strlen($str{$i}); $i++) {
			$c = ord($str{$i});
			if ($sjis) {
				if (($c >= 0x80 && $c < 0xA0) || ($c >= 0xE0)) {
					$i++;
				} 			}
			else {
				if ($c >= 0x80) {
					$i++;
				} 			}

			$n++;
		}

		return $n;
	}

	
	function euc_char2byte_pos($str, $pos, $charset) {
		$sjis = ($charset == 'shift_jis');
		$n = 0; 		$p = abs($pos); 
		if ($pos >= 0) {
			$i = 0;
			$d = 1;
		} else {
			$i = strlen($str) - 1;
			$d = -1;
		}

		for (; strlen($str{$i}) && $n < $p; $i += $d) {
			$c = ord($str{$i});
			if ($sjis) {
				if (($c >= 0x80 && $c < 0xA0) || ($c >= 0xE0)) {
					$i += $d;
				} 			}
			else {
				if ($c >= 0x80) {
					$i += $d;
				} 			}

			$n++;
		}
		if (!strlen($str{$i})) {
			return FALSE;
		} 
		if ($pos < 0) {
			$i++;
		} 
		return $i;
	}

	
	function euc_char_mapping($str, $charset, $mode, $opt = '') {
		switch ($mode) {
			case 'case':
				if (!$this->initCaseFolding($charset)) {
					return $str;
				} 				$map =& $this->caseFolding[$charset][$opt];
				break;

			case 'ascii':
				if (!$this->initToASCII($charset)) {
					return $str;
				} 				$map =& $this->toASCII[$charset];
				break;

			default:
				return $str;
		}

		$sjis = ($charset == 'shift_jis');
		$out = '';
		for ($i = 0; strlen($str{$i}); $i++) {
			$mbc = $str{$i};
			$c = ord($mbc);

			if ($sjis) {
				if (($c >= 0x80 && $c < 0xA0) || ($c >= 0xE0)) { 					$mbc = substr($str, $i, 2);
					$i++;
				}
			}
			else {
				if ($c >= 0x80) { 					$mbc = substr($str, $i, 2);
					$i++;
				}
			}

			if (isset($map[$mbc])) {
				$out .= $map[$mbc];
			} else {
				$out .= $mbc;
			}
		}

		return $out;
	}

}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_cs.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_cs.php']);
}

?>
