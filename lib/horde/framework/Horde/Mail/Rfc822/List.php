<?php



class Horde_Mail_Rfc822_List extends Horde_Mail_Rfc822_Object implements ArrayAccess, Countable, SeekableIterator, Serializable
{
    
    const HIDE_GROUPS = 1;
    const BASE_ELEMENTS = 2;

    
    protected $_data = array();

    
    protected $_filter = array();

    
    protected $_ptr;

    
    public function __construct($obs = null)
    {
        if (!is_null($obs)) {
            $this->add($obs);
        }
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'addresses':
        case 'bare_addresses':
        case 'bare_addresses_idn':
        case 'base_addresses':
        case 'raw_addresses':
            $old = $this->_filter;
            $mask = ($name == 'base_addresses')
                ? self::BASE_ELEMENTS
                : self::HIDE_GROUPS;
            $this->setIteratorFilter($mask, empty($old['filter']) ? null : $old['filter']);

            $out = array();
            foreach ($this as $val) {
                switch ($name) {
                case 'addresses':
                    $out[] = strval($val);
                    break;

                case 'bare_addresses':
                    $out[] = $val->bare_address;
                    break;

                case 'bare_addresses_idn':
                    $out[] = $val->bare_address_idn;
                    break;

                case 'base_addresses':
                case 'raw_addresses':
                    $out[] = clone $val;
                    break;
                }
            }

            $this->_filter = $old;
            return $out;
        }
    }

    
    public function add($obs)
    {
        foreach ($this->_normalize($obs) as $val) {
            $this->_data[] = $val;
        }
    }

    
    public function remove($obs)
    {
        $old = $this->_filter;
        $this->setIteratorFilter(self::HIDE_GROUPS | self::BASE_ELEMENTS);

        foreach ($this->_normalize($obs) as $val) {
            $remove = array();

            foreach ($this as $key => $val2) {
                if ($val2->match($val)) {
                    $remove[] = $key;
                }
            }

            foreach (array_reverse($remove) as $key) {
                unset($this[$key]);
            }
        }

        $this->_filter = $old;
    }

    
    public function unique()
    {
        $exist = $remove = array();

        $old = $this->_filter;
        $this->setIteratorFilter(self::HIDE_GROUPS | self::BASE_ELEMENTS);

                        foreach ($this as $key => $val) {
            $bare = $val->bare_address;
            if (isset($exist[$bare])) {
                if (($exist[$bare] == -1) || is_null($val->personal)) {
                    $remove[] = $key;
                } else {
                    $remove[] = $exist[$bare];
                    $exist[$bare] = -1;
                }
            } else {
                $exist[$bare] = is_null($val->personal)
                    ? $key
                    : -1;
            }
        }

        foreach (array_reverse($remove) as $key) {
            unset($this[$key]);
        }

        $this->_filter = $old;
    }

    
    public function groupCount()
    {
        $ret = 0;

        foreach ($this->_data as $val) {
            if ($val instanceof Horde_Mail_Rfc822_Group) {
                ++$ret;
            }
        }

        return $ret;
    }

    
    public function setIteratorFilter($mask = 0, $filter = null)
    {
        $this->_filter = array();

        if ($mask) {
            $this->_filter['mask'] = $mask;
        }

        if (!is_null($filter)) {
            $rfc822 = new Horde_Mail_Rfc822();
            $this->_filter['filter'] = $rfc822->parseAddressList($filter);
        }
    }

    
    protected function _writeAddress($opts)
    {
        $out = array();

        foreach ($this->_data as $val) {
            $out[] = $val->writeAddress($opts);
        }

        return implode(', ', $out);
    }

    
    public function match($ob)
    {
        if (!($ob instanceof Horde_Mail_Rfc822_List)) {
            $ob = new Horde_Mail_Rfc822_List($ob);
        }

        $a = $this->bare_addresses;
        sort($a);
        $b = $ob->bare_addresses;
        sort($b);

        return ($a == $b);
    }

    
    public function contains($address)
    {
        $ob = new Horde_Mail_Rfc822_Address($address);

        foreach ($this->raw_addresses as $val) {
            if ($val->match($ob)) {
                return true;
            }
        }

        return false;
    }

    
    protected function _normalize($obs)
    {
        $add = array();

        if (!($obs instanceof Horde_Mail_Rfc822_List) &&
            !is_array($obs)) {
            $obs = array($obs);
        }

        foreach ($obs as $val) {
            if (is_string($val)) {
                $rfc822 = new Horde_Mail_Rfc822();
                $val = $rfc822->parseAddressList($val);
            }

            if ($val instanceof Horde_Mail_Rfc822_List) {
                $val->setIteratorFilter(self::BASE_ELEMENTS);
                foreach ($val as $val2) {
                    $add[] = $val2;
                }
            } elseif ($val instanceof Horde_Mail_Rfc822_Object) {
                $add[] = $val;
            }
        }

        return $add;
    }

    

    
    public function offsetExists($offset)
    {
        return !is_null($this[$offset]);
    }

    
    public function offsetGet($offset)
    {
        try {
            $this->seek($offset);
            return $this->current();
        } catch (OutOfBoundsException $e) {
            return null;
        }
    }

    
    public function offsetSet($offset, $value)
    {
        if ($ob = $this[$offset]) {
            if (is_null($this->_ptr['subidx'])) {
                $tmp = $this->_normalize($value);
                if (isset($tmp[0])) {
                    $this->_data[$this->_ptr['idx']] = $tmp[0];
                }
            } else {
                $ob[$offset] = $value;
            }
            $this->_ptr = null;
        }
    }

    
    public function offsetUnset($offset)
    {
        if ($ob = $this[$offset]) {
            if (is_null($this->_ptr['subidx'])) {
                unset($this->_data[$this->_ptr['idx']]);
                $this->_data = array_values($this->_data);
            } else {
                unset($ob->addresses[$this->_ptr['subidx']]);
            }
            $this->_ptr = null;
        }
    }

    

    
    public function count()
    {
        return count($this->addresses);
    }

    

    public function current()
    {
        if (!$this->valid()) {
            return null;
        }

        $ob = $this->_data[$this->_ptr['idx']];

        return is_null($this->_ptr['subidx'])
            ? $ob
            : $ob->addresses[$this->_ptr['subidx']];
    }

    public function key()
    {
        return $this->_ptr['key'];
    }

    public function next()
    {
        if (is_null($this->_ptr['subidx'])) {
            $curr = $this->current();
            if (($curr instanceof Horde_Mail_Rfc822_Group) && count($curr)) {
                $this->_ptr['subidx'] = 0;
            } else {
                ++$this->_ptr['idx'];
            }
            $curr = $this->current();
        } elseif (!($curr = $this->_data[$this->_ptr['idx']]->addresses[++$this->_ptr['subidx']])) {
            $this->_ptr['subidx'] = null;
            ++$this->_ptr['idx'];
            $curr = $this->current();
        }

        if (!is_null($curr)) {
            if (!empty($this->_filter) && $this->_iteratorFilter($curr)) {
                $this->next();
            } else {
                ++$this->_ptr['key'];
            }
        }
    }

    public function rewind()
    {
        $this->_ptr = array(
            'idx' => 0,
            'key' => 0,
            'subidx' => null
        );

        if ($this->valid() &&
            !empty($this->_filter) &&
            $this->_iteratorFilter($this->current())) {
            $this->next();
            $this->_ptr['key'] = 0;
        }
    }

    public function valid()
    {
        return (!empty($this->_ptr) && isset($this->_data[$this->_ptr['idx']]));
    }

    public function seek($position)
    {
        if (!$this->valid() ||
            ($position < $this->_ptr['key'])) {
            $this->rewind();
        }

        for ($i = $this->_ptr['key']; ; ++$i) {
            if ($i == $position) {
                return;
            }

            $this->next();
            if (!$this->valid()) {
                throw new OutOfBoundsException('Position not found.');
            }
        }
    }

    protected function _iteratorFilter($ob)
    {
        if (!empty($this->_filter['mask'])) {
            if (($this->_filter['mask'] & self::HIDE_GROUPS) &&
                ($ob instanceof Horde_Mail_Rfc822_Group)) {
                return true;
            }

            if (($this->_filter['mask'] & self::BASE_ELEMENTS) &&
                !is_null($this->_ptr['subidx'])) {
                return true;
            }
        }

        if (!empty($this->_filter['filter']) &&
            ($ob instanceof Horde_Mail_Rfc822_Address)) {
            foreach ($this->_filter['filter'] as $val) {
                if ($ob->match($val)) {
                    return true;
                }
            }
        }

        return false;
    }

    

    public function serialize()
    {
        return serialize($this->_data);
    }

    public function unserialize($data)
    {
        $this->_data = unserialize($data);
    }

}
