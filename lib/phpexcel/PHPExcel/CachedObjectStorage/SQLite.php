<?php


class PHPExcel_CachedObjectStorage_SQLite extends PHPExcel_CachedObjectStorage_CacheBase implements PHPExcel_CachedObjectStorage_ICache
{
    
    private $TableName = null;

    
    private $DBHandle = null;

    
    protected function storeData()
    {
        if ($this->currentCellIsDirty && !empty($this->currentObjectID)) {
            $this->currentObject->detach();

            if (!$this->DBHandle->queryExec("INSERT OR REPLACE INTO kvp_".$this->TableName." VALUES('".$this->currentObjectID."','".sqlite_escape_string(serialize($this->currentObject))."')")) {
                throw new PHPExcel_Exception(sqlite_error_string($this->DBHandle->lastError()));
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

        $query = "SELECT value FROM kvp_".$this->TableName." WHERE id='".$pCoord."'";
        $cellResultSet = $this->DBHandle->query($query, SQLITE_ASSOC);
        if ($cellResultSet === false) {
            throw new PHPExcel_Exception(sqlite_error_string($this->DBHandle->lastError()));
        } elseif ($cellResultSet->numRows() == 0) {
                        return null;
        }

                $this->currentObjectID = $pCoord;

        $cellResult = $cellResultSet->fetchSingle();
        $this->currentObject = unserialize($cellResult);
                $this->currentObject->attach($this);

                return $this->currentObject;
    }

    
    public function isDataSet($pCoord)
    {
        if ($pCoord === $this->currentObjectID) {
            return true;
        }

                $query = "SELECT id FROM kvp_".$this->TableName." WHERE id='".$pCoord."'";
        $cellResultSet = $this->DBHandle->query($query, SQLITE_ASSOC);
        if ($cellResultSet === false) {
            throw new PHPExcel_Exception(sqlite_error_string($this->DBHandle->lastError()));
        } elseif ($cellResultSet->numRows() == 0) {
                        return false;
        }
        return true;
    }

    
    public function deleteCacheData($pCoord)
    {
        if ($pCoord === $this->currentObjectID) {
            $this->currentObject->detach();
            $this->currentObjectID = $this->currentObject = null;
        }

                $query = "DELETE FROM kvp_".$this->TableName." WHERE id='".$pCoord."'";
        if (!$this->DBHandle->queryExec($query)) {
            throw new PHPExcel_Exception(sqlite_error_string($this->DBHandle->lastError()));
        }

        $this->currentCellIsDirty = false;
    }

    
    public function moveCell($fromAddress, $toAddress)
    {
        if ($fromAddress === $this->currentObjectID) {
            $this->currentObjectID = $toAddress;
        }

        $query = "DELETE FROM kvp_".$this->TableName." WHERE id='".$toAddress."'";
        $result = $this->DBHandle->exec($query);
        if ($result === false) {
            throw new PHPExcel_Exception($this->DBHandle->lastErrorMsg());
        }

        $query = "UPDATE kvp_".$this->TableName." SET id='".$toAddress."' WHERE id='".$fromAddress."'";
        $result = $this->DBHandle->exec($query);
        if ($result === false) {
            throw new PHPExcel_Exception($this->DBHandle->lastErrorMsg());
        }

        return true;
    }

    
    public function getCellList()
    {
        if ($this->currentObjectID !== null) {
            $this->storeData();
        }

        $query = "SELECT id FROM kvp_".$this->TableName;
        $cellIdsResult = $this->DBHandle->unbufferedQuery($query, SQLITE_ASSOC);
        if ($cellIdsResult === false) {
            throw new PHPExcel_Exception(sqlite_error_string($this->DBHandle->lastError()));
        }

        $cellKeys = array();
        foreach ($cellIdsResult as $row) {
            $cellKeys[] = $row['id'];
        }

        return $cellKeys;
    }

    
    public function copyCellCollection(PHPExcel_Worksheet $parent)
    {
        $this->currentCellIsDirty;
        $this->storeData();

                $tableName = str_replace('.', '_', $this->getUniqueID());
        if (!$this->DBHandle->queryExec('CREATE TABLE kvp_'.$tableName.' (id VARCHAR(12) PRIMARY KEY, value BLOB)
            AS SELECT * FROM kvp_'.$this->TableName)
        ) {
            throw new PHPExcel_Exception(sqlite_error_string($this->DBHandle->lastError()));
        }

                $this->TableName = $tableName;
    }

    
    public function unsetWorksheetCells()
    {
        if (!is_null($this->currentObject)) {
            $this->currentObject->detach();
            $this->currentObject = $this->currentObjectID = null;
        }
                $this->parent = null;

                $this->__destruct();
    }

    
    public function __construct(PHPExcel_Worksheet $parent)
    {
        parent::__construct($parent);
        if (is_null($this->DBHandle)) {
            $this->TableName = str_replace('.', '_', $this->getUniqueID());
            $_DBName = ':memory:';

            $this->DBHandle = new SQLiteDatabase($_DBName);
            if ($this->DBHandle === false) {
                throw new PHPExcel_Exception(sqlite_error_string($this->DBHandle->lastError()));
            }
            if (!$this->DBHandle->queryExec('CREATE TABLE kvp_'.$this->TableName.' (id VARCHAR(12) PRIMARY KEY, value BLOB)')) {
                throw new PHPExcel_Exception(sqlite_error_string($this->DBHandle->lastError()));
            }
        }
    }

    
    public function __destruct()
    {
        if (!is_null($this->DBHandle)) {
            $this->DBHandle->queryExec('DROP TABLE kvp_'.$this->TableName);
        }
        $this->DBHandle = null;
    }

    
    public static function cacheMethodIsAvailable()
    {
        if (!function_exists('sqlite_open')) {
            return false;
        }

        return true;
    }
}
