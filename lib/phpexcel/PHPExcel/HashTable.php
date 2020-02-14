<?php


class PHPExcel_HashTable
{
    
    protected $items = array();

    
    protected $keyMap = array();

    
    public function __construct($pSource = null)
    {
        if ($pSource !== null) {
                        $this->addFromSource($pSource);
        }
    }

    
    public function addFromSource($pSource = null)
    {
                if ($pSource == null) {
            return;
        } elseif (!is_array($pSource)) {
            throw new PHPExcel_Exception('Invalid array parameter passed.');
        }

        foreach ($pSource as $item) {
            $this->add($item);
        }
    }

    
    public function add(PHPExcel_IComparable $pSource = null)
    {
        $hash = $pSource->getHashCode();
        if (!isset($this->items[$hash])) {
            $this->items[$hash] = $pSource;
            $this->keyMap[count($this->items) - 1] = $hash;
        }
    }

    
    public function remove(PHPExcel_IComparable $pSource = null)
    {
        $hash = $pSource->getHashCode();
        if (isset($this->items[$hash])) {
            unset($this->items[$hash]);

            $deleteKey = -1;
            foreach ($this->keyMap as $key => $value) {
                if ($deleteKey >= 0) {
                    $this->keyMap[$key - 1] = $value;
                }

                if ($value == $hash) {
                    $deleteKey = $key;
                }
            }
            unset($this->keyMap[count($this->keyMap) - 1]);
        }
    }

    
    public function clear()
    {
        $this->items = array();
        $this->keyMap = array();
    }

    
    public function count()
    {
        return count($this->items);
    }

    
    public function getIndexForHashCode($pHashCode = '')
    {
        return array_search($pHashCode, $this->keyMap);
    }

    
    public function getByIndex($pIndex = 0)
    {
        if (isset($this->keyMap[$pIndex])) {
            return $this->getByHashCode($this->keyMap[$pIndex]);
        }

        return null;
    }

    
    public function getByHashCode($pHashCode = '')
    {
        if (isset($this->items[$pHashCode])) {
            return $this->items[$pHashCode];
        }

        return null;
    }

    
    public function toArray()
    {
        return $this->items;
    }

    
    public function __clone()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if (is_object($value)) {
                $this->$key = clone $value;
            }
        }
    }
}
