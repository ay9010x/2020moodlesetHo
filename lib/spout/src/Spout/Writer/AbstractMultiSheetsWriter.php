<?php

namespace Box\Spout\Writer;

use Box\Spout\Writer\Exception\WriterNotOpenedException;


abstract class AbstractMultiSheetsWriter extends AbstractWriter
{
    
    protected $shouldCreateNewSheetsAutomatically = true;

    
    abstract protected function getWorkbook();

    
    public function setShouldCreateNewSheetsAutomatically($shouldCreateNewSheetsAutomatically)
    {
        $this->throwIfWriterAlreadyOpened('Writer must be configured before opening it.');

        $this->shouldCreateNewSheetsAutomatically = $shouldCreateNewSheetsAutomatically;
        return $this;
    }

    
    public function getSheets()
    {
        $this->throwIfBookIsNotAvailable();

        $externalSheets = [];
        $worksheets = $this->getWorkbook()->getWorksheets();

        
        foreach ($worksheets as $worksheet) {
            $externalSheets[] = $worksheet->getExternalSheet();
        }

        return $externalSheets;
    }

    
    public function addNewSheetAndMakeItCurrent()
    {
        $this->throwIfBookIsNotAvailable();
        $worksheet = $this->getWorkbook()->addNewSheetAndMakeItCurrent();

        return $worksheet->getExternalSheet();
    }

    
    public function getCurrentSheet()
    {
        $this->throwIfBookIsNotAvailable();
        return $this->getWorkbook()->getCurrentWorksheet()->getExternalSheet();
    }

    
    public function setCurrentSheet($sheet)
    {
        $this->throwIfBookIsNotAvailable();
        $this->getWorkbook()->setCurrentSheet($sheet);
    }

    
    protected function throwIfBookIsNotAvailable()
    {
        if (!$this->getWorkbook()) {
            throw new WriterNotOpenedException('The writer must be opened before performing this action.');
        }
    }
}

