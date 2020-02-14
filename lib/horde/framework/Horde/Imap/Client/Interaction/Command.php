<?php



class Horde_Imap_Client_Interaction_Command
extends Horde_Imap_Client_Data_Format_List
{
    
    public $debug = null;

    
    public $literalplus = true;

    
    public $literal8 = false;

    
    public $response;

    
    public $tag;

    
    protected $_timer;

    
    public function __construct($cmd, $tag = null)
    {
        $this->tag = is_null($tag)
            ? substr(new Horde_Support_Randomid(), 0, 10)
            : strval($tag);

        parent::__construct($this->tag);

        $this->add($cmd);
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'continuation':
            foreach ($this as $val) {
                if (($val instanceof Horde_Imap_Client_Interaction_Command_Continuation) ||
                    (($val instanceof Horde_Imap_Client_Data_Format_String) &&
                     $val->literal())) {

                    return true;
                }
            }
            return false;
        }
    }

    
    public function getCommand()
    {
        return $this->_data[1];
    }

    
    public function startTimer()
    {
        $this->_timer = new Horde_Support_Timer();
        $this->_timer->push();
    }

    
    public function getTimer()
    {
        return $this->_timer
            ? round($this->_timer->pop(), 4)
            : null;
    }

}
