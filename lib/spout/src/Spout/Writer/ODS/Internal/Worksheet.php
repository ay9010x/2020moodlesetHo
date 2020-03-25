<?php

namespace Box\Spout\Writer\ODS\Internal;

use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Helper\StringHelper;
use Box\Spout\Writer\Common\Helper\CellHelper;
use Box\Spout\Writer\Common\Internal\WorksheetInterface;


class Worksheet implements WorksheetInterface
{
    
    protected $externalSheet;

    
    protected $worksheetFilePath;

    
    protected $stringsEscaper;

    
    protected $stringHelper;

    
    protected $sheetFilePointer;

    
    protected $maxNumColumns = 1;

    
    protected $lastWrittenRowIndex = 0;

    
    public function __construct($externalSheet, $worksheetFilesFolder)
    {
        $this->externalSheet = $externalSheet;
        
        $this->stringsEscaper = new \Box\Spout\Common\Escaper\ODS();
        $this->worksheetFilePath = $worksheetFilesFolder . '/sheet' . $externalSheet->getIndex() . '.xml';

        $this->stringHelper = new StringHelper();

        $this->startSheet();
    }

    
    protected function startSheet()
    {
        $this->sheetFilePointer = fopen($this->worksheetFilePath, 'w');
        $this->throwIfSheetFilePointerIsNotAvailable();
    }

    
    protected function throwIfSheetFilePointerIsNotAvailable()
    {
        if (!$this->sheetFilePointer) {
            throw new IOException('Unable to open sheet for writing.');
        }
    }

    
    public function getWorksheetFilePath()
    {
        return $this->worksheetFilePath;
    }

    
    public function getTableElementStartAsString()
    {
        $escapedSheetName = $this->stringsEscaper->escape($this->externalSheet->getName());
        $tableStyleName = 'ta' . ($this->externalSheet->getIndex() + 1);

        $tableElement  = '<table:table table:style-name="' . $tableStyleName . '" table:name="' . $escapedSheetName . '">';
        $tableElement .= '<table:table-column table:default-cell-style-name="ce1" table:style-name="co1" table:number-columns-repeated="' . $this->maxNumColumns . '"/>';

        return $tableElement;
    }

    
    public function getExternalSheet()
    {
        return $this->externalSheet;
    }

    
    public function getLastWrittenRowIndex()
    {
        return $this->lastWrittenRowIndex;
    }

    
    public function addRow($dataRow, $style)
    {
        $styleIndex = ($style->getId() + 1);         $cellsCount = count($dataRow);
        $this->maxNumColumns = max($this->maxNumColumns, $cellsCount);

        $data = '<table:table-row table:style-name="ro1">';

        $currentCellIndex = 0;
        $nextCellIndex = 1;

        for ($i = 0; $i < $cellsCount; $i++) {
            $currentCellValue = $dataRow[$currentCellIndex];

                        if (!isset($dataRow[$nextCellIndex]) || $currentCellValue !== $dataRow[$nextCellIndex]) {
                $numTimesValueRepeated = ($nextCellIndex - $currentCellIndex);
                $data .= $this->getCellContent($currentCellValue, $styleIndex, $numTimesValueRepeated);

                $currentCellIndex = $nextCellIndex;
            }

            $nextCellIndex++;
        }

        $data .= '</table:table-row>';

        $wasWriteSuccessful = fwrite($this->sheetFilePointer, $data);
        if ($wasWriteSuccessful === false) {
            throw new IOException("Unable to write data in {$this->worksheetFilePath}");
        }

                $this->lastWrittenRowIndex++;
    }

    
    protected function getCellContent($cellValue, $styleIndex, $numTimesValueRepeated)
    {
        $data = '<table:table-cell table:style-name="ce' . $styleIndex . '"';

        if ($numTimesValueRepeated !== 1) {
            $data .= ' table:number-columns-repeated="' . $numTimesValueRepeated . '"';
        }

        if (CellHelper::isNonEmptyString($cellValue)) {
            $data .= ' office:value-type="string" calcext:value-type="string">';

            $cellValueLines = explode("\n", $cellValue);
            foreach ($cellValueLines as $cellValueLine) {
                $data .= '<text:p>' . $this->stringsEscaper->escape($cellValueLine) . '</text:p>';
            }

            $data .= '</table:table-cell>';
        } else if (CellHelper::isBoolean($cellValue)) {
            $data .= ' office:value-type="boolean" calcext:value-type="boolean" office:boolean-value="' . $cellValue . '">';
            $data .= '<text:p>' . $cellValue . '</text:p>';
            $data .= '</table:table-cell>';
        } else if (CellHelper::isNumeric($cellValue)) {
            $data .= ' office:value-type="float" calcext:value-type="float" office:value="' . $cellValue . '">';
            $data .= '<text:p>' . $cellValue . '</text:p>';
            $data .= '</table:table-cell>';
        } else if (empty($cellValue)) {
            $data .= '/>';
        } else {
            throw new InvalidArgumentException('Trying to add a value with an unsupported type: ' . gettype($cellValue));
        }

        return $data;
    }

    
    public function close()
    {
        fclose($this->sheetFilePointer);
    }
}
