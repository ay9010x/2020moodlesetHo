<?php


class HTMLPurifier_AttrDef_CSS_ImportantDecorator extends HTMLPurifier_AttrDef
{
    
    public $def;
    
    public $allow;

    
    public function __construct($def, $allow = false)
    {
        $this->def = $def;
        $this->allow = $allow;
    }

    
    public function validate($string, $config, $context)
    {
                $string = trim($string);
        $is_important = false;
                if (strlen($string) >= 9 && substr($string, -9) === 'important') {
            $temp = rtrim(substr($string, 0, -9));
                        if (strlen($temp) >= 1 && substr($temp, -1) === '!') {
                $string = rtrim(substr($temp, 0, -1));
                $is_important = true;
            }
        }
        $string = $this->def->validate($string, $config, $context);
        if ($this->allow && $is_important) {
            $string .= ' !important';
        }
        return $string;
    }
}

