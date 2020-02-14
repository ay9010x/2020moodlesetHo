<?php


class HTMLPurifier_Encoder
{

    
    private function __construct()
    {
        trigger_error('Cannot instantiate encoder, call methods statically', E_USER_ERROR);
    }

    
    public static function muteErrorHandler()
    {
    }

    
    public static function unsafeIconv($in, $out, $text)
    {
        set_error_handler(array('HTMLPurifier_Encoder', 'muteErrorHandler'));
        $r = iconv($in, $out, $text);
        restore_error_handler();
        return $r;
    }

    
    public static function iconv($in, $out, $text, $max_chunk_size = 8000)
    {
        $code = self::testIconvTruncateBug();
        if ($code == self::ICONV_OK) {
            return self::unsafeIconv($in, $out, $text);
        } elseif ($code == self::ICONV_TRUNCATES) {
                                    if ($in == 'utf-8') {
                if ($max_chunk_size < 4) {
                    trigger_error('max_chunk_size is too small', E_USER_WARNING);
                    return false;
                }
                                                if (($c = strlen($text)) <= $max_chunk_size) {
                    return self::unsafeIconv($in, $out, $text);
                }
                $r = '';
                $i = 0;
                while (true) {
                    if ($i + $max_chunk_size >= $c) {
                        $r .= self::unsafeIconv($in, $out, substr($text, $i));
                        break;
                    }
                                        if (0x80 != (0xC0 & ord($text[$i + $max_chunk_size]))) {
                        $chunk_size = $max_chunk_size;
                    } elseif (0x80 != (0xC0 & ord($text[$i + $max_chunk_size - 1]))) {
                        $chunk_size = $max_chunk_size - 1;
                    } elseif (0x80 != (0xC0 & ord($text[$i + $max_chunk_size - 2]))) {
                        $chunk_size = $max_chunk_size - 2;
                    } elseif (0x80 != (0xC0 & ord($text[$i + $max_chunk_size - 3]))) {
                        $chunk_size = $max_chunk_size - 3;
                    } else {
                        return false;                     }
                    $chunk = substr($text, $i, $chunk_size);                     $r .= self::unsafeIconv($in, $out, $chunk);
                    $i += $chunk_size;
                }
                return $r;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    
    public static function cleanUTF8($str, $force_php = false)
    {
                                                if (preg_match(
            '/^[\x{9}\x{A}\x{D}\x{20}-\x{7E}\x{A0}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]*$/Du',
            $str
        )) {
            return $str;
        }

        $mState = 0;                              $mUcs4  = 0;         $mBytes = 1; 
                                        
        $out = '';
        $char = '';

        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $in = ord($str{$i});
            $char .= $str[$i];             if (0 == $mState) {
                                                if (0 == (0x80 & ($in))) {
                                        if (($in <= 31 || $in == 127) &&
                        !($in == 9 || $in == 13 || $in == 10)                     ) {
                                            } else {
                        $out .= $char;
                    }
                                        $char = '';
                    $mBytes = 1;
                } elseif (0xC0 == (0xE0 & ($in))) {
                                        $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x1F) << 6;
                    $mState = 1;
                    $mBytes = 2;
                } elseif (0xE0 == (0xF0 & ($in))) {
                                        $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x0F) << 12;
                    $mState = 2;
                    $mBytes = 3;
                } elseif (0xF0 == (0xF8 & ($in))) {
                                        $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x07) << 18;
                    $mState = 3;
                    $mBytes = 4;
                } elseif (0xF8 == (0xFC & ($in))) {
                                                                                                                                                                                                        $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x03) << 24;
                    $mState = 4;
                    $mBytes = 5;
                } elseif (0xFC == (0xFE & ($in))) {
                                                            $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 1) << 30;
                    $mState = 5;
                    $mBytes = 6;
                } else {
                                                            $mState = 0;
                    $mUcs4  = 0;
                    $mBytes = 1;
                    $char = '';
                }
            } else {
                                                if (0x80 == (0xC0 & ($in))) {
                                        $shift = ($mState - 1) * 6;
                    $tmp = $in;
                    $tmp = ($tmp & 0x0000003F) << $shift;
                    $mUcs4 |= $tmp;

                    if (0 == --$mState) {
                                                
                        
                                                if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
                            ((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
                            ((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
                            (4 < $mBytes) ||
                                                        (($mUcs4 & 0xFFFFF800) == 0xD800) ||
                                                        ($mUcs4 > 0x10FFFF)
                        ) {

                        } elseif (0xFEFF != $mUcs4 &&                                                         (
                                0x9 == $mUcs4 ||
                                0xA == $mUcs4 ||
                                0xD == $mUcs4 ||
                                (0x20 <= $mUcs4 && 0x7E >= $mUcs4) ||
                                                                                                (0xA0 <= $mUcs4 && 0xD7FF >= $mUcs4) ||
                                (0x10000 <= $mUcs4 && 0x10FFFF >= $mUcs4)
                            )
                        ) {
                            $out .= $char;
                        }
                                                $mState = 0;
                        $mUcs4  = 0;
                        $mBytes = 1;
                        $char = '';
                    }
                } else {
                                                                                $mState = 0;
                    $mUcs4  = 0;
                    $mBytes = 1;
                    $char ='';
                }
            }
        }
        return $out;
    }

    

                                                
    public static function unichr($code)
    {
        if ($code > 1114111 or $code < 0 or
          ($code >= 55296 and $code <= 57343) ) {
                                    return '';
        }

        $x = $y = $z = $w = 0;
        if ($code < 128) {
                        $x = $code;
        } else {
                        $x = ($code & 63) | 128;
            if ($code < 2048) {
                $y = (($code & 2047) >> 6) | 192;
            } else {
                $y = (($code & 4032) >> 6) | 128;
                if ($code < 65536) {
                    $z = (($code >> 12) & 15) | 224;
                } else {
                    $z = (($code >> 12) & 63) | 128;
                    $w = (($code >> 18) & 7)  | 240;
                }
            }
        }
                $ret = '';
        if ($w) {
            $ret .= chr($w);
        }
        if ($z) {
            $ret .= chr($z);
        }
        if ($y) {
            $ret .= chr($y);
        }
        $ret .= chr($x);

        return $ret;
    }

    
    public static function iconvAvailable()
    {
        static $iconv = null;
        if ($iconv === null) {
            $iconv = function_exists('iconv') && self::testIconvTruncateBug() != self::ICONV_UNUSABLE;
        }
        return $iconv;
    }

    
    public static function convertToUTF8($str, $config, $context)
    {
        $encoding = $config->get('Core.Encoding');
        if ($encoding === 'utf-8') {
            return $str;
        }
        static $iconv = null;
        if ($iconv === null) {
            $iconv = self::iconvAvailable();
        }
        if ($iconv && !$config->get('Test.ForceNoIconv')) {
                        $str = self::unsafeIconv($encoding, 'utf-8//IGNORE', $str);
            if ($str === false) {
                                trigger_error('Invalid encoding ' . $encoding, E_USER_ERROR);
                return '';
            }
                                                $str = strtr($str, self::testEncodingSupportsASCII($encoding));
            return $str;
        } elseif ($encoding === 'iso-8859-1') {
            $str = utf8_encode($str);
            return $str;
        }
        $bug = HTMLPurifier_Encoder::testIconvTruncateBug();
        if ($bug == self::ICONV_OK) {
            trigger_error('Encoding not supported, please install iconv', E_USER_ERROR);
        } else {
            trigger_error(
                'You have a buggy version of iconv, see https://bugs.php.net/bug.php?id=48147 ' .
                'and http://sourceware.org/bugzilla/show_bug.cgi?id=13541',
                E_USER_ERROR
            );
        }
    }

    
    public static function convertFromUTF8($str, $config, $context)
    {
        $encoding = $config->get('Core.Encoding');
        if ($escape = $config->get('Core.EscapeNonASCIICharacters')) {
            $str = self::convertToASCIIDumbLossless($str);
        }
        if ($encoding === 'utf-8') {
            return $str;
        }
        static $iconv = null;
        if ($iconv === null) {
            $iconv = self::iconvAvailable();
        }
        if ($iconv && !$config->get('Test.ForceNoIconv')) {
                        $ascii_fix = self::testEncodingSupportsASCII($encoding);
            if (!$escape && !empty($ascii_fix)) {
                $clear_fix = array();
                foreach ($ascii_fix as $utf8 => $native) {
                    $clear_fix[$utf8] = '';
                }
                $str = strtr($str, $clear_fix);
            }
            $str = strtr($str, array_flip($ascii_fix));
                        $str = self::iconv('utf-8', $encoding . '//IGNORE', $str);
            return $str;
        } elseif ($encoding === 'iso-8859-1') {
            $str = utf8_decode($str);
            return $str;
        }
        trigger_error('Encoding not supported', E_USER_ERROR);
                                    }

    
    public static function convertToASCIIDumbLossless($str)
    {
        $bytesleft = 0;
        $result = '';
        $working = 0;
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $bytevalue = ord($str[$i]);
            if ($bytevalue <= 0x7F) {                 $result .= chr($bytevalue);
                $bytesleft = 0;
            } elseif ($bytevalue <= 0xBF) {                 $working = $working << 6;
                $working += ($bytevalue & 0x3F);
                $bytesleft--;
                if ($bytesleft <= 0) {
                    $result .= "&#" . $working . ";";
                }
            } elseif ($bytevalue <= 0xDF) {                 $working = $bytevalue & 0x1F;
                $bytesleft = 1;
            } elseif ($bytevalue <= 0xEF) {                 $working = $bytevalue & 0x0F;
                $bytesleft = 2;
            } else {                 $working = $bytevalue & 0x07;
                $bytesleft = 3;
            }
        }
        return $result;
    }

    
    const ICONV_OK = 0;

    
    const ICONV_TRUNCATES = 1;

    
    const ICONV_UNUSABLE = 2;

    
    public static function testIconvTruncateBug()
    {
        static $code = null;
        if ($code === null) {
                        $r = self::unsafeIconv('utf-8', 'ascii//IGNORE', "\xCE\xB1" . str_repeat('a', 9000));
            if ($r === false) {
                $code = self::ICONV_UNUSABLE;
            } elseif (($c = strlen($r)) < 9000) {
                $code = self::ICONV_TRUNCATES;
            } elseif ($c > 9000) {
                trigger_error(
                    'Your copy of iconv is extremely buggy. Please notify HTML Purifier maintainers: ' .
                    'include your iconv version as per phpversion()',
                    E_USER_ERROR
                );
            } else {
                $code = self::ICONV_OK;
            }
        }
        return $code;
    }

    
    public static function testEncodingSupportsASCII($encoding, $bypass = false)
    {
                                                static $encodings = array();
        if (!$bypass) {
            if (isset($encodings[$encoding])) {
                return $encodings[$encoding];
            }
            $lenc = strtolower($encoding);
            switch ($lenc) {
                case 'shift_jis':
                    return array("\xC2\xA5" => '\\', "\xE2\x80\xBE" => '~');
                case 'johab':
                    return array("\xE2\x82\xA9" => '\\');
            }
            if (strpos($lenc, 'iso-8859-') === 0) {
                return array();
            }
        }
        $ret = array();
        if (self::unsafeIconv('UTF-8', $encoding, 'a') === false) {
            return false;
        }
        for ($i = 0x20; $i <= 0x7E; $i++) {             $c = chr($i);             $r = self::unsafeIconv('UTF-8', "$encoding//IGNORE", $c);             if ($r === '' ||
                                                ($r === $c && self::unsafeIconv($encoding, 'UTF-8//IGNORE', $r) !== $c)
            ) {
                                                                $ret[self::unsafeIconv($encoding, 'UTF-8//IGNORE', $c)] = $c;
            }
        }
        $encodings[$encoding] = $ret;
        return $ret;
    }
}

