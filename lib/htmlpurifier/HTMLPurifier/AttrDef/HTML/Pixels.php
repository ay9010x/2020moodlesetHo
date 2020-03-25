<?php


class HTMLPurifier_AttrDef_HTML_Pixels extends HTMLPurifier_AttrDef
{

    
    protected $max;

    
    public function __construct($max = null)
    {
        $this->max = $max;
    }

    
    public function validate($string, $config, $context)
    {
        $string = trim($string);
        if ($string === '0') {
            return $string;
        }
        if ($string === '') {
            return false;
        }
        $length = strlen($string);
        if (substr($string, $length - 2) == 'px') {
            $string = substr($string, 0, $length - 2);
        }
        if (!is_numeric($string)) {
            return false;
        }
        $int = (int)$string;

        if ($int < 0) {
            return '0';
        }

                        
        if ($this->max !== null && $int > $this->max) {
            return (string)$this->max;
        }
        return (string)$int;
    }

    
    public function make($string)
    {
        if ($string === '') {
            $max = null;
        } else {
            $max = (int)$string;
        }
        $class = get_class($this);
        return new $class($max);
    }
}

