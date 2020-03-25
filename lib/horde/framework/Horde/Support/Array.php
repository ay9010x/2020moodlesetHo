<?php

class Horde_Support_Array implements ArrayAccess, Countable, IteratorAggregate
{
    
    protected $_array = array();

    
    public function __construct($vars = array())
    {
        if (is_array($vars)) {
            $this->update($vars);
        }
    }

    
    public function get($key, $default = null)
    {
        return isset($this->_array[$key]) ? $this->_array[$key] : $default;
    }

    
    public function getOrSet($offset, $default = null)
    {
        $value = $this->offsetGet($offset);
        if (is_null($value)) {
            $this->offsetSet($offset, $value = $default);
        }
        return $value;
    }

    
    public function pop($offset, $default = null)
    {
        $value = $this->offsetGet($offset);
        $this->offsetUnset($offset);
        return isset($value) ? $value : $default;
    }

    
    public function update($array)
    {
        if (!is_array($array) && !$array instanceof Traversable) {
            throw new InvalidArgumentException('expected array or traversable, got ' . gettype($array));
        }

        foreach ($array as $key => $val) {
            $this->offsetSet($key, $val);
        }
    }

    
    public function getKeys()
    {
        return array_keys($this->_array);
    }

    
    public function getValues()
    {
        return array_values($this->_array);
    }

    
    public function clear()
    {
        $this->_array = array();
    }

    
    public function __get($key)
    {
        return $this->get($key);
    }

    
    public function __set($key, $value)
    {
        $this->_array[$key] = $value;
    }

    
    public function __isset($key)
    {
        return array_key_exists($key, $this->_array);
    }

    
    public function __unset($key)
    {
        unset($this->_array[$key]);
    }

    
    public function count()
    {
        return count($this->_array);
    }

    
    public function getIterator()
    {
        return new ArrayIterator($this->_array);
    }

    
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    
    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    
    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }

}
