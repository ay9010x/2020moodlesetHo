<?php



class Horde_Imap_Client_Data_Sync
{
    
    static public $map = array(
        'H' => 'highestmodseq',
        'M' => 'messages',
        'U' => 'uidnext',
        'V' => 'uidvalidity'
    );

    
    public $flags = null;

    
    public $highestmodseq = null;

    
    public $mailbox;

    
    public $messages = null;

    
    public $newmsgs = null;

    
    public $uidnext = null;

    
    public $uidvalidity = null;

    
    public $vanished = null;

    
    protected $_flagsuids;

    
    protected $_newmsgsuids;

    
    protected $_vanisheduids;

    
    public function __construct(Horde_Imap_Client_Base $base_ob, $mailbox,
                                $sync, $curr, $criteria, $ids)
    {
        foreach (self::$map as $key => $val) {
            if (isset($sync[$key])) {
                $this->$val = $sync[$key];
            }
        }

        
        if (!$this->uidvalidity || ($curr['V'] != $this->uidvalidity)) {
            throw new Horde_Imap_Client_Exception_Sync('UIDs in cached mailbox have changed.', Horde_Imap_Client_Exception_Sync::UIDVALIDITY_CHANGED);
        }

        $this->mailbox = $mailbox;

        
        if (!$criteria) {
            return;
        }

        $sync_all = ($criteria & Horde_Imap_Client::SYNC_ALL);

        
        if ($sync_all ||
            ($criteria & Horde_Imap_Client::SYNC_NEWMSGS) ||
            ($criteria & Horde_Imap_Client::SYNC_NEWMSGSUIDS)) {
            $this->newmsgs = empty($this->uidnext)
                ? !empty($curr['U'])
                : (!empty($curr['U']) && ($curr['U'] > $this->uidnext));

            if ($this->newmsgs &&
                ($sync_all ||
                 ($criteria & Horde_Imap_Client::SYNC_NEWMSGSUIDS))) {
                $new_ids = empty($this->uidnext)
                    ? Horde_Imap_Client_Ids::ALL
                    : ($this->uidnext . ':' . $curr['U']);

                $squery = new Horde_Imap_Client_Search_Query();
                $squery->ids($new_ids);
                $sres = $base_ob->search($mailbox, $squery);

                $this->_newmsgsuids = $sres['match'];
            }
        }

        
        if ($this->highestmodseq &&
            ($sync_all ||
             ($criteria & Horde_Imap_Client::SYNC_FLAGS) ||
             ($criteria & Horde_Imap_Client::SYNC_FLAGSUIDS) ||
             ($criteria & Horde_Imap_Client::SYNC_VANISHED) ||
             ($criteria & Horde_Imap_Client::SYNC_VANISHEDUIDS))) {
            $status_sync = $base_ob->status($mailbox, Horde_Imap_Client::STATUS_SYNCMODSEQ | Horde_Imap_Client::STATUS_SYNCFLAGUIDS | Horde_Imap_Client::STATUS_SYNCVANISHED);

            if (!is_null($ids)) {
                $ids = $base_ob->resolveIds($mailbox, $ids);
            }
        }

        
        if ($sync_all || ($criteria & Horde_Imap_Client::SYNC_FLAGS)) {
            $this->flags = $this->highestmodseq
                ? ($this->highestmodseq != $curr['H'])
                : true;
        }

        if ($sync_all || ($criteria & Horde_Imap_Client::SYNC_FLAGSUIDS)) {
            if ($this->highestmodseq) {
                if ($this->highestmodseq == $status_sync['syncmodseq']) {
                    $this->_flagsuids = is_null($ids)
                        ? $status_sync['syncflaguids']
                        : $base_ob->getIdsOb(array_intersect($ids->ids, $status_sync['syncflaguids']->ids));
                } else {
                    $squery = new Horde_Imap_Client_Search_Query();
                    $squery->modseq($this->highestmodseq + 1);
                    $sres = $base_ob->search($mailbox, $squery, array(
                        'ids' => $ids
                    ));
                    $this->_flagsuids = $sres['match'];
                }
            } else {
                
                $this->_flagsuids = $base_ob->resolveIds($mailbox, is_null($ids) ? $base_ob->getIdsOb(Horde_Imap_Client_Ids::ALL) : $ids);
            }
        }

        
        if ($sync_all ||
            ($criteria & Horde_Imap_Client::SYNC_VANISHED) ||
            ($criteria & Horde_Imap_Client::SYNC_VANISHEDUIDS)) {
            if ($this->highestmodseq &&
                ($this->highestmodseq == $status_sync['syncmodseq'])) {
                $vanished = is_null($ids)
                    ? $status_sync['syncvanisheduids']
                    : $base_ob->getIdsOb(array_intersect($ids->ids, $status_sync['syncvanisheduids']->ids));
            } else {
                $vanished = $base_ob->vanished($mailbox, $this->highestmodseq ? $this->highestmodseq : 1, array(
                    'ids' => $ids
                ));
            }

            $this->vanished = (bool)count($vanished);
            $this->_vanisheduids = $vanished;
        }
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'flagsuids':
        case 'newmsgsuids':
        case 'vanisheduids':
            $varname = '_' . $name;
            return empty($this->$varname)
                ? new Horde_Imap_Client_Ids()
                : $this->$varname;
        }
    }

}
