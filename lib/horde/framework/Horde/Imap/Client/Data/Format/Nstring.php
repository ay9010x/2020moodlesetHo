<?php



class Horde_Imap_Client_Data_Format_Nstring extends Horde_Imap_Client_Data_Format_String
{
    
    public function __construct($data = null)
    {
        
        if (is_null($data)) {
            $this->_data = null;
        } else {
            parent::__construct($data);
        }
    }

    
    public function __toString()
    {
        return is_null($this->_data)
            ? ''
            : parent::__toString();
    }

    
    public function escape()
    {
        return is_null($this->_data)
            ? 'NIL'
            : parent::escape();
    }

    public function escapeStream()
    {
        if (is_null($this->_data)) {
            $stream = new Horde_Stream_Temp();
            $stream->add('NIL', true);
            return $stream->stream;
        }

        return parent::escapeStream();
    }

    
    public function quoted()
    {
        return is_null($this->_data)
            ? false
            : parent::quoted();
    }

    
    public function length()
    {
        return is_null($this->_data)
            ? 0
            : parent::length();
    }

    
    public function getStream()
    {
        return is_null($this->_data)
            ? new Horde_Stream_Temp()
            : parent::getStream();
    }

}
