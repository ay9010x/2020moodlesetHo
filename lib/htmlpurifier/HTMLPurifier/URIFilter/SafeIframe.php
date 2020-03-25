<?php


class HTMLPurifier_URIFilter_SafeIframe extends HTMLPurifier_URIFilter
{
    
    public $name = 'SafeIframe';

    
    public $always_load = true;

    
    protected $regexp = null;

                
    public function prepare($config)
    {
        $this->regexp = $config->get('URI.SafeIframeRegexp');
        return true;
    }

    
    public function filter(&$uri, $config, $context)
    {
                if (!$config->get('HTML.SafeIframe')) {
            return true;
        }
                if (!$context->get('EmbeddedURI', true)) {
            return true;
        }
        $token = $context->get('CurrentToken', true);
        if (!($token && $token->name == 'iframe')) {
            return true;
        }
                if ($this->regexp === null) {
            return false;
        }
                return preg_match($this->regexp, $uri->toString());
    }
}

