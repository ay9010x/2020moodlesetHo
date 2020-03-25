<?php



class Horde_Crypt_Blowfish_Openssl extends Horde_Crypt_Blowfish_Base
{
    
    static public function supported()
    {
        return extension_loaded('openssl');
    }

    
    public function encrypt($text)
    {
        if (PHP_VERSION_ID <= 50302) {
            return @openssl_encrypt($text, 'bf-' . $this->cipher, $this->key, true);
        } elseif (PHP_VERSION_ID == 50303) {
                                    return @openssl_encrypt($text, 'bf-' . $this->cipher, $this->key, true, strval($this->iv));
        }

        return openssl_encrypt($text, 'bf-' . $this->cipher, $this->key, true, strval($this->iv));
    }

    
    public function decrypt($text)
    {
        return (PHP_VERSION_ID <= 50302)
            ? openssl_decrypt($text, 'bf-' . $this->cipher, $this->key, true)
            : openssl_decrypt($text, 'bf-' . $this->cipher, $this->key, true, strval($this->iv));
    }

}
