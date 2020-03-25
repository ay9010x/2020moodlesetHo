<?php


class HTMLPurifier_AttrDef_CSS_URI extends HTMLPurifier_AttrDef_URI
{

    public function __construct()
    {
        parent::__construct(true);     }

    
    public function validate($uri_string, $config, $context)
    {
                
        $uri_string = $this->parseCDATA($uri_string);
        if (strpos($uri_string, 'url(') !== 0) {
            return false;
        }
        $uri_string = substr($uri_string, 4);
        $new_length = strlen($uri_string) - 1;
        if ($uri_string[$new_length] != ')') {
            return false;
        }
        $uri = trim(substr($uri_string, 0, $new_length));

        if (!empty($uri) && ($uri[0] == "'" || $uri[0] == '"')) {
            $quote = $uri[0];
            $new_length = strlen($uri) - 1;
            if ($uri[$new_length] !== $quote) {
                return false;
            }
            $uri = substr($uri, 1, $new_length - 1);
        }

        $uri = $this->expandCSSEscape($uri);

        $result = parent::validate($uri, $config, $context);

        if ($result === false) {
            return false;
        }

                $result = str_replace(array('"', "\\", "\n", "\x0c", "\r"), "", $result);

                        $result = str_replace(array('(', ')', "'"), array('%28', '%29', '%27'), $result);

                                        return "url(\"$result\")";
    }
}

