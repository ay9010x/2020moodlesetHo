<?php


class HTMLPurifier_AttrDef_URI_IPv4 extends HTMLPurifier_AttrDef
{

    
    protected $ip4;

    
    public function validate($aIP, $config, $context)
    {
        if (!$this->ip4) {
            $this->_loadRegex();
        }

        if (preg_match('#^' . $this->ip4 . '$#s', $aIP)) {
            return $aIP;
        }
        return false;
    }

    
    protected function _loadRegex()
    {
        $oct = '(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[0-9])';         $this->ip4 = "(?:{$oct}\\.{$oct}\\.{$oct}\\.{$oct})";
    }
}

