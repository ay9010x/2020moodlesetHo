<?php



class Horde_Imap_Client_Interaction_Client extends Horde_Imap_Client_Data_Format_List
{
    
    public $tag;

    
    public function __construct($tag = null)
    {
        $this->tag = is_null($tag)
            ? substr(strval(new Horde_Support_Randomid()), 0, 10)
            : strval($tag);

        parent::__construct($this->tag);
    }

    
    public function getCommand()
    {
        return isset($this->_data[1])
            ? $this->_data[1]
            : null;
    }

}
