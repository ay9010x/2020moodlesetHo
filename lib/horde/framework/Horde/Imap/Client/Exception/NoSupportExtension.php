<?php



class Horde_Imap_Client_Exception_NoSupportExtension
extends Horde_Imap_Client_Exception
{
    
    public $extension;

    
    public function __construct($extension, $msg = null)
    {
        $this->extension = $extension;

        if (is_null($msg)) {
            $msg = sprintf(
                Horde_Imap_Client_Translation::r("The server does not support the %s extension."),
                $extension
            );
        }

        parent::__construct($msg, self::NOT_SUPPORTED);
    }

}
