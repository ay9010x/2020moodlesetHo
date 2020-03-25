<?php



class Horde_Imap_Client_Namespace_List
implements ArrayAccess, Countable, IteratorAggregate
{
    
    protected $_ns = array();

    
    public function __construct($ns = array())
    {
        foreach ($ns as $val) {
            $this->_ns[strval($val)] = $val;
        }
    }

    
    public function getNamespace($mbox, $personal = false)
    {
        $mbox = strval($mbox);

        if ($ns = $this[$mbox]) {
            return $ns;
        }

        foreach ($this->_ns as $val) {
            $mbox = $mbox . $val->delimiter;
            if (strlen($val->name) && (strpos($mbox, $val->name) === 0)) {
                return $val;
            }
        }

        return (($ns = $this['']) && (!$personal || ($ns->type === $ns::NS_PERSONAL)))
            ? $ns
            : null;
    }

    

    
    public function offsetExists($offset)
    {
        return isset($this->_ns[strval($offset)]);
    }

    
    public function offsetGet($offset)
    {
        $offset = strval($offset);

        return isset($this->_ns[$offset])
            ? $this->_ns[$offset]
            : null;
    }

    
    public function offsetSet($offset, $value)
    {
        if ($value instanceof Horde_Imap_Client_Data_Namespace) {
            $this->_ns[strval($value)] = $value;
        }
    }

    
    public function offsetUnset($offset)
    {
        unset($this->_ns[strval($offset)]);
    }

    

    
    public function count()
    {
        return count($this->_ns);
    }

    

    
    public function getIterator()
    {
        return new ArrayIterator($this->_ns);
    }

}
