<?php


abstract class PHPExcel_CachedObjectStorage_CacheBase
{
    
    protected $parent;

    
    protected $currentObject = null;

    
    protected $currentObjectID = null;

    
    protected $currentCellIsDirty = true;

    
    protected $cellCache = array();

    
    public function __construct(PHPExcel_Worksheet $parent)
    {
                                $this->parent = $parent;
    }

    
    public function getParent()
    {
        return $this->parent;
    }

    
    public function isDataSet($pCoord)
    {
        if ($pCoord === $this->currentObjectID) {
            return true;
        }
                return isset($this->cellCache[$pCoord]);
    }

    
    public function moveCell($fromAddress, $toAddress)
    {
        if ($fromAddress === $this->currentObjectID) {
            $this->currentObjectID = $toAddress;
        }
        $this->currentCellIsDirty = true;
        if (isset($this->cellCache[$fromAddress])) {
            $this->cellCache[$toAddress] = &$this->cellCache[$fromAddress];
            unset($this->cellCache[$fromAddress]);
        }

        return true;
    }

    
    public function updateCacheData(PHPExcel_Cell $cell)
    {
        return $this->addCacheData($cell->getCoordinate(), $cell);
    }

    
    public function deleteCacheData($pCoord)
    {
        if ($pCoord === $this->currentObjectID && !is_null($this->currentObject)) {
            $this->currentObject->detach();
            $this->currentObjectID = $this->currentObject = null;
        }

        if (is_object($this->cellCache[$pCoord])) {
            $this->cellCache[$pCoord]->detach();
            unset($this->cellCache[$pCoord]);
        }
        $this->currentCellIsDirty = false;
    }

    
    public function getCellList()
    {
        return array_keys($this->cellCache);
    }

    
    public function getSortedCellList()
    {
        $sortKeys = array();
        foreach ($this->getCellList() as $coord) {
            sscanf($coord, '%[A-Z]%d', $column, $row);
            $sortKeys[sprintf('%09d%3s', $row, $column)] = $coord;
        }
        ksort($sortKeys);

        return array_values($sortKeys);
    }

    
    public function getHighestRowAndColumn()
    {
                $col = array('A' => '1A');
        $row = array(1);
        foreach ($this->getCellList() as $coord) {
            sscanf($coord, '%[A-Z]%d', $c, $r);
            $row[$r] = $r;
            $col[$c] = strlen($c).$c;
        }
        if (!empty($row)) {
                        $highestRow = max($row);
            $highestColumn = substr(max($col), 1);
        }

        return array(
            'row'    => $highestRow,
            'column' => $highestColumn
        );
    }

    
    public function getCurrentAddress()
    {
        return $this->currentObjectID;
    }

    
    public function getCurrentColumn()
    {
        sscanf($this->currentObjectID, '%[A-Z]%d', $column, $row);
        return $column;
    }

    
    public function getCurrentRow()
    {
        sscanf($this->currentObjectID, '%[A-Z]%d', $column, $row);
        return (integer) $row;
    }

    
    public function getHighestColumn($row = null)
    {
        if ($row == null) {
            $colRow = $this->getHighestRowAndColumn();
            return $colRow['column'];
        }

        $columnList = array(1);
        foreach ($this->getCellList() as $coord) {
            sscanf($coord, '%[A-Z]%d', $c, $r);
            if ($r != $row) {
                continue;
            }
            $columnList[] = PHPExcel_Cell::columnIndexFromString($c);
        }
        return PHPExcel_Cell::stringFromColumnIndex(max($columnList) - 1);
    }

    
    public function getHighestRow($column = null)
    {
        if ($column == null) {
            $colRow = $this->getHighestRowAndColumn();
            return $colRow['row'];
        }

        $rowList = array(0);
        foreach ($this->getCellList() as $coord) {
            sscanf($coord, '%[A-Z]%d', $c, $r);
            if ($c != $column) {
                continue;
            }
            $rowList[] = $r;
        }

        return max($rowList);
    }

    
    protected function getUniqueID()
    {
        if (function_exists('posix_getpid')) {
            $baseUnique = posix_getpid();
        } else {
            $baseUnique = mt_rand();
        }
        return uniqid($baseUnique, true);
    }

    
    public function copyCellCollection(PHPExcel_Worksheet $parent)
    {
        $this->currentCellIsDirty;
        $this->storeData();

        $this->parent = $parent;
        if (($this->currentObject !== null) && (is_object($this->currentObject))) {
            $this->currentObject->attach($this);
        }
    }    
    
    public function removeRow($row)
    {
        foreach ($this->getCellList() as $coord) {
            sscanf($coord, '%[A-Z]%d', $c, $r);
            if ($r == $row) {
                $this->deleteCacheData($coord);
            }
        }
    }

    
    public function removeColumn($column)
    {
        foreach ($this->getCellList() as $coord) {
            sscanf($coord, '%[A-Z]%d', $c, $r);
            if ($c == $column) {
                $this->deleteCacheData($coord);
            }
        }
    }

    
    public static function cacheMethodIsAvailable()
    {
        return true;
    }
}
