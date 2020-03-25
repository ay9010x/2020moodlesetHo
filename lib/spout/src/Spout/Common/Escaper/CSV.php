<?php

namespace Box\Spout\Common\Escaper;


class CSV implements EscaperInterface
{
    
    public function escape($string)
    {
        return $string;
    }

    
    public function unescape($string)
    {
        return $string;
    }
}
