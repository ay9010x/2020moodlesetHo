<?php


class HTMLPurifier_AttrDef_CSS_Ident extends HTMLPurifier_AttrDef
{

    
    public function validate($string, $config, $context)
    {
        $string = trim($string);

                if (!$string) {
            return false;
        }

        $pattern = '/^(-?[A-Za-z_][A-Za-z_\-0-9]*)$/';
        if (!preg_match($pattern, $string)) {
            return false;
        }
        return $string;
    }
}

