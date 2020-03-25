<?php


class PHPExcel_CachedObjectStorage_Memory extends PHPExcel_CachedObjectStorage_CacheBase implements PHPExcel_CachedObjectStorage_ICache
{
    
    protected function storeData()
    {
    }

    
    public function addCacheData($pCoord, PHPExcel_Cell $cell)
    {
        $this->cellCache[$pCoord] = $cell;

                $this->currentObjectID = $pCoord;

        return $cell;
    }


    
    public function getCacheData($pCoord)
    {
                if (!isset($this->cellCache[$pCoord])) {
            $this->currentObjectID = null;
                        return null;
        }

                $this->currentObjectID = $pCoord;

                return $this->cellCache[$pCoord];
    }


    
    public function copyCellCollection(PHPExcel_Worksheet $parent)
    {
        parent::copyCellCollection($parent);

        $newCollection = array();
        foreach ($this->cellCache as $k => &$cell) {
            $newCollection[$k] = clone $cell;
            $newCollection[$k]->attach($this);
        }

        $this->cellCache = $newCollection;
    }

    
    public function unsetWorksheetCells()
    {
                foreach ($this->cellCache as $k => &$cell) {
            $cell->detach();
            $this->cellCache[$k] = null;
        }
        unset($cell);

        $this->cellCache = array();

                $this->parent = null;
    }
}
