<?php

namespace Box\Spout\Common\Escaper;


class XLSX implements EscaperInterface
{
    
    protected $controlCharactersEscapingMap;

    
    public function __construct()
    {
        $this->controlCharactersEscapingMap = $this->getControlCharactersEscapingMap();
    }

    
    public function escape($string)
    {
        $escapedString = $this->escapeControlCharacters($string);
        $escapedString = htmlspecialchars($escapedString, ENT_QUOTES);

        return $escapedString;
    }

    
    public function unescape($string)
    {
        $unescapedString = htmlspecialchars_decode($string, ENT_QUOTES);
        $unescapedString = $this->unescapeControlCharacters($unescapedString);

        return $unescapedString;
    }

    
    protected function getControlCharactersEscapingMap()
    {
        $controlCharactersEscapingMap = [];
        $whitelistedControlCharacters = ["\t", "\r", "\n"];

                for ($charValue = 0x0; $charValue <= 0x1F; $charValue++) {
            if (!in_array(chr($charValue), $whitelistedControlCharacters)) {
                $charHexValue = dechex($charValue);
                $escapedChar = '_x' . sprintf('%04s' , strtoupper($charHexValue)) . '_';
                $controlCharactersEscapingMap[$escapedChar] = chr($charValue);
            }
        }

        return $controlCharactersEscapingMap;
    }

    
    protected function escapeControlCharacters($string)
    {
        $escapedString = $this->escapeEscapeCharacter($string);
        return str_replace(array_values($this->controlCharactersEscapingMap), array_keys($this->controlCharactersEscapingMap), $escapedString);
    }

    
    protected function escapeEscapeCharacter($string)
    {
        return preg_replace('/_(x[\dA-F]{4})_/', '_x005F_$1_', $string);
    }

    
    protected function unescapeControlCharacters($string)
    {
        $unescapedString = $string;
        foreach ($this->controlCharactersEscapingMap as $escapedCharValue => $charValue) {
                        $unescapedString = preg_replace("/(?<!_x005F)($escapedCharValue)/", $charValue, $unescapedString);
        }

        return $this->unescapeEscapeCharacter($unescapedString);
    }

    
    protected function unescapeEscapeCharacter($string)
    {
        return preg_replace('/_x005F(_x[\dA-F]{4}_)/', '$1', $string);
    }
}
