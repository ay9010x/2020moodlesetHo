<?php


class PHPExcel_Shared_Escher_DgContainer_SpgrContainer_SpContainer
{
    
    private $parent;

    
    private $spgr = false;

    
    private $spType;

    
    private $spFlag;

    
    private $spId;

    
    private $OPT;

    
    private $startCoordinates;

    
    private $startOffsetX;

    
    private $startOffsetY;

    
    private $endCoordinates;

    
    private $endOffsetX;

    
    private $endOffsetY;

    
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    
    public function getParent()
    {
        return $this->parent;
    }

    
    public function setSpgr($value = false)
    {
        $this->spgr = $value;
    }

    
    public function getSpgr()
    {
        return $this->spgr;
    }

    
    public function setSpType($value)
    {
        $this->spType = $value;
    }

    
    public function getSpType()
    {
        return $this->spType;
    }

    
    public function setSpFlag($value)
    {
        $this->spFlag = $value;
    }

    
    public function getSpFlag()
    {
        return $this->spFlag;
    }

    
    public function setSpId($value)
    {
        $this->spId = $value;
    }

    
    public function getSpId()
    {
        return $this->spId;
    }

    
    public function setOPT($property, $value)
    {
        $this->OPT[$property] = $value;
    }

    
    public function getOPT($property)
    {
        if (isset($this->OPT[$property])) {
            return $this->OPT[$property];
        }
        return null;
    }

    
    public function getOPTCollection()
    {
        return $this->OPT;
    }

    
    public function setStartCoordinates($value = 'A1')
    {
        $this->startCoordinates = $value;
    }

    
    public function getStartCoordinates()
    {
        return $this->startCoordinates;
    }

    
    public function setStartOffsetX($startOffsetX = 0)
    {
        $this->startOffsetX = $startOffsetX;
    }

    
    public function getStartOffsetX()
    {
        return $this->startOffsetX;
    }

    
    public function setStartOffsetY($startOffsetY = 0)
    {
        $this->startOffsetY = $startOffsetY;
    }

    
    public function getStartOffsetY()
    {
        return $this->startOffsetY;
    }

    
    public function setEndCoordinates($value = 'A1')
    {
        $this->endCoordinates = $value;
    }

    
    public function getEndCoordinates()
    {
        return $this->endCoordinates;
    }

    
    public function setEndOffsetX($endOffsetX = 0)
    {
        $this->endOffsetX = $endOffsetX;
    }

    
    public function getEndOffsetX()
    {
        return $this->endOffsetX;
    }

    
    public function setEndOffsetY($endOffsetY = 0)
    {
        $this->endOffsetY = $endOffsetY;
    }

    
    public function getEndOffsetY()
    {
        return $this->endOffsetY;
    }

    
    public function getNestingLevel()
    {
        $nestingLevel = 0;

        $parent = $this->getParent();
        while ($parent instanceof PHPExcel_Shared_Escher_DgContainer_SpgrContainer) {
            ++$nestingLevel;
            $parent = $parent->getParent();
        }

        return $nestingLevel;
    }
}
