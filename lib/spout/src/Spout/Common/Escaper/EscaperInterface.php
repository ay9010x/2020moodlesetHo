<?php

namespace Box\Spout\Common\Escaper;


interface EscaperInterface
{
    
    public function escape($string);

    
    public function unescape($string);
}
