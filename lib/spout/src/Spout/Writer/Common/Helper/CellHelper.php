<?php

namespace Box\Spout\Writer\Common\Helper;


class CellHelper
{
    
    private static $columnIndexToCellIndexCache = [];

    
    public static function getCellIndexFromColumnIndex($columnIndex)
    {
        $originalColumnIndex = $columnIndex;

                if (!isset(self::$columnIndexToCellIndexCache[$originalColumnIndex])) {
            $cellIndex = '';
            $capitalAAsciiValue = ord('A');

            do {
                $modulus = $columnIndex % 26;
                $cellIndex = chr($capitalAAsciiValue + $modulus) . $cellIndex;

                                $columnIndex = intval($columnIndex / 26) - 1;

            } while ($columnIndex >= 0);

            self::$columnIndexToCellIndexCache[$originalColumnIndex] = $cellIndex;
        }

        return self::$columnIndexToCellIndexCache[$originalColumnIndex];
    }

    
    public static function isNonEmptyString($value)
    {
        return (gettype($value) === 'string' && $value !== '');
    }

    
    public static function isNumeric($value)
    {
        $valueType = gettype($value);
        return ($valueType === 'integer' || $valueType === 'double');
    }

    
    public static function isBoolean($value)
    {
        return gettype($value) === 'boolean';
    }
}
