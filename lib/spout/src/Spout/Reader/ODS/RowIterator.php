<?php

namespace Box\Spout\Reader\ODS;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\Exception\IteratorNotRewindableException;
use Box\Spout\Reader\Exception\XMLProcessingException;
use Box\Spout\Reader\IteratorInterface;
use Box\Spout\Reader\ODS\Helper\CellValueFormatter;
use Box\Spout\Reader\Wrapper\XMLReader;


class RowIterator implements IteratorInterface
{
    
    const XML_NODE_TABLE = 'table:table';
    const XML_NODE_ROW = 'table:table-row';
    const XML_NODE_CELL = 'table:table-cell';
    const MAX_COLUMNS_EXCEL = 16384;

    
    const XML_ATTRIBUTE_NUM_COLUMNS_REPEATED = 'table:number-columns-repeated';

    
    protected $xmlReader;

    
    protected $cellValueFormatter;

    
    protected $hasAlreadyBeenRewound = false;

    
    protected $numReadRows = 0;

    
    protected $rowDataBuffer = null;

    
    protected $hasReachedEndOfFile = false;

    
    public function __construct($xmlReader)
    {
        $this->xmlReader = $xmlReader;
        $this->cellValueFormatter = new CellValueFormatter();
    }

    
    public function rewind()
    {
                                if ($this->hasAlreadyBeenRewound) {
            throw new IteratorNotRewindableException();
        }

        $this->hasAlreadyBeenRewound = true;
        $this->numReadRows = 0;
        $this->rowDataBuffer = null;
        $this->hasReachedEndOfFile = false;

        $this->next();
    }

    
    public function valid()
    {
        return (!$this->hasReachedEndOfFile);
    }

    
    public function next()
    {
        $rowData = [];
        $cellValue = null;
        $numColumnsRepeated = 1;
        $numCellsRead = 0;
        $hasAlreadyReadOneCell = false;

        try {
            while ($this->xmlReader->read()) {
                if ($this->xmlReader->isPositionedOnStartingNode(self::XML_NODE_CELL)) {
                                        $currentNumColumnsRepeated = $this->getNumColumnsRepeatedForCurrentNode();

                    $node = $this->xmlReader->expand();
                    $currentCellValue = $this->getCellValue($node);

                                        if ($hasAlreadyReadOneCell) {
                        for ($i = 0; $i < $numColumnsRepeated; $i++) {
                            $rowData[] = $cellValue;
                        }
                    }

                    $cellValue = $currentCellValue;
                    $numColumnsRepeated = $currentNumColumnsRepeated;

                    $numCellsRead++;
                    $hasAlreadyReadOneCell = true;

                } else if ($this->xmlReader->isPositionedOnEndingNode(self::XML_NODE_ROW)) {
                                        $isEmptyRow = ($numCellsRead <= 1 && $this->isEmptyCellValue($cellValue));
                    if ($isEmptyRow) {
                                                $this->next();
                        return;
                    }

                                                                                                                                            if ((count($rowData) + $numColumnsRepeated) !== self::MAX_COLUMNS_EXCEL) {
                        for ($i = 0; $i < $numColumnsRepeated; $i++) {
                            $rowData[] = $cellValue;
                        }
                        $this->numReadRows++;
                    }
                    break;

                } else if ($this->xmlReader->isPositionedOnEndingNode(self::XML_NODE_TABLE)) {
                                        $this->hasReachedEndOfFile = true;
                    break;
                }
            }

        } catch (XMLProcessingException $exception) {
            throw new IOException("The sheet's data cannot be read. [{$exception->getMessage()}]");
        }

        $this->rowDataBuffer = $rowData;
    }

    
    protected function getNumColumnsRepeatedForCurrentNode()
    {
        $numColumnsRepeated = $this->xmlReader->getAttribute(self::XML_ATTRIBUTE_NUM_COLUMNS_REPEATED);
        return ($numColumnsRepeated !== null) ? intval($numColumnsRepeated) : 1;
    }

    
    protected function getCellValue($node)
    {
        return $this->cellValueFormatter->extractAndFormatNodeValue($node);
    }

    
    protected function isEmptyCellValue($value)
    {
        return (!isset($value) || trim($value) === '');
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
