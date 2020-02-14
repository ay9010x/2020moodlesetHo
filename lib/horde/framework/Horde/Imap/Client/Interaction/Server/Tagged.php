<?php



class Horde_Imap_Client_Interaction_Server_Tagged
extends Horde_Imap_Client_Interaction_Server
{
    
    public $tag;

    
    public function __construct(Horde_Imap_Client_Tokenize $token, $tag)
    {
        $this->tag = $tag;

        parent::__construct($token);

        if (is_null($this->status)) {
            throw new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Bad tagged response.")
            );
        }
    }

}
