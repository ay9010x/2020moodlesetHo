<?php


class PHPExcel_Worksheet_ColumnDimension extends PHPExcel_Worksheet_Dimension
{
    
    private $columnIndex;

    
    private $width = -1;

    
    private $autoSize = false;

    
    public function __construct($pIndex = 'A')
    {
                $this->columnIndex = $pIndex;

                parent::__construct(0);
    }

    
    public function getColumnIndex()
    {
        return $this->columnIndex;
    }

    
    public function setColumnIndex($pValue)
    {
        $this->columnIndex = $pValue;
        return $this;
    }

    
    public function getWidth()
    {
        return $this->width;
    }

    
    public function setWidth($pValue = -1)
    {
        $this->width = $pValue;
        return $this;
    }

    
    public function getAutoSize()
    {
        return $this->autoSize;
    }

    
    public function setAutoSize($pValue = false)
    {
        $this->autoSize = $pValue;
        return $this;
    }
}
