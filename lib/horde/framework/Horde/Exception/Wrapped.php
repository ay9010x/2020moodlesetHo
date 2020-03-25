<?php

class Horde_Exception_Wrapped extends Horde_Exception
{
    
    public function __construct($message = null, $code = 0)
    {
        $previous = null;
        if (is_object($message) &&
            method_exists($message, 'getMessage')) {
            if (empty($code) &&
                method_exists($message, 'getCode')) {
                $code = (int)$message->getCode();
            }
            if ($message instanceof Exception) {
                $previous = $message;
            }
            if (method_exists($message, 'getUserinfo') &&
                $details = $message->getUserinfo()) {
                $this->details = $details;
            } elseif (!empty($message->details)) {
                $this->details = $message->details;
            }
            $message = (string)$message->getMessage();
        }

        parent::__construct($message, $code, $previous);
    }
}
