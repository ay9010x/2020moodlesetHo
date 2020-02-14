<?php



class Horde_Crypt_Blowfish_Php_Cbc extends Horde_Crypt_Blowfish_Php_Base
{
    
    public function encrypt($text, $iv)
    {
        $cipherText = '';
        $len = strlen($text);

        list(, $Xl, $Xr) = unpack('N2', substr($text, 0, 8) ^ $iv);
        $this->_encipher($Xl, $Xr);
        $cipherText .= pack('N2', $Xl, $Xr);

        for ($i = 8; $i < $len; $i += 8) {
            list(, $Xl, $Xr) = unpack('N2', substr($text, $i, 8) ^ substr($cipherText, $i - 8, 8));
            $this->_encipher($Xl, $Xr);
            $cipherText .= pack('N2', $Xl, $Xr);
        }

        return $cipherText;
    }

    
    public function decrypt($text, $iv)
    {
        $plainText = '';
        $len = strlen($text);

        list(, $Xl, $Xr) = unpack('N2', substr($text, 0, 8));
        $this->_decipher($Xl, $Xr);
        $plainText .= (pack('N2', $Xl, $Xr) ^ $iv);

        for ($i = 8; $i < $len; $i += 8) {
            list(, $Xl, $Xr) = unpack('N2', substr($text, $i, 8));
            $this->_decipher($Xl, $Xr);
            $plainText .= (pack('N2', $Xl, $Xr) ^ substr($text, $i - 8, 8));
        }

        return $plainText;
    }

}
