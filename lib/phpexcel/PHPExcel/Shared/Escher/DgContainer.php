<?php


class PHPExcel_Shared_Escher_DgContainer
{
    
    private $dgId;

    
    private $lastSpId;

    private $spgrContainer = null;

    public function getDgId()
    {
        return $this->dgId;
    }

    public function setDgId($value)
    {
        $this->dgId = $value;
    }

    public function getLastSpId()
    {
        return $this->lastSpId;
    }

    public function setLastSpId($value)
    {
        $this->lastSpId = $value;
    }

    public function getSpgrContainer()
    {
        return $this->spgrContainer;
    }

    public function setSpgrContainer($spgrContainer)
    {
        return $this->spgrContainer = $spgrContainer;
    }
}
