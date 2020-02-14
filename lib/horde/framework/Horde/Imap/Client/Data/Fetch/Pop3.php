<?php



class Horde_Imap_Client_Data_Fetch_Pop3 extends Horde_Imap_Client_Data_Fetch
{
    
    public function setUid($uid)
    {
        $this->_data[Horde_Imap_Client::FETCH_UID] = strval($uid);
    }

}
