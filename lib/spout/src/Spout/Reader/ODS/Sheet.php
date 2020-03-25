<?php

namespace Box\Spout\Reader\ODS;

use Box\Spout\Reader\SheetInterface;
use Box\Spout\Reader\Wrapper\XMLReader;


class Sheet implements SheetInterface
{
    
    protected $rowIterator;

    
    protected $id;

    
    protected $index;

    
    protected $name;

    
    public function __construct($xmlReader, $sheetIndex, $sheetName)
    {
        $this->rowIterator = new RowIterator($xmlReader);
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
