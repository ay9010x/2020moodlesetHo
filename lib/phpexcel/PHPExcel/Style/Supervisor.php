<?php


abstract class PHPExcel_Style_Supervisor
{
    
    protected $isSupervisor;

    
    protected $parent;

    
    public function __construct($isSupervisor = false)
    {
                $this->isSupervisor = $isSupervisor;
    }

    
    public function bindParent($parent, $parentPropertyName = null)
    {
        $this->parent = $parent;
        return $this;
    }

    
    public function getIsSupervisor()
    {
        return $this->isSupervisor;
    }

    
    public function getActiveSheet()
    {
        return $this->parent->getActiveSheet();
    }

    
    public function getSelectedCells()
    {
        return $this->getActiveSheet()->getSelectedCells();
    }

    
    public function getActiveCell()
    {
        return $this->getActiveSheet()->getActiveCell();
    }

    
    public function __clone()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if ((is_object($value)) && ($key != 'parent')) {
                $this->$key = clone $value;
            } else {
                $this->$key = $value;
            }
        }
    }
}
