<?php


if (!defined('PHPEXCEL_ROOT')) {
    
    define('PHPEXCEL_ROOT', dirname(__FILE__) . '/../../');
    require(PHPEXCEL_ROOT . 'PHPExcel/Autoloader.php');
}


class PHPExcel_Calculation_Database
{
    
    private static function fieldExtract($database, $field)
    {
        $field = strtoupper(PHPExcel_Calculation_Functions::flattenSingleValue($field));
        $fieldNames = array_map('strtoupper', array_shift($database));

        if (is_numeric($field)) {
            $keys = array_keys($fieldNames);
            return $keys[$field-1];
        }
        $key = array_search($field, $fieldNames);
        return ($key) ? $key : null;
    }

    
    private static function filter($database, $criteria)
    {
        $fieldNames = array_shift($database);
        $criteriaNames = array_shift($criteria);

                $testConditions = $testValues = array();
        $testConditionsCount = 0;
        foreach ($criteriaNames as $key => $criteriaName) {
            $testCondition = array();
            $testConditionCount = 0;
            foreach ($criteria as $row => $criterion) {
                if ($criterion[$key] > '') {
                    $testCondition[] = '[:'.$criteriaName.']'.PHPExcel_Calculation_Functions::ifCondition($criterion[$key]);
                    $testConditionCount++;
                }
            }
            if ($testConditionCount > 1) {
                $testConditions[] = 'OR(' . implode(',', $testCondition) . ')';
                $testConditionsCount++;
            } elseif ($testConditionCount == 1) {
                $testConditions[] = $testCondition[0];
                $testConditionsCount++;
            }
        }

        if ($testConditionsCount > 1) {
            $testConditionSet = 'AND(' . implode(',', $testConditions) . ')';
        } elseif ($testConditionsCount == 1) {
            $testConditionSet = $testConditions[0];
        }

                foreach ($database as $dataRow => $dataValues) {
                        $testConditionList = $testConditionSet;
            foreach ($criteriaNames as $key => $criteriaName) {
                $k = array_search($criteriaName, $fieldNames);
                if (isset($dataValues[$k])) {
                    $dataValue = $dataValues[$k];
                    $dataValue = (is_string($dataValue)) ? PHPExcel_Calculation::wrapResult(strtoupper($dataValue)) : $dataValue;
                    $testConditionList = str_replace('[:' . $criteriaName . ']', $dataValue, $testConditionList);
                }
            }
                        $result = PHPExcel_Calculation::getInstance()->_calculateFormulaValue('='.$testConditionList);
                        if (!$result) {
                unset($database[$dataRow]);
            }
        }

        return $database;
    }


    private static function getFilteredColumn($database, $field, $criteria)
    {
                $database = self::filter($database, $criteria);
                $colData = array();
        foreach ($database as $row) {
            $colData[] = $row[$field];
        }
        
        return $colData;
    }

    
    public static function DAVERAGE($database, $field, $criteria)
    {
        $field = self::fieldExtract($database, $field);
        if (is_null($field)) {
            return null;
        }

                return PHPExcel_Calculation_Statistical::AVERAGE(
            self::getFilteredColumn($database, $field, $criteria)
        );
    }


    
    public static function DCOUNT($database, $field, $criteria)
    {
        $field = self::fieldExtract($database, $field);
        if (is_null($field)) {
            return null;
        }

                return PHPExcel_Calculation_Statistical::COUNT(
            self::getFilteredColumn($database, $field, $criteria)
        );
    }


    
    public static function DCOUNTA($database, $field, $criteria)
    {
        $field = self::fieldExtract($database, $field);
        if (is_null($field)) {
            return null;
        }

                $database = self::filter($database, $criteria);
                $colData = array();
        foreach ($database as $row) {
            $colData[] = $row[$field];
        }

                return PHPExcel_Calculation_Statistical::COUNTA(
            self::getFilteredColumn($database, $field, $criteria)
        );
    }


    
    public static function DGET($database, $field, $criteria)
    {
        $field = self::fieldExtract($database, $field);
        if (is_null($field)) {
            return null;
        }

                $colData = self::getFilteredColumn($database, $field, $criteria);
        if (count($colData) > 1) {
            return PHPExcel_Calculation_Functions::NaN();
        }

        return $colData[0];
    }


    
    public static function DMAX($database, $field, $criteria)
    {
        $field = self::fieldExtract($database, $field);
        if (is_null($field)) {
            return null;
        }

                return PHPExcel_Calculation_Statistical::MAX(
            self::getFilteredColumn($database, $field, $criteria)
        );
    }


    
    public static function DMIN($database, $field, $criteria)
    {
        $field = self::fieldExtract($database, $field);
        if (is_null($field)) {
            return null;
        }

                return PHPExcel_Calculation_Statistical::MIN(
            self::getFilteredColumn($database, $field, $criteria)
        );
    }


    
    public static function DPRODUCT($database, $field, $criteria)
    {
        $field = self::fieldExtract($database, $field);
        if (is_null($field)) {
            return null;
        }

                return PHPExcel_Calculation_MathTrig::PRODUCT(
            self::getFilteredColumn($database, $field, $criteria)
        );
    }


    
    public static function DSTDEV($database, $field, $criteria)
    {
        $field = self::fieldExtract($database, $field);
        if (is_null($field)) {
            return null;
        }

                return PHPExcel_Calculation_Statistical::STDEV(
            self::getFilteredColumn($database, $field, $criteria)
        );
    }


    
    public static function DSTDEVP($database, $field, $criteria)
    {
        $field = self::fieldExtract($database, $field);
        if (is_null($field)) {
            return null;
        }

                return PHPExcel_Calculation_Statistical::STDEVP(
            self::getFilteredColumn($database, $field, $criteria)
        );
    }


    
    public static function DSUM($database, $field, $criteria)
    {
        $field = self::fieldExtract($database, $field);
        if (is_null($field)) {
            return null;
        }

                return PHPExcel_Calculation_MathTrig::SUM(
            self::getFilteredColumn($database, $field, $criteria)
        );
    }


    
    public static function DVAR($database, $field, $criteria)
    {
        $field = self::fieldExtract($database, $field);
        if (is_null($field)) {
            return null;
        }

                return PHPExcel_Calculation_Statistical::VARFunc(
            self::getFilteredColumn($database, $field, $criteria)
        );
    }


    
    public static function DVARP($database, $field, $criteria)
    {
        $field = self::fieldExtract($database, $field);
        if (is_null($field)) {
            return null;
        }

                return PHPExcel_Calculation_Statistical::VARP(
            self::getFilteredColumn($database, $field, $criteria)
        );
    }
}
