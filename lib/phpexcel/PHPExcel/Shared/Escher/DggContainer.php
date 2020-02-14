<?php


class PHPExcel_Shared_Escher_DggContainer
{
    
    private $spIdMax;

    
    private $cDgSaved;

    
    private $cSpSaved;

    
    private $bstoreContainer;

    
    private $OPT = array();

    
    private $IDCLs = array();

    
    public function getSpIdMax()
    {
        return $this->spIdMax;
    }

    
    public function setSpIdMax($value)
    {
        $this->spIdMax = $value;
    }

    
    public function getCDgSaved()
    {
        return $this->cDgSaved;
    }

    
    public function setCDgSaved($value)
    {
        $this->cDgSaved = $value;
    }

    
    public function getCSpSaved()
    {
        return $this->cSpSaved;
    }

    
    public function setCSpSaved($value)
    {
        $this->cSpSaved = $value;
    }

    
    public function getBstoreContainer()
    {
        return $this->bstoreContainer;
    }

    
    public function setBstoreContainer($bstoreContainer)
    {
        $this->bstoreContainer = $bstoreContainer;
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

    
    public function getIDCLs()
    {
        return $this->IDCLs;
    }

    
    public function setIDCLs($pValue)
    {
        $this->IDCLs = $pValue;
    }
}
