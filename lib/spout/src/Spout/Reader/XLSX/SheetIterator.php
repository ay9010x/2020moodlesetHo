<?php

namespace Box\Spout\Reader\XLSX;

use Box\Spout\Reader\IteratorInterface;
use Box\Spout\Reader\XLSX\Helper\SheetHelper;
use Box\Spout\Reader\Exception\NoSheetsFoundException;


class SheetIterator implements IteratorInterface
{
    
    protected $sheets;

    
    protected $currentSheetIndex;

    
    public function __construct($filePath, $sharedStringsHelper, $globalFunctionsHelper)
    {
                $sheetHelper = new SheetHelper($filePath, $sharedStringsHelper, $globalFunctionsHelper);
        $this->sheets = $sheetHelper->getSheets();

        if (count($this->sheets) === 0) {
            throw new NoSheetsFoundException('The file must contain at least one sheet.');
        }
    }

    
    public function rewind()
    {
        $this->currentSheetIndex = 0;
    }

    
    public function valid()
    {
        return ($this->currentSheetIndex < count($this->sheets));
    }

    
    public function next()
    {
                if (isset($this->sheets[$this->currentSheetIndex])) {
            $currentSheet = $this->sheets[$this->currentSheetIndex];
            $currentSheet->getRowIterator()->end();

            $this->currentSheetIndex++;
        }
    }

    
    public function current()
    {
        return $this->sheets[$this->currentSheetIndex];
    }

    
    public function key()
    {
        return $this->currentSheetIndex + 1;
    }

    
    public function end()
    {
                foreach ($this->sheets as $sheet) {
            $sheet->getRowIterator()->end();
        }
    }
}
