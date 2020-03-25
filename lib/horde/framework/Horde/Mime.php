<?php

class Horde_Mime
{
    
    const EOL = "\r\n";

    
    const MIME_PARAM_QUOTED = '/[\x01-\x20\x22\x28\x29\x2c\x2f\x3a-\x40\x5b-\x5d]/';

    
    static public $brokenRFC2231 = false;

    
    static public $decodeWindows1252 = false;

    
    static public function is8bit($string, $charset = null)
    {
        return ($string != Horde_String::convertCharset($string, $charset, 'US-ASCII'));
    }

    
    static public function encode($text, $charset = 'UTF-8')
    {
        
        if (!self::is8bit($text, 'UTF-8') && (strpos($text, null) === false)) {
            return $text;
        }

        $charset = Horde_String::lower($charset);
        $text = Horde_String::convertCharset($text, 'UTF-8', $charset);

        
        $size = preg_match_all('/([^\s]+)([\s]*)/', $text, $matches, PREG_SET_ORDER);

        $line = '';

        
        foreach ($matches as $key => $val) {
            if (self::is8bit($val[1], $charset)) {
                if ((($key + 1) < $size) &&
                    self::is8bit($matches[$key + 1][1], $charset)) {
                    $line .= self::_encode($val[1] . $val[2], $charset) . ' ';
                } else {
                    $line .= self::_encode($val[1], $charset) . $val[2];
                }
            } else {
                $line .= $val[1] . $val[2];
            }
        }

        return rtrim($line);
    }

    
    static protected function _encode($text, $charset)
    {
        $encoded = trim(base64_encode($text));
        $c_size = strlen($charset) + 7;

        if ((strlen($encoded) + $c_size) > 75) {
            $parts = explode(self::EOL, rtrim(chunk_split($encoded, intval((75 - $c_size) / 4) * 4)));
        } else {
            $parts[] = $encoded;
        }

        $p_size = count($parts);
        $out = '';

        foreach ($parts as $key => $val) {
            $out .= '=?' . $charset . '?b?' . $val . '?=';
            if ($p_size > $key + 1) {
                
                $out .= self::EOL . ' ';
            }
        }

        return $out;
    }

    
    static public function quotedPrintableEncode($text, $eol = self::EOL,
                                                 $wrap = 76)
    {
        $curr_length = 0;
        $output = '';

        
        for ($i = 0, $length = strlen($text); $i < $length; ++$i) {
            $char = $text[$i];

            
            if ($char == "\n") {
                $output .= $eol;
                $curr_length = 0;
                continue;
            } elseif ($char == "\r") {
                continue;
            }

            
            $ascii = ord($char);
            if ((($ascii === 32) &&
                 ($i + 1 != $length) &&
                 (($text[$i + 1] == "\n") || ($text[$i + 1] == "\r"))) ||
                (($ascii < 32) || ($ascii > 126) || ($ascii === 61))) {
                $char_len = 3;
                $char = '=' . Horde_String::upper(sprintf('%02s', dechex($ascii)));
            } else {
                $char_len = 1;
            }

            
            $curr_length += $char_len;
            if ($curr_length > $wrap) {
                $output .= '=' . $eol;
                $curr_length = $char_len;
            }
            $output .= $char;
        }

        return $output;
    }

    
    static public function decode($string)
    {
        
        $string = preg_replace('|\?=\s+=\?|', '?==?', $string);

        $out = '';
        $old_pos = 0;

        while (($pos = strpos($string, '=?', $old_pos)) !== false) {
            
            $out .= substr($string, $old_pos, $pos - $old_pos);

            
            if (($d1 = strpos($string, '?', $pos + 2)) === false) {
                break;
            }

            $orig_charset = substr($string, $pos + 2, $d1 - $pos - 2);
            if (self::$decodeWindows1252 &&
                (Horde_String::lower($orig_charset) == 'iso-8859-1')) {
                $orig_charset = 'windows-1252';
            }

            
            if (($d2 = strpos($string, '?', $d1 + 1)) === false) {
                break;
            }

            $encoding = substr($string, $d1 + 1, $d2 - $d1 - 1);

            
            if (($end = strpos($string, '?=', $d2 + 1)) === false) {
                break;
            }

            $encoded_text = substr($string, $d2 + 1, $end - $d2 - 1);

            switch ($encoding) {
            case 'Q':
            case 'q':
                $out .= Horde_String::convertCharset(
                    preg_replace_callback(
                        '/=([0-9a-f]{2})/i',
                        function($ord) {
                            return chr(hexdec($ord[1]));
                        },
                        str_replace('_', ' ', $encoded_text)),
                    $orig_charset,
                    'UTF-8'
                );
            break;

            case 'B':
            case 'b':
                $out .= Horde_String::convertCharset(
                    base64_decode($encoded_text),
                    $orig_charset,
                    'UTF-8'
                );
            break;

            default:
                                break;
            }

            $old_pos = $end + 2;
        }

        return $out . substr($string, $old_pos);
    }

    
    static public function encodeParam($name, $val, array $opts = array())
    {
        $curr = 0;
        $encode = $wrap = false;
        $output = array();

        $charset = isset($opts['charset'])
            ? $opts['charset']
            : 'UTF-8';

                $pre_len = strlen($name) + 2;

        
        if (empty($opts['lang']) && !self::is8bit($val, 'UTF-8')) {
            $string = $val;
        } else {
            $cval = Horde_String::convertCharset($val, 'UTF-8', $charset);
            $string = Horde_String::lower($charset) . '\'' . (empty($opts['lang']) ? '' : Horde_String::lower($opts['lang'])) . '\'' . rawurlencode($cval);
            $encode = true;
            
            ++$pre_len;
        }

        if (($pre_len + strlen($string)) > 75) {
            
            ++$pre_len;
            $wrap = true;

            while ($string) {
                $chunk = 75 - $pre_len - strlen($curr);
                $pos = min($chunk, strlen($string) - 1);

                
                if (($chunk == $pos) && ($pos > 2)) {
                    for ($i = 0; $i <= 2; ++$i) {
                        if ($string[$pos - $i] == '%') {
                            $pos -= $i + 1;
                            break;
                        }
                    }
                }

                $lines[] = substr($string, 0, $pos + 1);
                $string = substr($string, $pos + 1);
                ++$curr;
            }
        } else {
            $lines = array($string);
        }

        foreach ($lines as $i => $line) {
            $output[$name . (($wrap) ? ('*' . $i) : '') . (($encode) ? '*' : '')] = $line;
        }

        if (self::$brokenRFC2231 && !isset($output[$name])) {
            $output = array_merge(array(
                $name => self::encode($val, $charset)
            ), $output);
        }

        
        foreach ($output as $k => $v) {
            if (preg_match(self::MIME_PARAM_QUOTED, $v)) {
                $output[$k] = '"' . addcslashes($v, '\\"') . '"';
            }
        }

        return $output;
    }

    
    static public function decodeParam($type, $data)
    {
        $convert = array();
        $ret = array('params' => array(), 'val' => '');
        $splitRegex = '/([^;\'"]*[\'"]([^\'"]*([^\'"]*)*)[\'"][^;\'"]*|([^;]+))(;|$)/';
        $type = Horde_String::lower($type);

        if (is_array($data)) {
                        $ret['val'] = ($type == 'content-type')
                ? 'text/plain'
                : 'attachment';
            $params = $data;
        } else {
            
            if (($pos = strpos($data, ';')) === false) {
                $ret['val'] = trim($data);
                return $ret;
            }

            $ret['val'] = trim(substr($data, 0, $pos));
            $data = trim(substr($data, ++$pos));
            $params = $tmp = array();

            if (strlen($data) > 0) {
                
                preg_match_all($splitRegex, $data, $matches);

                for ($i = 0, $cnt = count($matches[0]); $i < $cnt; ++$i) {
                    $param = $matches[0][$i];
                    while (substr($param, -2) == '\;') {
                        $param .= $matches[0][++$i];
                    }
                    $tmp[] = $param;
                }

                for ($i = 0, $cnt = count($tmp); $i < $cnt; ++$i) {
                    $pos = strpos($tmp[$i], '=');
                    $p_name = trim(substr($tmp[$i], 0, $pos), "'\";\t\\ ");
                    $p_val = trim(str_replace('\;', ';', substr($tmp[$i], $pos + 1)), "'\";\t\\ ");
                    if (strlen($p_val) && ($p_val[0] == '"')) {
                        $p_val = substr($p_val, 1, -1);
                    }

                    $params[$p_name] = $p_val;
                }
            }
            
        }

        
        uksort($params, 'strnatcasecmp');

        foreach ($params as $name => $val) {
            
            if (substr($name, -1) == '*') {
                $name = substr($name, 0, -1);
                $encode = true;
            } else {
                $encode = false;
            }

            
            if (($pos = strrpos($name, '*')) !== false) {
                $name = substr($name, 0, $pos);
            }

            if (!isset($ret['params'][$name]) ||
                ($encode && !isset($convert[$name]))) {
                $ret['params'][$name] = '';
            }

            $ret['params'][$name] .= $val;

            if ($encode) {
                $convert[$name] = true;
            }
        }

        foreach (array_keys($convert) as $name) {
            $val = $ret['params'][$name];
            $quote = strpos($val, "'");
            $orig_charset = substr($val, 0, $quote);
            if (self::$decodeWindows1252 &&
                (Horde_String::lower($orig_charset) == 'iso-8859-1')) {
                $orig_charset = 'windows-1252';
            }
            
            $quote = strpos($val, "'", $quote + 1);
            substr($val, $quote + 1);
            $ret['params'][$name] = Horde_String::convertCharset(urldecode(substr($val, $quote + 1)), $orig_charset, 'UTF-8');
        }

        
        if (empty($convert)) {
            foreach (array_diff(array_keys($ret['params']), array_keys($convert)) as $name) {
                $ret['params'][$name] = self::decode($ret['params'][$name]);
            }
        }

        return $ret;
    }

    
    static public function generateMessageId()
    {
        return '<' . strval(new Horde_Support_Guid(array('prefix' => 'Horde'))) . '>';
    }

    
    static public function mimeIdArithmetic($id, $action, $options = array())
    {
        $pos = strrpos($id, '.');
        $end = ($pos === false) ? $id : substr($id, $pos + 1);

        switch ($action) {
        case 'down':
            if ($end == '0') {
                $id = ($pos === false) ? 1 : substr_replace($id, '1', $pos + 1);
            } else {
                $id .= empty($options['norfc822']) ? '.0' : '.1';
            }
            break;

        case 'next':
            ++$end;
            $id = ($pos === false) ? $end : substr_replace($id, $end, $pos + 1);
            break;

        case 'prev':
            if (($end == '0') ||
                (empty($options['norfc822']) && ($end == '1'))) {
                $id = null;
            } elseif ($pos === false) {
                $id = --$end;
            } else {
                $id = substr_replace($id, --$end, $pos + 1);
            }
            break;

        case 'up':
            if ($pos === false) {
                $id = ($end == '0') ? null : '0';
            } elseif (!empty($options['norfc822']) || ($end == '0')) {
                $id = substr($id, 0, $pos);
            } else {
                $id = substr_replace($id, '0', $pos + 1);
            }
            break;
        }

        return (!is_null($id) && !empty($options['count']) && --$options['count'])
            ? self::mimeIdArithmetic($id, $action, $options)
            : $id;
    }

    
    static public function isChild($base, $id)
    {
        $base = (substr($base, -2) == '.0')
            ? substr($base, 0, -1)
            : rtrim($base, '.') . '.';

        return ((($base == 0) && ($id != 0)) ||
                (strpos(strval($id), strval($base)) === 0));
    }

    
    static public function uudecode($input)
    {
        $data = array();

        
        if (preg_match_all("/begin ([0-7]{3}) (.+)\r?\n(.+)\r?\nend/Us", $input, $matches, PREG_SET_ORDER)) {
            reset($matches);
            while (list(,$v) = each($matches)) {
                $data[] = array(
                    'data' => self::_uudecode($v[3]),
                    'name' => $v[2],
                    'perm' => $v[1]
                );
            }
        }

        return $data;
    }

    
    static protected function _uudecode($input)
    {
        $decoded = '';

        foreach (explode("\n", $input) as $line) {
            $c = count($bytes = unpack('c*', substr(trim($line,"\r\n\t"), 1)));

            while ($c % 4) {
                $bytes[++$c] = 0;
            }

            foreach (array_chunk($bytes, 4) as $b) {
                $b0 = ($b[0] == 0x60) ? 0 : $b[0] - 0x20;
                $b1 = ($b[1] == 0x60) ? 0 : $b[1] - 0x20;
                $b2 = ($b[2] == 0x60) ? 0 : $b[2] - 0x20;
                $b3 = ($b[3] == 0x60) ? 0 : $b[3] - 0x20;

                $b0 <<= 2;
                $b0 |= ($b1 >> 4) & 0x03;
                $b1 <<= 4;
                $b1 |= ($b2 >> 2) & 0x0F;
                $b2 <<= 6;
                $b2 |= $b3 & 0x3F;

                $decoded .= pack('c*', $b0, $b1, $b2);
            }
        }

        return rtrim($decoded, "\0");
    }

}
