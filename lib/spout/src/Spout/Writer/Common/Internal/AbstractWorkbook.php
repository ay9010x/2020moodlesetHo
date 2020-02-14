<?php

namespace Box\Spout\Writer\Common\Internal;

use Box\Spout\Writer\Exception\SheetNotFoundException;


abstract class AbstractWorkbook implements WorkbookInterface
{
    
    protected $shouldCreateNewSheetsAutomatically;

    
    protected $worksheets = [];

    
    protected $currentWorksheet;

    
    public function __construct($shouldCreateNewSheetsAutomatically, $defaultRowStyle)
    {
        $this->shouldCreateNewSheetsAutomatically = $shouldCreateNewSheetsAutomatically;
    }

    
    abstract protected function getStyleHelper();

    
    abstract protected function getMaxRowsPerWorksheet();

    
    abstract public function addNewSheet();

    
    public function addNewSheetAndMakeItCurrent()
    {
        $worksheet = $this->addNewSheet();
        $this->setCurrentWorksheet($worksheet);

        return $worksheet;
    }

    
    public function getWorksheets()
    {
        return $this->worksheets;
    }

    
    public function getCurrentWorksheet()
    {
        return $this->currentWorksheet;
    }

    
    public function setCurrentSheet($sheet)
    {
        $worksheet = $this->getWorksheetFromExternalSheet($sheet);
        if ($worksheet !== null) {
            $this->currentWorksheet = $worksheet;
        } else {
            throw new SheetNotFoundException('The given sheet does not exist in the workbook.');
        }
    }

    
    protected function setCurrentWorksheet($worksheet)
    {
        $this->currentWorksheet = $worksheet;
    }

    
    protected function getWorksheetFromExternalSheet($sheet)
    {
        $worksheetFound = null;

        foreach ($this->worksheets as $worksheet) {
            if ($worksheet->getExternalSheet() === $sheet) {
                $worksheetFound = $worksheet;
                break;
            }
        }

        return $worksheetFound;
    }

    
    public function addRowToCurrentWorksheet($dataRow, $style)
    {
        $currentWorksheet = $this->getCurrentWorksheet();
        $hasReachedMaxRows = $this->hasCurrentWorkseetReachedMaxRows();
        $styleHelper = $this->getStyleHelper();

                if ($hasReachedMaxRows) {
                        if ($this->shouldCreateNewSheetsAutomatically) {
                $currentWorksheet = $this->addNewSheetAndMakeItCurrent();

                $updatedStyle = $styleHelper->applyExtraStylesIfNeeded($style, $dataRow);
                $registeredStyle = $styleHelper->registerStyle($updatedStyle);
                $currentWorksheet->addRow($dataRow, $registeredStyle);
            } else {
                            }
        } else {
            $updatedStyle = $styleHelper->applyExtraStylesIfNeeded($style, $dataRow);
            $registeredStyle = $styleHelper->registerStyle($updatedStyle);
            $currentWorksheet->addRow($dataRow, $registeredStyle);
        }
    }

    
    protected function hasCurrentWorkseetReachedMaxRows()
    {
        $currentWorksheet = $this->getCurrentWorksheet();
        return ($currentWorksheet->getLastWrittenRowIndex() >= $this->getMaxRowsPerWorksheet());
    }

    
    abstract public function close($finalFilePointer);
}
