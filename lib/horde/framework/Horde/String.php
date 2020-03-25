<?php

class Horde_String
{
    
    static protected $_lowers = array();

    
    static protected $_uppers = array();

    
    static public function convertCharset($input, $from, $to, $force = false)
    {
        
        if (is_numeric($input)) {
            return $input;
        }

        
        if (!$force && $from == $to) {
            return $input;
        }
        $from = self::lower($from);
        $to = self::lower($to);
        if (!$force && $from == $to) {
            return $input;
        }

        if (is_array($input)) {
            $tmp = array();
            reset($input);
            while (list($key, $val) = each($input)) {
                $tmp[self::_convertCharset($key, $from, $to)] = self::convertCharset($val, $from, $to, $force);
            }
            return $tmp;
        }

        if (is_object($input)) {
                                                if (($input instanceof Exception) ||
                ($input instanceof PEAR_Error)) {
                return '';
            }

            $input = clone $input;
            $vars = get_object_vars($input);
            while (list($key, $val) = each($vars)) {
                $input->$key = self::convertCharset($val, $from, $to, $force);
            }
            return $input;
        }

        if (!is_string($input)) {
            return $input;
        }

        return self::_convertCharset($input, $from, $to);
    }

    
    static protected function _convertCharset($input, $from, $to)
    {
        
        if (Horde_Util::extensionExists('xml') &&
            ((strlen($input) < 16777216) ||
             !Horde_Util::extensionExists('iconv') ||
             !Horde_Util::extensionExists('mbstring'))) {
            if (($to == 'utf-8') &&
                in_array($from, array('iso-8859-1', 'us-ascii', 'utf-8'))) {
                return utf8_encode($input);
            }

            if (($from == 'utf-8') &&
                in_array($to, array('iso-8859-1', 'us-ascii', 'utf-8'))) {
                return utf8_decode($input);
            }
        }

        
        if (($from == 'utf7-imap') || ($to == 'utf7-imap')) {
            try {
                if ($from == 'utf7-imap') {
                    return self::convertCharset(Horde_Imap_Client_Utf7imap::Utf7ImapToUtf8($input), 'UTF-8', $to);
                } else {
                    if ($from == 'utf-8') {
                        $conv = $input;
                    } else {
                        $conv = self::convertCharset($input, $from, 'UTF-8');
                    }
                    return Horde_Imap_Client_Utf7imap::Utf8ToUtf7Imap($conv);
                }
            } catch (Horde_Imap_Client_Exception $e) {
                return $input;
            }
        }

        
        if (Horde_Util::extensionExists('iconv')) {
            unset($php_errormsg);
            ini_set('track_errors', 1);
            $out = @iconv($from, $to . '//TRANSLIT', $input);
            $errmsg = isset($php_errormsg);
            ini_restore('track_errors');
            if (!$errmsg && $out !== false) {
                return $out;
            }
        }

        
        if (Horde_Util::extensionExists('mbstring')) {
            $out = @mb_convert_encoding($input, $to, self::_mbstringCharset($from));
            if (!empty($out)) {
                return $out;
            }
        }

        return $input;
    }

    
    static public function lower($string, $locale = false, $charset = null)
    {
        if ($locale) {
            if (Horde_Util::extensionExists('mbstring')) {
                if (is_null($charset)) {
                    throw new InvalidArgumentException('$charset argument must not be null');
                }
                $ret = @mb_strtolower($string, self::_mbstringCharset($charset));
                if (!empty($ret)) {
                    return $ret;
                }
            }
            return strtolower($string);
        }

        if (!isset(self::$_lowers[$string])) {
            $language = setlocale(LC_CTYPE, 0);
            setlocale(LC_CTYPE, 'C');
            self::$_lowers[$string] = strtolower($string);
            setlocale(LC_CTYPE, $language);
        }

        return self::$_lowers[$string];
    }

    
    static public function upper($string, $locale = false, $charset = null)
    {
        if ($locale) {
            if (Horde_Util::extensionExists('mbstring')) {
                if (is_null($charset)) {
                    throw new InvalidArgumentException('$charset argument must not be null');
                }
                $ret = @mb_strtoupper($string, self::_mbstringCharset($charset));
                if (!empty($ret)) {
                    return $ret;
                }
            }
            return strtoupper($string);
        }

        if (!isset(self::$_uppers[$string])) {
            $language = setlocale(LC_CTYPE, 0);
            setlocale(LC_CTYPE, 'C');
            self::$_uppers[$string] = strtoupper($string);
            setlocale(LC_CTYPE, $language);
        }

        return self::$_uppers[$string];
    }

    
    static public function ucfirst($string, $locale = false, $charset = null)
    {
        if ($locale) {
            if (is_null($charset)) {
                throw new InvalidArgumentException('$charset argument must not be null');
            }
            $first = self::substr($string, 0, 1, $charset);
            if (self::isAlpha($first, $charset)) {
                $string = self::upper($first, true, $charset) . self::substr($string, 1, null, $charset);
            }
        } else {
            $string = self::upper(substr($string, 0, 1), false) . substr($string, 1);
        }

        return $string;
    }

    
    static public function ucwords($string, $locale = false, $charset = null)
    {
        $words = preg_split('/(\s+)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($i = 0, $c = count($words); $i < $c; $i += 2) {
            $words[$i] = self::ucfirst($words[$i], $locale, $charset);
        }
        return implode('', $words);
    }

    
    static public function substr($string, $start, $length = null,
                                  $charset = 'UTF-8')
    {
        if (is_null($length)) {
            $length = self::length($string, $charset) - $start;
        }

        if ($length == 0) {
            return '';
        }

        
        if (Horde_Util::extensionExists('mbstring')) {
            $ret = @mb_substr($string, $start, $length, self::_mbstringCharset($charset));

            
            if (strlen($ret)) {
                return $ret;
            }
        }

        
        if (Horde_Util::extensionExists('iconv')) {
            $ret = @iconv_substr($string, $start, $length, $charset);

            
            if ($ret !== false) {
                return $ret;
            }
        }

        return substr($string, $start, $length);
    }

    
    static public function length($string, $charset = 'UTF-8')
    {
        $charset = self::lower($charset);

        if ($charset == 'utf-8' || $charset == 'utf8') {
            return strlen(utf8_decode($string));
        }

        if (Horde_Util::extensionExists('mbstring')) {
            $ret = @mb_strlen($string, self::_mbstringCharset($charset));
            if (!empty($ret)) {
                return $ret;
            }
        }

        return strlen($string);
    }

    
    static public function pos($haystack, $needle, $offset = 0,
                               $charset = 'UTF-8')
    {
        if (Horde_Util::extensionExists('mbstring')) {
            $track_errors = ini_set('track_errors', 1);
            $ret = @mb_strpos($haystack, $needle, $offset, self::_mbstringCharset($charset));
            ini_set('track_errors', $track_errors);
            if (!isset($php_errormsg)) {
                return $ret;
            }
        }

        return strpos($haystack, $needle, $offset);
    }

    
    static public function rpos($haystack, $needle, $offset = 0,
                                $charset = 'UTF-8')
    {
        if (Horde_Util::extensionExists('mbstring')) {
            $track_errors = ini_set('track_errors', 1);
            $ret = @mb_strrpos($haystack, $needle, $offset, self::_mbstringCharset($charset));
            ini_set('track_errors', $track_errors);
            if (!isset($php_errormsg)) {
                return $ret;
            }
        }

        return strrpos($haystack, $needle, $offset);
    }

    
    static public function pad($input, $length, $pad = ' ',
                               $type = STR_PAD_RIGHT, $charset = 'UTF-8')
    {
        $mb_length = self::length($input, $charset);
        $sb_length = strlen($input);
        $pad_length = self::length($pad, $charset);

        
        if ($mb_length >= $length) {
            return $input;
        }

        
        if ($mb_length == $sb_length && $pad_length == strlen($pad)) {
            return str_pad($input, $length, $pad, $type);
        }

        switch ($type) {
        case STR_PAD_LEFT:
            $left = $length - $mb_length;
            $output = self::substr(str_repeat($pad, ceil($left / $pad_length)), 0, $left, $charset) . $input;
            break;

        case STR_PAD_BOTH:
            $left = floor(($length - $mb_length) / 2);
            $right = ceil(($length - $mb_length) / 2);
            $output = self::substr(str_repeat($pad, ceil($left / $pad_length)), 0, $left, $charset) .
                $input .
                self::substr(str_repeat($pad, ceil($right / $pad_length)), 0, $right, $charset);
            break;

        case STR_PAD_RIGHT:
            $right = $length - $mb_length;
            $output = $input . self::substr(str_repeat($pad, ceil($right / $pad_length)), 0, $right, $charset);
            break;
        }

        return $output;
    }

    
    static public function wordwrap($string, $width = 75, $break = "\n",
                                    $cut = false, $line_folding = false)
    {
        $wrapped = '';

        while (self::length($string, 'UTF-8') > $width) {
            $line = self::substr($string, 0, $width, 'UTF-8');
            $string = self::substr($string, self::length($line, 'UTF-8'), null, 'UTF-8');

                                    if (!$cut && preg_match('/^(.+?)((\s|\r?\n).*)/us', $string, $match)) {
                $line .= $match[1];
                $string = $match[2];
            }

                        if (preg_match('/^(.*?)(\r?\n)(.*)$/su', $line, $match)) {
                $wrapped .= $match[1] . $match[2];
                $string = $match[3] . $string;
                continue;
            }

                                    if ($line_folding &&
                preg_match('/^(.*?)(;|:)(\s+.*)$/u', $line, $match)) {
                $wrapped .= $match[1] . $match[2] . $break;
                $string = $match[3] . $string;
                continue;
            }

                        $sub = $line_folding
                ? '(.+[^\s])'
                : '(.*)';

            if (preg_match('/^' . $sub . '(\s+)(.*)$/u', $line, $match)) {
                $wrapped .= $match[1] . $break;
                $string = ($line_folding ? $match[2] : '') . $match[3] . $string;
                continue;
            }

                        if ($cut) {
                $wrapped .= $line . $break;
                continue;
            }

            $wrapped .= $line;
        }

        return $wrapped . $string;
    }

    
    static public function wrap($text, $length = 80, $break_char = "\n",
                                $quote = false)
    {
        $paragraphs = array();

        foreach (preg_split('/\r?\n/', $text) as $input) {
            if ($quote && (strpos($input, '>') === 0)) {
                $line = $input;
            } else {
                
                if ($input != '-- ') {
                    $input = rtrim($input);
                }
                $line = self::wordwrap($input, $length, $break_char);
            }

            $paragraphs[] = $line;
        }

        return implode($break_char, $paragraphs);
    }

    
    static public function truncate($text, $length = 100)
    {
        return (self::length($text) > $length)
            ? rtrim(self::substr($text, 0, $length - 3)) . '...'
            : $text;
    }

    
    static public function abbreviate($text, $length = 20)
    {
        return (self::length($text) > $length)
            ? rtrim(self::substr($text, 0, round(($length - 3) / 2))) . '...' . ltrim(self::substr($text, (($length - 3) / 2) * -1))
            : $text;
    }

    
    static public function common($str1, $str2)
    {
        for ($result = '', $i = 0;
             isset($str1[$i]) && isset($str2[$i]) && $str1[$i] == $str2[$i];
             $i++) {
            $result .= $str1[$i];
        }
        return $result;
    }

    
    static public function isAlpha($string, $charset)
    {
        if (!Horde_Util::extensionExists('mbstring')) {
            return ctype_alpha($string);
        }

        $charset = self::_mbstringCharset($charset);
        $old_charset = mb_regex_encoding();

        if ($charset != $old_charset) {
            @mb_regex_encoding($charset);
        }
        $alpha = !@mb_ereg_match('[^[:alpha:]]', $string);
        if ($charset != $old_charset) {
            @mb_regex_encoding($old_charset);
        }

        return $alpha;
    }

    
    static public function isLower($string, $charset)
    {
        return ((self::lower($string, true, $charset) === $string) &&
                self::isAlpha($string, $charset));
    }

    
    static public function isUpper($string, $charset)
    {
        return ((self::upper($string, true, $charset) === $string) &&
                self::isAlpha($string, $charset));
    }

    
    static public function regexMatch($text, $regex, $charset = null)
    {
        if (!empty($charset)) {
            $regex = self::convertCharset($regex, $charset, 'utf-8');
            $text = self::convertCharset($text, $charset, 'utf-8');
        }

        $matches = array();
        foreach ($regex as $val) {
            if (preg_match('/' . $val . '/u', $text, $matches)) {
                break;
            }
        }

        if (!empty($charset)) {
            $matches = self::convertCharset($matches, 'utf-8', $charset);
        }

        return $matches;
    }

    
    static public function validUtf8($text)
    {
        $text = strval($text);

        for ($i = 0, $len = strlen($text); $i < $len; ++$i) {
            $c = ord($text[$i]);

            if ($c > 128) {
                if ($c > 247) {
                                        return false;
                } elseif ($c > 239) {
                    $j = 3;
                } elseif ($c > 223) {
                    $j = 2;
                } elseif ($c > 191) {
                    $j = 1;
                } else {
                    return false;
                }

                if (($i + $j) > $len) {
                    return false;
                }

                do {
                    $c = ord($text[++$i]);
                    if (($c < 128) || ($c > 191)) {
                        return false;
                    }
                } while (--$j);
            }
        }

        return true;
    }

    
    static protected function _mbstringCharset($charset)
    {
        
        return in_array(self::lower($charset), array('ks_c_5601-1987', 'ks_c_5601-1989'))
            ? 'UHC'
            : $charset;
    }

    
    static public function trimUtf8Bom($str)
    {
        return (substr($str, 0, 3) == pack('CCC', 239, 187, 191))
            ? substr($str, 3)
            : $str;
    }

}
