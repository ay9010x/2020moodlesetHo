<?php



class Horde_Mail_Rfc822_Address extends Horde_Mail_Rfc822_Object
{
    
    public $comment = array();

    
    public $mailbox = null;

    
    protected $_host = null;

    
    protected $_personal = null;

    
    public function __construct($address = null)
    {
        if (!is_null($address)) {
            $rfc822 = new Horde_Mail_Rfc822();
            $addr = $rfc822->parseAddressList($address);
            if (count($addr)) {
                foreach ($addr[0] as $key => $val) {
                    $this->$key = $val;
                }
            }
        }
    }

    
    public function __set($name, $value)
    {
        switch ($name) {
        case 'host':
            $value = ltrim($value, '@');
            $this->_host = function_exists('idn_to_utf8')
                ? strtolower(idn_to_utf8($value))
                : strtolower($value);
            break;

        case 'personal':
            $this->_personal = strlen($value)
                ? Horde_Mime::decode($value)
                : null;
            break;
        }
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'bare_address':
            return is_null($this->host)
                ? $this->mailbox
                : $this->mailbox . '@' . $this->host;

        case 'bare_address_idn':
            $personal = $this->_personal;
            $this->_personal = null;
            $res = $this->encoded;
            $this->_personal = $personal;
            return $res;

        case 'encoded':
            return $this->writeAddress(true);

        case 'host':
            return $this->_host;

        case 'host_idn':
            return function_exists('idn_to_ascii')
                ? idn_to_ascii($this->_host)
                : $this->host;

        case 'label':
            return is_null($this->personal)
                ? $this->bare_address
                : $this->_personal;

        case 'personal':
            return (strcasecmp($this->_personal, $this->bare_address) === 0)
                ? null
                : $this->_personal;

        case 'personal_encoded':
            return Horde_Mime::encode($this->personal);

        case 'valid':
            return (bool)strlen($this->mailbox);

        default:
            return null;
        }
    }

    
    protected function _writeAddress($opts)
    {
        $rfc822 = new Horde_Mail_Rfc822();

        $address = $rfc822->encode($this->mailbox, 'address');
        $host = empty($opts['idn']) ? $this->host : $this->host_idn;
        if (strlen($host)) {
            $address .= '@' . $host;
        }
        $personal = $this->personal;
        if (strlen($personal)) {
            if (!empty($opts['encode'])) {
                $personal = Horde_Mime::encode($this->personal, $opts['encode']);
            }
            $personal = $rfc822->encode($personal, 'personal');
        }

        return (strlen($personal) && ($personal != $address))
            ? $personal . ' <' . $address . '>'
            : $address;
    }

    
    public function match($ob)
    {
        if (!($ob instanceof Horde_Mail_Rfc822_Address)) {
            $ob = new Horde_Mail_Rfc822_Address($ob);
        }

        return ($this->bare_address == $ob->bare_address);
    }

    
    public function matchInsensitive($ob)
    {
        if (!($ob instanceof Horde_Mail_Rfc822_Address)) {
            $ob = new Horde_Mail_Rfc822_Address($ob);
        }

        return (Horde_String::lower($this->bare_address) == Horde_String::lower($ob->bare_address));
    }

    
    public function matchDomain($domain)
    {
        $host = $this->host;
        if (is_null($host)) {
            return false;
        }

        $match_domain = explode('.', $domain);
        $match_host = array_slice(explode('.', $host), count($match_domain) * -1);

        return (strcasecmp($domain, implode('.', $match_host)) === 0);
    }

}
