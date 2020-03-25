<?php



class Horde_Crypt_Blowfish_Php_Ecb extends Horde_Crypt_Blowfish_Php_Base
{
    
    public function encrypt($text, $iv)
    {
        $cipherText = '';
        $len = strlen($text);

        for ($i = 0; $i < $len; $i += 8) {
            list(, $Xl, $Xr) = unpack('N2', substr($text, $i, 8));
            $this->_encipher($Xl, $Xr);
            $cipherText .= pack('N2', $Xl, $Xr);
        }

        return $cipherText;
    }

    
    public function decrypt($text, $iv)
    {
        $plainText = '';
        $len = strlen($text);

        for ($i = 0; $i < $len; $i += 8) {
            list(, $Xl, $Xr) = unpack('N2', substr($text, $i, 8));
            $this->_decipher($Xl, $Xr);
            $plainText .= pack('N2', $Xl, $Xr);
        }

        return $plainText;
    }

}
