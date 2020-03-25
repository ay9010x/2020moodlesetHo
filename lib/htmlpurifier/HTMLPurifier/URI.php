<?php


class HTMLPurifier_URI
{
    
    public $scheme;

    
    public $userinfo;

    
    public $host;

    
    public $port;

    
    public $path;

    
    public $query;

    
    public $fragment;

    
    public function __construct($scheme, $userinfo, $host, $port, $path, $query, $fragment)
    {
        $this->scheme = is_null($scheme) || ctype_lower($scheme) ? $scheme : strtolower($scheme);
        $this->userinfo = $userinfo;
        $this->host = $host;
        $this->port = is_null($port) ? $port : (int)$port;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
    }

    
    public function getSchemeObj($config, $context)
    {
        $registry = HTMLPurifier_URISchemeRegistry::instance();
        if ($this->scheme !== null) {
            $scheme_obj = $registry->getScheme($this->scheme, $config, $context);
            if (!$scheme_obj) {
                return false;
            }         } else {
                        $def = $config->getDefinition('URI');
            $scheme_obj = $def->getDefaultScheme($config, $context);
            if (!$scheme_obj) {
                                trigger_error(
                    'Default scheme object "' . $def->defaultScheme . '" was not readable',
                    E_USER_WARNING
                );
                return false;
            }
        }
        return $scheme_obj;
    }

    
    public function validate($config, $context)
    {
                $chars_sub_delims = '!$&\'()*+,;=';
        $chars_gen_delims = ':/?#[]@';
        $chars_pchar = $chars_sub_delims . ':@';

                if (!is_null($this->host)) {
            $host_def = new HTMLPurifier_AttrDef_URI_Host();
            $this->host = $host_def->validate($this->host, $config, $context);
            if ($this->host === false) {
                $this->host = null;
            }
        }

                                                        if (!is_null($this->scheme) && is_null($this->host) || $this->host === '') {
                                    $def = $config->getDefinition('URI');
            if ($def->defaultScheme === $this->scheme) {
                $this->scheme = null;
            }
        }

                if (!is_null($this->userinfo)) {
            $encoder = new HTMLPurifier_PercentEncoder($chars_sub_delims . ':');
            $this->userinfo = $encoder->encode($this->userinfo);
        }

                if (!is_null($this->port)) {
            if ($this->port < 1 || $this->port > 65535) {
                $this->port = null;
            }
        }

                $segments_encoder = new HTMLPurifier_PercentEncoder($chars_pchar . '/');
        if (!is_null($this->host)) {                                                                                                             $this->path = $segments_encoder->encode($this->path);
        } elseif ($this->path !== '') {
            if ($this->path[0] === '/') {
                                                                if (strlen($this->path) >= 2 && $this->path[1] === '/') {
                                                                                                    $this->path = '';
                } else {
                    $this->path = $segments_encoder->encode($this->path);
                }
            } elseif (!is_null($this->scheme)) {
                                                                $this->path = $segments_encoder->encode($this->path);
            } else {
                                                                $segment_nc_encoder = new HTMLPurifier_PercentEncoder($chars_sub_delims . '@');
                $c = strpos($this->path, '/');
                if ($c !== false) {
                    $this->path =
                        $segment_nc_encoder->encode(substr($this->path, 0, $c)) .
                        $segments_encoder->encode(substr($this->path, $c));
                } else {
                    $this->path = $segment_nc_encoder->encode($this->path);
                }
            }
        } else {
                        $this->path = '';         }

                $qf_encoder = new HTMLPurifier_PercentEncoder($chars_pchar . '/?');

        if (!is_null($this->query)) {
            $this->query = $qf_encoder->encode($this->query);
        }

        if (!is_null($this->fragment)) {
            $this->fragment = $qf_encoder->encode($this->fragment);
        }
        return true;
    }

    
    public function toString()
    {
                $authority = null;
                                if (!is_null($this->host)) {
            $authority = '';
            if (!is_null($this->userinfo)) {
                $authority .= $this->userinfo . '@';
            }
            $authority .= $this->host;
            if (!is_null($this->port)) {
                $authority .= ':' . $this->port;
            }
        }

                                                        $result = '';
        if (!is_null($this->scheme)) {
            $result .= $this->scheme . ':';
        }
        if (!is_null($authority)) {
            $result .= '//' . $authority;
        }
        $result .= $this->path;
        if (!is_null($this->query)) {
            $result .= '?' . $this->query;
        }
        if (!is_null($this->fragment)) {
            $result .= '#' . $this->fragment;
        }

        return $result;
    }

    
    public function isLocal($config, $context)
    {
        if ($this->host === null) {
            return true;
        }
        $uri_def = $config->getDefinition('URI');
        if ($uri_def->host === $this->host) {
            return true;
        }
        return false;
    }

    
    public function isBenign($config, $context)
    {
        if (!$this->isLocal($config, $context)) {
            return false;
        }

        $scheme_obj = $this->getSchemeObj($config, $context);
        if (!$scheme_obj) {
            return false;
        } 
        $current_scheme_obj = $config->getDefinition('URI')->getDefaultScheme($config, $context);
        if ($current_scheme_obj->secure) {
            if (!$scheme_obj->secure) {
                return false;
            }
        }
        return true;
    }
}

