<?php


class PHPExcel_CachedObjectStorage_DiscISAM extends PHPExcel_CachedObjectStorage_CacheBase implements PHPExcel_CachedObjectStorage_ICache
{
    
    private $fileName = null;

    
    private $fileHandle = null;

    
    private $cacheDirectory = null;

    
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
                $baseUnique = $this->getUniqueID();
        $newFileName = $this->cacheDirectory.'/PHPExcel.'.$baseUnique.'.cache';
                copy($this->fileName, $newFileName);
        $this->fileName = $newFileName;
                $this->fileHandle = fopen($this->fileName, 'a+');
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
        $this->cacheDirectory    = ((isset($arguments['dir'])) && ($arguments['dir'] !== null))
                                    ? $arguments['dir']
                                    : PHPExcel_Shared_File::sys_get_temp_dir();

        parent::__construct($parent);
        if (is_null($this->fileHandle)) {
            $baseUnique = $this->getUniqueID();
            $this->fileName = $this->cacheDirectory.'/PHPExcel.'.$baseUnique.'.cache';
            $this->fileHandle = fopen($this->fileName, 'a+');
        }
    }

    
    public function __destruct()
    {
        if (!is_null($this->fileHandle)) {
            fclose($this->fileHandle);
            unlink($this->fileName);
        }
        $this->fileHandle = null;
    }
}
