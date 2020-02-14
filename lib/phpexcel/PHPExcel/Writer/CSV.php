<?php


class PHPExcel_Writer_CSV extends PHPExcel_Writer_Abstract implements PHPExcel_Writer_IWriter
{
    
    private $phpExcel;

    
    private $delimiter    = ',';

    
    private $enclosure    = '"';

    
    private $lineEnding    = PHP_EOL;

    
    private $sheetIndex    = 0;

    
    private $useBOM = false;

    
    private $excelCompatibility = false;

    
    public function __construct(PHPExcel $phpExcel)
    {
        $this->phpExcel    = $phpExcel;
    }

    
    public function save($pFilename = null)
    {
                $sheet = $this->phpExcel->getSheet($this->sheetIndex);

        $saveDebugLog = PHPExcel_Calculation::getInstance($this->phpExcel)->getDebugLog()->getWriteDebugLog();
        PHPExcel_Calculation::getInstance($this->phpExcel)->getDebugLog()->setWriteDebugLog(false);
        $saveArrayReturnType = PHPExcel_Calculation::getArrayReturnType();
        PHPExcel_Calculation::setArrayReturnType(PHPExcel_Calculation::RETURN_ARRAY_AS_VALUE);

                $fileHandle = fopen($pFilename, 'wb+');
        if ($fileHandle === false) {
            throw new PHPExcel_Writer_Exception("Could not open file $pFilename for writing.");
        }

        if ($this->excelCompatibility) {
            fwrite($fileHandle, "\xEF\xBB\xBF");                $this->setEnclosure('"');                            $this->setDelimiter(";");                            $this->setLineEnding("\r\n");
            fwrite($fileHandle, 'sep=' . $this->getDelimiter() . $this->lineEnding);
        } elseif ($this->useBOM) {
                        fwrite($fileHandle, "\xEF\xBB\xBF");
        }

                $maxCol = $sheet->getHighestDataColumn();
        $maxRow = $sheet->getHighestDataRow();

                for ($row = 1; $row <= $maxRow; ++$row) {
                        $cellsArray = $sheet->rangeToArray('A'.$row.':'.$maxCol.$row, '', $this->preCalculateFormulas);
                        $this->writeLine($fileHandle, $cellsArray[0]);
        }

                fclose($fileHandle);

        PHPExcel_Calculation::setArrayReturnType($saveArrayReturnType);
        PHPExcel_Calculation::getInstance($this->phpExcel)->getDebugLog()->setWriteDebugLog($saveDebugLog);
    }

    
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    
    public function setDelimiter($pValue = ',')
    {
        $this->delimiter = $pValue;
        return $this;
    }

    
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    
    public function setEnclosure($pValue = '"')
    {
        if ($pValue == '') {
            $pValue = null;
        }
        $this->enclosure = $pValue;
        return $this;
    }

    
    public function getLineEnding()
    {
        return $this->lineEnding;
    }

    
    public function setLineEnding($pValue = PHP_EOL)
    {
        $this->lineEnding = $pValue;
        return $this;
    }

    
    public function getUseBOM()
    {
        return $this->useBOM;
    }

    
    public function setUseBOM($pValue = false)
    {
        $this->useBOM = $pValue;
        return $this;
    }

    
    public function getExcelCompatibility()
    {
        return $this->excelCompatibility;
    }

    
    public function setExcelCompatibility($pValue = false)
    {
        $this->excelCompatibility = $pValue;
        return $this;
    }

    
    public function getSheetIndex()
    {
        return $this->sheetIndex;
    }

    
    public function setSheetIndex($pValue = 0)
    {
        $this->sheetIndex = $pValue;
        return $this;
    }

    
    private function writeLine($pFileHandle = null, $pValues = null)
    {
        if (is_array($pValues)) {
                        $writeDelimiter = false;

                        $line = '';

            foreach ($pValues as $element) {
                                $element = str_replace($this->enclosure, $this->enclosure . $this->enclosure, $element);

                                if ($writeDelimiter) {
                    $line .= $this->delimiter;
                } else {
                    $writeDelimiter = true;
                }

                                $line .= $this->enclosure . $element . $this->enclosure;
            }

                        $line .= $this->lineEnding;

                        fwrite($pFileHandle, $line);
        } else {
            throw new PHPExcel_Writer_Exception("Invalid data row passed to CSV writer.");
        }
    }
}
