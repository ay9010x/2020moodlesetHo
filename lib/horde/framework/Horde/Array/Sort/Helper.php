<?php

class Horde_Array_Sort_Helper
{
    
    public $key;

    
    public function compare($a, $b)
    {
        return strcoll(Horde_String::lower($a[$this->key], true, 'UTF-8'), Horde_String::lower($b[$this->key], true, 'UTF-8'));
    }

    
    public function reverseCompare($a, $b)
    {
        return strcoll(Horde_String::lower($b[$this->key], true, 'UTF-8'), Horde_String::lower($a[$this->key], true, 'UTF-8'));
    }

    
    public function compareKeys($a, $b)
    {
        return strcoll(Horde_String::lower($a, true, 'UTF-8'), Horde_String::lower($b, true, 'UTF-8'));
    }

    
    public function reverseCompareKeys($a, $b)
    {
        return strcoll(Horde_String::lower($b, true, 'UTF-8'), Horde_String::lower($a, true, 'UTF-8'));
    }

}
