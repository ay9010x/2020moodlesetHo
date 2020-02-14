<?php



defined('MOODLE_INTERNAL') || die();


class core_text {

    
    protected static function typo3($reset = false) {
        static $typo3cs = null;

        if ($reset) {
            $typo3cs = null;
            return null;
        }

        if (isset($typo3cs)) {
            return $typo3cs;
        }

        global $CFG;

                require_once($CFG->libdir.'/typo3/class.t3lib_cs.php');
        require_once($CFG->libdir.'/typo3/class.t3lib_div.php');
        require_once($CFG->libdir.'/typo3/interface.t3lib_singleton.php');
        require_once($CFG->libdir.'/typo3/class.t3lib_l10n_locales.php');

                $GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_convMethod'] = 'iconv';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] = 'iconv';

                $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlUse'] = '1';

                        make_temp_directory('typo3temp/cs');

                $GLOBALS['TYPO3_CONF_VARS']['BE']['folderCreateMask'] = decoct($CFG->directorypermissions);

                $GLOBALS['TYPO3_CONF_VARS']['BE']['fileCreateMask'] = $CFG->directorypermissions;

                        if (!defined('PATH_t3lib')) {
            define('PATH_t3lib', str_replace('\\','/',$CFG->libdir.'/typo3/'));
            define('PATH_typo3', str_replace('\\','/',$CFG->libdir.'/typo3/'));
            define('PATH_site', str_replace('\\','/',$CFG->tempdir.'/'));
            define('TYPO3_OS', stristr(PHP_OS,'win')&&!stristr(PHP_OS,'darwin')?'WIN':'');
        }

        $typo3cs = new t3lib_cs();

        return $typo3cs;
    }

    
    public static function reset_caches() {
        self::typo3(true);
    }

    
    public static function parse_charset($charset) {
        $charset = strtolower($charset);

        
        if ($charset === 'utf8' or $charset === 'utf-8') {
            return 'utf-8';
        }

        if (preg_match('/^(cp|win|windows)-?(12[0-9]{2})$/', $charset, $matches)) {
            return 'windows-'.$matches[2];
        }

        if (preg_match('/^iso-8859-[0-9]+$/', $charset, $matches)) {
            return $charset;
        }

        if ($charset === 'euc-jp') {
            return 'euc-jp';
        }
        if ($charset === 'iso-2022-jp') {
            return 'iso-2022-jp';
        }
        if ($charset === 'shift-jis' or $charset === 'shift_jis') {
            return 'shift_jis';
        }
        if ($charset === 'gb2312') {
            return 'gb2312';
        }
        if ($charset === 'gb18030') {
            return 'gb18030';
        }

                return self::typo3()->parse_charset($charset);
    }

    
    public static function convert($text, $fromCS, $toCS='utf-8') {
        $fromCS = self::parse_charset($fromCS);
        $toCS   = self::parse_charset($toCS);

        $text = (string)$text; 
        if ($text === '') {
            return '';
        }

        if ($fromCS === 'utf-8') {
            $text = fix_utf8($text);
            if ($toCS === 'utf-8') {
                return $text;
            }
        }

        if ($toCS === 'ascii') {
                        $text = self::specialtoascii($text, $fromCS);
        }

                        $result = @iconv($fromCS, $toCS.'//TRANSLIT', $text);

        if ($result === false or $result === '') {
                        $oldlevel = error_reporting(E_PARSE);
            $result = self::typo3()->conv((string)$text, $fromCS, $toCS);
            error_reporting($oldlevel);
        }

        return $result;
    }

    
    public static function substr($text, $start, $len=null, $charset='utf-8') {
        $charset = self::parse_charset($charset);

        if ($charset === 'utf-8') {
            if (function_exists('mb_substr')) {
                                if ($len === null) {
                    $oldcharset = mb_internal_encoding();
                    mb_internal_encoding('UTF-8');
                    $result = mb_substr($text, $start);
                    mb_internal_encoding($oldcharset);
                    return $result;
                } else {
                    return mb_substr($text, $start, $len, 'UTF-8');
                }

            } else {
                if ($len === null) {
                    $len = iconv_strlen($text, 'UTF-8');
                }
                return iconv_substr($text, $start, $len, 'UTF-8');
            }
        }

        $oldlevel = error_reporting(E_PARSE);
        if ($len === null) {
            $result = self::typo3()->substr($charset, (string)$text, $start);
        } else {
            $result = self::typo3()->substr($charset, (string)$text, $start, $len);
        }
        error_reporting($oldlevel);

        return $result;
    }

    
    public static function str_max_bytes($string, $bytes) {
        if (function_exists('mb_strcut')) {
            return mb_strcut($string, 0, $bytes, 'UTF-8');
        }

        $oldlevel = error_reporting(E_PARSE);
        $result = self::typo3()->strtrunc('utf-8', $string, $bytes);
        error_reporting($oldlevel);

        return $result;
    }

    
    public static function strrchr($haystack, $needle, $part = false) {

        if (function_exists('mb_strrchr')) {
            return mb_strrchr($haystack, $needle, $part, 'UTF-8');
        }

        $pos = self::strrpos($haystack, $needle);
        if ($pos === false) {
            return false;
        }

        $length = null;
        if ($part) {
            $length = $pos;
            $pos = 0;
        }

        return self::substr($haystack, $pos, $length, 'utf-8');
    }

    
    public static function strlen($text, $charset='utf-8') {
        $charset = self::parse_charset($charset);

        if ($charset === 'utf-8') {
            if (function_exists('mb_strlen')) {
                return mb_strlen($text, 'UTF-8');
            } else {
                return iconv_strlen($text, 'UTF-8');
            }
        }

        $oldlevel = error_reporting(E_PARSE);
        $result = self::typo3()->strlen($charset, (string)$text);
        error_reporting($oldlevel);

        return $result;
    }

    
    public static function strtolower($text, $charset='utf-8') {
        $charset = self::parse_charset($charset);

        if ($charset === 'utf-8' and function_exists('mb_strtolower')) {
            return mb_strtolower($text, 'UTF-8');
        }

        $oldlevel = error_reporting(E_PARSE);
        $result = self::typo3()->conv_case($charset, (string)$text, 'toLower');
        error_reporting($oldlevel);

        return $result;
    }

    
    public static function strtoupper($text, $charset='utf-8') {
        $charset = self::parse_charset($charset);

        if ($charset === 'utf-8' and function_exists('mb_strtoupper')) {
            return mb_strtoupper($text, 'UTF-8');
        }

        $oldlevel = error_reporting(E_PARSE);
        $result = self::typo3()->conv_case($charset, (string)$text, 'toUpper');
        error_reporting($oldlevel);

        return $result;
    }

    
    public static function strpos($haystack, $needle, $offset=0) {
        if (function_exists('mb_strpos')) {
            return mb_strpos($haystack, $needle, $offset, 'UTF-8');
        } else {
            return iconv_strpos($haystack, $needle, $offset, 'UTF-8');
        }
    }

    
    public static function strrpos($haystack, $needle) {
        if (function_exists('mb_strrpos')) {
            return mb_strrpos($haystack, $needle, null, 'UTF-8');
        } else {
            return iconv_strrpos($haystack, $needle, 'UTF-8');
        }
    }

    
    public static function strrev($str) {
        preg_match_all('/./us', $str, $ar);
        return join('', array_reverse($ar[0]));
    }

    
    public static function specialtoascii($text, $charset='utf-8') {
        $charset = self::parse_charset($charset);
        $oldlevel = error_reporting(E_PARSE);
        $result = self::typo3()->specCharsToASCII($charset, (string)$text);
        error_reporting($oldlevel);
        return $result;
    }

    
    public static function encode_mimeheader($text, $charset='utf-8') {
        if (empty($text)) {
            return (string)$text;
        }
                $charset = self::parse_charset($charset);
                if (self::convert($text, $charset, 'ascii') == $text) {
            return $text;
        }
                        $linefeed="\n";
                $start = "=?$charset?B?";
        $end = "?=";
                $encoded = '';
                $length = 75 - strlen($start) - strlen($end);
                $multilength = self::strlen($text, $charset);
                if ($multilength === false) {
            if ($charset == 'GB18030' or $charset == 'gb18030') {
                while (strlen($text)) {
                                        if (preg_match('/^(([\x00-\x7f])|([\x81-\xfe][\x40-\x7e])|([\x81-\xfe][\x80-\xfe])|([\x81-\xfe][\x30-\x39]..)){1,22}/m', $text, $matches)) {
                        $chunk = $matches[0];
                        $encchunk = base64_encode($chunk);
                        if (strlen($encchunk) > $length) {
                                                        preg_match('/^(([\x00-\x7f])|([\x81-\xfe][\x40-\x7e])|([\x81-\xfe][\x80-\xfe])|([\x81-\xfe][\x30-\x39]..)){1,11}/m', $text, $matches);
                            $chunk = $matches[0];
                            $encchunk = base64_encode($chunk);
                        }
                        $text = substr($text, strlen($chunk));
                        $encoded .= ' '.$start.$encchunk.$end.$linefeed;
                    } else {
                        break;
                    }
                }
                $encoded = trim($encoded);
                return $encoded;
            } else {
                return false;
            }
        }
        $ratio = $multilength / strlen($text);
                $magic = $avglength = floor(3 * $length * $ratio / 4);
                $maxiterations = strlen($text)*2;
        $iteration = 0;
                for ($i=0; $i <= $multilength; $i+=$magic) {
            if ($iteration++ > $maxiterations) {
                return false;             }
            $magic = $avglength;
            $offset = 0;
                        do {
                $magic -= $offset;
                $chunk = self::substr($text, $i, $magic, $charset);
                $chunk = base64_encode($chunk);
                $offset++;
            } while (strlen($chunk) > $length);
                        if ($chunk)
                $encoded .= ' '.$start.$chunk.$end.$linefeed;
        }
                $encoded = substr($encoded, 1, -strlen($linefeed));

        return $encoded;
    }

    
    protected static function get_entities_table() {
        static $trans_tbl = null;

                if (!isset($trans_tbl)) {
            if (version_compare(phpversion(), '5.3.4') < 0) {
                $trans_tbl = array();
                foreach (get_html_translation_table(HTML_ENTITIES) as $val=>$key) {
                    $trans_tbl[$key] = self::convert($val, 'ISO-8859-1', 'utf-8');
                }

            } else if (version_compare(phpversion(), '5.4.0') < 0) {
                $trans_tbl = get_html_translation_table(HTML_ENTITIES, ENT_COMPAT, 'UTF-8');
                $trans_tbl = array_flip($trans_tbl);

            } else {
                $trans_tbl = get_html_translation_table(HTML_ENTITIES, ENT_COMPAT | ENT_HTML401, 'UTF-8');
                $trans_tbl = array_flip($trans_tbl);
            }
        }

        return $trans_tbl;
    }

    
    public static function entities_to_utf8($str, $htmlent=true) {
        static $callback1 = null ;
        static $callback2 = null ;

        if (!$callback1 or !$callback2) {
            $callback1 = create_function('$matches', 'return core_text::code2utf8(hexdec($matches[1]));');
            $callback2 = create_function('$matches', 'return core_text::code2utf8($matches[1]);');
        }

        $result = (string)$str;
        $result = preg_replace_callback('/&#x([0-9a-f]+);/i', $callback1, $result);
        $result = preg_replace_callback('/&#([0-9]+);/', $callback2, $result);

                if ($htmlent) {
            $trans_tbl = self::get_entities_table();
                        $result = strtr($result, $trans_tbl);
        }
                return $result;
    }

    
    public static function utf8_to_entities($str, $dec=false, $nonnum=false) {
        static $callback = null ;

        if ($nonnum) {
            $str = self::entities_to_utf8($str, true);
        }

                $oldlevel = error_reporting(E_PARSE);
        $result = self::typo3()->utf8_to_entities((string)$str);
        error_reporting($oldlevel);

        if ($dec) {
            if (!$callback) {
                $callback = create_function('$matches', 'return \'&#\'.(hexdec($matches[1])).\';\';');
            }
            $result = preg_replace_callback('/&#x([0-9a-f]+);/i', $callback, $result);
        }

        return $result;
    }

    
    public static function trim_utf8_bom($str) {
        $bom = "\xef\xbb\xbf";
        if (strpos($str, $bom) === 0) {
            return substr($str, strlen($bom));
        }
        return $str;
    }

    
    public static function get_encodings() {
        $encodings = array();
        $encodings['UTF-8'] = 'UTF-8';
        $winenc = strtoupper(get_string('localewincharset', 'langconfig'));
        if ($winenc != '') {
            $encodings[$winenc] = $winenc;
        }
        $nixenc = strtoupper(get_string('oldcharset', 'langconfig'));
        $encodings[$nixenc] = $nixenc;

        foreach (self::typo3()->synonyms as $enc) {
            $enc = strtoupper($enc);
            $encodings[$enc] = $enc;
        }
        return $encodings;
    }

    
    public static function code2utf8($num) {
        if ($num < 128) {
            return chr($num);
        }
        if ($num < 2048) {
            return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
        }
        if ($num < 65536) {
            return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
        }
        if ($num < 2097152) {
            return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
        }
        return '';
    }

    
    public static function utf8ord($utf8char) {
        if ($utf8char == '') {
            return 0;
        }
        $ord0 = ord($utf8char{0});
        if ($ord0 >= 0 && $ord0 <= 127) {
            return $ord0;
        }
        $ord1 = ord($utf8char{1});
        if ($ord0 >= 192 && $ord0 <= 223) {
            return ($ord0 - 192) * 64 + ($ord1 - 128);
        }
        $ord2 = ord($utf8char{2});
        if ($ord0 >= 224 && $ord0 <= 239) {
            return ($ord0 - 224) * 4096 + ($ord1 - 128) * 64 + ($ord2 - 128);
        }
        $ord3 = ord($utf8char{3});
        if ($ord0 >= 240 && $ord0 <= 247) {
            return ($ord0 - 240) * 262144 + ($ord1 - 128 )* 4096 + ($ord2 - 128) * 64 + ($ord3 - 128);
        }
        return false;
    }

    
    public static function strtotitle($text) {
        if (empty($text)) {
            return $text;
        }

        if (function_exists('mb_convert_case')) {
            return mb_convert_case($text, MB_CASE_TITLE, 'UTF-8');
        }

        $text = self::strtolower($text);
        $words = explode(' ', $text);
        foreach ($words as $i=>$word) {
            $length = self::strlen($word);
            if (!$length) {
                continue;

            } else if ($length == 1) {
                $words[$i] = self::strtoupper($word);

            } else {
                $letter = self::substr($word, 0, 1);
                $letter = self::strtoupper($letter);
                $rest   = self::substr($word, 1);
                $words[$i] = $letter.$rest;
            }
        }
        return implode(' ', $words);
    }
}
