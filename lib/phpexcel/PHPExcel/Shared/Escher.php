<?php


class PHPExcel_Shared_Escher
{
    
    private $dggContainer;

    
    private $dgContainer;

    
    public function getDggContainer()
    {
        return $this->dggContainer;
    }

    
    public function setDggContainer($dggContainer)
    {
        return $this->dggContainer = $dggContainer;
    }

    
    public function getDgContainer()
    {
        return $this->dgContainer;
    }

    
    public function setDgContainer($dgContainer)
    {
        return $this->dgContainer = $dgContainer;
    }
}
