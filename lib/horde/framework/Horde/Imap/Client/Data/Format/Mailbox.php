<?php



class Horde_Imap_Client_Data_Format_Mailbox extends Horde_Imap_Client_Data_Format_Astring
{
    
    protected $_mailbox;

    
    public function __construct($data)
    {
        $this->_mailbox = Horde_Imap_Client_Mailbox::get($data);

        parent::__construct($this->_mailbox->utf7imap);
    }

    
    public function __toString()
    {
        return strval($this->_mailbox);
    }

    
    public function getData()
    {
        return $this->_mailbox;
    }

    
    public function binary()
    {
        if (parent::binary()) {
                        
                                    
                        $this->_mailbox = Horde_Imap_Client_Mailbox::get('');
        }

        return false;
    }

    
    public function length()
    {
        return strlen($this->_mailbox->utf7imap);
    }

    
    public function getStream()
    {
        $stream = new Horde_Stream_Temp();
        $stream->add($this->_mailbox->utf7imap);
        return $stream;
    }

}
