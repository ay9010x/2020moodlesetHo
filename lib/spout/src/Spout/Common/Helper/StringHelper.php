<?php

namespace Box\Spout\Common\Helper;


class StringHelper
{
    
    protected $hasMbstringSupport;

    
    public function __construct()
    {
        $this->hasMbstringSupport = extension_loaded('mbstring');
    }

    
    public function getStringLength($string)
    {
        return $this->hasMbstringSupport ? mb_strlen($string) : strlen($string);
    }

    
    public function getCharFirstOccurrencePosition($char, $string)
    {
        $position = $this->hasMbstringSupport ? mb_strpos($string, $char) : strpos($string, $char);
        return ($position !== false) ? $position : -1;
    }

    
    public function getCharLastOccurrencePosition($char, $string)
    {
        $position = $this->hasMbstringSupport ? mb_strrpos($string, $char) : strrpos($string, $char);
        return ($position !== false) ? $position : -1;
    }
}
