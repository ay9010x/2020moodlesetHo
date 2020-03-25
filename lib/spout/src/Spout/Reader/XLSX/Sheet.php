<?php

namespace Box\Spout\Reader\XLSX;

use Box\Spout\Reader\SheetInterface;


class Sheet implements SheetInterface
{
    
    protected $rowIterator;

    
    protected $index;

    
    protected $name;

    
    public function __construct($filePath, $sheetDataXMLFilePath, $sharedStringsHelper, $sheetIndex, $sheetName)
    {
        $this->rowIterator = new RowIterator($filePath, $sheetDataXMLFilePath, $sharedStringsHelper);
        $this->index = $sheetIndex;
        $this->name = $sheetName;
    }

    
    public function getRowIterator()
    {
        return $this->rowIterator;
    }

    
    public function getIndex()
    {
        return $this->index;
    }

    
    public function getName()
    {
        return $this->name;
    }
}
