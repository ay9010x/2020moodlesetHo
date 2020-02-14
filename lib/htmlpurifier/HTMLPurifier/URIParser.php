<?php


class HTMLPurifier_URIParser
{

    
    protected $percentEncoder;

    public function __construct()
    {
        $this->percentEncoder = new HTMLPurifier_PercentEncoder();
    }

    
    public function parse($uri)
    {
        $uri = $this->percentEncoder->normalize($uri);

                                $r_URI = '!'.
            '(([a-zA-Z0-9\.\+\-]+):)?'.             '(//([^/?#"<>]*))?'.             '([^?#"<>]*)'.                   '(\?([^#"<>]*))?'.               '(#([^"<>]*))?'.                 '!';

        $matches = array();
        $result = preg_match($r_URI, $uri, $matches);

        if (!$result) return false; 
                $scheme     = !empty($matches[1]) ? $matches[2] : null;
        $authority  = !empty($matches[3]) ? $matches[4] : null;
        $path       = $matches[5];         $query      = !empty($matches[6]) ? $matches[7] : null;
        $fragment   = !empty($matches[8]) ? $matches[9] : null;

                if ($authority !== null) {
            $r_authority = "/^((.+?)@)?(\[[^\]]+\]|[^:]*)(:(\d*))?/";
            $matches = array();
            preg_match($r_authority, $authority, $matches);
            $userinfo   = !empty($matches[1]) ? $matches[2] : null;
            $host       = !empty($matches[3]) ? $matches[3] : '';
            $port       = !empty($matches[4]) ? (int) $matches[5] : null;
        } else {
            $port = $host = $userinfo = null;
        }

        return new HTMLPurifier_URI(
            $scheme, $userinfo, $host, $port, $path, $query, $fragment);
    }

}

