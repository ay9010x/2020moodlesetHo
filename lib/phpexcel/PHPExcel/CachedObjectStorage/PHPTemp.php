<?php


class PHPExcel_CachedObjectStorage_PHPTemp extends PHPExcel_CachedObjectStorage_CacheBase implements PHPExcel_CachedObjectStorage_ICache
{
    
    private $fileHandle = null;

    
    private $memoryCacheSize = null;

    
    protected function storeData()
    {
        if ($this->currentCellIsDirty && !empty($this->currentObjectID)) {
            $this->currentObject->detach();

            fseek($this->fileHandle, 0, SEEK_END);

            $this->cellCache[$this->currentObjectID] = array(
                'ptr' => ftell($this->fileHandle),
                'sz'  => fwrite($this->fileHandle, serialize($this->currentObject))
            );
            $this->currentCellIsDirty = false;
        }
        $this->currentObjectID = $this->currentObject = null;
    }


    
    public function addCacheData($pCoord, PHPExcel_Cell $cell)
    {
        if (($pCoord !== $this->currentObjectID) && ($this->currentObjectID !== null)) {
            $this->storeData();
        }

        $this->currentObjectID = $pCoord;
        $this->currentObject = $cell;
        $this->currentCellIsDirty = true;

        return $cell;
    }


    
    public function getCacheData($pCoord)
    {
        if ($pCoord === $this->currentObjectID) {
            return $this->currentObject;
        }
        $this->storeData();

                if (!isset($this->cellCache[$pCoord])) {
                        return null;
        }

                $this->currentObjectID = $pCoord;
        fseek($this->fileHandle, $this->cellCache[$pCoord]['ptr']);
        $this->currentObject = unserialize(fread($this->fileHandle, $this->cellCache[$pCoord]['sz']));
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

    
    public function copyCellCollection(PHPExcel_Worksheet $parent)
    {
        parent::copyCellCollection($parent);
                $newFileHandle = fopen('php://temp/maxmemory:' . $this->memoryCacheSize, 'a+');
                fseek($this->fileHandle, 0);
        while (!feof($this->fileHandle)) {
            fwrite($newFileHandle, fread($this->fileHandle, 1024));
        }
        $this->fileHandle = $newFileHandle;
    }

    
    public function unsetWorksheetCells()
    {
        if (!is_null($this->currentObject)) {
            $this->currentObject->detach();
            $this->currentObject = $this->currentObjectID = null;
        }
        $this->cellCache = array();

                $this->parent = null;

                $this->__destruct();
    }

    
    public function __construct(PHPExcel_Worksheet $parent, $arguments)
    {
        $this->memoryCacheSize = (isset($arguments['memoryCacheSize'])) ? $arguments['memoryCacheSize'] : '1MB';

        parent::__construct($parent);
        if (is_null($this->fileHandle)) {
            $this->fileHandle = fopen('php://temp/maxmemory:' . $this->memoryCacheSize, 'a+');
        }
    }

    
    public function __destruct()
    {
        if (!is_null($this->fileHandle)) {
            fclose($this->fileHandle);
        }
        $this->fileHandle = null;
    }
}
