<?php

namespace Box\Spout\Reader\XLSX;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\Exception\XMLProcessingException;
use Box\Spout\Reader\IteratorInterface;
use Box\Spout\Reader\Wrapper\XMLReader;
use Box\Spout\Reader\XLSX\Helper\CellHelper;
use Box\Spout\Reader\XLSX\Helper\CellValueFormatter;
use Box\Spout\Reader\XLSX\Helper\StyleHelper;


class RowIterator implements IteratorInterface
{
    
    const XML_NODE_DIMENSION = 'dimension';
    const XML_NODE_WORKSHEET = 'worksheet';
    const XML_NODE_ROW = 'row';
    const XML_NODE_CELL = 'c';

    
    const XML_ATTRIBUTE_REF = 'ref';
    const XML_ATTRIBUTE_SPANS = 'spans';
    const XML_ATTRIBUTE_CELL_INDEX = 'r';

    
    protected $filePath;

    
    protected $sheetDataXMLFilePath;

    
    protected $xmlReader;

    
    protected $cellValueFormatter;

    
    protected $styleHelper;

    
    protected $numReadRows = 0;

    
    protected $rowDataBuffer = null;

    
    protected $hasReachedEndOfFile = false;

    
    protected $numColumns = 0;

    
    public function __construct($filePath, $sheetDataXMLFilePath, $sharedStringsHelper)
    {
        $this->filePath = $filePath;
        $this->sheetDataXMLFilePath = $this->normalizeSheetDataXMLFilePath($sheetDataXMLFilePath);

        $this->xmlReader = new XMLReader();

        $this->styleHelper = new StyleHelper($filePath);
        $this->cellValueFormatter = new CellValueFormatter($sharedStringsHelper, $this->styleHelper);
    }

    
    protected function normalizeSheetDataXMLFilePath($sheetDataXMLFilePath)
    {
        return ltrim($sheetDataXMLFilePath, '/');
    }

    
    public function rewind()
    {
        $this->xmlReader->close();

        $sheetDataFilePath = 'zip://' . $this->filePath . '#' . $this->sheetDataXMLFilePath;
        if ($this->xmlReader->open($sheetDataFilePath) === false) {
            throw new IOException("Could not open \"{$this->sheetDataXMLFilePath}\".");
        }

        $this->numReadRows = 0;
        $this->rowDataBuffer = null;
        $this->hasReachedEndOfFile = false;
        $this->numColumns = 0;

        $this->next();
    }

    
    public function valid()
    {
        return (!$this->hasReachedEndOfFile);
    }

    
    public function next()
    {
        $rowData = [];

        try {
            while ($this->xmlReader->read()) {
                if ($this->xmlReader->isPositionedOnStartingNode(self::XML_NODE_DIMENSION)) {
                                        $dimensionRef = $this->xmlReader->getAttribute(self::XML_ATTRIBUTE_REF);                     if (preg_match('/[A-Z\d]+:([A-Z\d]+)/', $dimensionRef, $matches)) {
                        $lastCellIndex = $matches[1];
                        $this->numColumns = CellHelper::getColumnIndexFromCellIndex($lastCellIndex) + 1;
                    }

                } else if ($this->xmlReader->isPositionedOnStartingNode(self::XML_NODE_ROW)) {
                    
                                        $numberOfColumnsForRow = $this->numColumns;
                    $spans = $this->xmlReader->getAttribute(self::XML_ATTRIBUTE_SPANS);                     if ($spans) {
                        list(, $numberOfColumnsForRow) = explode(':', $spans);
                        $numberOfColumnsForRow = intval($numberOfColumnsForRow);
                    }
                    $rowData = ($numberOfColumnsForRow !== 0) ? array_fill(0, $numberOfColumnsForRow, '') : [];

                } else if ($this->xmlReader->isPositionedOnStartingNode(self::XML_NODE_CELL)) {
                                        $currentCellIndex = $this->xmlReader->getAttribute(self::XML_ATTRIBUTE_CELL_INDEX);
                    $currentColumnIndex = CellHelper::getColumnIndexFromCellIndex($currentCellIndex);

                    $node = $this->xmlReader->expand();
                    $rowData[$currentColumnIndex] = $this->getCellValue($node);

                } else if ($this->xmlReader->isPositionedOnEndingNode(self::XML_NODE_ROW)) {
                                                            $rowData = ($this->numColumns !== 0) ? $rowData : CellHelper::fillMissingArrayIndexes($rowData);
                    $this->numReadRows++;
                    break;

                } else if ($this->xmlReader->isPositionedOnEndingNode(self::XML_NODE_WORKSHEET)) {
                                        $this->hasReachedEndOfFile = true;
                    break;
                }
            }

        } catch (XMLProcessingException $exception) {
            throw new IOException("The {$this->sheetDataXMLFilePath} file cannot be read. [{$exception->getMessage()}]");
        }

        $this->rowDataBuffer = $rowData;
    }

    
    protected function getCellValue($node)
    {
        return $this->cellValueFormatter->extractAndFormatNodeValue($node);
    }

    
    public function current()
    {
        return $this->rowDataBuffer;
    }

    
    public function key()
    {
        return $this->numReadRows;
    }


    
    public function end()
    {
        $this->xmlReader->close();
    }
}
