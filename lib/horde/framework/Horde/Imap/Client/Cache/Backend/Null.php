<?php



class Horde_Imap_Client_Cache_Backend_Null extends Horde_Imap_Client_Cache_Backend
{
    
    public function get($mailbox, $uids, $fields, $uidvalid)
    {
        return array();
    }

    
    public function getCachedUids($mailbox, $uidvalid)
    {
        return array();
    }

    
    public function set($mailbox, $data, $uidvalid)
    {
    }

    
    public function getMetaData($mailbox, $uidvalid, $entries)
    {
        return array(
            'uidvalid' => 0
        );
    }

    
    public function setMetaData($mailbox, $data)
    {
    }

    
    public function deleteMsgs($mailbox, $uids)
    {
    }

    
    public function deleteMailbox($mailbox)
    {
    }

    
    public function clear($lifetime)
    {
    }

}
