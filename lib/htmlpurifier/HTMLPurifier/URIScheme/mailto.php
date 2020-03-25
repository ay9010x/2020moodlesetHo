<?php




class HTMLPurifier_URIScheme_mailto extends HTMLPurifier_URIScheme
{
    
    public $browsable = false;

    
    public $may_omit_host = true;

    
    public function doValidate(&$uri, $config, $context)
    {
        $uri->userinfo = null;
        $uri->host     = null;
        $uri->port     = null;
                return true;
    }
}

