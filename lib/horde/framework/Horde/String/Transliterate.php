<?php



class Horde_String_Transliterate
{
    
    static protected $_map;

    
    static protected $_transliterator;

    
    static public function toAscii($str)
    {
        switch (true) {
        case class_exists('Transliterator'):
            return self::_intlToAscii($str);
        case extension_loaded('iconv'):
            return self::_iconvToAscii($str);
        default:
            return self::_fallbackToAscii($str);
        }
    }

    
    static protected function _intlToAscii($str)
    {
        if (!isset(self::$_transliterator)) {
            self::$_transliterator = Transliterator::create(
                'Any-Latin; Latin-ASCII'
            );
        }
        return self::$_transliterator->transliterate($str);
    }

    
    static protected function _iconvToAscii($str)
    {
        return iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    }

    
    static protected function _fallbackToAscii($str)
    {
        if (!isset(self::$_map)) {
            self::$_map = array(
                'À' => 'A',
                'Á' => 'A',
                'Â' => 'A',
                'Ã' => 'A',
                'Ä' => 'A',
                'Å' => 'A',
                'Æ' => 'AE',
                'à' => 'a',
                'á' => 'a',
                'â' => 'a',
                'ã' => 'a',
                'ä' => 'a',
                'å' => 'a',
                'æ' => 'ae',
                'Þ' => 'TH',
                'þ' => 'th',
                'Ç' => 'C',
                'ç' => 'c',
                'Ð' => 'D',
                'ð' => 'd',
                'È' => 'E',
                'É' => 'E',
                'Ê' => 'E',
                'Ë' => 'E',
                'è' => 'e',
                'é' => 'e',
                'ê' => 'e',
                'ë' => 'e',
                'ƒ' => 'f',
                'Ì' => 'I',
                'Í' => 'I',
                'Î' => 'I',
                'Ï' => 'I',
                'ì' => 'i',
                'í' => 'i',
                'î' => 'i',
                'ï' => 'i',
                'Ñ' => 'N',
                'ñ' => 'n',
                'Ò' => 'O',
                'Ó' => 'O',
                'Ô' => 'O',
                'Õ' => 'O',
                'Ö' => 'O',
                'Ø' => 'O',
                'ò' => 'o',
                'ó' => 'o',
                'ô' => 'o',
                'õ' => 'o',
                'ö' => 'o',
                'ø' => 'o',
                'Š' => 'S',
                'ẞ' => 'SS',
                'ß' => 'ss',
                'š' => 's',
                'ś' => 's',
                'Ù' => 'U',
                'Ú' => 'U',
                'Û' => 'U',
                'Ü' => 'U',
                'ù' => 'u',
                'ú' => 'u',
                'û' => 'u',
                'Ý' => 'Y',
                'ý' => 'y',
                'ÿ' => 'y',
                'Ž' => 'Z',
                'ž' => 'z'
            );
        }

        return strtr($str, self::$_map);
    }
}
