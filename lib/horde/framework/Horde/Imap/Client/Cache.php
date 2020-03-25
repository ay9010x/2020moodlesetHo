<?php



class Horde_Imap_Client_Cache
{
    
    protected $_baseob;

    
    protected $_backend;

    
    protected $_debug = false;

    
    protected $_params = array();

    
    public function __construct(array $params = array())
    {
        $this->_backend = $params['backend'];
        $this->_baseob = $params['baseob'];

        $this->_backend->setParams(array(
            'hostspec' => $this->_baseob->getParam('hostspec'),
            'port' => $this->_baseob->getParam('port'),
            'username' => $this->_baseob->getParam('username')
        ));

        if (isset($params['debug']) &&
            ($params['debug'] instanceof Horde_Imap_Client_Base_Debug)) {
            $this->_debug = $params['debug'];
            $this->_debug->info(sprintf(
                'CACHE: Using the %s storage driver.',
                get_class($this->_backend)
            ));
        }
    }

    
    public function get($mailbox, array $uids = array(), $fields = array(),
                        $uidvalid = null)
    {
        $mailbox = strval($mailbox);

        if (empty($uids)) {
            $ret = $this->_backend->getCachedUids($mailbox, $uidvalid);
        } else {
            $ret = $this->_backend->get($mailbox, $uids, $fields, $uidvalid);

            if ($this->_debug && !empty($ret)) {
                $this->_debug->info(sprintf(
                    'CACHE: Retrieved messages (%s [%s; %s])',
                    empty($fields) ? 'ALL' : implode(',', $fields),
                    $mailbox,
                    $this->_baseob->getIdsOb(array_keys($ret))->tostring_sort
                ));
            }
        }

        return $ret;
    }

    
    public function set($mailbox, $data, $uidvalid)
    {
        $mailbox = strval($mailbox);

        if (empty($data)) {
            $this->_backend->getMetaData($mailbox, $uidvalid, array('uidvalid'));
        } else {
            $this->_backend->set($mailbox, $data, $uidvalid);

            if ($this->_debug) {
                $this->_debug->info(sprintf(
                    'CACHE: Stored messages [%s; %s]',
                    $mailbox,
                    $this->_baseob->getIdsOb(array_keys($data))->tostring_sort
                ));
            }
        }
    }

    
    public function getMetaData($mailbox, $uidvalid = null,
                                array $entries = array())
    {
        return $this->_backend->getMetaData(strval($mailbox), $uidvalid, $entries);
    }

    
    public function setMetaData($mailbox, $uidvalid, array $data = array())
    {
        unset($data['uidvalid']);

        if (!empty($data)) {
            if (!empty($uidvalid)) {
                $data['uidvalid'] = $uidvalid;
            }
            $mailbox = strval($mailbox);

            $this->_backend->setMetaData($mailbox, $data);

            if ($this->_debug) {
                $this->_debug->info(sprintf(
                    'CACHE: Stored metadata (%s [%s])',
                    implode(',', array_keys($data)),
                    $mailbox
                ));
            }
        }
    }

    
    public function deleteMsgs($mailbox, $uids)
    {
        if (empty($uids)) {
            return;
        }

        $mailbox = strval($mailbox);

        $this->_backend->deleteMsgs($mailbox, $uids);

        if ($this->_debug) {
            $this->_debug->info(sprintf(
                'CACHE: Deleted messages [%s; %s]',
                $mailbox,
                $this->_baseob->getIdsOb($uids)->tostring_sort
            ));
        }
    }

    
    public function deleteMailbox($mbox)
    {
        $mbox = strval($mbox);
        $this->_backend->deleteMailbox($mbox);

        if ($this->_debug) {
            $this->_debug->info(sprintf(
                'CACHE: Deleted mailbox [%s]',
                $mbox
            ));
        }
    }

    
    public function clear($lifetime = null)
    {
        $this->_backend->clear($lifetime);
    }

}
