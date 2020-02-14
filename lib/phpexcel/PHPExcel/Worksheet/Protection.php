<?php




class PHPExcel_Worksheet_Protection
{
    
    private $sheet                    = false;

    
    private $objects                = false;

    
    private $scenarios                = false;

    
    private $formatCells            = false;

    
    private $formatColumns            = false;

    
    private $formatRows            = false;

    
    private $insertColumns            = false;

    
    private $insertRows            = false;

    
    private $insertHyperlinks        = false;

    
    private $deleteColumns            = false;

    
    private $deleteRows            = false;

    
    private $selectLockedCells        = false;

    
    private $sort                    = false;

    
    private $autoFilter            = false;

    
    private $pivotTables            = false;

    
    private $selectUnlockedCells    = false;

    
    private $password                = '';

    
    public function __construct()
    {
    }

    
    public function isProtectionEnabled()
    {
        return $this->sheet ||
            $this->objects ||
            $this->scenarios ||
            $this->formatCells ||
            $this->formatColumns ||
            $this->formatRows ||
            $this->insertColumns ||
            $this->insertRows ||
            $this->insertHyperlinks ||
            $this->deleteColumns ||
            $this->deleteRows ||
            $this->selectLockedCells ||
            $this->sort ||
            $this->autoFilter ||
            $this->pivotTables ||
            $this->selectUnlockedCells;
    }

    
    public function getSheet()
    {
        return $this->sheet;
    }

    
    public function setSheet($pValue = false)
    {
        $this->sheet = $pValue;
        return $this;
    }

    
    public function getObjects()
    {
        return $this->objects;
    }

    
    public function setObjects($pValue = false)
    {
        $this->objects = $pValue;
        return $this;
    }

    
    public function getScenarios()
    {
        return $this->scenarios;
    }

    
    public function setScenarios($pValue = false)
    {
        $this->scenarios = $pValue;
        return $this;
    }

    
    public function getFormatCells()
    {
        return $this->formatCells;
    }

    
    public function setFormatCells($pValue = false)
    {
        $this->formatCells = $pValue;
        return $this;
    }

    
    public function getFormatColumns()
    {
        return $this->formatColumns;
    }

    
    public function setFormatColumns($pValue = false)
    {
        $this->formatColumns = $pValue;
        return $this;
    }

    
    public function getFormatRows()
    {
        return $this->formatRows;
    }

    
    public function setFormatRows($pValue = false)
    {
        $this->formatRows = $pValue;
        return $this;
    }

    
    public function getInsertColumns()
    {
        return $this->insertColumns;
    }

    
    public function setInsertColumns($pValue = false)
    {
        $this->insertColumns = $pValue;
        return $this;
    }

    
    public function getInsertRows()
    {
        return $this->insertRows;
    }

    
    public function setInsertRows($pValue = false)
    {
        $this->insertRows = $pValue;
        return $this;
    }

    
    public function getInsertHyperlinks()
    {
        return $this->insertHyperlinks;
    }

    
    public function setInsertHyperlinks($pValue = false)
    {
        $this->insertHyperlinks = $pValue;
        return $this;
    }

    
    public function getDeleteColumns()
    {
        return $this->deleteColumns;
    }

    
    public function setDeleteColumns($pValue = false)
    {
        $this->deleteColumns = $pValue;
        return $this;
    }

    
    public function getDeleteRows()
    {
        return $this->deleteRows;
    }

    
    public function setDeleteRows($pValue = false)
    {
        $this->deleteRows = $pValue;
        return $this;
    }

    
    public function getSelectLockedCells()
    {
        return $this->selectLockedCells;
    }

    
    public function setSelectLockedCells($pValue = false)
    {
        $this->selectLockedCells = $pValue;
        return $this;
    }

    
    public function getSort()
    {
        return $this->sort;
    }

    
    public function setSort($pValue = false)
    {
        $this->sort = $pValue;
        return $this;
    }

    
    public function getAutoFilter()
    {
        return $this->autoFilter;
    }

    
    public function setAutoFilter($pValue = false)
    {
        $this->autoFilter = $pValue;
        return $this;
    }

    
    public function getPivotTables()
    {
        return $this->pivotTables;
    }

    
    public function setPivotTables($pValue = false)
    {
        $this->pivotTables = $pValue;
        return $this;
    }

    
    public function getSelectUnlockedCells()
    {
        return $this->selectUnlockedCells;
    }

    
    public function setSelectUnlockedCells($pValue = false)
    {
        $this->selectUnlockedCells = $pValue;
        return $this;
    }

    
    public function getPassword()
    {
        return $this->password;
    }

    
    public function setPassword($pValue = '', $pAlreadyHashed = false)
    {
        if (!$pAlreadyHashed) {
            $pValue = PHPExcel_Shared_PasswordHasher::hashPassword($pValue);
        }
        $this->password = $pValue;
        return $this;
    }

    
    public function __clone()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if (is_object($value)) {
                $this->$key = clone $value;
            } else {
                $this->$key = $value;
            }
        }
    }
}
