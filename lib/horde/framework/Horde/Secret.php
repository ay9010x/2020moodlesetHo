<?php

class Horde_Secret
{
    
    const DEFAULT_KEY = 'generic';

    
    protected $_params = array(
        'cookie_domain' => '',
        'cookie_path' => '',
        'cookie_ssl' => false,
        'session_name' => 'horde_secret'
    );

    
    protected $_cipherCache = array();

    
    protected $_keyCache = array();

    
    public function __construct($params = array())
    {
        $this->_params = array_merge($this->_params, $params);
    }

    
    public function write($key, $message)
    {
        $message = strval($message);
        return (strlen($key) && strlen($message))
            ? $this->_getCipherOb($key)->encrypt($message)
            : '';
    }

    
    public function read($key, $ciphertext)
    {
        $ciphertext = strval($ciphertext);
        return (strlen($key) && strlen($ciphertext))
            ? $this->_getCipherOb($key)->decrypt($ciphertext)
            : '';
    }

    
    protected function _getCipherOb($key)
    {
        if (!is_string($key)) {
            throw new Horde_Secret_Exception('Key must be a string', Horde_Secret_Exception::KEY_NOT_STRING);
        }

        if (!strlen($key)) {
            throw new Horde_Secret_Exception('Key must be non-zero.', Horde_Secret_Exception::KEY_ZERO_LENGTH);
        }

        $key = substr($key, 0, 56);

        $idx = hash('md5', $key);
        if (!isset($this->_cipherCache[$idx])) {
            $this->_cipherCache[$idx] = new Horde_Crypt_Blowfish($key);
        }

        return $this->_cipherCache[$idx];
    }

    
    public function setKey($keyname = self::DEFAULT_KEY)
    {
        $set = true;

        if (isset($_COOKIE[$this->_params['session_name']])) {
            if (isset($_COOKIE[$keyname . '_key'])) {
                $key = $_COOKIE[$keyname . '_key'];
                $set = false;
            } else {
                $key = $_COOKIE[$keyname . '_key'] = strval(new Horde_Support_Randomid());
            }
        } else {
            $key = session_id();
        }

        if ($set) {
            $this->_setCookie($keyname, $key);
        }

        return $key;
    }

    
    public function getKey($keyname = self::DEFAULT_KEY)
    {
        if (!isset($this->_keyCache[$keyname])) {
            if (isset($_COOKIE[$keyname . '_key'])) {
                $key = $_COOKIE[$keyname . '_key'];
            } else {
                $key = session_id();
                $this->_setCookie($keyname, $key);
            }

            $this->_keyCache[$keyname] = $key;
        }

        return $this->_keyCache[$keyname];
    }

    
    public function clearKey($keyname = self::DEFAULT_KEY)
    {
        if (isset($_COOKIE[$this->_params['session_name']]) &&
            isset($_COOKIE[$keyname . '_key'])) {
            $this->_setCookie($keyname, false);
            return true;
        }

        return false;
    }

    
    protected function _setCookie($keyname, $key)
    {
        @setcookie(
            $keyname . '_key',
            $key,
            0,
            $this->_params['cookie_path'],
            $this->_params['cookie_domain'],
            $this->_params['cookie_ssl'],
            true
        );

        if ($key === false) {
            unset($_COOKIE[$keyname], $this->_keyCache[$keyname]);
        } else {
            $_COOKIE[$keyname] = $this->_keyCache[$keyname] = $key;
        }
    }

}
