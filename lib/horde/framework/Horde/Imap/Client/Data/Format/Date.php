<?php



class Horde_Imap_Client_Data_Format_Date extends Horde_Imap_Client_Data_Format
{
    
    public function __construct($data)
    {
        if (!($data instanceof DateTime)) {
            $data = new Horde_Imap_Client_DateTime($data);
        }

        parent::__construct($data);
    }

    
    public function __toString()
    {
        return $this->_data->format('j-M-Y');
    }

}
