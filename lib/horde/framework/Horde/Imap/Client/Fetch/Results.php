<?php



class Horde_Imap_Client_Fetch_Results implements ArrayAccess, Countable, IteratorAggregate
{
    
    const SEQUENCE = 1;
    const UID = 2;

    
    protected $_data = array();

    
    protected $_keyType;

    
    protected $_obClass;

    
    public function __construct($ob_class = 'Horde_Imap_Client_Data_Fetch',
                                $key_type = self::UID)
    {
        $this->_obClass = $ob_class;
        $this->_keyType = $key_type;
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'key_type':
            return $this->_keyType;
        }
    }

    
    public function get($key)
    {
        if (!isset($this->_data[$key])) {
            $this->_data[$key] = new $this->_obClass();
        }

        return $this->_data[$key];
    }

    
    public function ids()
    {
        ksort($this->_data);
        return array_keys($this->_data);
    }

    
    public function first()
    {
        return (count($this->_data) === 1)
            ? reset($this->_data)
            : null;
    }

    
    public function clear()
    {
        $this->_data = array();
    }

    

    
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    
    public function offsetGet($offset)
    {
        return isset($this->_data[$offset])
            ? $this->_data[$offset]
            : null;
    }

    
    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
    }

    
    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    

    
    public function count()
    {
        return count($this->_data);
    }

    

    
    public function getIterator()
    {
        ksort($this->_data);
        return new ArrayIterator($this->_data);
    }

}
