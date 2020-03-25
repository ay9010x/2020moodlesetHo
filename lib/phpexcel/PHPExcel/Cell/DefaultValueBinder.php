<?php


if (!defined('PHPEXCEL_ROOT')) {
    
    define('PHPEXCEL_ROOT', dirname(__FILE__) . '/../../');
    require(PHPEXCEL_ROOT . 'PHPExcel/Autoloader.php');
}


class PHPExcel_Cell_DefaultValueBinder implements PHPExcel_Cell_IValueBinder
{
    
    public function bindValue(PHPExcel_Cell $cell, $value = null)
    {
                if (is_string($value)) {
            $value = PHPExcel_Shared_String::SanitizeUTF8($value);
        } elseif (is_object($value)) {
                        if ($value instanceof DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            } elseif (!($value instanceof PHPExcel_RichText)) {
                $value = (string) $value;
            }
        }

                $cell->setValueExplicit($value, self::dataTypeForValue($value));

                return true;
    }

    
    public static function dataTypeForValue($pValue = null)
    {
                if ($pValue === null) {
            return PHPExcel_Cell_DataType::TYPE_NULL;
        } elseif ($pValue === '') {
            return PHPExcel_Cell_DataType::TYPE_STRING;
        } elseif ($pValue instanceof PHPExcel_RichText) {
            return PHPExcel_Cell_DataType::TYPE_INLINE;
        } elseif ($pValue{0} === '=' && strlen($pValue) > 1) {
            return PHPExcel_Cell_DataType::TYPE_FORMULA;
        } elseif (is_bool($pValue)) {
            return PHPExcel_Cell_DataType::TYPE_BOOL;
        } elseif (is_float($pValue) || is_int($pValue)) {
            return PHPExcel_Cell_DataType::TYPE_NUMERIC;
        } elseif (preg_match('/^[\+\-]?([0-9]+\\.?[0-9]*|[0-9]*\\.?[0-9]+)([Ee][\-\+]?[0-2]?\d{1,3})?$/', $pValue)) {
            $tValue = ltrim($pValue, '+-');
            if (is_string($pValue) && $tValue{0} === '0' && strlen($tValue) > 1 && $tValue{1} !== '.') {
                return PHPExcel_Cell_DataType::TYPE_STRING;
            } elseif ((strpos($pValue, '.') === false) && ($pValue > PHP_INT_MAX)) {
                return PHPExcel_Cell_DataType::TYPE_STRING;
            }
            return PHPExcel_Cell_DataType::TYPE_NUMERIC;
        } elseif (is_string($pValue) && array_key_exists($pValue, PHPExcel_Cell_DataType::getErrorCodes())) {
            return PHPExcel_Cell_DataType::TYPE_ERROR;
        }

        return PHPExcel_Cell_DataType::TYPE_STRING;
    }
}
