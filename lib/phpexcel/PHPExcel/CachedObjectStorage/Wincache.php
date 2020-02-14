<?php


class PHPExcel_CachedObjectStorage_Wincache extends PHPExcel_CachedObjectStorage_CacheBase implements PHPExcel_CachedObjectStorage_ICache
{
    
    private $cachePrefix = null;

    
    private $cacheTime = 600;


    
    protected function storeData()
    {
        if ($this->currentCellIsDirty && !empty($this->currentObjectID)) {
            $this->currentObject->detach();

            $obj = serialize($this->currentObject);
            if (wincache_ucache_exists($this->cachePrefix.$this->currentObjectID.'.cache')) {
                if (!wincache_ucache_set($this->cachePrefix.$this->currentObjectID.'.cache', $obj, $this->cacheTime)) {
                    $this->__destruct();
                    throw new PHPExcel_Exception('Failed to store cell '.$this->currentObjectID.' in WinCache');
                }
            } else {
                if (!wincache_ucache_add($this->cachePrefix.$this->currentObjectID.'.cache', $obj, $this->cacheTime)) {
                    $this->__destruct();
                    throw new PHPExcel_Exception('Failed to store cell '.$this->currentObjectID.' in WinCache');
                }
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
                        $success = wincache_ucache_exists($this->cachePrefix.$pCoord.'.cache');
            if ($success === false) {
                                parent::deleteCacheData($pCoord);
                throw new PHPExcel_Exception('Cell entry '.$pCoord.' no longer exists in WinCache');
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

                $obj = null;
        if (parent::isDataSet($pCoord)) {
            $success = false;
            $obj = wincache_ucache_get($this->cachePrefix.$pCoord.'.cache', $success);
            if ($success === false) {
                                parent::deleteCacheData($pCoord);
                throw new PHPExcel_Exception('Cell entry '.$pCoord.' no longer exists in WinCache');
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
                wincache_ucache_delete($this->cachePrefix.$pCoord.'.cache');

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
                $success = false;
                $obj = wincache_ucache_get($this->cachePrefix.$cellID.'.cache', $success);
                if ($success === false) {
                                        parent::deleteCacheData($cellID);
                    throw new PHPExcel_Exception('Cell entry '.$cellID.' no longer exists in Wincache');
                }
                if (!wincache_ucache_add($newCachePrefix.$cellID.'.cache', $obj, $this->cacheTime)) {
                    $this->__destruct();
                    throw new PHPExcel_Exception('Failed to store cell '.$cellID.' in Wincache');
                }
            }
        }
        $this->cachePrefix = $newCachePrefix;
    }


    
    public function unsetWorksheetCells()
    {
        if (!is_null($this->currentObject)) {
            $this->currentObject->detach();
            $this->currentObject = $this->currentObjectID = null;
        }

                $this->__destruct();

        $this->cellCache = array();

                $this->parent = null;
    }

    
    public function __construct(PHPExcel_Worksheet $parent, $arguments)
    {
        $cacheTime    = (isset($arguments['cacheTime']))    ? $arguments['cacheTime']    : 600;

        if (is_null($this->cachePrefix)) {
            $baseUnique = $this->getUniqueID();
            $this->cachePrefix = substr(md5($baseUnique), 0, 8).'.';
            $this->cacheTime = $cacheTime;

            parent::__construct($parent);
        }
    }

    
    public function __destruct()
    {
        $cacheList = $this->getCellList();
        foreach ($cacheList as $cellID) {
            wincache_ucache_delete($this->cachePrefix.$cellID.'.cache');
        }
    }

    
    public static function cacheMethodIsAvailable()
    {
        if (!function_exists('wincache_ucache_add')) {
            return false;
        }

        return true;
    }
}
