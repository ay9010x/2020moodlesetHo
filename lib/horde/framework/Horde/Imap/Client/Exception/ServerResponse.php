<?php



class Horde_Imap_Client_Exception_ServerResponse extends Horde_Imap_Client_Exception
{
    
    protected $_pipeline;

    
    protected $_server;

    
    public function __construct(
        $msg = null,
        $code = 0,
        Horde_Imap_Client_Interaction_Server $server,
        Horde_Imap_Client_Interaction_Pipeline $pipeline
    )
    {
        $this->details = strval($server->token);

        $this->_pipeline = $pipeline;
        $this->_server = $server;

        parent::__construct($msg, $code);
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'command':
            return ($this->_server instanceof Horde_Imap_Client_Interaction_Server_Tagged)
                ? $this->_pipeline->getCmd($this->_server->tag)->getCommand()
                : null;

        case 'resp_data':
            return $this->_pipeline->data;

        case 'status':
            return $this->_server->status;

        default:
            return parent::__get($name);
        }
    }

}
