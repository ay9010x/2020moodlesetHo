<?php



class Horde_Crypt_Blowfish_Php extends Horde_Crypt_Blowfish_Base
{
    
    protected $_ob;

    
    public function encrypt($text)
    {
        $this->_init();
        return $this->_ob->encrypt($this->_pad($text), $this->iv);
    }

    
    public function decrypt($text)
    {
        $this->_init();
        return $this->_unpad($this->_ob->decrypt($this->_pad($text, true), $this->iv));
    }

    
    protected function _init()
    {
        if (!isset($this->_ob) ||
            ($this->_ob->md5 != hash('md5', $this->key))) {
            switch ($this->cipher) {
            case 'cbc':
                $this->_ob = new Horde_Crypt_Blowfish_Php_Cbc($this->key);
                break;

            case 'ecb':
                $this->_ob = new Horde_Crypt_Blowfish_Php_Ecb($this->key);
                break;
            }
        }
    }

}
