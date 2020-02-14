<?php



class Horde_Imap_Client_Data_Format_DateTime extends Horde_Imap_Client_Data_Format_Date
{
    
    public function __toString()
    {
        return $this->_data->format('j-M-Y H:i:s O');
    }

    
    public function escape()
    {
        return '"' . strval($this) . '"';
    }

}
