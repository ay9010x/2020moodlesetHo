<?php


class PHPExcel_Worksheet_RowIterator implements Iterator
{
    
    private $subject;

    
    private $position = 1;

    
    private $startRow = 1;


    
    private $endRow = 1;


    
    public function __construct(PHPExcel_Worksheet $subject = null, $startRow = 1, $endRow = null)
    {
                $this->subject = $subject;
        $this->resetEnd($endRow);
        $this->resetStart($startRow);
    }

    
    public function __destruct()
    {
        unset($this->subject);
    }

    
    public function resetStart($startRow = 1)
    {
        $this->startRow = $startRow;
        $this->seek($startRow);

        return $this;
    }

    
    public function resetEnd($endRow = null)
    {
        $this->endRow = ($endRow) ? $endRow : $this->subject->getHighestRow();

        return $this;
    }

    
    public function seek($row = 1)
    {
        if (($row < $this->startRow) || ($row > $this->endRow)) {
            throw new PHPExcel_Exception("Row $row is out of range ({$this->startRow} - {$this->endRow})");
        }
        $this->position = $row;

        return $this;
    }

    
    public function rewind()
    {
        $this->position = $this->startRow;
    }

    
    public function current()
    {
        return new PHPExcel_Worksheet_Row($this->subject, $this->position);
    }

    
    public function key()
    {
        return $this->position;
    }

    
    public function next()
    {
        ++$this->position;
    }

    
    public function prev()
    {
        if ($this->position <= $this->startRow) {
            throw new PHPExcel_Exception("Row is already at the beginning of range ({$this->startRow} - {$this->endRow})");
        }

        --$this->position;
    }

    
    public function valid()
    {
        return $this->position <= $this->endRow;
    }
}
