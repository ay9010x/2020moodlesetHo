<?php


class HTMLPurifier_URIScheme_file extends HTMLPurifier_URIScheme
{
    
    public $browsable = false;

    
    public $may_omit_host = true;

    
    public function doValidate(&$uri, $config, $context)
    {
                $uri->userinfo = null;
                $uri->port = null;
                        $uri->query = null;
        return true;
    }
}

