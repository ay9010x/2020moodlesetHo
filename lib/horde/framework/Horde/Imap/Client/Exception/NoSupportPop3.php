<?php



class Horde_Imap_Client_Exception_NoSupportPop3
extends Horde_Imap_Client_Exception
{
    
    public function __construct($feature)
    {
        parent::__construct(
            sprintf(Horde_Imap_Client_Translation::r("%s not supported on POP3 servers."), $feature),
            self::NOT_SUPPORTED
        );
    }

}
