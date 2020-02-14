<?php



class Horde_Imap_Client_Ids implements Countable, Iterator, Serializable
{
    
    const ALL = "\01";
    const SEARCH_RES = "\02";
    const LARGEST = "\03";

    
    public $duplicates = false;

    
    protected $_ids = array();

    
    protected $_sequence = false;

    
    protected $_sorted = false;

    
    public function __construct($ids = null, $sequence = false)
    {
        $this->add($ids);
        $this->_sequence = $sequence;
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'all':
            return ($this->_ids === self::ALL);

        case 'ids':
            return is_array($this->_ids)
                ? $this->_ids
                : array();

        case 'largest':
            return ($this->_ids === self::LARGEST);

        case 'max':
            $this->sort();
            return end($this->_ids);

        case 'min':
            $this->sort();
            return reset($this->_ids);

        case 'range_string':
            if (!count($this)) {
                return '';
            }

            $min = $this->min;
            $max = $this->max;

            return ($min == $max)
                ? $min
                : $min . ':' . $max;

        case 'search_res':
            return ($this->_ids === self::SEARCH_RES);

        case 'sequence':
            return (bool)$this->_sequence;

        case 'special':
            return is_string($this->_ids);

        case 'tostring':
        case 'tostring_sort':
            if ($this->all) {
                return '1:*';
            } elseif ($this->largest) {
                return '*';
            } elseif ($this->search_res) {
                return '$';
            }
            return strval($this->_toSequenceString($name == 'tostring_sort'));
        }
    }

    
    public function __toString()
    {
        return $this->tostring;
    }

    
    public function add($ids)
    {
        if (!is_null($ids)) {
            if (is_string($ids) &&
                in_array($ids, array(self::ALL, self::SEARCH_RES, self::LARGEST))) {
                $this->_ids = $ids;
            } elseif ($add = $this->_resolveIds($ids)) {
                if (is_array($this->_ids) && !empty($this->_ids)) {
                    foreach ($add as $val) {
                        $this->_ids[] = $val;
                    }
                } else {
                    $this->_ids = $add;
                }
                if (!$this->duplicates) {
                    $this->_ids = (count($this->_ids) > 25000)
                        ? array_unique($this->_ids)
                        : array_keys(array_flip($this->_ids));
                }
            }

            $this->_sorted = (count($this->_ids) === 1);
        }
    }

    
    public function remove($ids)
    {
        if (!$this->isEmpty() &&
            ($remove = $this->_resolveIds($ids))) {
            $this->_ids = array_diff($this->_ids, array_unique($remove));
        }
    }

    
    public function isEmpty()
    {
        return (is_array($this->_ids) && !count($this->_ids));
    }

    
    public function reverse()
    {
        if (is_array($this->_ids)) {
            $this->_ids = array_reverse($this->_ids);
        }
    }

    
    public function sort()
    {
        if (!$this->_sorted && is_array($this->_ids)) {
            sort($this->_ids, SORT_NUMERIC);
            $this->_sorted = true;
        }
    }

    
    public function split($length)
    {
        $id = new Horde_Stream_Temp();
        $id->add($this->tostring_sort, true);

        $out = array();

        do {
            $out[] = $id->substring(0, $length) . $id->getToChar(',');
        } while (!$id->eof());

        return $out;
    }

    
    protected function _resolveIds($ids)
    {
        if ($ids instanceof Horde_Imap_Client_Ids) {
            return $ids->ids;
        } elseif (is_array($ids)) {
            return $ids;
        } elseif (is_string($ids) || is_integer($ids)) {
            return is_numeric($ids)
                ? array($ids)
                : $this->_fromSequenceString($ids);
        }

        return array();
    }

    
    protected function _toSequenceString($sort = true)
    {
        if (empty($this->_ids)) {
            return '';
        }

        $in = $this->_ids;

        if ($sort && !$this->_sorted) {
            sort($in, SORT_NUMERIC);
        }

        $first = $last = array_shift($in);
        $i = count($in) - 1;
        $out = array();

        reset($in);
        while (list($key, $val) = each($in)) {
            if (($last + 1) == $val) {
                $last = $val;
            }

            if (($i == $key) || ($last != $val)) {
                if ($last == $first) {
                    $out[] = $first;
                    if ($i == $key) {
                        $out[] = $val;
                    }
                } else {
                    $out[] = $first . ':' . $last;
                    if (($i == $key) && ($last != $val)) {
                        $out[] = $val;
                    }
                }
                $first = $last = $val;
            }
        }

        return empty($out)
            ? $first
            : implode(',', $out);
    }

    
    protected function _fromSequenceString($str)
    {
        $ids = array();
        $str = trim($str);

        if (!strlen($str)) {
            return $ids;
        }

        $idarray = explode(',', $str);

        reset($idarray);
        while (list(,$val) = each($idarray)) {
            $range = explode(':', $val);
            if (isset($range[1])) {
                for ($i = min($range), $j = max($range); $i <= $j; ++$i) {
                    $ids[] = $i;
                }
            } else {
                $ids[] = $val;
            }
        }

        return $ids;
    }

    

    
    public function count()
    {
        return is_array($this->_ids)
            ? count($this->_ids)
            : 0;
    }

    

    
    public function current()
    {
        return is_array($this->_ids)
            ? current($this->_ids)
            : null;
    }

    
    public function key()
    {
        return is_array($this->_ids)
            ? key($this->_ids)
            : null;
    }

    
    public function next()
    {
        if (is_array($this->_ids)) {
            next($this->_ids);
        }
    }

    
    public function rewind()
    {
        if (is_array($this->_ids)) {
            reset($this->_ids);
        }
    }

    
    public function valid()
    {
        return !is_null($this->key());
    }

    

    
    public function serialize()
    {
        $save = array();

        if ($this->duplicates) {
            $save['d'] = 1;
        }

        if ($this->_sequence) {
            $save['s'] = 1;
        }

        if ($this->_sorted) {
            $save['is'] = 1;
        }

        switch ($this->_ids) {
        case self::ALL:
            $save['a'] = true;
            break;

        case self::LARGEST:
            $save['l'] = true;
            break;

        case self::SEARCH_RES:
            $save['sr'] = true;
            break;

        default:
            $save['i'] = strval($this);
            break;
        }

        return serialize($save);
    }

    
    public function unserialize($data)
    {
        $save = @unserialize($data);

        $this->duplicates = !empty($save['d']);
        $this->_sequence = !empty($save['s']);
        $this->_sorted = !empty($save['is']);

        if (isset($save['a'])) {
            $this->_ids = self::ALL;
        } elseif (isset($save['l'])) {
            $this->_ids = self::LARGEST;
        } elseif (isset($save['sr'])) {
            $this->_ids = self::SEARCH_RES;
        } elseif (isset($save['i'])) {
            $this->add($save['i']);
        }
    }

}
