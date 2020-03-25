<?php



abstract class Horde_Imap_Client_Cache_Backend implements Serializable
{
    
    protected $_params = array();

    
    public function __construct(array $params = array())
    {
        $this->setParams($params);
        $this->_initOb();
    }

    
    protected function _initOb()
    {
    }

    
    public function setParams(array $params = array())
    {
        $this->_params = array_merge($this->_params, $params);
    }

    
    abstract public function get($mailbox, $uids, $fields, $uidvalid);

    
    abstract public function getCachedUids($mailbox, $uidvalid);

    
    abstract public function set($mailbox, $data, $uidvalid);

    
    abstract public function getMetaData($mailbox, $uidvalid, $entries);

    
    abstract public function setMetaData($mailbox, $data);

    
    abstract public function deleteMsgs($mailbox, $uids);

    
    abstract public function deleteMailbox($mailbox);

    
    abstract public function clear($lifetime);


    

    
    public function serialize()
    {
        return serialize($this->_params);
    }

    
    public function unserialize($data)
    {
        $this->_params = unserialize($data);
        $this->_initOb();
    }

}
