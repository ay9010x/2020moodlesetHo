<?php



class Horde_Imap_Client_Data_Acl extends Horde_Imap_Client_Data_AclCommon implements ArrayAccess, IteratorAggregate, Serializable
{
    
    protected $_rights;

    
    public function __construct($rights = '')
    {
        $this->_rights = str_split($rights);
        $this->_normalize();
    }

    
    public function __toString()
    {
        return implode('', $this->_rights);
    }

    
    public function diff($rights)
    {
        $rlist = array_diff(str_split($rights), array_keys($this->_virtual));

        return array(
            'added' => implode('', array_diff($rlist, $this->_rights)),
            'removed' => implode('', array_diff($this->_rights, $rlist))
        );
    }

    
    protected function _normalize()
    {
        
        foreach ($this->_virtual as $key => $val) {
            if ($this[$key]) {
                unset($this[$key]);
                if (!$this[reset($val)]) {
                    $this->_rights = array_unique(array_merge($this->_rights, $val));
                }
            }
        }
    }

    

    
    public function offsetExists($offset)
    {
        return $this[$offset];
    }

    
    public function offsetGet($offset)
    {
        return in_array($offset, $this->_rights);
    }

    
    public function offsetSet($offset, $value)
    {
        if ($value) {
            if (!$this[$offset]) {
                $this->_rights[] = $offset;
                $this->_normalize();
            }
        } elseif ($this[$offset]) {
            if (isset($this->_virtual[$offset])) {
                foreach ($this->_virtual[$offset] as $val) {
                    unset($this[$val]);
                }
            }
            unset($this[$offset]);
        }
    }

    
    public function offsetUnset($offset)
    {
        $this->_rights = array_values(array_diff($this->_rights, array($offset)));
    }

    

    public function getIterator()
    {
        return new ArrayIterator($this->_rights);
    }

    

    
    public function serialize()
    {
        return json_encode($this->_rights);
    }

    
    public function unserialize($data)
    {
        $this->_rights = json_decode($data);
    }

}
