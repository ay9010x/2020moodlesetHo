<?php



class Horde_Imap_Client_Exception_SearchCharset
extends Horde_Imap_Client_Exception
{
    
    public $charset;

    
    public function __construct($charset)
    {
        $this->charset = $charset;

        parent::__construct(
            Horde_Imap_Client_Translation::r("Cannot convert search query text to new charset"),
            self::BADCHARSET
        );
    }

}
