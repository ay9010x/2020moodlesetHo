<?php

class Horde_Array
{
    
    static public function arraySort(array &$array, $key = null, $dir = 0,
                                     $assoc = true)
    {
        
        if (empty($array)) {
            return;
        }

        
        if (is_null($key)) {
            $keys = array_keys(reset($array));
            $key = array_shift($keys);
        }

        
        $helper = new Horde_Array_Sort_Helper();
        $helper->key = $key;
        $function = $dir ? 'reverseCompare' : 'compare';
        if ($assoc) {
            uasort($array, array($helper, $function));
        } else {
            usort($array, array($helper, $function));
        }
    }

    
    static public function getArrayParts($field, &$base, &$keys)
    {
        if (!preg_match('|([^\[]*)((\[[^\[\]]*\])+)|', $field, $matches)) {
            return false;
        }

        $base = $matches[1];
        $keys = explode('][', $matches[2]);
        $keys[0] = substr($keys[0], 1);
        $keys[count($keys) - 1] = substr($keys[count($keys) - 1], 0, strlen($keys[count($keys) - 1]) - 1);
        return true;
    }

    
    static public function getElement(&$array, array &$keys, $value = null)
    {
        if (count($keys)) {
            $key = array_shift($keys);
            return isset($array[$key])
                ? self::getElement($array[$key], $keys, $value)
                : false;
        }

        if (!is_null($value)) {
            $array = $value;
        }

        return $array;
    }

    
    static public function getRectangle(array $array, $row, $col, $height,
                                        $width)
    {
        $rec = array();
        for ($y = $row; $y < $row + $height; $y++) {
            $rec[] = array_slice($array[$y], $col, $width);
        }
        return $rec;
    }

    
    static public function valuesToKeys(array $array)
    {
        return $array
            ? array_combine($array, $array)
            : array();
    }
}
