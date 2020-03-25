<?php



class Horde_Imap_Client_Data_BaseSubject
{
    
    protected $_subject;

    
    public function __construct($str, array $opts = array())
    {
                $str = Horde_Mime::decode($str);

                $str = preg_replace("/[\t\r\n ]+/", ' ', $str);

        do {
            
            $str = preg_replace("/(?:\s*\(fwd\)\s*)+$/i", '', $str);

            do {
                
                $found = $this->_removeSubjLeader($str, !empty($opts['keepblob']));

                
                $found = (empty($opts['keepblob']) && $this->_removeBlobWhenNonempty($str)) || $found;

                
            } while ($found);

            
        } while ($this->_removeSubjFwdHdr($str));

        $this->_subject = strval($str);
    }

    
    public function __toString()
    {
        return $this->_subject;
    }

    
    protected function _removeSubjLeader(&$str, $keepblob = false)
    {
        $ret = false;

        if (!strlen($str)) {
            return $ret;
        }

        if ($len = strspn($str, " \t")) {
            $str = substr($str, $len);
            $ret = true;
        }

        $i = 0;

        if (!$keepblob) {
            while (isset($str[$i]) && ($str[$i] === '[')) {
                if (($i = $this->_removeBlob($str, $i)) === false) {
                    return $ret;
                }
            }
        }

        if (stripos($str, 're', $i) === 0) {
            $i += 2;
        } elseif (stripos($str, 'fwd', $i) === 0) {
            $i += 3;
        } elseif (stripos($str, 'fw', $i) === 0) {
            $i += 2;
        } else {
            return $ret;
        }

        $i += strspn($str, " \t", $i);

        if (!$keepblob) {
            while (isset($str[$i]) && ($str[$i] === '[')) {
                if (($i = $this->_removeBlob($str, $i)) === false) {
                    return $ret;
                }
            }
        }

        if (!isset($str[$i]) || ($str[$i] !== ':')) {
            return $ret;
        }

        $str = substr($str, ++$i);

        return true;
    }

    
    protected function _removeBlob($str, $i)
    {
        if ($str[$i] !== '[') {
            return false;
        }

        ++$i;

        for ($cnt = strlen($str); $i < $cnt; ++$i) {
            if ($str[$i] === ']') {
                break;
            }

            if ($str[$i] === '[') {
                return false;
            }
        }

        if ($i === ($cnt - 1)) {
            return false;
        }

        ++$i;

        if ($str[$i] === ' ') {
            ++$i;
        }

        return $i;
    }

    
    protected function _removeBlobWhenNonempty(&$str)
    {
        if ($str &&
            ($str[0] === '[') &&
            (($i = $this->_removeBlob($str, 0)) !== false) &&
            ($i !== strlen($str))) {
            $str = substr($str, $i);
            return true;
        }

        return false;
    }

    
    protected function _removeSubjFwdHdr(&$str)
    {
        if ((stripos($str, '[fwd:') !== 0) || (substr($str, -1) !== ']')) {
            return false;
        }

        $str = substr($str, 5, -1);
        return true;
    }

}
