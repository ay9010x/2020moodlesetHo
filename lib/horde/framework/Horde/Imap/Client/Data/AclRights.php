<?php



class Horde_Imap_Client_Data_AclRights extends Horde_Imap_Client_Data_AclCommon implements ArrayAccess, Iterator, Serializable
{
    
    protected $_optional = array();

    
    protected $_required = array();

    
    public function __construct(array $required = array(),
                                array $optional = array())
    {
        $this->_required = $required;

        foreach ($optional as $val) {
            foreach (str_split($val) as $right) {
                $this->_optional[$right] = $val;
            }
        }

        $this->_normalize();
    }

    
    public function __toString()
    {
        return implode('', array_keys(array_flip(array_merge(array_values($this->_required), array_keys($this->_optional)))));
    }

    
    protected function _normalize()
    {
        
        foreach ($this->_virtual as $key => $val) {
            if (isset($this->_optional[$key])) {
                unset($this->_optional[$key]);
                foreach ($val as $val2) {
                    $this->_optional[$val2] = implode('', $val);
                }
            } elseif (($pos = array_search($key, $this->_required)) !== false) {
                unset($this->_required[$pos]);
                $this->_required = array_unique(array_merge($this->_required, $val));
            }
        }
    }

    

    
    public function offsetExists($offset)
    {
        return (bool)$this[$offset];
    }

    
    public function offsetGet($offset)
    {
        if (isset($this->_optional[$offset])) {
            return $this->_optional[$offset];
        }

        $pos = array_search($offset, $this->_required);

        return ($pos === false)
            ? null
            : $this->_required[$pos];
    }

    
    public function offsetSet($offset, $value)
    {
        $this->_optional[$offset] = $value;
        $this->_normalize();
    }

    
    public function offsetUnset($offset)
    {
        unset($this->_optional[$offset]);
        $this->_required = array_values(array_diff($this->_required, array($offset)));

        if (isset($this->_virtual[$offset])) {
            foreach ($this->_virtual[$offset] as $val) {
                unset($this[$val]);
            }
        }
    }

    

    
    public function current()
    {
        $val = current($this->_required);
        return is_null($val)
            ? current($this->_optional)
            : $val;
    }

    
    public function key()
    {
        $key = key($this->_required);
        return is_null($key)
            ? key($this->_optional)
            : $key;
    }

    
    public function next()
    {
        if (key($this->_required) === null) {
            next($this->_optional);
        } else {
            next($this->_required);
        }
    }

    
    public function rewind()
    {
        reset($this->_required);
        reset($this->_optional);
    }

    
    public function valid()
    {
        return ((key($this->_required) !== null) ||
                (key($this->_optional) !== null));

    }

    

    
    public function serialize()
    {
        return json_encode(array(
            $this->_required,
            $this->_optional
        ));
    }

    
    public function unserialize($data)
    {
        list($this->_required, $this->_optional) = json_decode($data);
    }

}
