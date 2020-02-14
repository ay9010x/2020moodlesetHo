<?php



class Horde_Imap_Client_Base_Mailbox
{
    
    public $map;

    
    public $open;

    
    public $sync;

    
    protected $_status = array();

    
    public function __construct()
    {
        $this->reset();
    }

    
    public function getStatus($entry)
    {
        if (isset($this->_status[$entry])) {
            return $this->_status[$entry];
        }

        switch ($entry) {
        case Horde_Imap_Client::STATUS_FIRSTUNSEEN:
            
            return empty($this->_status[Horde_Imap_Client::STATUS_MESSAGES])
                ? false
                : null;

        case Horde_Imap_Client::STATUS_RECENT_TOTAL:
        case Horde_Imap_Client::STATUS_SYNCMODSEQ:
            return 0;

        case Horde_Imap_Client::STATUS_SYNCFLAGUIDS:
        case Horde_Imap_Client::STATUS_SYNCVANISHED:
            return array();

        case Horde_Imap_Client::STATUS_PERMFLAGS:
            
            $flags = isset($this->_status[Horde_Imap_Client::STATUS_FLAGS])
                ? $this->_status[Horde_Imap_Client::STATUS_FLAGS]
                : array();
            $flags[] = "\\*";
            return $flags;

        case Horde_Imap_Client::STATUS_UIDNOTSTICKY:
            
            return false;

        case Horde_Imap_Client::STATUS_UNSEEN:
            
            return empty($this->_status[Horde_Imap_Client::STATUS_MESSAGES])
                ? 0
                : null;

        default:
            return null;
        }
    }

    
    public function setStatus($entry, $value)
    {
        switch ($entry) {
        case Horde_Imap_Client::STATUS_FIRSTUNSEEN:
        case Horde_Imap_Client::STATUS_HIGHESTMODSEQ:
        case Horde_Imap_Client::STATUS_MESSAGES:
        case Horde_Imap_Client::STATUS_UNSEEN:
        case Horde_Imap_Client::STATUS_UIDNEXT:
        case Horde_Imap_Client::STATUS_UIDVALIDITY:
            $value = intval($value);
            break;

        case Horde_Imap_Client::STATUS_RECENT:
            
            $this->_status[Horde_Imap_Client::STATUS_RECENT_TOTAL] = isset($this->_status[Horde_Imap_Client::STATUS_RECENT_TOTAL])
                ? ($this->_status[Horde_Imap_Client::STATUS_RECENT_TOTAL] + $value)
                : intval($value);
            break;

        case Horde_Imap_Client::STATUS_SYNCMODSEQ:
            
            if (isset($this->_status[$entry])) {
                return;
            }
            $value = intval($value);
            break;

        case Horde_Imap_Client::STATUS_SYNCFLAGUIDS:
        case Horde_Imap_Client::STATUS_SYNCVANISHED:
            if (!isset($this->_status[$entry])) {
                $this->_status[$entry] = array();
            }
            $this->_status[$entry] = array_merge($this->_status[$entry], $value);
            return;
        }

        $this->_status[$entry] = $value;
    }

    
    public function reset()
    {
        $keep = array(
            Horde_Imap_Client::STATUS_SYNCFLAGUIDS,
            Horde_Imap_Client::STATUS_SYNCMODSEQ,
            Horde_Imap_Client::STATUS_SYNCVANISHED
        );

        foreach (array_diff(array_keys($this->_status), $keep) as $val) {
            unset($this->_status[$val]);
        }

        $this->map = new Horde_Imap_Client_Ids_Map();
        $this->open = $this->sync = false;
    }

}
