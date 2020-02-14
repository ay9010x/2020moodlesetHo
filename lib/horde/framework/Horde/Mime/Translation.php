<?php



class Horde_Mime_Translation extends Horde_Translation
{
    
    static public function t($message)
    {
        self::$_domain = 'Horde_Mime';
        self::$_directory = '@data_dir@' == '@'.'data_dir'.'@' ? __DIR__ . '/../../../locale' : '@data_dir@/Horde_Mime/locale';
        return parent::t($message);
    }

    
    static public function ngettext($singular, $plural, $number)
    {
        self::$_domain = 'Horde_Mime';
        self::$_directory = '@data_dir@' == '@'.'data_dir'.'@' ? __DIR__ . '/../../../locale' : '@data_dir@/Horde_Mime/locale';
        return parent::ngettext($singular, $plural, $number);
    }
}