<?php



class Horde_Support_ObjectStub
{
    
    protected $_data;

    
    public function __construct($data)
    {
        $this->_data = $data;
    }

    
    public function __get($name)
    {
        return isset($this->_data->$name)
            ? $this->_data->$name
            : null;
    }

    
    public function __set($name, $value)
    {
        $this->_data->$name = $value;
    }

    
    public function __isset($name)
    {
        return isset($this->_data->$name);
    }

    
    public function __unset($name)
    {
        unset($this->_data->$name);
    }

}
