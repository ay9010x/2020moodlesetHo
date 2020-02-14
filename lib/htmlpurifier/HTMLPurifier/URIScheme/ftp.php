<?php


class HTMLPurifier_URIScheme_ftp extends HTMLPurifier_URIScheme
{
    
    public $default_port = 21;

    
    public $browsable = true; 
    
    public $hierarchical = true;

    
    public function doValidate(&$uri, $config, $context)
    {
        $uri->query = null;

                $semicolon_pos = strrpos($uri->path, ';');         if ($semicolon_pos !== false) {
            $type = substr($uri->path, $semicolon_pos + 1);             $uri->path = substr($uri->path, 0, $semicolon_pos);
            $type_ret = '';
            if (strpos($type, '=') !== false) {
                                list($key, $typecode) = explode('=', $type, 2);
                if ($key !== 'type') {
                                        $uri->path .= '%3B' . $type;
                } elseif ($typecode === 'a' || $typecode === 'i' || $typecode === 'd') {
                    $type_ret = ";type=$typecode";
                }
            } else {
                $uri->path .= '%3B' . $type;
            }
            $uri->path = str_replace(';', '%3B', $uri->path);
            $uri->path .= $type_ret;
        }
        return true;
    }
}

