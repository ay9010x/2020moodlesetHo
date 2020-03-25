<?php



class Horde_Crypt_Blowfish
{
        const IGNORE_OPENSSL = 1;
    const IGNORE_MCRYPT = 2;

        const BLOCKSIZE = 8;

        const MAXKEYSIZE = 56;

        const IV_LENGTH = 8;

    
    protected $_crypt;

    
    public function __construct($key, array $opts = array())
    {
        $opts = array_merge(array(
            'cipher' => 'ecb',
            'ignore' => 0,
            'iv' => null
        ), $opts);

        if (!($opts['ignore'] & self::IGNORE_OPENSSL) &&
            Horde_Crypt_Blowfish_Openssl::supported()) {
            $this->_crypt = new Horde_Crypt_Blowfish_Openssl($opts['cipher']);
        } elseif (!($opts['ignore'] & self::IGNORE_MCRYPT) &&
                  Horde_Crypt_Blowfish_Mcrypt::supported()) {
            $this->_crypt = new Horde_Crypt_Blowfish_Mcrypt($opts['cipher']);
        } else {
            $this->_crypt = new Horde_Crypt_Blowfish_Php($opts['cipher']);
        }

        $this->setKey($key, $opts['iv']);
    }

    
    public function __get($name)
    {
        switch ($name) {
        case 'cipher':
        case 'key':
        case 'iv':
            return $this->_crypt->$name;
        }
    }

    
    public function encrypt($text)
    {
        if (!is_string($text)) {
            throw new Horde_Crypt_Blowfish_Exception('Data to encrypt must be a string.');
        }

        return $this->_crypt->encrypt($text);
    }

    
    public function decrypt($text)
    {
        if (!is_string($text)) {
            throw new Horde_Crypt_Blowfish_Exception('Data to decrypt must be a string.');
        }

        return $this->_crypt->decrypt($text);
    }

    
    public function setKey($key, $iv = null)
    {
        if (!is_string($key)) {
            throw new Horde_Crypt_Blowfish_Exception('Encryption key must be a string.');
        }

        $len = strlen($key);
        if (($len > self::MAXKEYSIZE) || ($len == 0)) {
            throw new Horde_Crypt_Blowfish_Exception(sprintf('Encryption key must be less than %d characters (bytes) and non-zero. Supplied key length: %d', self::MAXKEYSIZE, $len));
        }

        $this->_crypt->key = $key;

        switch ($this->_crypt->cipher) {
        case 'cbc':
            if (is_null($iv)) {
                if (is_null($this->iv)) {
                    $this->_crypt->setIv();
                }
            } else {
                $iv = substr($iv, 0, self::IV_LENGTH);
                if (($len = strlen($iv)) < self::IV_LENGTH) {
                    $iv .= str_repeat(chr(0), self::IV_LENGTH - $len);
                }
                $this->_crypt->setIv($iv);
            }
            break;

        case 'ecb':
            $this->iv = false;
            break;
        }
    }

}
