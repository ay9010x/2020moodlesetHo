<?php



abstract class Horde_Crypt_Blowfish_Base
{
    
    public $cipher;

    
    public $iv = null;

    
    public $key;

    
    static public function supported()
    {
        return true;
    }

    
    public function __construct($cipher)
    {
        $this->cipher = $cipher;
    }

    
    abstract public function encrypt($text);

    
    abstract public function decrypt($text);

    
    public function setIv($iv = null)
    {
        $this->iv = is_null($iv)
            ? substr(new Horde_Support_Randomid(), 0, 8)
            : $iv;
    }

    
    protected function _pad($text, $ignore = false)
    {
        $blocksize = Horde_Crypt_Blowfish::BLOCKSIZE;
        $padding = $blocksize - (strlen($text) % $blocksize);

        return ($ignore && ($padding == $blocksize))
            ? $text
            : $text . str_repeat(chr($padding), $padding);
    }

    
    protected function _unpad($text)
    {
        return substr($text, 0, ord(substr($text, -1)) * -1);
    }

}
