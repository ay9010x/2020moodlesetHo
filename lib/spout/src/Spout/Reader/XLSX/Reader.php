<?php

namespace Box\Spout\Reader\XLSX;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\AbstractReader;
use Box\Spout\Reader\XLSX\Helper\SharedStringsHelper;


class Reader extends AbstractReader
{
    
    protected $tempFolder;

    
    protected $zip;

    
    protected $sharedStringsHelper;

    
    protected $sheetIterator;


    
    public function setTempFolder($tempFolder)
    {
        $this->tempFolder = $tempFolder;
        return $this;
    }

    
    protected function doesSupportStreamWrapper()
    {
        return false;
    }

    
    protected function openReader($filePath)
    {
        $this->zip = new \ZipArchive();

        if ($this->zip->open($filePath) === true) {
            $this->sharedStringsHelper = new SharedStringsHelper($filePath, $this->tempFolder);

            if ($this->sharedStringsHelper->hasSharedStrings()) {
                                $this->sharedStringsHelper->extractSharedStrings();
            }

            $this->sheetIterator = new SheetIterator($filePath, $this->sharedStringsHelper, $this->globalFunctionsHelper);
        } else {
            throw new IOException("Could not open $filePath for reading.");
        }
    }

    
    public function getConcreteSheetIterator()
    {
        return $this->sheetIterator;
    }

    
    protected function closeReader()
    {
        if ($this->zip) {
            $this->zip->close();
        }

        if ($this->sharedStringsHelper) {
            $this->sharedStringsHelper->cleanup();
        }
    }
}
