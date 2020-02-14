<?php


class PHPExcel_CachedObjectStorage_Memcache extends PHPExcel_CachedObjectStorage_CacheBase implements PHPExcel_CachedObjectStorage_ICache
{
    
    private $cachePrefix = null;

    
    private $cacheTime = 600;

    
    private $memcache = null;


    
    protected function storeData()
    {
        if ($this->currentCellIsDirty && !empty($this->currentObjectID)) {
            $this->currentObject->detach();

            $obj = serialize($this->currentObject);
            if (!$this->memcache->replace($this->cachePrefix . $this->currentObjectID . '.cache', $obj, null, $this->cacheTime)) {
                if (!$this->memcache->add($this->cachePrefix . $this->currentObjectID . '.cache', $obj, null, $this->cacheTime)) {
                    $this->__destruct();
                    throw new PHPExcel_Exception("Failed to store cell {$this->currentObjectID} in MemCache");
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
                        $success = $this->memcache->get($this->cachePrefix.$pCoord.'.cache');
            if ($success === false) {
                                parent::deleteCacheData($pCoord);
                throw new PHPExcel_Exception('Cell entry '.$pCoord.' no longer exists in MemCache');
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
            $obj = $this->memcache->get($this->cachePrefix . $pCoord . '.cache');
            if ($obj === false) {
                                parent::deleteCacheData($pCoord);
                throw new PHPExcel_Exception("Cell entry {$pCoord} no longer exists in MemCache");
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
                $this->memcache->delete($this->cachePrefix . $pCoord . '.cache');

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
                $obj = $this->memcache->get($this->cachePrefix.$cellID.'.cache');
                if ($obj === false) {
                                        parent::deleteCacheData($cellID);
                    throw new PHPExcel_Exception("Cell entry {$cellID} no longer exists in MemCache");
                }
                if (!$this->memcache->add($newCachePrefix . $cellID . '.cache', $obj, null, $this->cacheTime)) {
                    $this->__destruct();
                    throw new PHPExcel_Exception("Failed to store cell {$cellID} in MemCache");
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
        $memcacheServer = (isset($arguments['memcacheServer'])) ? $arguments['memcacheServer'] : 'localhost';
        $memcachePort = (isset($arguments['memcachePort'])) ? $arguments['memcachePort'] : 11211;
        $cacheTime = (isset($arguments['cacheTime'])) ? $arguments['cacheTime'] : 600;

        if (is_null($this->cachePrefix)) {
            $baseUnique = $this->getUniqueID();
            $this->cachePrefix = substr(md5($baseUnique), 0, 8) . '.';

                        $this->memcache = new Memcache();
            if (!$this->memcache->addServer($memcacheServer, $memcachePort, false, 50, 5, 5, true, array($this, 'failureCallback'))) {
                throw new PHPExcel_Exception("Could not connect to MemCache server at {$memcacheServer}:{$memcachePort}");
            }
            $this->cacheTime = $cacheTime;

            parent::__construct($parent);
        }
    }

    
    public function failureCallback($host, $port)
    {
        throw new PHPExcel_Exception("memcache {$host}:{$port} failed");
    }

    
    public function __destruct()
    {
        $cacheList = $this->getCellList();
        foreach ($cacheList as $cellID) {
            $this->memcache->delete($this->cachePrefix.$cellID . '.cache');
        }
    }

    
    public static function cacheMethodIsAvailable()
    {
        if (!function_exists('memcache_add')) {
            return false;
        }

        return true;
    }
}
