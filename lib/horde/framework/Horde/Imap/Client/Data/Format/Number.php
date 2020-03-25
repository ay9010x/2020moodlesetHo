<?php



class Horde_Imap_Client_Data_Format_Number extends Horde_Imap_Client_Data_Format
{
    
    public function __toString()
    {
        return strval(intval($this->_data));
    }

    
    public function verify()
    {
        if (!is_numeric($this->_data)) {
            throw new Horde_Imap_Client_Data_Format_Exception('Illegal character in IMAP number.');
        }
    }

}
