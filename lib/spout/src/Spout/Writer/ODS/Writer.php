<?php

namespace Box\Spout\Writer\ODS;

use Box\Spout\Writer\AbstractMultiSheetsWriter;
use Box\Spout\Writer\Common;
use Box\Spout\Writer\ODS\Internal\Workbook;


class Writer extends AbstractMultiSheetsWriter
{
    
    protected static $headerContentType = 'application/vnd.oasis.opendocument.spreadsheet';

    
    protected $tempFolder;

    
    protected $book;

    
    public function setTempFolder($tempFolder)
    {
        $this->throwIfWriterAlreadyOpened('Writer must be configured before opening it.');

        $this->tempFolder = $tempFolder;
        return $this;
    }

    
    protected function openWriter()
    {
        $tempFolder = ($this->tempFolder) ? : sys_get_temp_dir();
        $this->book = new Workbook($tempFolder, $this->shouldCreateNewSheetsAutomatically, $this->defaultRowStyle);
        $this->book->addNewSheetAndMakeItCurrent();
    }

    
    protected function getWorkbook()
    {
        return $this->book;
    }

    
    protected function addRowToWriter(array $dataRow, $style)
    {
        $this->throwIfBookIsNotAvailable();
        $this->book->addRowToCurrentWorksheet($dataRow, $style);
    }

    
    protected function closeWriter()
    {
        if ($this->book) {
            $this->book->close($this->filePointer);
        }
    }
}
