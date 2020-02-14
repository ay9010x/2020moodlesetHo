<?php



class Horde_Imap_Client_Data_Namespace implements Serializable
{
    
    const NS_PERSONAL = 1;
    const NS_OTHER = 2;
    const NS_SHARED = 3;

    
    protected $_data = array();

    
    public function stripNamespace($mbox)
    {
        $mbox = strval($mbox);
        $name = $this->name;

        return (strlen($name) && (strpos($mbox, $name) === 0))
            ? substr($mbox, strlen($name))
            : $mbox;
    }

    
    public function __get($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }

        switch ($name) {
        case 'base':
            return rtrim($this->name, $this->delimiter);

        case 'delimiter':
        case 'name':
        case 'translation':
            return '';

        case 'hidden':
            return false;

        case 'type':
            return self::NS_PERSONAL;
        }

        return null;
    }

    
    public function __set($name, $value)
    {
        switch ($name) {
        case 'delimiter':
        case 'name':
        case 'translation':
            $this->_data[$name] = strval($value);
            break;

        case 'hidden':
            $this->_data[$name] = (bool)$value;
            break;

        case 'type':
            $this->_data[$name] = intval($value);
            break;
        }
    }

    
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    
    public function __toString()
    {
        return $this->name;
    }

    

    
    public function serialize()
    {
        return json_encode($this->_data);
    }

    
    public function unserialize($data)
    {
        $this->_data = json_decode($data, true);
    }

}
