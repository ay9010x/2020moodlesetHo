<?php


class PHPExcel_Worksheet_ColumnCellIterator extends PHPExcel_Worksheet_CellIterator implements Iterator
{
    
    protected $columnIndex;

    
    protected $startRow = 1;

    
    protected $endRow = 1;

    
    public function __construct(PHPExcel_Worksheet $subject = null, $columnIndex = 'A', $startRow = 1, $endRow = null)
    {
                $this->subject = $subject;
        $this->columnIndex = PHPExcel_Cell::columnIndexFromString($columnIndex) - 1;
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
        $this->adjustForExistingOnlyRange();
        $this->seek($startRow);

        return $this;
    }

    
    public function resetEnd($endRow = null)
    {
        $this->endRow = ($endRow) ? $endRow : $this->subject->getHighestRow();
        $this->adjustForExistingOnlyRange();

        return $this;
    }

    
    public function seek($row = 1)
    {
        if (($row < $this->startRow) || ($row > $this->endRow)) {
            throw new PHPExcel_Exception("Row $row is out of range ({$this->startRow} - {$this->endRow})");
        } elseif ($this->onlyExistingCells && !($this->subject->cellExistsByColumnAndRow($this->columnIndex, $row))) {
            throw new PHPExcel_Exception('In "IterateOnlyExistingCells" mode and Cell does not exist');
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
        return $this->subject->getCellByColumnAndRow($this->columnIndex, $this->position);
    }

    
    public function key()
    {
        return $this->position;
    }

    
    public function next()
    {
        do {
            ++$this->position;
        } while (($this->onlyExistingCells) &&
            (!$this->subject->cellExistsByColumnAndRow($this->columnIndex, $this->position)) &&
            ($this->position <= $this->endRow));
    }

    
    public function prev()
    {
        if ($this->position <= $this->startRow) {
            throw new PHPExcel_Exception("Row is already at the beginning of range ({$this->startRow} - {$this->endRow})");
        }

        do {
            --$this->position;
        } while (($this->onlyExistingCells) &&
            (!$this->subject->cellExistsByColumnAndRow($this->columnIndex, $this->position)) &&
            ($this->position >= $this->startRow));
    }

    
    public function valid()
    {
        return $this->position <= $this->endRow;
    }

    
    protected function adjustForExistingOnlyRange()
    {
        if ($this->onlyExistingCells) {
            while ((!$this->subject->cellExistsByColumnAndRow($this->columnIndex, $this->startRow)) &&
                ($this->startRow <= $this->endRow)) {
                ++$this->startRow;
            }
            if ($this->startRow > $this->endRow) {
                throw new PHPExcel_Exception('No cells exist within the specified range');
            }
            while ((!$this->subject->cellExistsByColumnAndRow($this->columnIndex, $this->endRow)) &&
                ($this->endRow >= $this->startRow)) {
                --$this->endRow;
            }
            if ($this->endRow < $this->startRow) {
                throw new PHPExcel_Exception('No cells exist within the specified range');
            }
        }
    }
}
