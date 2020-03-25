<?php



class Horde_Imap_Client_Data_Envelope implements Serializable
{
    
    const VERSION = 2;

    
    protected $_data;

    
    public function __construct(array $data = array())
    {
        $this->_data = new Horde_Mime_Headers();

        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'reply_to':
            $name = 'reply-to';
            
        case 'bcc':
        case 'cc':
        case 'from':
        case 'sender':
        case 'to':
            if (($ob = $this->_data->getOb($name)) !== null) {
                return $ob;
            }

            if (in_array($name, array('sender', 'reply-to'))) {
                return $this->from;
            }
            break;

        case 'date':
            if (($val = $this->_data->getValue($name)) !== null) {
                return new Horde_Imap_Client_DateTime($val);
            }
            break;

        case 'in_reply_to':
        case 'message_id':
        case 'subject':
            if (($val = $this->_data->getValue($name)) !== null) {
                return $val;
            }
            break;
        }

                switch ($name) {
        case 'bcc':
        case 'cc':
        case 'from':
        case 'to':
            return new Horde_Mail_Rfc822_List();

        case 'date':
            return new Horde_Imap_Client_DateTime();

        case 'in_reply_to':
        case 'message_id':
        case 'subject':
            return '';
        }

        return null;
    }

    
    public function __set($name, $value)
    {
        if (!strlen($value)) {
            return;
        }

        switch ($name) {
        case 'bcc':
        case 'cc':
        case 'date':
        case 'from':
        case 'in_reply_to':
        case 'message_id':
        case 'reply_to':
        case 'sender':
        case 'subject':
        case 'to':
            switch ($name) {
            case 'from':
                foreach (array('reply_to', 'sender') as $val) {
                    if ($this->$val->match($value)) {
                        $this->_data->removeHeader($val);
                    }
                }
                break;

            case 'reply_to':
            case 'sender':
                if ($this->from->match($value)) {
                    $this->_data->removeHeader($name);
                    return;
                }

                
                if ($name == 'reply_to') {
                    $name = 'reply-to';
                }
                break;
            }

            $this->_data->addHeader($name, $value, array(
                'sanity_check' => true
            ));
            break;
        }
    }

    
    public function __isset($name)
    {
        switch ($name) {
        case 'reply_to':
            $name = 'reply-to';
            
        case 'sender':
            if ($this->_data->getValue($name) !== null) {
                return true;
            }
            $name = 'from';
            break;
        }

        return ($this->_data->getValue($name) !== null);
    }

    

    
    public function serialize()
    {
        return serialize(array(
            'd' => $this->_data,
            'v' => self::VERSION
        ));
    }

    
    public function unserialize($data)
    {
        $data = @unserialize($data);
        if (empty($data['v']) || ($data['v'] != self::VERSION)) {
            throw new Exception('Cache version change');
        }

        $this->_data = $data['d'];
    }

}
