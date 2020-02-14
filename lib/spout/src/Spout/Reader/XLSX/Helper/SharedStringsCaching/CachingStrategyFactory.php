<?php

namespace Box\Spout\Reader\XLSX\Helper\SharedStringsCaching;


class CachingStrategyFactory
{
    
    const AMOUNT_MEMORY_NEEDED_PER_STRING_IN_KB = 12;

    
    const MAX_NUM_STRINGS_PER_TEMP_FILE = 10000;

    
    protected static $instance = null;

    
    private function __construct()
    {
    }

    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new CachingStrategyFactory();
        }

        return self::$instance;
    }

    
    public function getBestCachingStrategy($sharedStringsUniqueCount, $tempFolder = null)
    {
        if ($this->isInMemoryStrategyUsageSafe($sharedStringsUniqueCount)) {
            return new InMemoryStrategy($sharedStringsUniqueCount);
        } else {
            return new FileBasedStrategy($tempFolder, self::MAX_NUM_STRINGS_PER_TEMP_FILE);
        }
    }

    
    protected function isInMemoryStrategyUsageSafe($sharedStringsUniqueCount)
    {
        $memoryAvailable = $this->getMemoryLimitInKB();

        if ($memoryAvailable === -1) {
                        return ($sharedStringsUniqueCount < self::MAX_NUM_STRINGS_PER_TEMP_FILE);
        } else {
            $memoryNeeded = $sharedStringsUniqueCount * self::AMOUNT_MEMORY_NEEDED_PER_STRING_IN_KB;
            return ($memoryAvailable > $memoryNeeded);
        }
    }

    
    protected function getMemoryLimitInKB()
    {
        $memoryLimitFormatted = $this->getMemoryLimitFromIni();
        $memoryLimitFormatted = strtolower(trim($memoryLimitFormatted));

                if ($memoryLimitFormatted === '-1') {
            return -1;
        }

        if (preg_match('/(\d+)([bkmgt])b?/', $memoryLimitFormatted, $matches)) {
            $amount = intval($matches[1]);
            $unit = $matches[2];

            switch ($unit) {
                case 'b': return ($amount / 1024);
                case 'k': return $amount;
                case 'm': return ($amount * 1024);
                case 'g': return ($amount * 1024 * 1024);
                case 't': return ($amount * 1024 * 1024 * 1024);
            }
        }

        return -1;
    }

    
    protected function getMemoryLimitFromIni()
    {
        return ini_get('memory_limit');
    }
}
