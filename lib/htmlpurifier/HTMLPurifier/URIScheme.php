<?php


abstract class HTMLPurifier_URIScheme
{

    
    public $default_port = null;

    
    public $browsable = false;

    
    public $secure = false;

    
    public $hierarchical = false;

    
    public $may_omit_host = false;

    
    abstract public function doValidate(&$uri, $config, $context);

    
    public function validate(&$uri, $config, $context)
    {
        if ($this->default_port == $uri->port) {
            $uri->port = null;
        }
                        if (!$this->may_omit_host &&
                        (!is_null($uri->scheme) && ($uri->host === '' || is_null($uri->host))) ||
                                                (is_null($uri->scheme) && $uri->host === '')
        ) {
            do {
                if (is_null($uri->scheme)) {
                    if (substr($uri->path, 0, 2) != '//') {
                        $uri->host = null;
                        break;
                    }
                                                                            }
                                $host = $config->get('URI.Host');
                if (!is_null($host)) {
                    $uri->host = $host;
                } else {
                                        return false;
                }
            } while (false);
        }
        return $this->doValidate($uri, $config, $context);
    }
}

