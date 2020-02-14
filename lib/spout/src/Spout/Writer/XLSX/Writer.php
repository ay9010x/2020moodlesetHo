<?php

namespace Box\Spout\Writer\XLSX;

use Box\Spout\Writer\AbstractMultiSheetsWriter;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\XLSX\Internal\Workbook;


class Writer extends AbstractMultiSheetsWriter
{
    
    const DEFAULT_FONT_SIZE = 12;
    const DEFAULT_FONT_NAME = 'Calibri';

    
    protected static $headerContentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    
    protected $tempFolder;

    
    protected $shouldUseInlineStrings = true;

    
    protected $book;

    
    public function setTempFolder($tempFolder)
    {
        $this->throwIfWriterAlreadyOpened('Writer must be configured before opening it.');

        $this->tempFolder = $tempFolder;
        return $this;
    }

    
    public function setShouldUseInlineStrings($shouldUseInlineStrings)
    {
        $this->throwIfWriterAlreadyOpened('Writer must be configured before opening it.');

        $this->shouldUseInlineStrings = $shouldUseInlineStrings;
        return $this;
    }

    
    protected function openWriter()
    {
        if (!$this->book) {
            $tempFolder = ($this->tempFolder) ? : sys_get_temp_dir();
            $this->book = new Workbook($tempFolder, $this->shouldUseInlineStrings, $this->shouldCreateNewSheetsAutomatically, $this->defaultRowStyle);
            $this->book->addNewSheetAndMakeItCurrent();
        }
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

    
    protected function getDefaultRowStyle()
    {
        return (new StyleBuilder())
            ->setFontSize(self::DEFAULT_FONT_SIZE)
            ->setFontName(self::DEFAULT_FONT_NAME)
            ->build();
    }

    
    protected function closeWriter()
    {
        if ($this->book) {
            $this->book->close($this->filePointer);
        }
    }
}
