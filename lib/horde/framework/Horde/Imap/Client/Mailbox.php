<?php



class Horde_Imap_Client_Mailbox implements Serializable
{
    
    protected $_utf7imap;

    
    protected $_utf8;

    
    static public function get($mbox, $utf7imap = false)
    {
        return ($mbox instanceof Horde_Imap_Client_Mailbox)
            ? $mbox
            : new Horde_Imap_Client_Mailbox($mbox, $utf7imap);
    }

    
    public function __construct($mbox, $utf7imap = false)
    {
        if (strcasecmp($mbox, 'INBOX') === 0) {
            $mbox = 'INBOX';
        }

        if ($utf7imap) {
            $this->_utf7imap = $mbox;
        } else {
            $this->_utf8 = $mbox;
        }
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'list_escape':
            return preg_replace("/\*+/", '%', $this->utf8);

        case 'utf7imap':
            if (!isset($this->_utf7imap)) {
                $n = Horde_Imap_Client_Utf7imap::Utf8ToUtf7Imap($this->_utf8);
                $this->_utf7imap = ($n == $this->_utf8)
                    ? true
                    : $n;
            }

            return ($this->_utf7imap === true)
                ? $this->_utf8
                : $this->_utf7imap;

        case 'utf8':
            if (!isset($this->_utf8)) {
                $this->_utf8 = Horde_Imap_Client_Utf7imap::Utf7ImapToUtf8($this->_utf7imap);
                if ($this->_utf8 == $this->_utf7imap) {
                    $this->_utf7imap = true;
                }
            }
            return (string)$this->_utf8;
        }
    }

    
    public function __toString()
    {
        return $this->utf8;
    }

    
    public function equals($mbox)
    {
        return ($this->utf8 == $mbox);
    }

    

    
    public function serialize()
    {
        return json_encode(array($this->_utf7imap, $this->_utf8));
    }

    
    public function unserialize($data)
    {
        list($this->_utf7imap, $this->_utf8) = json_decode($data, true);
    }

}
