<?php



class Horde_Imap_Client_Data_AclCommon
{
    
    const RFC_2086 = 1;
    const RFC_4314 = 2;

    
    protected $_virtual = array(
        Horde_Imap_Client::ACL_CREATE => array(
            Horde_Imap_Client::ACL_CREATEMBOX,
            Horde_Imap_Client::ACL_DELETEMBOX
        ),
        Horde_Imap_Client::ACL_DELETE => array(
            Horde_Imap_Client::ACL_DELETEMSGS,
                                                Horde_Imap_Client::ACL_DELETEMBOX,
            Horde_Imap_Client::ACL_EXPUNGE
        )
    );

    
    public function getString($type = self::RFC_4314)
    {
        $acl = strval($this);

        if ($type == self::RFC_2086) {
            foreach ($this->_virtual as $key => $val) {
                $acl = str_replace($val, '', $acl, $count);
                if ($count) {
                    $acl .= $key;
                }
            }
        }

        return $acl;
    }

}
