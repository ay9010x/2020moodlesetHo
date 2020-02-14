<?php



class Horde_Imap_Client_Utf7imap
{
    
    private static $_index64 = array(
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 62, 63, -1, -1, -1,
        52, 53, 54, 55, 56, 57, 58, 59, 60, 61, -1, -1, -1, -1, -1, -1,
        -1,  0,  1,  2,  3,  4,  5,  6,  7,  8,  9, 10, 11, 12, 13, 14,
        15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, -1, -1, -1, -1, -1,
        -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
        41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1, -1, -1
    );

    
    private static $_base64 = array(
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N',
        'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b',
        'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
        'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3',
        '4', '5', '6', '7', '8', '9', '+', ','
    );

    
    private static $_mbstring = null;

    
    public static function Utf7ImapToUtf8($str)
    {
        if ($str instanceof Horde_Imap_Client_Mailbox) {
            return $str->utf8;
        }

        $str = strval($str);

        
        if (is_null(self::$_mbstring)) {
            self::$_mbstring = extension_loaded('mbstring');
        }
        if (self::$_mbstring) {
            return @mb_convert_encoding($str, 'UTF-8', 'UTF7-IMAP');
        }

        $p = '';
        $ptr = &self::$_index64;

        for ($i = 0, $u7len = strlen($str); $u7len > 0; ++$i, --$u7len) {
            $u7 = $str[$i];
            if ($u7 === '&') {
                $u7 = $str[++$i];
                if (--$u7len && ($u7 === '-')) {
                    $p .= '&';
                    continue;
                }

                $ch = 0;
                $k = 10;
                for (; $u7len > 0; ++$i, --$u7len) {
                    $u7 = $str[$i];

                    if ((ord($u7) & 0x80) || ($b = $ptr[ord($u7)]) === -1) {
                        break;
                    }

                    if ($k > 0) {
                        $ch |= $b << $k;
                        $k -= 6;
                    } else {
                        $ch |= $b >> (-$k);
                        if ($ch < 0x80) {
                            
                            if ((0x20 <= $ch) && ($ch < 0x7f)) {
                                throw new Horde_Imap_Client_Exception(
                                    Horde_Imap_Client_Translation::r("Error converting UTF7-IMAP string."),
                                    Horde_Imap_Client_Exception::UTF7IMAP_CONVERSION
                                );
                            }
                            $p .= chr($ch);
                        } else if ($ch < 0x800) {
                            $p .= chr(0xc0 | ($ch >> 6)) .
                                  chr(0x80 | ($ch & 0x3f));
                        } else {
                            $p .= chr(0xe0 | ($ch >> 12)) .
                                  chr(0x80 | (($ch >> 6) & 0x3f)) .
                                  chr(0x80 | ($ch & 0x3f));
                        }

                        $ch = ($b << (16 + $k)) & 0xffff;
                        $k += 10;
                    }
                }

                
                if (($ch || ($k < 6)) ||
                    (!$u7len || $u7 !== '-') ||
                    (($u7len > 2) &&
                     ($str[$i + 1] === '&') &&
                     ($str[$i + 2] !== '-'))) {
                    throw new Horde_Imap_Client_Exception(
                        Horde_Imap_Client_Translation::r("Error converting UTF7-IMAP string."),
                        Horde_Imap_Client_Exception::UTF7IMAP_CONVERSION
                    );
                }
            } elseif ((ord($u7) < 0x20) || (ord($u7) >= 0x7f)) {
                
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("Error converting UTF7-IMAP string."),
                    Horde_Imap_Client_Exception::UTF7IMAP_CONVERSION
                );
            } else {
                $p .= $u7;
            }
        }

        return $p;
    }

    
    public static function Utf8ToUtf7Imap($str, $force = true)
    {
        if ($str instanceof Horde_Imap_Client_Mailbox) {
            return $str->utf7imap;
        }

        $str = strval($str);

        
        if (!$force &&
            !preg_match('/[\x80-\xff]|&$|&(?![,+A-Za-z0-9]*-)/', $str)) {
            return $str;
        }

        
        if (is_null(self::$_mbstring)) {
            self::$_mbstring = extension_loaded('mbstring');
        }
        if (self::$_mbstring) {
            return @mb_convert_encoding($str, 'UTF7-IMAP', 'UTF-8');
        }

        $u8len = strlen($str);
        $i = 0;
        $base64 = false;
        $p = '';
        $ptr = &self::$_base64;

        while ($u8len) {
            $u8 = $str[$i];
            $c = ord($u8);

            if ($c < 0x80) {
                $ch = $c;
                $n = 0;
            } elseif ($c < 0xc2) {
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("Error converting UTF7-IMAP string."),
                    Horde_Imap_Client_Exception::UTF7IMAP_CONVERSION
                );
            } elseif ($c < 0xe0) {
                $ch = $c & 0x1f;
                $n = 1;
            } elseif ($c < 0xf0) {
                $ch = $c & 0x0f;
                $n = 2;
            } elseif ($c < 0xf8) {
                $ch = $c & 0x07;
                $n = 3;
            } elseif ($c < 0xfc) {
                $ch = $c & 0x03;
                $n = 4;
            } elseif ($c < 0xfe) {
                $ch = $c & 0x01;
                $n = 5;
            } else {
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("Error converting UTF7-IMAP string."),
                    Horde_Imap_Client_Exception::UTF7IMAP_CONVERSION
                );
            }

            if ($n > --$u8len) {
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("Error converting UTF7-IMAP string."),
                    Horde_Imap_Client_Exception::UTF7IMAP_CONVERSION
                );
            }

            ++$i;

            for ($j = 0; $j < $n; ++$j) {
                $o = ord($str[$i + $j]);
                if (($o & 0xc0) !== 0x80) {
                    throw new Horde_Imap_Client_Exception(
                        Horde_Imap_Client_Translation::r("Error converting UTF7-IMAP string."),
                        Horde_Imap_Client_Exception::UTF7IMAP_CONVERSION
                    );
                }
                $ch = ($ch << 6) | ($o & 0x3f);
            }

            if (($n > 1) && !($ch >> ($n * 5 + 1))) {
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("Error converting UTF7-IMAP string."),
                    Horde_Imap_Client_Exception::UTF7IMAP_CONVERSION
                );
            }

            $i += $n;
            $u8len -= $n;

            if (($ch < 0x20) || ($ch >= 0x7f)) {
                if (!$base64) {
                    $p .= '&';
                    $base64 = true;
                    $b = 0;
                    $k = 10;
                }

                if ($ch & ~0xffff) {
                    $ch = 0xfffe;
                }

                $p .= $ptr[($b | $ch >> $k)];
                $k -= 6;
                for (; $k >= 0; $k -= 6) {
                    $p .= $ptr[(($ch >> $k) & 0x3f)];
                }

                $b = ($ch << (-$k)) & 0x3f;
                $k += 16;
            } else {
                if ($base64) {
                    if ($k > 10) {
                        $p .= $ptr[$b];
                    }
                    $p .= '-';
                    $base64 = false;
                }

                $p .= chr($ch);
                if (chr($ch) === '&') {
                    $p .= '-';
                }
            }
        }

        if ($base64) {
            if ($k > 10) {
                $p .= $ptr[$b];
            }
            $p .= '-';
        }

        return $p;
    }

}
