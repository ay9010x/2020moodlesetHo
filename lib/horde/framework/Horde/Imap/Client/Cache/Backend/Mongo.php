<?php



class Horde_Imap_Client_Cache_Backend_Mongo extends Horde_Imap_Client_Cache_Backend implements Horde_Mongo_Collection_Index
{
    
    const BASE = 'horde_imap_client_cache_data';
    const MD = 'horde_imap_client_cache_metadata';
    const MSG = 'horde_imap_client_cache_message';

    
    const BASE_HOSTSPEC = 'hostspec';
    const BASE_MAILBOX = 'mailbox';
    const BASE_MODIFIED = 'modified';
    const BASE_PORT = 'port';
    const BASE_UID = 'data';
    const BASE_USERNAME = 'username';

    
    const MD_DATA = 'data';
    const MD_FIELD = 'field';
    const MD_UID = 'uid';

    
    const MSG_DATA = 'data';
    const MSG_MSGUID = 'msguid';
    const MSG_UID = 'uid';

    
    protected $_db;

    
    protected $_indices = array(
        self::BASE => array(
            'base_index_1' => array(
                self::BASE_HOSTSPEC => 1,
                self::BASE_MAILBOX => 1,
                self::BASE_PORT => 1,
                self::BASE_USERNAME => 1,
            )
        ),
        self::MSG => array(
            'msg_index_1' => array(
                self::MSG_MSGUID => 1,
                self::MSG_UID => 1
            )
        )
    );

    
    public function __construct(array $params = array())
    {
        if (!isset($params['mongo_db'])) {
            throw new InvalidArgumentException('Missing mongo_db parameter.');
        }

        parent::__construct($params);
    }

    
    protected function _initOb()
    {
        $this->_db = $this->_params['mongo_db']->selectDB(null);
    }

    
    public function get($mailbox, $uids, $fields, $uidvalid)
    {
        $this->getMetaData($mailbox, $uidvalid, array('uidvalid'));

        if (!($uid = $this->_getUid($mailbox))) {
            return array();
        }

        $out = array();
        $query = array(
            self::MSG_MSGUID => array('$in' => array_map('strval', $uids)),
            self::MSG_UID => $uid
        );

        try {
            $cursor = $this->_db->selectCollection(self::MSG)->find(
                $query,
                array(
                    self::MSG_DATA => true,
                    self::MSG_MSGUID => true
                )
            );
            foreach ($cursor as $val) {
                $out[$val[self::MSG_MSGUID]] = $this->_value($val[self::MSG_DATA]);
            }
        } catch (MongoException $e) {}

        return $out;
    }

    
    public function getCachedUids($mailbox, $uidvalid)
    {
        $this->getMetaData($mailbox, $uidvalid, array('uidvalid'));

        if (!($uid = $this->_getUid($mailbox))) {
            return array();
        }

        $out = array();
        $query = array(
            self::MSG_UID => $uid
        );

        try {
            $cursor = $this->_db->selectCollection(self::MSG)->find(
                $query,
                array(
                    self::MSG_MSGUID => true
                )
            );
            foreach ($cursor as $val) {
                $out[] = $val[self::MSG_MSGUID];
            }
        } catch (MongoException $e) {}

        return $out;
    }

    
    public function set($mailbox, $data, $uidvalid)
    {
        if ($uid = $this->_getUid($mailbox)) {
            $res = $this->get($mailbox, array_keys($data), array(), $uidvalid);
        } else {
            $res = array();
            $uid = $this->_createUid($mailbox);
        }

        $coll = $this->_db->selectCollection(self::MSG);

        foreach ($data as $key => $val) {
            try {
                if (isset($res[$key])) {
                    $coll->update(array(
                        self::MSG_MSGUID => strval($key),
                        self::MSG_UID => $uid
                    ), array(
                        self::MSG_DATA => $this->_value(array_merge($res[$key], $val)),
                        self::MSG_MSGUID => strval($key),
                        self::MSG_UID => $uid
                    ));
                } else {
                    $coll->insert(array(
                        self::MSG_DATA => $this->_value($val),
                        self::MSG_MSGUID => strval($key),
                        self::MSG_UID => $uid
                    ));
                }
            } catch (MongoException $e) {}
        }

        
        try {
            $this->_db->selectCollection(self::BASE)->update(
                array(self::BASE_UID => $uid),
                array(self::BASE_MODIFIED => time())
            );
        } catch (MongoException $e) {}

        
        $this->setMetaData($mailbox, array('uidvalid' => $uidvalid));
    }

    
    public function getMetaData($mailbox, $uidvalid, $entries)
    {
        if (!($uid = $this->_getUid($mailbox))) {
            return array();
        }

        $out = array();
        $query = array(
            self::MD_UID => $uid
        );

        if (!empty($entries)) {
            $entries[] = 'uidvalid';
            $query[self::MD_FIELD] = array(
                '$in' => array_unique($entries)
            );
        }

        try {
            $cursor = $this->_db->selectCollection(self::MD)->find(
                $query,
                array(
                    self::MD_DATA => true,
                    self::MD_FIELD => true
                )
            );
            foreach ($cursor as $val) {
                $out[$val[self::MD_FIELD]] = $this->_value($val[self::MD_DATA]);
            }

            if (is_null($uidvalid) ||
                !isset($out['uidvalid']) ||
                ($out['uidvalid'] == $uidvalid)) {
                return $out;
            }

            $this->deleteMailbox($mailbox);
        } catch (MongoException $e) {}

        return array();
    }

    
    public function setMetaData($mailbox, $data)
    {
        if (!($uid = $this->_getUid($mailbox))) {
            $uid = $this->_createUid($mailbox);
        }

        $coll = $this->_db->selectCollection(self::MD);

        foreach ($data as $key => $val) {
            try {
                $coll->update(
                    array(
                        self::MD_FIELD => $key,
                        self::MD_UID => $uid
                    ),
                    array(
                        self::MD_DATA => $this->_value($val),
                        self::MD_FIELD => $key,
                        self::MD_UID => $uid
                    ),
                    array('upsert' => true)
                );
            } catch (MongoException $e) {}
        }
    }

    
    public function deleteMsgs($mailbox, $uids)
    {
        if (!empty($uids) && ($uid = $this->_getUid($mailbox))) {
            try {
                $this->_db->selectCollection(self::MSG)->remove(array(
                    self::MSG_MSGUID => array('$in' => array_map('strval', $uids)),
                    self::MSG_UID => $uid
                ));
            } catch (MongoException $e) {}
        }
    }

    
    public function deleteMailbox($mailbox)
    {
        if (!($uid = $this->_getUid($mailbox))) {
            return;
        }

        foreach (array(self::BASE, self::MD, self::MSG) as $val) {
            try {
                $this->_db->selectCollection($val)->remove(array(
                    'uid' => $uid
                ));
            } catch (MongoException $e) {}
        }
    }

    
    public function clear($lifetime)
    {
        if (is_null($lifetime)) {
            foreach (array(self::BASE, self::MD, self::MSG) as $val) {
                $this->_db->selectCollection($val)->drop();
            }
            return;
        }

        $query = array(
            self::BASE_MODIFIED => array('$lt' => (time() - $lifetime))
        );
        $uids = array();

        try {
            $cursor = $this->_db->selectCollection(self::BASE)->find($query);
            foreach ($cursor as $val) {
                $uids[] = strval($val['_id']);
            }
        } catch (MongoException $e) {}

        if (empty($uids)) {
            return;
        }

        foreach (array(self::BASE, self::MD, self::MSG) as $val) {
            try {
                $this->_db->selectCollection($val)->remove(array(
                    'uid' => array('$in' => $uids)
                ));
            } catch (MongoException $e) {}
        }
    }

    
    protected function _getUid($mailbox)
    {
        $query = array(
            self::BASE_HOSTSPEC => $this->_params['hostspec'],
            self::BASE_MAILBOX => $mailbox,
            self::BASE_PORT => $this->_params['port'],
            self::BASE_USERNAME => $this->_params['username']
        );

        try {
            if ($result = $this->_db->selectCollection(self::BASE)->findOne($query)) {
                return strval($result['_id']);
            }
        } catch (MongoException $e) {}

        return null;
    }

    
    protected function _createUid($mailbox)
    {
        $this->_db->selectCollection(self::BASE)->insert(array(
            self::BASE_HOSTSPEC => $this->_params['hostspec'],
            self::BASE_MAILBOX => $mailbox,
            self::BASE_PORT => $this->_params['port'],
            self::BASE_USERNAME => $this->_params['username']
        ));

        return $this->_getUid($mailbox);
    }

    
    protected function _value($data)
    {
        static $compress;

        if (!isset($compress)) {
            $compress = new Horde_Compress_Fast();
        }

        return ($data instanceof MongoBinData)
            ? @unserialize($compress->decompress($data->bin))
            : new MongoBinData($compress->compress(serialize($data)), MongoBinData::BYTE_ARRAY);
    }

    

    
    public function checkMongoIndices()
    {
        foreach ($this->_indices as $key => $val) {
            if (!$this->_params['mongo_db']->checkIndices($key, $val)) {
                return false;
            }
        }

        return true;
    }

    
    public function createMongoIndices()
    {
        foreach ($this->_indices as $key => $val) {
            $this->_params['mongo_db']->createIndices($key, $val);
        }
    }

}
