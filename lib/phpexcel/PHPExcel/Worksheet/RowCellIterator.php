<?php


class PHPExcel_Worksheet_RowCellIterator extends PHPExcel_Worksheet_CellIterator implements Iterator
{
    
    protected $rowIndex;

    
    protected $startColumn = 0;

    
    protected $endColumn = 0;

    
    public function __construct(PHPExcel_Worksheet $subject = null, $rowIndex = 1, $startColumn = 'A', $endColumn = null)
    {
                $this->subject = $subject;
        $this->rowIndex = $rowIndex;
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
        $this->adjustForExistingOnlyRange();
        $this->seek(PHPExcel_Cell::stringFromColumnIndex($this->startColumn));

        return $this;
    }

    
    public function resetEnd($endColumn = null)
    {
        $endColumn = ($endColumn) ? $endColumn : $this->subject->getHighestColumn();
        $this->endColumn = PHPExcel_Cell::columnIndexFromString($endColumn) - 1;
        $this->adjustForExistingOnlyRange();

        return $this;
    }

    
    public function seek($column = 'A')
    {
        $column = PHPExcel_Cell::columnIndexFromString($column) - 1;
        if (($column < $this->startColumn) || ($column > $this->endColumn)) {
            throw new PHPExcel_Exception("Column $column is out of range ({$this->startColumn} - {$this->endColumn})");
        } elseif ($this->onlyExistingCells && !($this->subject->cellExistsByColumnAndRow($column, $this->rowIndex))) {
            throw new PHPExcel_Exception('In "IterateOnlyExistingCells" mode and Cell does not exist');
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
        return $this->subject->getCellByColumnAndRow($this->position, $this->rowIndex);
    }

    
    public function key()
    {
        return PHPExcel_Cell::stringFromColumnIndex($this->position);
    }

    
    public function next()
    {
        do {
            ++$this->position;
        } while (($this->onlyExistingCells) &&
            (!$this->subject->cellExistsByColumnAndRow($this->position, $this->rowIndex)) &&
            ($this->position <= $this->endColumn));
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

        do {
            --$this->position;
        } while (($this->onlyExistingCells) &&
            (!$this->subject->cellExistsByColumnAndRow($this->position, $this->rowIndex)) &&
            ($this->position >= $this->startColumn));
    }

    
    public function valid()
    {
        return $this->position <= $this->endColumn;
    }

    
    protected function adjustForExistingOnlyRange()
    {
        if ($this->onlyExistingCells) {
            while ((!$this->subject->cellExistsByColumnAndRow($this->startColumn, $this->rowIndex)) &&
                ($this->startColumn <= $this->endColumn)) {
                ++$this->startColumn;
            }
            if ($this->startColumn > $this->endColumn) {
                throw new PHPExcel_Exception('No cells exist within the specified range');
            }
            while ((!$this->subject->cellExistsByColumnAndRow($this->endColumn, $this->rowIndex)) &&
                ($this->endColumn >= $this->startColumn)) {
                --$this->endColumn;
            }
            if ($this->endColumn < $this->startColumn) {
                throw new PHPExcel_Exception('No cells exist within the specified range');
            }
        }
    }
}
