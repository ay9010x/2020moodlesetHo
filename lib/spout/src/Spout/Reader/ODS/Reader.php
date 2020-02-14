<?php

namespace Box\Spout\Reader\ODS;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\AbstractReader;


class Reader extends AbstractReader
{
    
    protected $zip;

    
    protected $sheetIterator;

    
    protected function doesSupportStreamWrapper()
    {
        return false;
    }

    
    protected function openReader($filePath)
    {
        $this->zip = new \ZipArchive();

        if ($this->zip->open($filePath) === true) {
            $this->sheetIterator = new SheetIterator($filePath);
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
    }
}
