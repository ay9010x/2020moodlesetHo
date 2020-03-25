<?php



interface Horde_Translation_Handler
{
    
    public function t($message);

    
    public function ngettext($singular, $plural, $number);
}
