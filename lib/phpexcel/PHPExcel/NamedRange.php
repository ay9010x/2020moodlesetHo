<?php


class PHPExcel_NamedRange
{
    
    private $name;

    
    private $worksheet;

    
    private $range;

    
    private $localOnly;

    
    private $scope;

    
    public function __construct($pName = null, PHPExcel_Worksheet $pWorksheet, $pRange = 'A1', $pLocalOnly = false, $pScope = null)
    {
                if (($pName === null) || ($pWorksheet === null) || ($pRange === null)) {
            throw new PHPExcel_Exception('Parameters can not be null.');
        }

                $this->name       = $pName;
        $this->worksheet  = $pWorksheet;
        $this->range      = $pRange;
        $this->localOnly  = $pLocalOnly;
        $this->scope      = ($pLocalOnly == true) ? (($pScope == null) ? $pWorksheet : $pScope) : null;
    }

    
    public function getName()
    {
        return $this->name;
    }

    
    public function setName($value = null)
    {
        if ($value !== null) {
                        $oldTitle = $this->name;

                        if ($this->worksheet !== null) {
                $this->worksheet->getParent()->removeNamedRange($this->name, $this->worksheet);
            }
            $this->name = $value;

            if ($this->worksheet !== null) {
                $this->worksheet->getParent()->addNamedRange($this);
            }

                        $newTitle = $this->name;
            PHPExcel_ReferenceHelper::getInstance()->updateNamedFormulas($this->worksheet->getParent(), $oldTitle, $newTitle);
        }
        return $this;
    }

    
    public function getWorksheet()
    {
        return $this->worksheet;
    }

    
    public function setWorksheet(PHPExcel_Worksheet $value = null)
    {
        if ($value !== null) {
            $this->worksheet = $value;
        }
        return $this;
    }

    
    public function getRange()
    {
        return $this->range;
    }

    
    public function setRange($value = null)
    {
        if ($value !== null) {
            $this->range = $value;
        }
        return $this;
    }

    
    public function getLocalOnly()
    {
        return $this->localOnly;
    }

    
    public function setLocalOnly($value = false)
    {
        $this->localOnly = $value;
        $this->scope = $value ? $this->worksheet : null;
        return $this;
    }

    
    public function getScope()
    {
        return $this->scope;
    }

    
    public function setScope(PHPExcel_Worksheet $value = null)
    {
        $this->scope = $value;
        $this->localOnly = ($value == null) ? false : true;
        return $this;
    }

    
    public static function resolveRange($pNamedRange = '', PHPExcel_Worksheet $pSheet)
    {
        return $pSheet->getParent()->getNamedRange($pNamedRange, $pSheet);
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
