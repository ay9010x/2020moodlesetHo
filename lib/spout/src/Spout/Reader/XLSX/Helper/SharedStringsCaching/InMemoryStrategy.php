<?php

namespace Box\Spout\Reader\XLSX\Helper\SharedStringsCaching;

use Box\Spout\Reader\Exception\SharedStringNotFoundException;


class InMemoryStrategy implements CachingStrategyInterface
{
    
    protected $inMemoryCache;

    
    protected $isCacheClosed;

    
    public function __construct($sharedStringsUniqueCount)
    {
        $this->inMemoryCache = new \SplFixedArray($sharedStringsUniqueCount);
        $this->isCacheClosed = false;
    }

    
    public function addStringForIndex($sharedString, $sharedStringIndex)
    {
        if (!$this->isCacheClosed) {
            $this->inMemoryCache->offsetSet($sharedStringIndex, $sharedString);
        }
    }

    
    public function closeCache()
    {
        $this->isCacheClosed = true;
    }

    
    public function getStringAtIndex($sharedStringIndex)
    {
        try {
            return $this->inMemoryCache->offsetGet($sharedStringIndex);
        } catch (\RuntimeException $e) {
            throw new SharedStringNotFoundException("Shared string not found for index: $sharedStringIndex");
        }
    }

    
    public function clearCache()
    {
        unset($this->inMemoryCache);
        $this->isCacheClosed = false;
    }
}
