<?php


class PHPExcel_Calculation_Function
{
    
    const CATEGORY_CUBE                 = 'Cube';
    const CATEGORY_DATABASE             = 'Database';
    const CATEGORY_DATE_AND_TIME        = 'Date and Time';
    const CATEGORY_ENGINEERING          = 'Engineering';
    const CATEGORY_FINANCIAL            = 'Financial';
    const CATEGORY_INFORMATION          = 'Information';
    const CATEGORY_LOGICAL              = 'Logical';
    const CATEGORY_LOOKUP_AND_REFERENCE = 'Lookup and Reference';
    const CATEGORY_MATH_AND_TRIG        = 'Math and Trig';
    const CATEGORY_STATISTICAL          = 'Statistical';
    const CATEGORY_TEXT_AND_DATA        = 'Text and Data';

    
    private $category;

    
    private $excelName;

    
    private $phpExcelName;

    
    public function __construct($pCategory = null, $pExcelName = null, $pPHPExcelName = null)
    {
        if (($pCategory !== null) && ($pExcelName !== null) && ($pPHPExcelName !== null)) {
                        $this->category     = $pCategory;
            $this->excelName    = $pExcelName;
            $this->phpExcelName = $pPHPExcelName;
        } else {
            throw new PHPExcel_Calculation_Exception("Invalid parameters passed.");
        }
    }

    
    public function getCategory()
    {
        return $this->category;
    }

    
    public function setCategory($value = null)
    {
        if (!is_null($value)) {
            $this->category = $value;
        } else {
            throw new PHPExcel_Calculation_Exception("Invalid parameter passed.");
        }
    }

    
    public function getExcelName()
    {
        return $this->excelName;
    }

    
    public function setExcelName($value)
    {
        $this->excelName = $value;
    }

    
    public function getPHPExcelName()
    {
        return $this->phpExcelName;
    }

    
    public function setPHPExcelName($value)
    {
        $this->phpExcelName = $value;
    }
}
