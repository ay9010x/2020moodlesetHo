<?php


class PHPExcel_Worksheet_Column
{
    
    private $parent;

    
    private $columnIndex;

    
    public function __construct(PHPExcel_Worksheet $parent = null, $columnIndex = 'A')
    {
                $this->parent         = $parent;
        $this->columnIndex = $columnIndex;
    }

    
    public function __destruct()
    {
        unset($this->parent);
    }

    
    public function getColumnIndex()
    {
        return $this->columnIndex;
    }

    
    public function getCellIterator($startRow = 1, $endRow = null)
    {
        return new PHPExcel_Worksheet_ColumnCellIterator($this->parent, $this->columnIndex, $startRow, $endRow);
    }
}
