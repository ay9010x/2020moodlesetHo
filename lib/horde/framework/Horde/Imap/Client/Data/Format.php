<?php



class Horde_Imap_Client_Data_Format
{
    
    protected $_data;

    
    public function __construct($data)
    {
        $this->_data = is_resource($data)
            ? stream_get_contents($data, -1, 0)
            : $data;
    }

    
    public function __toString()
    {
        return strval($this->_data);
    }

    
    public function getData()
    {
        return $this->_data;
    }

    
    public function escape()
    {
        return strval($this);
    }

    
    public function verify()
    {
    }

}
