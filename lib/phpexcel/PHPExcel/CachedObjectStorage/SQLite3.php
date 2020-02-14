<?php


class PHPExcel_CachedObjectStorage_SQLite3 extends PHPExcel_CachedObjectStorage_CacheBase implements PHPExcel_CachedObjectStorage_ICache
{
    
    private $TableName = null;

    
    private $DBHandle = null;

    
    private $selectQuery;

    
    private $insertQuery;

    
    private $updateQuery;

    
    private $deleteQuery;

    
    protected function storeData()
    {
        if ($this->currentCellIsDirty && !empty($this->currentObjectID)) {
            $this->currentObject->detach();

            $this->insertQuery->bindValue('id', $this->currentObjectID, SQLITE3_TEXT);
            $this->insertQuery->bindValue('data', serialize($this->currentObject), SQLITE3_BLOB);
            $result = $this->insertQuery->execute();
            if ($result === false) {
                throw new PHPExcel_Exception($this->DBHandle->lastErrorMsg());
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

        $this->selectQuery->bindValue('id', $pCoord, SQLITE3_TEXT);
        $cellResult = $this->selectQuery->execute();
        if ($cellResult === false) {
            throw new PHPExcel_Exception($this->DBHandle->lastErrorMsg());
        }
        $cellData = $cellResult->fetchArray(SQLITE3_ASSOC);
        if ($cellData === false) {
                        return null;
        }

                $this->currentObjectID = $pCoord;

        $this->currentObject = unserialize($cellData['value']);
                $this->currentObject->attach($this);

                return $this->currentObject;
    }

    
    public function isDataSet($pCoord)
    {
        if ($pCoord === $this->currentObjectID) {
            return true;
        }

                $this->selectQuery->bindValue('id', $pCoord, SQLITE3_TEXT);
        $cellResult = $this->selectQuery->execute();
        if ($cellResult === false) {
            throw new PHPExcel_Exception($this->DBHandle->lastErrorMsg());
        }
        $cellData = $cellResult->fetchArray(SQLITE3_ASSOC);

        return ($cellData === false) ? false : true;
    }

    
    public function deleteCacheData($pCoord)
    {
        if ($pCoord === $this->currentObjectID) {
            $this->currentObject->detach();
            $this->currentObjectID = $this->currentObject = null;
        }

                $this->deleteQuery->bindValue('id', $pCoord, SQLITE3_TEXT);
        $result = $this->deleteQuery->execute();
        if ($result === false) {
            throw new PHPExcel_Exception($this->DBHandle->lastErrorMsg());
        }

        $this->currentCellIsDirty = false;
    }

    
    public function moveCell($fromAddress, $toAddress)
    {
        if ($fromAddress === $this->currentObjectID) {
            $this->currentObjectID = $toAddress;
        }

        $this->deleteQuery->bindValue('id', $toAddress, SQLITE3_TEXT);
        $result = $this->deleteQuery->execute();
        if ($result === false) {
            throw new PHPExcel_Exception($this->DBHandle->lastErrorMsg());
        }

        $this->updateQuery->bindValue('toid', $toAddress, SQLITE3_TEXT);
        $this->updateQuery->bindValue('fromid', $fromAddress, SQLITE3_TEXT);
        $result = $this->updateQuery->execute();
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
        $cellIdsResult = $this->DBHandle->query($query);
        if ($cellIdsResult === false) {
            throw new PHPExcel_Exception($this->DBHandle->lastErrorMsg());
        }

        $cellKeys = array();
        while ($row = $cellIdsResult->fetchArray(SQLITE3_ASSOC)) {
            $cellKeys[] = $row['id'];
        }

        return $cellKeys;
    }

    
    public function copyCellCollection(PHPExcel_Worksheet $parent)
    {
        $this->currentCellIsDirty;
        $this->storeData();

                $tableName = str_replace('.', '_', $this->getUniqueID());
        if (!$this->DBHandle->exec('CREATE TABLE kvp_'.$tableName.' (id VARCHAR(12) PRIMARY KEY, value BLOB)
            AS SELECT * FROM kvp_'.$this->TableName)
        ) {
            throw new PHPExcel_Exception($this->DBHandle->lastErrorMsg());
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

            $this->DBHandle = new SQLite3($_DBName);
            if ($this->DBHandle === false) {
                throw new PHPExcel_Exception($this->DBHandle->lastErrorMsg());
            }
            if (!$this->DBHandle->exec('CREATE TABLE kvp_'.$this->TableName.' (id VARCHAR(12) PRIMARY KEY, value BLOB)')) {
                throw new PHPExcel_Exception($this->DBHandle->lastErrorMsg());
            }
        }

        $this->selectQuery = $this->DBHandle->prepare("SELECT value FROM kvp_".$this->TableName." WHERE id = :id");
        $this->insertQuery = $this->DBHandle->prepare("INSERT OR REPLACE INTO kvp_".$this->TableName." VALUES(:id,:data)");
        $this->updateQuery = $this->DBHandle->prepare("UPDATE kvp_".$this->TableName." SET id=:toId WHERE id=:fromId");
        $this->deleteQuery = $this->DBHandle->prepare("DELETE FROM kvp_".$this->TableName." WHERE id = :id");
    }

    
    public function __destruct()
    {
        if (!is_null($this->DBHandle)) {
            $this->DBHandle->exec('DROP TABLE kvp_'.$this->TableName);
            $this->DBHandle->close();
        }
        $this->DBHandle = null;
    }

    
    public static function cacheMethodIsAvailable()
    {
        if (!class_exists('SQLite3', false)) {
            return false;
        }

        return true;
    }
}
