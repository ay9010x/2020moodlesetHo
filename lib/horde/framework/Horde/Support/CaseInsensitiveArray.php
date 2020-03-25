<?php



class Horde_Support_CaseInsensitiveArray extends ArrayIterator
{
    
    public function offsetGet($offset)
    {
        return (is_null($offset = $this->_getRealOffset($offset)))
            ? null
            : parent::offsetGet($offset);
    }

    
    public function offsetSet($offset, $value)
    {
        if (is_null($roffset = $this->_getRealOffset($offset))) {
            parent::offsetSet($offset, $value);
        } else {
            parent::offsetSet($roffset, $value);
        }
    }

    
    public function offsetExists($offset)
    {
        return (is_null($offset = $this->_getRealOffset($offset)))
            ? false
            : parent::offsetExists($offset);
    }

    
    public function offsetUnset($offset)
    {
        if (!is_null($offset = $this->_getRealOffset($offset))) {
            parent::offsetUnset($offset);
        }
    }

    
    protected function _getRealOffset($offset)
    {
        foreach (array_keys($this->getArrayCopy()) as $key) {
            if (strcasecmp($key, $offset) === 0) {
                return $key;
            }
        }

        return null;
    }

}
