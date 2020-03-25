<?php

namespace Box\Spout\Reader\XLSX\Helper\SharedStringsCaching;

use Box\Spout\Common\Helper\FileSystemHelper;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Reader\Exception\SharedStringNotFoundException;


class FileBasedStrategy implements CachingStrategyInterface
{
    
    const ESCAPED_LINE_FEED_CHARACTER = '_x000A_';

    
    protected $globalFunctionsHelper;

    
    protected $fileSystemHelper;

    
    protected $tempFolder;

    
    protected $maxNumStringsPerTempFile;

    
    protected $tempFilePointer;

    
    protected $inMemoryTempFilePath;

    
    protected $inMemoryTempFileContents;

    
    public function __construct($tempFolder, $maxNumStringsPerTempFile)
    {
        $rootTempFolder = ($tempFolder) ?: sys_get_temp_dir();
        $this->fileSystemHelper = new FileSystemHelper($rootTempFolder);
        $this->tempFolder = $this->fileSystemHelper->createFolder($rootTempFolder, uniqid('sharedstrings'));

        $this->maxNumStringsPerTempFile = $maxNumStringsPerTempFile;

        $this->globalFunctionsHelper = new GlobalFunctionsHelper();
        $this->tempFilePointer = null;
    }

    
    public function addStringForIndex($sharedString, $sharedStringIndex)
    {
        $tempFilePath = $this->getSharedStringTempFilePath($sharedStringIndex);

        if (!$this->globalFunctionsHelper->file_exists($tempFilePath)) {
            if ($this->tempFilePointer) {
                $this->globalFunctionsHelper->fclose($this->tempFilePointer);
            }
            $this->tempFilePointer = $this->globalFunctionsHelper->fopen($tempFilePath, 'w');
        }

                        $lineFeedEncodedSharedString = $this->escapeLineFeed($sharedString);

        $this->globalFunctionsHelper->fwrite($this->tempFilePointer, $lineFeedEncodedSharedString . PHP_EOL);
    }

    
    protected function getSharedStringTempFilePath($sharedStringIndex)
    {
        $numTempFile = intval($sharedStringIndex / $this->maxNumStringsPerTempFile);
        return $this->tempFolder . '/sharedstrings' . $numTempFile;
    }

    
    public function closeCache()
    {
                if ($this->tempFilePointer) {
            $this->globalFunctionsHelper->fclose($this->tempFilePointer);
        }
    }


    
    public function getStringAtIndex($sharedStringIndex)
    {
        $tempFilePath = $this->getSharedStringTempFilePath($sharedStringIndex);
        $indexInFile = $sharedStringIndex % $this->maxNumStringsPerTempFile;

        if (!$this->globalFunctionsHelper->file_exists($tempFilePath)) {
            throw new SharedStringNotFoundException("Shared string temp file not found: $tempFilePath ; for index: $sharedStringIndex");
        }

        if ($this->inMemoryTempFilePath !== $tempFilePath) {
                        unset($this->inMemoryTempFileContents);

            $this->inMemoryTempFileContents = explode(PHP_EOL, $this->globalFunctionsHelper->file_get_contents($tempFilePath));
            $this->inMemoryTempFilePath = $tempFilePath;
        }

        $sharedString = null;

                if (isset($this->inMemoryTempFileContents[$indexInFile])) {
            $escapedSharedString = $this->inMemoryTempFileContents[$indexInFile];
            $sharedString = $this->unescapeLineFeed($escapedSharedString);
        }

        if ($sharedString === null) {
            throw new SharedStringNotFoundException("Shared string not found for index: $sharedStringIndex");
        }

        return rtrim($sharedString, PHP_EOL);
    }

    
    private function escapeLineFeed($unescapedString)
    {
        return str_replace("\n", self::ESCAPED_LINE_FEED_CHARACTER, $unescapedString);
    }

    
    private function unescapeLineFeed($escapedString)
    {
        return str_replace(self::ESCAPED_LINE_FEED_CHARACTER, "\n", $escapedString);
    }

    
    public function clearCache()
    {
        if ($this->tempFolder) {
            $this->fileSystemHelper->deleteFolderRecursively($this->tempFolder);
        }
    }
}
