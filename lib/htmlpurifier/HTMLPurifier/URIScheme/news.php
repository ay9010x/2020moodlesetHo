<?php


class HTMLPurifier_URIScheme_news extends HTMLPurifier_URIScheme
{
    
    public $browsable = false;

    
    public $may_omit_host = true;

    
    public function doValidate(&$uri, $config, $context)
    {
        $uri->userinfo = null;
        $uri->host = null;
        $uri->port = null;
        $uri->query = null;
                return true;
    }
}

