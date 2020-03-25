<?php


abstract class PHPExcel_Worksheet_CellIterator
{
    
    protected $subject;

    
    protected $position = null;

    
    protected $onlyExistingCells = false;

    
    public function __destruct()
    {
        unset($this->subject);
    }

    
    public function getIterateOnlyExistingCells()
    {
        return $this->onlyExistingCells;
    }

    
    abstract protected function adjustForExistingOnlyRange();

    
    public function setIterateOnlyExistingCells($value = true)
    {
        $this->onlyExistingCells = (boolean) $value;

        $this->adjustForExistingOnlyRange();
    }
}
