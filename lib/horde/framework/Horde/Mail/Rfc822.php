<?php



class Horde_Mail_Rfc822
{
    
    const ATEXT = '!#$%&\'*+-./0123456789=?ABCDEFGHIJKLMNOPQRSTUVWXYZ^_`abcdefghijklmnopqrstuvwxyz{|}~';

    
    const ENCODE_FILTER = "\0\1\2\3\4\5\6\7\10\12\13\14\15\16\17\20\21\22\23\24\25\26\27\30\31\32\33\34\35\36\37\"(),:;<>@[\\]\177";

    
    protected $_data;

    
    protected $_datalen;

    
    protected $_comments = array();

    
    protected $_listob;

    
    protected $_params = array();

    
    protected $_ptr;

    
    public function parseAddressList($address, array $params = array())
    {
        if ($address instanceof Horde_Mail_Rfc822_List) {
            return $address;
        }

        if (empty($params['limit'])) {
            $params['limit'] = -1;
        }

        $this->_params = array_merge(array(
            'default_domain' => null,
            'validate' => false
        ), $params);

        $this->_listob = empty($this->_params['group'])
            ? new Horde_Mail_Rfc822_List()
            : new Horde_Mail_Rfc822_GroupList();

        if (!is_array($address)) {
            $address = array($address);
        }

        $tmp = array();
        foreach ($address as $val) {
            if ($val instanceof Horde_Mail_Rfc822_Object) {
                $this->_listob->add($val);
            } else {
                $tmp[] = rtrim(trim($val), ',');
            }
        }

        if (!empty($tmp)) {
            $this->_data = implode(',', $tmp);
            $this->_datalen = strlen($this->_data);
            $this->_ptr = 0;

            $this->_parseAddressList();
        }

        $ret = $this->_listob;
        unset($this->_listob);

        return $ret;
    }

   
    public function encode($str, $type = 'address')
    {
        switch ($type) {
        case 'personal':
                        $filter = '.';
            break;

        case 'address':
        default:
                        $filter = "\11\40";
            break;
        }

                                $str = trim($str);
        if ($str && ($str[0] == '"') && (substr($str, -1) == '"')) {
            $str = stripslashes(substr($str, 1, -1));
        }

        return (strcspn($str, self::ENCODE_FILTER . $filter) != strlen($str))
            ? '"' . addcslashes($str, '\\"') . '"'
            : $str;
    }

    
    public function trimAddress($address)
    {
        $address = trim($address);

        return (($address[0] == '<') && (substr($address, -1) == '>'))
            ? substr($address, 1, -1)
            : $address;
    }

    

    
    protected function _parseAddressList()
    {
        $limit = $this->_params['limit'];

        while (($this->_curr() !== false) && ($limit-- !== 0)) {
            try {
                $this->_parseAddress();
            } catch (Horde_Mail_Exception $e) {
               if ($this->_params['validate']) {
                   throw $e;
               }
               ++$this->_ptr;
            }

            switch ($this->_curr()) {
            case ',':
                $this->_rfc822SkipLwsp(true);
                break;

            case false:
                                break;

            default:
               if ($this->_params['validate']) {
                    throw new Horde_Mail_Exception('Error when parsing address list.');
               }
               break;
            }
        }
    }

    
    protected function _parseAddress()
    {
        $start = $this->_ptr;
        if (!$this->_parseGroup()) {
            $this->_ptr = $start;
            if ($mbox = $this->_parseMailbox()) {
                $this->_listob->add($mbox);
            }
        }
    }

    
    protected function _parseGroup()
    {
        $this->_rfc822ParsePhrase($groupname);

        if ($this->_curr(true) != ':') {
            return false;
        }

        $addresses = new Horde_Mail_Rfc822_GroupList();

        $this->_rfc822SkipLwsp();

        while (($chr = $this->_curr()) !== false) {
            if ($chr == ';') {
                ++$this->_ptr;

                if (count($addresses)) {
                    $this->_listob->add(new Horde_Mail_Rfc822_Group($groupname, $addresses));
                }

                return true;
            }

            
            $addresses->add($this->_parseMailbox());

            switch ($this->_curr()) {
            case ',':
                $this->_rfc822SkipLwsp(true);
                break;

            case ';':
                                break;

            default:
                break 2;
            }
        }

        throw new Horde_Mail_Exception('Error when parsing group.');
    }

    
    protected function _parseMailbox()
    {
        $this->_comments = array();
        $start = $this->_ptr;

        if (!($ob = $this->_parseNameAddr())) {
            $this->_comments = array();
            $this->_ptr = $start;
            $ob = $this->_parseAddrSpec();
        }

        if ($ob) {
            $ob->comment = $this->_comments;
        }

        return $ob;
    }

    
    protected function _parseNameAddr()
    {
        $this->_rfc822ParsePhrase($personal);

        if ($ob = $this->_parseAngleAddr()) {
            $ob->personal = $personal;
            return $ob;
        }

        return false;
    }

    
    protected function _parseAddrSpec()
    {
        $ob = new Horde_Mail_Rfc822_Address();
        $ob->mailbox = $this->_parseLocalPart();

        if ($this->_curr() == '@') {
            try {
                $this->_rfc822ParseDomain($host);
                if (strlen($host)) {
                    $ob->host = $host;
                }
            } catch (Horde_Mail_Exception $e) {
                if (!empty($this->_params['validate'])) {
                    throw $e;
                }
            }
        }

        if (is_null($ob->host)) {
            if (!is_null($this->_params['default_domain'])) {
                $ob->host = $this->_params['default_domain'];
            } elseif (!empty($this->_params['validate'])) {
                throw new Horde_Mail_Exception('Address is missing domain.');
            }
        }

        return $ob;
    }

    
    protected function _parseLocalPart()
    {
        if (($curr = $this->_curr()) === false) {
            throw new Horde_Mail_Exception('Error when parsing local part.');
        }

        if ($curr == '"') {
            $this->_rfc822ParseQuotedString($str);
        } else {
            $this->_rfc822ParseDotAtom($str, ',;@');
        }

        return $str;
    }

    
    protected function _parseAngleAddr()
    {
        if ($this->_curr() != '<') {
            return false;
        }

        $this->_rfc822SkipLwsp(true);

        if ($this->_curr() == '@') {
                        $this->_parseDomainList();
            if ($this->_curr() != ':') {
                throw new Horde_Mail_Exception('Invalid route.');
            }

            $this->_rfc822SkipLwsp(true);
        }

        $ob = $this->_parseAddrSpec();

        if ($this->_curr() != '>') {
            throw new Horde_Mail_Exception('Error when parsing angle address.');
        }

        $this->_rfc822SkipLwsp(true);

        return $ob;
    }

    
    protected function _parseDomainList()
    {
        $route = array();

        while ($this->_curr() !== false) {
            $this->_rfc822ParseDomain($str);
            $route[] = '@' . $str;

            $this->_rfc822SkipLwsp();
            if ($this->_curr() != ',') {
                return $route;
            }
            ++$this->_ptr;
        }

        throw new Horde_Mail_Exception('Invalid domain list.');
    }

    

    
    protected function _rfc822ParsePhrase(&$phrase)
    {
        $curr = $this->_curr();
        if (($curr === false) || ($curr == '.')) {
            throw new Horde_Mail_Exception('Error when parsing a group.');
        }

        do {
            if ($curr == '"') {
                $this->_rfc822ParseQuotedString($phrase);
            } else {
                $this->_rfc822ParseAtomOrDot($phrase);
            }

            $curr = $this->_curr();
            if (($curr != '"') &&
                ($curr != '.') &&
                !$this->_rfc822IsAtext($curr)) {
                break;
            }

            $phrase .= ' ';
        } while ($this->_ptr < $this->_datalen);

        $this->_rfc822SkipLwsp();
    }

    
    protected function _rfc822ParseQuotedString(&$str)
    {
        if ($this->_curr(true) != '"') {
            throw new Horde_Mail_Exception('Error when parsing a quoted string.');
        }

        while (($chr = $this->_curr(true)) !== false) {
            switch ($chr) {
            case '"':
                $this->_rfc822SkipLwsp();
                return;

            case "\n":
                
                if (substr($str, -1) == "\r") {
                    $str = substr($str, 0, -1);
                }
                continue;

            case '\\':
                if (($chr = $this->_curr(true)) === false) {
                    break 2;
                }
                break;
            }

            $str .= $chr;
        }

        
        throw new Horde_Mail_Exception('Error when parsing a quoted string.');
    }

    
    protected function _rfc822ParseDotAtom(&$str, $validate = null)
    {
        $is_validate = $this->_params['validate'];
        $valid = false;

        while ($this->_ptr < $this->_datalen) {
            $chr = $this->_data[$this->_ptr];

            
            if (($is_validate && !strcspn($chr, self::ATEXT)) ||
                (!$is_validate && strcspn($chr, $validate))) {
                $str .= $chr;
                ++$this->_ptr;
            } elseif (!$valid) {
                throw new Horde_Mail_Exception('Error when parsing dot-atom.');
            } else {
                $this->_rfc822SkipLwsp();

                if ($this->_curr() != '.') {
                    return;
                }
                $str .= $chr;

                $this->_rfc822SkipLwsp(true);
            }

            $valid = true;
        }
    }

    
    protected function _rfc822ParseAtomOrDot(&$str)
    {
        $validate = $this->_params['validate'];

        while ($this->_ptr < $this->_datalen) {
            $chr = $this->_data[$this->_ptr];
            if (($chr != '.') &&
                
                !(($validate && !strcspn($chr, self::ATEXT)) ||
                  (!$validate && strcspn($chr, ',<:')))) {
                $this->_rfc822SkipLwsp();
                if (!$validate) {
                    $str = trim($str);
                }
                return;
            }

            $str .= $chr;
            ++$this->_ptr;
        }
    }

    
    protected function _rfc822ParseDomain(&$str)
    {
        if ($this->_curr(true) != '@') {
            throw new Horde_Mail_Exception('Error when parsing domain.');
        }

        $this->_rfc822SkipLwsp();

        if ($this->_curr() == '[') {
            $this->_rfc822ParseDomainLiteral($str);
        } else {
            $this->_rfc822ParseDotAtom($str, ';,> ');
        }
    }

    
    protected function _rfc822ParseDomainLiteral(&$str)
    {
        if ($this->_curr(true) != '[') {
            throw new Horde_Mail_Exception('Error parsing domain literal.');
        }

        while (($chr = $this->_curr(true)) !== false) {
            switch ($chr) {
            case '\\':
                if (($chr = $this->_curr(true)) === false) {
                    break 2;
                }
                break;

            case ']':
                $this->_rfc822SkipLwsp();
                return;
            }

            $str .= $chr;
        }

        throw new Horde_Mail_Exception('Error parsing domain literal.');
    }

    
    protected function _rfc822SkipLwsp($advance = false)
    {
        if ($advance) {
            ++$this->_ptr;
        }

        while (($chr = $this->_curr()) !== false) {
            switch ($chr) {
            case ' ':
            case "\n":
            case "\r":
            case "\t":
                ++$this->_ptr;
                continue;

            case '(':
                $this->_rfc822SkipComment();
                break;

            default:
                return;
            }
        }
    }

    
    protected function _rfc822SkipComment()
    {
        if ($this->_curr(true) != '(') {
            throw new Horde_Mail_Exception('Error when parsing a comment.');
        }

        $comment = '';
        $level = 1;

        while (($chr = $this->_curr(true)) !== false) {
            switch ($chr) {
            case '(':
                ++$level;
                continue;

            case ')':
                if (--$level == 0) {
                    $this->_comments[] = $comment;
                    return;
                }
                break;

            case '\\':
                if (($chr = $this->_curr(true)) === false) {
                    break 2;
                }
                break;
            }

            $comment .= $chr;
        }

        throw new Horde_Mail_Exception('Error when parsing a comment.');
    }

    
    protected function _rfc822IsAtext($chr, $validate = null)
    {
        return (!$this->_params['validate'] && !is_null($validate))
            ? strcspn($chr, $validate)
            : !strcspn($chr, self::ATEXT);
    }

    

    
    protected function _curr($advance = false)
    {
        return ($this->_ptr >= $this->_datalen)
            ? false
            : $this->_data[$advance ? $this->_ptr++ : $this->_ptr];
    }

    

    
    public function approximateCount($data)
    {
        return count(preg_split('/(?<!\\\\),/', $data));
    }

    
    public function isValidInetAddress($data, $strict = false)
    {
        $regex = $strict
            ? '/^([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})$/i'
            : '/^([*+!.&#$|\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})$/i';

        return preg_match($regex, trim($data), $matches)
            ? array($matches[1], $matches[2])
            : false;
    }

}
