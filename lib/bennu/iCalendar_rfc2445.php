<?php





define('RFC2445_CRLF',               "\r\n");
define('RFC2445_WSP',                "\t ");
define('RFC2445_WEEKDAYS',           'MO,TU,WE,TH,FR,SA,SU');
define('RFC2445_FOLDED_LINE_LENGTH', 75);

define('RFC2445_PARAMETER_SEPARATOR',	';');
define('RFC2445_VALUE_SEPARATOR',    	':');

define('RFC2445_REQUIRED', 0x01);
define('RFC2445_OPTIONAL', 0x02);
define('RFC2445_ONCE',     0x04);

define('RFC2445_PROP_FLAGS',       0);
define('RFC2445_PROP_TYPE',        1);
define('RFC2445_PROP_DEFAULT',     2);

define('RFC2445_XNAME', 'X-');

define('RFC2445_TYPE_BINARY',       0);
define('RFC2445_TYPE_BOOLEAN',      1);
define('RFC2445_TYPE_CAL_ADDRESS',  2);
define('RFC2445_TYPE_DATE',         3);
define('RFC2445_TYPE_DATE_TIME',    4);
define('RFC2445_TYPE_DURATION',     5);
define('RFC2445_TYPE_FLOAT',        6);
define('RFC2445_TYPE_INTEGER',      7);
define('RFC2445_TYPE_PERIOD',       8);
define('RFC2445_TYPE_RECUR',        9);
define('RFC2445_TYPE_TEXT',        10);
define('RFC2445_TYPE_TIME',        11);
define('RFC2445_TYPE_URI',         12); define('RFC2445_TYPE_UTC_OFFSET',  13);


function rfc2445_fold($string) {
    if(core_text::strlen($string, 'utf-8') <= RFC2445_FOLDED_LINE_LENGTH) {
        return $string;
    }

    $retval = '';
  
    $i=0;
    $len_count=0;

        $section_len = core_text::strlen($string, 'utf-8');

    while($len_count<$section_len) {
        
                $section = core_text::substr($string, ($i * RFC2445_FOLDED_LINE_LENGTH), (RFC2445_FOLDED_LINE_LENGTH), 'utf-8');

                $len_count += core_text::strlen($section, 'utf-8');
        
        
        $retval .= $section . RFC2445_CRLF . substr(RFC2445_WSP, 0, 1);
        
        $i++;
    }

    return $retval;

}

function rfc2445_unfold($string) {
    for($i = 0; $i < strlen(RFC2445_WSP); ++$i) {
        $string = str_replace(RFC2445_CRLF.substr(RFC2445_WSP, $i, 1), '', $string);
    }

    return $string;
}

