<?php

namespace Box\Spout\Writer\ODS\Internal;

use Box\Spout\Writer\Common\Internal\AbstractWorkbook;
use Box\Spout\Writer\ODS\Helper\FileSystemHelper;
use Box\Spout\Writer\ODS\Helper\StyleHelper;
use Box\Spout\Writer\Common\Sheet;


class Workbook extends AbstractWorkbook
{
    
    protected static $maxRowsPerWorksheet = 1048576;

    
    protected $fileSystemHelper;

    
    protected $styleHelper;

    
    public function __construct($tempFolder, $shouldCreateNewSheetsAutomatically, $defaultRowStyle)
    {
        parent::__construct($shouldCreateNewSheetsAutomatically, $defaultRowStyle);

        $this->fileSystemHelper = new FileSystemHelper($tempFolder);
        $this->fileSystemHelper->createBaseFilesAndFolders();

        $this->styleHelper = new StyleHelper($defaultRowStyle);
    }

    
    protected function getStyleHelper()
    {
        return $this->styleHelper;
    }

    
    protected function getMaxRowsPerWorksheet()
    {
        return self::$maxRowsPerWorksheet;
    }

    
    public function addNewSheet()
    {
        $newSheetIndex = count($this->worksheets);
        $sheet = new Sheet($newSheetIndex);

        $sheetsContentTempFolder = $this->fileSystemHelper->getSheetsContentTempFolder();
        $worksheet = new Worksheet($sheet, $sheetsContentTempFolder);
        $this->worksheets[] = $worksheet;

        return $worksheet;
    }

    
    public function close($finalFilePointer)
    {
        
        $worksheets = $this->worksheets;
        $numWorksheets = count($worksheets);

        foreach ($worksheets as $worksheet) {
            $worksheet->close();
        }

                $this->fileSystemHelper
            ->createContentFile($worksheets, $this->styleHelper)
            ->deleteWorksheetTempFolder()
            ->createStylesFile($this->styleHelper, $numWorksheets)
            ->zipRootFolderAndCopyToStream($finalFilePointer);

        $this->cleanupTempFolder();
    }

    
    protected function cleanupTempFolder()
    {
        $xlsxRootFolder = $this->fileSystemHelper->getRootFolder();
        $this->fileSystemHelper->deleteFolderRecursively($xlsxRootFolder);
    }
}
