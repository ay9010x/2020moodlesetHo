<?php



class Horde_Support_Stub implements ArrayAccess, Countable, IteratorAggregate
{
    
    public function __toString()
    {
        return '';
    }

    
    public function __set($key, $val)
    {
    }

    
    public function __get($key)
    {
        return null;
    }

    
    public function __isset($key)
    {
        return false;
    }

    
    public function __unset($key)
    {
    }

    
    public function __call($method, $args)
    {
    }

    
    public static function __callStatic($method, $args)
    {
    }

    

     
     public function offsetGet($offset)
     {
         return null;
     }

    
    public function offsetSet($offset, $value)
    {
    }

    
    public function offsetExists($offset)
    {
        return false;
    }

    
    public function offsetUnset($offset)
    {
    }

    

    
    public function count()
    {
        return 0;
    }

    

    
    public function getIterator()
    {
        return new ArrayIterator(array());
    }

}
