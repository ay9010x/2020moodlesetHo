<?php


class HTMLPurifier_AttrDef_HTML_Nmtokens extends HTMLPurifier_AttrDef
{

    
    public function validate($string, $config, $context)
    {
        $string = trim($string);

                if (!$string) {
            return false;
        }

        $tokens = $this->split($string, $config, $context);
        $tokens = $this->filter($tokens, $config, $context);
        if (empty($tokens)) {
            return false;
        }
        return implode(' ', $tokens);
    }

    
    protected function split($string, $config, $context)
    {
                
                                        $pattern = '/(?:(?<=\s)|\A)' .             '((?:--|-?[A-Za-z_])[A-Za-z_\-0-9]*)' .
            '(?:(?=\s)|\z)/';         preg_match_all($pattern, $string, $matches);
        return $matches[1];
    }

    
    protected function filter($tokens, $config, $context)
    {
        return $tokens;
    }
}

