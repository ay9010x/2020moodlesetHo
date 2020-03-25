<?php


class PHPExcel_Worksheet_ColumnIterator implements Iterator
{
    
    private $subject;

    
    private $position = 0;

    
    private $startColumn = 0;


    
    private $endColumn = 0;


    
    public function __construct(PHPExcel_Worksheet $subject = null, $startColumn = 'A', $endColumn = null)
    {
                $this->subject = $subject;
        $this->resetEnd($endColumn);
        $this->resetStart($startColumn);
    }

    
    public function __destruct()
    {
        unset($this->subject);
    }

    
    public function resetStart($startColumn = 'A')
    {
        $startColumnIndex = PHPExcel_Cell::columnIndexFromString($startColumn) - 1;
        $this->startColumn = $startColumnIndex;
        $this->seek($startColumn);

        return $this;
    }

    
    public function resetEnd($endColumn = null)
    {
        $endColumn = ($endColumn) ? $endColumn : $this->subject->getHighestColumn();
        $this->endColumn = PHPExcel_Cell::columnIndexFromString($endColumn) - 1;

        return $this;
    }

    
    public function seek($column = 'A')
    {
        $column = PHPExcel_Cell::columnIndexFromString($column) - 1;
        if (($column < $this->startColumn) || ($column > $this->endColumn)) {
            throw new PHPExcel_Exception("Column $column is out of range ({$this->startColumn} - {$this->endColumn})");
        }
        $this->position = $column;

        return $this;
    }

    
    public function rewind()
    {
        $this->position = $this->startColumn;
    }

    
    public function current()
    {
        return new PHPExcel_Worksheet_Column($this->subject, PHPExcel_Cell::stringFromColumnIndex($this->position));
    }

    
    public function key()
    {
        return PHPExcel_Cell::stringFromColumnIndex($this->position);
    }

    
    public function next()
    {
        ++$this->position;
    }

    
    public function prev()
    {
        if ($this->position <= $this->startColumn) {
            throw new PHPExcel_Exception(
                "Column is already at the beginning of range (" .
                PHPExcel_Cell::stringFromColumnIndex($this->endColumn) . " - " .
                PHPExcel_Cell::stringFromColumnIndex($this->endColumn) . ")"
            );
        }

        --$this->position;
    }

    
    public function valid()
    {
        return $this->position <= $this->endColumn;
    }
}
