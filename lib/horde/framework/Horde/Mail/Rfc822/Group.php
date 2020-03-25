<?php



class Horde_Mail_Rfc822_Group extends Horde_Mail_Rfc822_Object implements Countable
{
    
    public $addresses;

    
    protected $_groupname = 'Group';

    
    public function __construct($groupname = null, $addresses = null)
    {
        if (!is_null($groupname)) {
            $this->groupname = $groupname;
        }

        if (is_null($addresses)) {
            $this->addresses = new Horde_Mail_Rfc822_GroupList();
        } elseif ($addresses instanceof Horde_Mail_Rfc822_GroupList) {
            $this->addresses = clone $addresses;
        } else {
            $rfc822 = new Horde_Mail_Rfc822();
            $this->addresses = $rfc822->parseAddressList($addresses, array(
                'group' => true
            ));
        }
    }

    
    public function __set($name, $value)
    {
        switch ($name) {
        case 'groupname':
            $this->_groupname = Horde_Mime::decode($value);
            break;
        }
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'groupname':
        case 'label':
            return $this->_groupname;

        case 'groupname_encoded':
            return Horde_Mime::encode($this->_groupname);

        case 'valid':
            return (bool)strlen($this->_groupname);

        default:
            return null;
        }
    }

    
    protected function _writeAddress($opts)
    {
        $addr = $this->addresses->writeAddress($opts);
        $groupname = $this->groupname;
        if (!empty($opts['encode'])) {
            $groupname = Horde_Mime::encode($groupname, $opts['encode']);
        }

        $rfc822 = new Horde_Mail_Rfc822();

        return $rfc822->encode($groupname, 'personal') . ':' .
            (strlen($addr) ? (' ' . $addr) : '') . ';';
    }

    
    public function match($ob)
    {
        return $this->addresses->match($ob);
    }

    

    
    public function count()
    {
        return count($this->addresses);
    }

}
