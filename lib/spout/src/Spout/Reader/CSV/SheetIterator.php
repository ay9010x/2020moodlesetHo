<?php

namespace Box\Spout\Reader\CSV;

use Box\Spout\Reader\IteratorInterface;


class SheetIterator implements IteratorInterface
{
    
    protected $sheet;

    
    protected $hasReadUniqueSheet = false;

    
    public function __construct($filePointer, $fieldDelimiter, $fieldEnclosure, $encoding, $endOfLineCharacter, $globalFunctionsHelper)
    {
        $this->sheet = new Sheet($filePointer, $fieldDelimiter, $fieldEnclosure, $encoding, $endOfLineCharacter, $globalFunctionsHelper);
    }

    
    public function rewind()
    {
        $this->hasReadUniqueSheet = false;
    }

    
    public function valid()
    {
        return (!$this->hasReadUniqueSheet);
    }

    
    public function next()
    {
        $this->hasReadUniqueSheet = true;
    }

    
    public function current()
    {
        return $this->sheet;
    }

    
    public function key()
    {
        return 1;
    }

    
    public function end()
    {
            }
}
