<?php



class Horde_Imap_Client_Interaction_Command_Continuation
{
    
    public $optional = false;

    
    protected $_closure;

    
    public function __construct($closure)
    {
        $this->_closure = $closure;
    }

    
    public function getCommands(
        Horde_Imap_Client_Interaction_Server_Continuation $ob
    )
    {
        $closure = $this->_closure;
        return $closure($ob);
    }

}
