<?php


class HTMLPurifier_URIFilter_MakeAbsolute extends HTMLPurifier_URIFilter
{
    
    public $name = 'MakeAbsolute';

    
    protected $base;

    
    protected $basePathStack = array();

    
    public function prepare($config)
    {
        $def = $config->getDefinition('URI');
        $this->base = $def->base;
        if (is_null($this->base)) {
            trigger_error(
                'URI.MakeAbsolute is being ignored due to lack of ' .
                'value for URI.Base configuration',
                E_USER_WARNING
            );
            return false;
        }
        $this->base->fragment = null;         $stack = explode('/', $this->base->path);
        array_pop($stack);         $stack = $this->_collapseStack($stack);         $this->basePathStack = $stack;
        return true;
    }

    
    public function filter(&$uri, $config, $context)
    {
        if (is_null($this->base)) {
            return true;
        }         if ($uri->path === '' && is_null($uri->scheme) &&
            is_null($uri->host) && is_null($uri->query) && is_null($uri->fragment)) {
                        $uri = clone $this->base;
            return true;
        }
        if (!is_null($uri->scheme)) {
                        if (!is_null($uri->host)) {
                return true;
            }
            $scheme_obj = $uri->getSchemeObj($config, $context);
            if (!$scheme_obj) {
                                return false;
            }
            if (!$scheme_obj->hierarchical) {
                                return true;
            }
                    }
        if (!is_null($uri->host)) {
                        return true;
        }
        if ($uri->path === '') {
            $uri->path = $this->base->path;
        } elseif ($uri->path[0] !== '/') {
                        $stack = explode('/', $uri->path);
            $new_stack = array_merge($this->basePathStack, $stack);
            if ($new_stack[0] !== '' && !is_null($this->base->host)) {
                array_unshift($new_stack, '');
            }
            $new_stack = $this->_collapseStack($new_stack);
            $uri->path = implode('/', $new_stack);
        } else {
                        $uri->path = implode('/', $this->_collapseStack(explode('/', $uri->path)));
        }
                $uri->scheme = $this->base->scheme;
        if (is_null($uri->userinfo)) {
            $uri->userinfo = $this->base->userinfo;
        }
        if (is_null($uri->host)) {
            $uri->host = $this->base->host;
        }
        if (is_null($uri->port)) {
            $uri->port = $this->base->port;
        }
        return true;
    }

    
    private function _collapseStack($stack)
    {
        $result = array();
        $is_folder = false;
        for ($i = 0; isset($stack[$i]); $i++) {
            $is_folder = false;
                        if ($stack[$i] == '' && $i && isset($stack[$i + 1])) {
                continue;
            }
            if ($stack[$i] == '..') {
                if (!empty($result)) {
                    $segment = array_pop($result);
                    if ($segment === '' && empty($result)) {
                                                                        $result[] = '';
                    } elseif ($segment === '..') {
                        $result[] = '..';                     }
                } else {
                                        $result[] = '..';
                }
                $is_folder = true;
                continue;
            }
            if ($stack[$i] == '.') {
                                $is_folder = true;
                continue;
            }
            $result[] = $stack[$i];
        }
        if ($is_folder) {
            $result[] = '';
        }
        return $result;
    }
}