function rfc2445_is_xname($name) {

        if(strlen($name) < 3) {
        return false;
    }

        if(strspn($name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-') != strlen($name)) {
        return false;
    }

        return substr($name, 0, 2) === 'X-';
}

function rfc2445_is_valid_value($value, $type) {

        if($type === NULL) {
        return true;
    }

    switch($type) {
        case RFC2445_TYPE_CAL_ADDRESS:
        case RFC2445_TYPE_URI:
            if(!is_string($value)) {
                return false;
            }

            $valid_schemes = array('ftp', 'http', 'ldap', 'gopher', 'mailto', 'news', 'nntp', 'telnet', 'wais', 'file', 'prospero');

            $pos = strpos($value, ':');
            if(!$pos) {
                return false;
            }
        
            $scheme = strtolower(substr($value, 0, $pos));
            $remain = substr($value, $pos + 1);
            
            if(!in_array($scheme, $valid_schemes)) {
                return false;
            }
        
            if($scheme === 'mailto') {
                $regexp = '#^[a-zA-Z0-9]+[_a-zA-Z0-9\-]*(\.[_a-z0-9\-]+)*@(([0-9a-zA-Z\-]+\.)+[a-zA-Z][0-9a-zA-Z\-]+|([0-9]{1,3}\.){3}[0-9]{1,3})$#';
            }
            else {
                $regexp = '#^//(.+(:.*)?@)?(([0-9a-zA-Z\-]+\.)+[a-zA-Z][0-9a-zA-Z\-]+|([0-9]{1,3}\.){3}[0-9]{1,3})(:[0-9]{1,5})?(/.*)?$#';
            }
        
            return preg_match($regexp, $remain);
        break;

        case RFC2445_TYPE_BINARY:
            if(!is_string($value)) {
                return false;
            }

            $len = strlen($value);
            
            if($len % 4 != 0) {
                return false;
            }

            for($i = 0; $i < $len; ++$i) {
                $ch = $value{$i};
                if(!($ch >= 'a' && $ch <= 'z' || $ch >= 'A' && $ch <= 'Z' || $ch >= '0' && $ch <= '9' || $ch == '-' || $ch == '+')) {
                    if($ch == '=' && $len - $i <= 2) {
                        continue;
                    }
                    return false;
                }
            }
            return true;
        break;

        case RFC2445_TYPE_BOOLEAN:
            if(is_bool($value)) {
                return true;
            }
            if(is_string($value)) {
                $value = strtoupper($value);
                return ($value == 'TRUE' || $value == 'FALSE');
            }
            return false;
        break;

        case RFC2445_TYPE_DATE:
            if(is_int($value)) {
                if($value < 0) {
                    return false;
                }
                $value = "$value";
            }
            else if(!is_string($value)) {
                return false;
            }

            if(strlen($value) != 8) {
                return false;
            }

            $y = intval(substr($value, 0, 4));
            $m = intval(substr($value, 4, 2));
            $d = intval(substr($value, 6, 2));

            return checkdate($m, $d, $y);
        break;

        case RFC2445_TYPE_DATE_TIME:
            if(!is_string($value) || strlen($value) < 15) {
                return false;
            }

            return($value{8} == 'T' && 
                   rfc2445_is_valid_value(substr($value, 0, 8), RFC2445_TYPE_DATE) &&
                   rfc2445_is_valid_value(substr($value, 9), RFC2445_TYPE_TIME));
        break;

        case RFC2445_TYPE_DURATION:
            if(!is_string($value)) {
                return false;
            }

            $len = strlen($value);

            if($len < 3) {
                                return false;
            }

            if($value{0} == '+' || $value{0} == '-') {
                $value = substr($value, 1);
                --$len;             }

            if($value{0} != 'P') {
                return false;
            }

                        $num = '';
            $allowed = 'WDT';

            for($i = 1; $i < $len; ++$i) {
                $ch = $value{$i};
                if($ch >= '0' && $ch <= '9') {
                    $num .= $ch;
                    continue;
                }
                if(strpos($allowed, $ch) === false) {
                                        return false;
                }
                if($num === '' && $ch != 'T') {
                                        return false;
                }

                                switch($ch) {
                    case 'W':
                                                return ($i == $len - 1);
                    break;

                    case 'D':
                                                $allowed = 'T';
                    break;

                    case 'T':
                                                $allowed = 'HMS';
                    break;

                    case 'H':
                        $allowed = 'M';
                    break;

                    case 'M':
                        $allowed = 'S';
                    break;

                    case 'S':
                        return ($i == $len - 1);
                    break;
                }

                                $num = '';

            }

                                    return ($num === '' && $ch != 'T');

        break;
        
        case RFC2445_TYPE_FLOAT:
            if(is_float($value)) {
                return true;
            }
            if(!is_string($value) || $value === '') {
                return false;
            }

            $dot = false;
            $int = false;
            $len = strlen($value);
            for($i = 0; $i < $len; ++$i) {
                switch($value{$i}) {
                    case '-': case '+':
                                                if($i != 0 || $len == 1) {
                            return false;
                        }
                    break;
                    case '.':
                                                                        if($dot || !$int) {
                            return false;
                        }
                        $dot = true;
                                                if($i == $len - 1) {
                            return false;
                        }
                    break;
                    case '0': case '1': case '2': case '3': case '4':
                    case '5': case '6': case '7': case '8': case '9':
                        $int = true;
                    break;
                    default:
                                                return false;
                    break;
                }
            }
            return true;
        break;

        case RFC2445_TYPE_INTEGER:
            if(is_int($value)) {
                return true;
            }
            if(!is_string($value) || $value === '') {
                return false;
            }

            if($value{0} == '+' || $value{0} == '-') {
                if(strlen($value) == 1) {
                    return false;
                }
                $value = substr($value, 1);
            }

            if(strspn($value, '0123456789') != strlen($value)) {
                return false;
            }

            return ($value >= -2147483648 && $value <= 2147483647);
        break;

        case RFC2445_TYPE_PERIOD:
            if(!is_string($value) || empty($value)) {
                return false;
            }

            $parts = explode('/', $value);
            if(count($parts) != 2) {
                return false;
            }

            if(!rfc2445_is_valid_value($parts[0], RFC2445_TYPE_DATE_TIME)) {
                return false;
            }

                        if(rfc2445_is_valid_value($parts[1], RFC2445_TYPE_DATE_TIME)) {
                                return ($parts[1] > $parts[0]);
            }
            else if(rfc2445_is_valid_value($parts[1], RFC2445_TYPE_DURATION)) {
                                return ($parts[1]{0} != '-');
            }

                        return false;
        break;

        case RFC2445_TYPE_RECUR:
            if(!is_string($value)) {
                return false;
            }

            $parts = explode(';', strtoupper($value));

                        if(empty($parts)) {
                return false;
            }

                        $vars = array();
            foreach($parts as $part) {

                $pieces = explode('=', $part);
                                if(count($pieces) != 2) {
                    return false;
                }

                                if(isset($vars[$pieces[0]])) {
                    return false;
                }

                                $vars[$pieces[0]] = $pieces[1];
            }

            
                        reset($vars);
            if(key($vars) != 'FREQ') {
                return false;
            }

                        if(isset($vars['UNTIL']) && isset($vars['COUNT'])) {
                return false;
            }

                        if(isset($vars['BYWEEKNO']) && $vars['FREQ'] != 'YEARLY') {
                return false;
            }

                        if(isset($vars['BYSETPOS'])) {
                $options = array('BYSECOND', 'BYMINUTE', 'BYHOUR', 'BYDAY', 'BYMONTHDAY', 'BYYEARDAY', 'BYWEEKNO', 'BYMONTH');
                $defined = array_keys($vars);
                $common  = array_intersect($options, $defined);
                if(empty($common)) {
                    return false;
                }
            }

                                    
            if($vars['FREQ'] != 'SECONDLY' && $vars['FREQ'] != 'MINUTELY' && $vars['FREQ'] != 'HOURLY' && 
               $vars['FREQ'] != 'DAILY'    && $vars['FREQ'] != 'WEEKLY' &&
               $vars['FREQ'] != 'MONTHLY'  && $vars['FREQ'] != 'YEARLY') {
                return false;
            }
            unset($vars['FREQ']);

                        $weekdays = explode(',', RFC2445_WEEKDAYS);

            if(isset($vars['UNTIL'])) {
                if(rfc2445_is_valid_value($vars['UNTIL'], RFC2445_TYPE_DATE_TIME)) {
                                        if(!(substr($vars['UNTIL'], -1) == 'Z')) {
                        return false;
                    }
                }
                else if(!rfc2445_is_valid_value($vars['UNTIL'], RFC2445_TYPE_DATE_TIME)) {
                    return false;
                }
            }
            unset($vars['UNTIL']);


            if(isset($vars['COUNT'])) {
                if(empty($vars['COUNT'])) {
                                        return false;
                }
                if(strspn($vars['COUNT'], '0123456789') != strlen($vars['COUNT'])) {
                    return false;
                }
            }
            unset($vars['COUNT']);

            
            if(isset($vars['INTERVAL'])) {
                if(empty($vars['INTERVAL'])) {
                                        return false;
                }
                if(strspn($vars['INTERVAL'], '0123456789') != strlen($vars['INTERVAL'])) {
                    return false;
                }
            }
            unset($vars['INTERVAL']);

            
            if(isset($vars['BYSECOND'])) {
                if($vars['BYSECOND'] == '') {
                    return false;
                }
                                if(strspn($vars['BYSECOND'], '0123456789,') != strlen($vars['BYSECOND'])) {
                    return false;
                }
                $secs = explode(',', $vars['BYSECOND']);
                foreach($secs as $sec) {
                    if($sec == '' || $sec < 0 || $sec > 59) {
                        return false;
                    }
                }
            }
            unset($vars['BYSECOND']);

            
            if(isset($vars['BYMINUTE'])) {
                if($vars['BYMINUTE'] == '') {
                    return false;
                }
                                if(strspn($vars['BYMINUTE'], '0123456789,') != strlen($vars['BYMINUTE'])) {
                    return false;
                }
                $mins = explode(',', $vars['BYMINUTE']);
                foreach($mins as $min) {
                    if($min == '' || $min < 0 || $min > 59) {
                        return false;
                    }
                }
            }
            unset($vars['BYMINUTE']);

            
            if(isset($vars['BYHOUR'])) {
                if($vars['BYHOUR'] == '') {
                    return false;
                }
                                if(strspn($vars['BYHOUR'], '0123456789,') != strlen($vars['BYHOUR'])) {
                    return false;
                }
                $hours = explode(',', $vars['BYHOUR']);
                foreach($hours as $hour) {
                    if($hour == '' || $hour < 0 || $hour > 23) {
                        return false;
                    }
                }
            }
            unset($vars['BYHOUR']);
            

            if(isset($vars['BYDAY'])) {
                if(empty($vars['BYDAY'])) {
                    return false;
                }

                                $days = explode(',', $vars['BYDAY']);
                
                foreach($days as $day) {
                    $daypart = substr($day, -2);
                    if(!in_array($daypart, $weekdays)) {
                        return false;
                    }

                    if(strlen($day) > 2) {
                        $intpart = substr($day, 0, strlen($day) - 2);
                        if(!rfc2445_is_valid_value($intpart, RFC2445_TYPE_INTEGER)) {
                            return false;
                        }
                        if(intval($intpart) == 0) {
                            return false;
                        }
                    }
                }
            }
            unset($vars['BYDAY']);


            if(isset($vars['BYMONTHDAY'])) {
                if(empty($vars['BYMONTHDAY'])) {
                    return false;
                }
                $mdays = explode(',', $vars['BYMONTHDAY']);
                foreach($mdays as $mday) {
                    if(!rfc2445_is_valid_value($mday, RFC2445_TYPE_INTEGER)) {
                        return false;
                    }
                    $mday = abs(intval($mday));
                    if($mday == 0 || $mday > 31) {
                        return false;
                    }
                }
            }
            unset($vars['BYMONTHDAY']);


            if(isset($vars['BYYEARDAY'])) {
                if(empty($vars['BYYEARDAY'])) {
                    return false;
                }
                $ydays = explode(',', $vars['BYYEARDAY']);
                foreach($ydays as $yday) {
                    if(!rfc2445_is_valid_value($yday, RFC2445_TYPE_INTEGER)) {
                        return false;
                    }
                    $yday = abs(intval($yday));
                    if($yday == 0 || $yday > 366) {
                        return false;
                    }
                }
            }
            unset($vars['BYYEARDAY']);


            if(isset($vars['BYWEEKNO'])) {
                if(empty($vars['BYWEEKNO'])) {
                    return false;
                }
                $weeknos = explode(',', $vars['BYWEEKNO']);
                foreach($weeknos as $weekno) {
                    if(!rfc2445_is_valid_value($weekno, RFC2445_TYPE_INTEGER)) {
                        return false;
                    }
                    $weekno = abs(intval($weekno));
                    if($weekno == 0 || $weekno > 53) {
                        return false;
                    }
                }
            }
            unset($vars['BYWEEKNO']);


            if(isset($vars['BYMONTH'])) {
                if(empty($vars['BYMONTH'])) {
                    return false;
                }
                                if(strspn($vars['BYMONTH'], '0123456789,') != strlen($vars['BYMONTH'])) {
                    return false;
                }
                $months = explode(',', $vars['BYMONTH']);
                foreach($months as $month) {
                    if($month == '' || $month < 1 || $month > 12) {
                        return false;
                    }
                }
            }
            unset($vars['BYMONTH']);


            if(isset($vars['BYSETPOS'])) {
                if(empty($vars['BYSETPOS'])) {
                    return false;
                }
                $sets = explode(',', $vars['BYSETPOS']);
                foreach($sets as $set) {
                    if(!rfc2445_is_valid_value($set, RFC2445_TYPE_INTEGER)) {
                        return false;
                    }
                    $set = abs(intval($set));
                    if($set == 0 || $set > 366) {
                        return false;
                    }
                }
            }
            unset($vars['BYSETPOS']);


            if(isset($vars['WKST'])) {
                if(!in_array($vars['WKST'], $weekdays)) {
                    return false;
                }
            }
            unset($vars['WKST']);


                        if(empty($vars)) {
                return true;
            }

            foreach($vars as $name => $var) {
                if(!rfc2445_is_xname($name)) {
                    return false;
                }
            }

                        return true;

        break;

        case RFC2445_TYPE_TEXT:
            return true;
        break;

        case RFC2445_TYPE_TIME:
            if(is_int($value)) {
                if($value < 0) {
                    return false;
                }
                $value = "$value";
            }
            else if(!is_string($value)) {
                return false;
            }

            if(strlen($value) == 7) {
                if(strtoupper(substr($value, -1)) != 'Z') {
                    return false;
                }
                $value = substr($value, 0, 6);
            }
            if(strlen($value) != 6) {
                return false;
            }

            $h = intval(substr($value, 0, 2));
            $m = intval(substr($value, 2, 2));
            $s = intval(substr($value, 4, 2));

            return ($h <= 23 && $m <= 59 && $s <= 60);
        break;

        case RFC2445_TYPE_UTC_OFFSET:
            if(is_int($value)) {
                if($value >= 0) {
                    $value = "+$value";
                }
                else {
                    $value = "$value";
                }
            }
            else if(!is_string($value)) {
                return false;
            }

            $s = 0;
            if(strlen($value) == 7) {
                $s = intval(substr($value, 5, 2));
                $value = substr($value, 0, 5);
            }
            if(strlen($value) != 5 || $value == "-0000") {
                return false;
            }

            if($value{0} != '+' && $value{0} != '-') {
                return false;
            }

            $h = intval(substr($value, 1, 2));
            $m = intval(substr($value, 3, 2));

            return ($h <= 23 && $m <= 59 && $s <= 59);
        break;
    }

        trigger_error('bad code path', E_USER_WARNING);
    var_dump($type);
    return false;
}

function rfc2445_do_value_formatting($value, $type) {
        switch($type) {
        case RFC2445_TYPE_CAL_ADDRESS:
        case RFC2445_TYPE_URI:
                        $value = '"'.$value.'"';
        break;
        case RFC2445_TYPE_TEXT:
                        $value = strtr($value, array("\r\n" => '\\n', "\n" => '\\n', '\\' => '\\\\', ',' => '\\,', ';' => '\\;'));
        break;
    }
    return $value;
}

function rfc2445_undo_value_formatting($value, $type) {
    switch($type) {
        case RFC2445_TYPE_CAL_ADDRESS:
        case RFC2445_TYPE_URI:
                        $value = substr($value, 1, strlen($value) - 2);
        break;
        case RFC2445_TYPE_TEXT:
                        $value = strtr($value, array('\\n' => "\n", '\\N' => "\n", '\\\\' => '\\', '\\,' => ',', '\\;' => ';'));
        break;
    }
    return $value;
}
