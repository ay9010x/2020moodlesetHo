<?php

namespace Box\Spout\Common\Escaper;


class ODS implements EscaperInterface
{
    
    public function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES);
    }

    
    public function unescape($string)
    {
        return htmlspecialchars_decode($string, ENT_QUOTES);
    }
}
