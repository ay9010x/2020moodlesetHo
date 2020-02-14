<?php


class HTMLPurifier_AttrDef_Integer extends HTMLPurifier_AttrDef
{

    
    protected $negative = true;

    
    protected $zero = true;

    
    protected $positive = true;

    
    public function __construct($negative = true, $zero = true, $positive = true)
    {
        $this->negative = $negative;
        $this->zero = $zero;
        $this->positive = $positive;
    }

    
    public function validate($integer, $config, $context)
    {
        $integer = $this->parseCDATA($integer);
        if ($integer === '') {
            return false;
        }

                
                if ($this->negative && $integer[0] === '-') {
            $digits = substr($integer, 1);
            if ($digits === '0') {
                $integer = '0';
            }         } elseif ($this->positive && $integer[0] === '+') {
            $digits = $integer = substr($integer, 1);         } else {
            $digits = $integer;
        }

                if (!ctype_digit($digits)) {
            return false;
        }

                if (!$this->zero && $integer == 0) {
            return false;
        }
        if (!$this->positive && $integer > 0) {
            return false;
        }
        if (!$this->negative && $integer < 0) {
            return false;
        }

        return $integer;
    }
}

