<?php


class PHPExcel_Shared_Escher_DggContainer_BstoreContainer
{
    
    private $BSECollection = array();

    
    public function addBSE($BSE)
    {
        $this->BSECollection[] = $BSE;
        $BSE->setParent($this);
    }

    
    public function getBSECollection()
    {
        return $this->BSECollection;
    }
}
