<?php

namespace Box\Spout\Writer\XLSX\Internal;

use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Writer\Common\Helper\CellHelper;
use Box\Spout\Writer\Common\Internal\WorksheetInterface;


class Worksheet implements WorksheetInterface
{
    const SHEET_XML_FILE_HEADER = <<<EOD
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
EOD;

    
    protected $externalSheet;

    
    protected $worksheetFilePath;

    
    protected $sharedStringsHelper;

    
    protected $shouldUseInlineStrings;

    
    protected $stringsEscaper;

    
    protected $sheetFilePointer;

    
    protected $lastWrittenRowIndex = 0;

    
    public function __construct($externalSheet, $worksheetFilesFolder, $sharedStringsHelper, $shouldUseInlineStrings)
    {
        $this->externalSheet = $externalSheet;
        $this->sharedStringsHelper = $sharedStringsHelper;
        $this->shouldUseInlineStrings = $shouldUseInlineStrings;

        
        $this->stringsEscaper = new \Box\Spout\Common\Escaper\XLSX();

        $this->worksheetFilePath = $worksheetFilesFolder . '/' . strtolower($this->externalSheet->getName()) . '.xml';
        $this->startSheet();
    }

    
    protected function startSheet()
    {
        $this->sheetFilePointer = fopen($this->worksheetFilePath, 'w');
        $this->throwIfSheetFilePointerIsNotAvailable();

        fwrite($this->sheetFilePointer, self::SHEET_XML_FILE_HEADER);
        fwrite($this->sheetFilePointer, '<sheetData>');
    }

    
    protected function throwIfSheetFilePointerIsNotAvailable()
    {
        if (!$this->sheetFilePointer) {
            throw new IOException('Unable to open sheet for writing.');
        }
    }

    
    public function getExternalSheet()
    {
        return $this->externalSheet;
    }

    
    public function getLastWrittenRowIndex()
    {
        return $this->lastWrittenRowIndex;
    }

    
    public function getId()
    {
                return $this->externalSheet->getIndex() + 1;
    }

    
    public function addRow($dataRow, $style)
    {
        $cellNumber = 0;
        $rowIndex = $this->lastWrittenRowIndex + 1;
        $numCells = count($dataRow);

        $rowXML = '<row r="' . $rowIndex . '" spans="1:' . $numCells . '">';

        foreach($dataRow as $cellValue) {
            $columnIndex = CellHelper::getCellIndexFromColumnIndex($cellNumber);
            $cellXML = '<c r="' . $columnIndex . $rowIndex . '"';
            $cellXML .= ' s="' . $style->getId() . '"';

            if (CellHelper::isNonEmptyString($cellValue)) {
                if ($this->shouldUseInlineStrings) {
                    $cellXML .= ' t="inlineStr"><is><t>' . $this->stringsEscaper->escape($cellValue) . '</t></is></c>';
                } else {
                    $sharedStringId = $this->sharedStringsHelper->writeString($cellValue);
                    $cellXML .= ' t="s"><v>' . $sharedStringId . '</v></c>';
                }
            } else if (CellHelper::isBoolean($cellValue)) {
                    $cellXML .= ' t="b"><v>' . intval($cellValue) . '</v></c>';
            } else if (CellHelper::isNumeric($cellValue)) {
                $cellXML .= '><v>' . $cellValue . '</v></c>';
            } else if (empty($cellValue)) {
                                $cellXML = '';
            } else {
                throw new InvalidArgumentException('Trying to add a value with an unsupported type: ' . gettype($cellValue));
            }

            $rowXML .= $cellXML;
            $cellNumber++;
        }

        $rowXML .= '</row>';

        $wasWriteSuccessful = fwrite($this->sheetFilePointer, $rowXML);
        if ($wasWriteSuccessful === false) {
            throw new IOException("Unable to write data in {$this->worksheetFilePath}");
        }

                $this->lastWrittenRowIndex++;
    }

    
    public function close()
    {
        fwrite($this->sheetFilePointer, '</sheetData>');
        fwrite($this->sheetFilePointer, '</worksheet>');
        fclose($this->sheetFilePointer);
    }
}
