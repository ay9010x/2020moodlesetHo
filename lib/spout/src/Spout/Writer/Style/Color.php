<?php

namespace Box\Spout\Writer\Style;

use Box\Spout\Writer\Exception\InvalidColorException;


class Color
{
    
    const BLACK = '000000';
    const WHITE = 'FFFFFF';
    const RED = 'FF0000';
    const DARK_RED = 'C00000';
    const ORANGE = 'FFC000';
    const YELLOW = 'FFFF00';
    const LIGHT_GREEN = '92D040';
    const GREEN = '00B050';
    const LIGHT_BLUE = '00B0E0';
    const BLUE = '0070C0';
    const DARK_BLUE = '002060';
    const PURPLE = '7030A0';

    
    public static function rgb($red, $green, $blue)
    {
        self::throwIfInvalidColorComponentValue($red);
        self::throwIfInvalidColorComponentValue($green);
        self::throwIfInvalidColorComponentValue($blue);

        return strtoupper(
            self::convertColorComponentToHex($red) .
            self::convertColorComponentToHex($green) .
            self::convertColorComponentToHex($blue)
        );
    }

    
    protected static function throwIfInvalidColorComponentValue($colorComponent)
    {
        if (!is_int($colorComponent) || $colorComponent < 0 || $colorComponent > 255) {
            throw new InvalidColorException("The RGB components must be between 0 and 255. Received: $colorComponent");
        }
    }

    
    protected static function convertColorComponentToHex($colorComponent)
    {
        return str_pad(dechex($colorComponent), 2, '0', STR_PAD_LEFT);
    }

    
    public static function toARGB($rgbColor)
    {
        return 'FF' . $rgbColor;
    }
}
