<?php

namespace Box\Spout\Reader\CSV;

use Box\Spout\Reader\SheetInterface;


class Sheet implements SheetInterface
{
    
    protected $rowIterator;

    
    public function __construct($filePointer, $fieldDelimiter, $fieldEnclosure, $encoding, $endOfLineCharacter, $globalFunctionsHelper)
    {
        $this->rowIterator = new RowIterator($filePointer, $fieldDelimiter, $fieldEnclosure, $encoding, $endOfLineCharacter, $globalFunctionsHelper);
    }

    
    public function getRowIterator()
    {
        return $this->rowIterator;
    }
}
