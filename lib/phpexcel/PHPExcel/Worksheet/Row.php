<?php


class PHPExcel_Worksheet_Row
{
    
    private $parent;

    
    private $rowIndex = 0;

    
    public function __construct(PHPExcel_Worksheet $parent = null, $rowIndex = 1)
    {
                $this->parent   = $parent;
        $this->rowIndex = $rowIndex;
    }

    
    public function __destruct()
    {
        unset($this->parent);
    }

    
    public function getRowIndex()
    {
        return $this->rowIndex;
    }

    
    public function getCellIterator($startColumn = 'A', $endColumn = null)
    {
        return new PHPExcel_Worksheet_RowCellIterator($this->parent, $this->rowIndex, $startColumn, $endColumn);
    }
}
