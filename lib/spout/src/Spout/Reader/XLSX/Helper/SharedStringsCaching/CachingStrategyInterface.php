<?php

namespace Box\Spout\Reader\XLSX\Helper\SharedStringsCaching;


interface CachingStrategyInterface
{
    
    public function addStringForIndex($sharedString, $sharedStringIndex);

    
    public function closeCache();

    
    public function getStringAtIndex($sharedStringIndex);

    
    public function clearCache();
}
