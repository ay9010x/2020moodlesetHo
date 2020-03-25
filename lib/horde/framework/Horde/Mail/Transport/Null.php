<?php



class Horde_Mail_Transport_Null extends Horde_Mail_Transport
{
    
    public function send($recipients, array $headers, $body)
    {
    }

}
