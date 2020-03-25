<?php



class Horde_Imap_Client_Interaction_Pipeline implements Countable, IteratorAggregate
{
    
    public $data = array(
        'modseqs' => array(),
        'modseqs_nouid' => array()
    );

    
    public $fetch;

    
    protected $_commands = array();

    
    protected $_todo = array();

    
    public function __construct(Horde_Imap_Client_Fetch_Results $fetch)
    {
        $this->fetch = $fetch;
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'finished':
            return empty($this->_todo);
        }
    }

    
    public function add(Horde_Imap_Client_Interaction_Command $cmd,
                        $top = false)
    {
        if ($top) {
                        $this->_commands = array($cmd->tag => $cmd) + $this->_commands;
        } else {
            $this->_commands[$cmd->tag] = $cmd;
        }
        $this->_todo[$cmd->tag] = true;
    }

    
    public function complete(Horde_Imap_Client_Interaction_Server_Tagged $resp)
    {
        $cmd = $this->_commands[$resp->tag];
        $cmd->response = $resp;
        unset($this->_todo[$resp->tag]);

        return $cmd;
    }

    
    public function getCmd($tag)
    {
        return isset($this->_commands[$tag])
            ? $this->_commands[$tag]
            : null;
    }

    

    
    public function count()
    {
        return count($this->_commands);
    }

    

    
    public function getIterator()
    {
        return new ArrayIterator($this->_commands);
    }

}
