<?php



class Horde_Crypt_Blowfish_Mcrypt extends Horde_Crypt_Blowfish_Base
{
    
    private $_mcrypt;

    
    static public function supported()
    {
        return extension_loaded('mcrypt');
    }

    
    public function __construct($cipher)
    {
        parent::__construct($cipher);

        $this->_mcrypt = mcrypt_module_open(MCRYPT_BLOWFISH, '', $cipher, '');
    }

    
    public function encrypt($text)
    {
        mcrypt_generic_init($this->_mcrypt, $this->key, empty($this->iv) ? str_repeat('0', Horde_Crypt_Blowfish::IV_LENGTH) : $this->iv);
        $out = mcrypt_generic($this->_mcrypt, $this->_pad($text));
        mcrypt_generic_deinit($this->_mcrypt);

        return $out;
    }

    
    public function decrypt($text)
    {
        mcrypt_generic_init($this->_mcrypt, $this->key, empty($this->iv) ? str_repeat('0', Horde_Crypt_Blowfish::IV_LENGTH) : $this->iv);
        $out = mdecrypt_generic($this->_mcrypt, $this->_pad($text, true));
        mcrypt_generic_deinit($this->_mcrypt);

        return $this->_unpad($out);
    }

    
    public function setIv($iv = null)
    {
        $this->iv = is_null($iv)
            ? mcrypt_create_iv(Horde_Crypt_Blowfish::IV_LENGTH, MCRYPT_RAND)
            : $iv;
    }

}