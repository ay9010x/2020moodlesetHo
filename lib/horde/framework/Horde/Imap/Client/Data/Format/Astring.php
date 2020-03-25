<?php



class Horde_Imap_Client_Data_Format_Astring extends Horde_Imap_Client_Data_Format_String
{
    
    public function quoted()
    {
        return $this->_filter->quoted || !$this->_data->length();
    }

}
