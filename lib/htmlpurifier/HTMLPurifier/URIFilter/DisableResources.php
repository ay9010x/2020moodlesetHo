<?php

class HTMLPurifier_URIFilter_DisableResources extends HTMLPurifier_URIFilter
{
    
    public $name = 'DisableResources';

    
    public function filter(&$uri, $config, $context)
    {
        return !$context->get('EmbeddedURI', true);
    }
}

