<?php



class Horde_Imap_Client_Data_Format_ListMailbox extends Horde_Imap_Client_Data_Format_Mailbox
{
    
    protected function _filterParams()
    {
        $ob = parent::_filterParams();

        
        $ob->no_quote_list = true;

        return $ob;
    }

}
