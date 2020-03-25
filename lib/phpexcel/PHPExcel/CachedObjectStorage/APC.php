<?php


class PHPExcel_CachedObjectStorage_APC extends PHPExcel_CachedObjectStorage_CacheBase implements PHPExcel_CachedObjectStorage_ICache
{
    
    private $cachePrefix = null;

    
    private $cacheTime = 600;

    
    protected function storeData()
    {
        if ($this->currentCellIsDirty && !empty($this->currentObjectID)) {
            $this->currentObject->detach();

            if (!apc_store(
                $this->cachePrefix . $this->currentObjectID . '.cache',
                serialize($this->currentObject),
                $this->cacheTime
            )) {
                $this->__destruct();
                throw new PHPExcel_Exception('Failed to store cell ' . $this->currentObjectID . ' in APC');
            }
            $this->currentCellIsDirty = false;
        }
        $this->currentObjectID = $this->currentObject = null;
    }

    
    public function addCacheData($pCoord, PHPExcel_Cell $cell)
    {
        if (($pCoord !== $this->currentObjectID) && ($this->currentObjectID !== null)) {
            $this->storeData();
        }
        $this->cellCache[$pCoord] = true;

        $this->currentObjectID = $pCoord;
        $this->currentObject = $cell;
        $this->currentCellIsDirty = true;

        return $cell;
    }

    
    public function isDataSet($pCoord)
    {
                if (parent::isDataSet($pCoord)) {
            if ($this->currentObjectID == $pCoord) {
                return true;
            }
                        $success = apc_fetch($this->cachePrefix.$pCoord.'.cache');
            if ($success === false) {
                                parent::deleteCacheData($pCoord);
                throw new PHPExcel_Exception('Cell entry '.$pCoord.' no longer exists in APC cache');
            }
            return true;
        }
        return false;
    }

    
    public function getCacheData($pCoord)
    {
        if ($pCoord === $this->currentObjectID) {
            return $this->currentObject;
        }
        $this->storeData();

                if (parent::isDataSet($pCoord)) {
            $obj = apc_fetch($this->cachePrefix . $pCoord . '.cache');
            if ($obj === false) {
                                parent::deleteCacheData($pCoord);
                throw new PHPExcel_Exception('Cell entry '.$pCoord.' no longer exists in APC cache');
            }
        } else {
                        return null;
        }

                $this->currentObjectID = $pCoord;
        $this->currentObject = unserialize($obj);
                $this->currentObject->attach($this);

                return $this->currentObject;
    }

    
    public function getCellList()
    {
        if ($this->currentObjectID !== null) {
            $this->storeData();
        }

        return parent::getCellList();
    }

    
    public function deleteCacheData($pCoord)
    {
                apc_delete($this->cachePrefix.$pCoord.'.cache');

                parent::deleteCacheData($pCoord);
    }

    
    public function copyCellCollection(PHPExcel_Worksheet $parent)
    {
        parent::copyCellCollection($parent);
                $baseUnique = $this->getUniqueID();
        $newCachePrefix = substr(md5($baseUnique), 0, 8) . '.';
        $cacheList = $this->getCellList();
        foreach ($cacheList as $cellID) {
            if ($cellID != $this->currentObjectID) {
                $obj = apc_fetch($this->cachePrefix . $cellID . '.cache');
                if ($obj === false) {
                                        parent::deleteCacheData($cellID);
                    throw new PHPExcel_Exception('Cell entry ' . $cellID . ' no longer exists in APC');
                }
                if (!apc_store($newCachePrefix . $cellID . '.cache', $obj, $this->cacheTime)) {
                    $this->__destruct();
                    throw new PHPExcel_Exception('Failed to store cell ' . $cellID . ' in APC');
                }
            }
        }
        $this->cachePrefix = $newCachePrefix;
    }

    
    public function unsetWorksheetCells()
    {
        if ($this->currentObject !== null) {
            $this->currentObject->detach();
            $this->currentObject = $this->currentObjectID = null;
        }

                $this->__destruct();

        $this->cellCache = array();

                $this->parent = null;
    }

    
    public function __construct(PHPExcel_Worksheet $parent, $arguments)
    {
        $cacheTime = (isset($arguments['cacheTime'])) ? $arguments['cacheTime'] : 600;

        if ($this->cachePrefix === null) {
            $baseUnique = $this->getUniqueID();
            $this->cachePrefix = substr(md5($baseUnique), 0, 8) . '.';
            $this->cacheTime = $cacheTime;

            parent::__construct($parent);
        }
    }

    
    public function __destruct()
    {
        $cacheList = $this->getCellList();
        foreach ($cacheList as $cellID) {
            apc_delete($this->cachePrefix . $cellID . '.cache');
        }
    }

    
    public static function cacheMethodIsAvailable()
    {
        if (!function_exists('apc_store')) {
            return false;
        }
        if (apc_sma_info() === false) {
            return false;
        }

        return true;
    }
}
