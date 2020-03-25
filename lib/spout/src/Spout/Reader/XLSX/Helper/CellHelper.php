<?php

namespace Box\Spout\Reader\XLSX\Helper;

use Box\Spout\Common\Exception\InvalidArgumentException;


class CellHelper
{
        private static $columnLetterToIndexMapping = [
        'A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4, 'F' => 5, 'G' => 6,
        'H' => 7, 'I' => 8, 'J' => 9, 'K' => 10, 'L' => 11, 'M' => 12, 'N' => 13,
        'O' => 14, 'P' => 15, 'Q' => 16, 'R' => 17, 'S' => 18, 'T' => 19, 'U' => 20,
        'V' => 21, 'W' => 22, 'X' => 23, 'Y' => 24, 'Z' => 25,
    ];

    
    public static function fillMissingArrayIndexes($dataArray, $fillValue = '')
    {
        $existingIndexes = array_keys($dataArray);

        $newIndexes = array_fill_keys(range(0, max($existingIndexes)), $fillValue);
        $dataArray += $newIndexes;

        ksort($dataArray);

        return $dataArray;
    }

    
    public static function getColumnIndexFromCellIndex($cellIndex)
    {
        if (!self::isValidCellIndex($cellIndex)) {
            throw new InvalidArgumentException('Cannot get column index from an invalid cell index.');
        }

        $columnIndex = 0;

                $columnLetters = preg_replace('/\d/', '', $cellIndex);

                        $columnLength = isset($columnLetters[1]) ? (isset($columnLetters[2]) ? 3 : 2) : 1;

                        switch ($columnLength) {
            case 1:
                $columnIndex = (self::$columnLetterToIndexMapping[$columnLetters]);
                break;
            case 2:
                $firstLetterIndex = (self::$columnLetterToIndexMapping[$columnLetters[0]] + 1) * 26;
                $secondLetterIndex = self::$columnLetterToIndexMapping[$columnLetters[1]];
                $columnIndex = $firstLetterIndex + $secondLetterIndex;
                break;
            case 3:
                $firstLetterIndex = (self::$columnLetterToIndexMapping[$columnLetters[0]] + 1) * 676;
                $secondLetterIndex = (self::$columnLetterToIndexMapping[$columnLetters[1]] + 1) * 26;
                $thirdLetterIndex = self::$columnLetterToIndexMapping[$columnLetters[2]];
                $columnIndex = $firstLetterIndex + $secondLetterIndex + $thirdLetterIndex;
                break;
        }

        return $columnIndex;
    }

    
    protected static function isValidCellIndex($cellIndex)
    {
        return (preg_match('/^[A-Z]{1,3}\d+$/', $cellIndex) === 1);
    }
}
