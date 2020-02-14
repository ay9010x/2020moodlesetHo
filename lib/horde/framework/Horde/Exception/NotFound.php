<?php

class Horde_Exception_NotFound extends Horde_Exception
{
    
    public function __construct($message = null, $code = null)
    {
        if (is_null($message)) {
            $message = Horde_Exception_Translation::t("Not Found");
        }
        parent::__construct($message, $code);
    }
}