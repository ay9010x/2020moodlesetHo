<?php



class Horde_Imap_Client_Data_Format_Nil extends Horde_Imap_Client_Data_Format
{
    
    public function __construct($data = null)
    {
            }

    
    public function __toString()
    {
        return '';
    }

    
    public function escape()
    {
        return 'NIL';
    }

}
