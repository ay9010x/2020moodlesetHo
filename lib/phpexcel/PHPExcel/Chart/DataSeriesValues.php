<?php


class PHPExcel_Chart_DataSeriesValues
{

    const DATASERIES_TYPE_STRING    = 'String';
    const DATASERIES_TYPE_NUMBER    = 'Number';

    private static $dataTypeValues = array(
        self::DATASERIES_TYPE_STRING,
        self::DATASERIES_TYPE_NUMBER,
    );

    
    private $dataType;

    
    private $dataSource;

    
    private $formatCode;

    
    private $pointMarker;

    
    private $pointCount = 0;

    
    private $dataValues = array();

    
    public function __construct($dataType = self::DATASERIES_TYPE_NUMBER, $dataSource = null, $formatCode = null, $pointCount = 0, $dataValues = array(), $marker = null)
    {
        $this->setDataType($dataType);
        $this->dataSource = $dataSource;
        $this->formatCode = $formatCode;
        $this->pointCount = $pointCount;
        $this->dataValues = $dataValues;
        $this->pointMarker = $marker;
    }

    
    public function getDataType()
    {
        return $this->dataType;
    }

    
    public function setDataType($dataType = self::DATASERIES_TYPE_NUMBER)
    {
        if (!in_array($dataType, self::$dataTypeValues)) {
            throw new PHPExcel_Chart_Exception('Invalid datatype for chart data series values');
        }
        $this->dataType = $dataType;

        return $this;
    }

    
    public function getDataSource()
    {
        return $this->dataSource;
    }

    
    public function setDataSource($dataSource = null, $refreshDataValues = true)
    {
        $this->dataSource = $dataSource;

        if ($refreshDataValues) {
                    }

        return $this;
    }

    
    public function getPointMarker()
    {
        return $this->pointMarker;
    }

    
    public function setPointMarker($marker = null)
    {
        $this->pointMarker = $marker;

        return $this;
    }

    
    public function getFormatCode()
    {
        return $this->formatCode;
    }

    
    public function setFormatCode($formatCode = null)
    {
        $this->formatCode = $formatCode;

        return $this;
    }

    
    public function getPointCount()
    {
        return $this->pointCount;
    }

    
    public function isMultiLevelSeries()
    {
        if (count($this->dataValues) > 0) {
            return is_array($this->dataValues[0]);
        }
        return null;
    }

    
    public function multiLevelCount()
    {
        $levelCount = 0;
        foreach ($this->dataValues as $dataValueSet) {
            $levelCount = max($levelCount, count($dataValueSet));
        }
        return $levelCount;
    }

    
    public function getDataValues()
    {
        return $this->dataValues;
    }

    
    public function getDataValue()
    {
        $count = count($this->dataValues);
        if ($count == 0) {
            return null;
        } elseif ($count == 1) {
            return $this->dataValues[0];
        }
        return $this->dataValues;
    }

    
    public function setDataValues($dataValues = array(), $refreshDataSource = true)
    {
        $this->dataValues = PHPExcel_Calculation_Functions::flattenArray($dataValues);
        $this->pointCount = count($dataValues);

        if ($refreshDataSource) {
                    }

        return $this;
    }

    private function stripNulls($var)
    {
        return $var !== null;
    }

    public function refresh(PHPExcel_Worksheet $worksheet, $flatten = true)
    {
        if ($this->dataSource !== null) {
            $calcEngine = PHPExcel_Calculation::getInstance($worksheet->getParent());
            $newDataValues = PHPExcel_Calculation::unwrapResult(
                $calcEngine->_calculateFormulaValue(
                    '='.$this->dataSource,
                    null,
                    $worksheet->getCell('A1')
                )
            );
            if ($flatten) {
                $this->dataValues = PHPExcel_Calculation_Functions::flattenArray($newDataValues);
                foreach ($this->dataValues as &$dataValue) {
                    if ((!empty($dataValue)) && ($dataValue[0] == '#')) {
                        $dataValue = 0.0;
                    }
                }
                unset($dataValue);
            } else {
                $cellRange = explode('!', $this->dataSource);
                if (count($cellRange) > 1) {
                    list(, $cellRange) = $cellRange;
                }

                $dimensions = PHPExcel_Cell::rangeDimension(str_replace('$', '', $cellRange));
                if (($dimensions[0] == 1) || ($dimensions[1] == 1)) {
                    $this->dataValues = PHPExcel_Calculation_Functions::flattenArray($newDataValues);
                } else {
                    $newArray = array_values(array_shift($newDataValues));
                    foreach ($newArray as $i => $newDataSet) {
                        $newArray[$i] = array($newDataSet);
                    }

                    foreach ($newDataValues as $newDataSet) {
                        $i = 0;
                        foreach ($newDataSet as $newDataVal) {
                            array_unshift($newArray[$i++], $newDataVal);
                        }
                    }
                    $this->dataValues = $newArray;
                }
            }
            $this->pointCount = count($this->dataValues);
        }
    }
}
