<?php


abstract class PHPExcel_Writer_Excel2007_WriterPart
{
    
    private $parentWriter;

    
    public function setParentWriter(PHPExcel_Writer_IWriter $pWriter = null)
    {
        $this->parentWriter = $pWriter;
    }

    
    public function getParentWriter()
    {
        if (!is_null($this->parentWriter)) {
            return $this->parentWriter;
        } else {
            throw new PHPExcel_Writer_Exception("No parent PHPExcel_Writer_IWriter assigned.");
        }
    }

    
    public function __construct(PHPExcel_Writer_IWriter $pWriter = null)
    {
        if (!is_null($pWriter)) {
            $this->parentWriter = $pWriter;
        }
    }
}
