<?php

namespace Box\Spout\Writer\XLSX\Internal;

use Box\Spout\Writer\Common\Internal\AbstractWorkbook;
use Box\Spout\Writer\XLSX\Helper\FileSystemHelper;
use Box\Spout\Writer\XLSX\Helper\SharedStringsHelper;
use Box\Spout\Writer\XLSX\Helper\StyleHelper;
use Box\Spout\Writer\Common\Sheet;


class Workbook extends AbstractWorkbook
{
    
    protected static $maxRowsPerWorksheet = 1048576;

    
    protected $shouldUseInlineStrings;

    
    protected $fileSystemHelper;

    
    protected $sharedStringsHelper;

    
    protected $styleHelper;

    
    public function __construct($tempFolder, $shouldUseInlineStrings, $shouldCreateNewSheetsAutomatically, $defaultRowStyle)
    {
        parent::__construct($shouldCreateNewSheetsAutomatically, $defaultRowStyle);

        $this->shouldUseInlineStrings = $shouldUseInlineStrings;

        $this->fileSystemHelper = new FileSystemHelper($tempFolder);
        $this->fileSystemHelper->createBaseFilesAndFolders();

        $this->styleHelper = new StyleHelper($defaultRowStyle);

                $xlFolder = $this->fileSystemHelper->getXlFolder();
        $this->sharedStringsHelper = new SharedStringsHelper($xlFolder);
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

        $worksheetFilesFolder = $this->fileSystemHelper->getXlWorksheetsFolder();
        $worksheet = new Worksheet($sheet, $worksheetFilesFolder, $this->sharedStringsHelper, $this->shouldUseInlineStrings);
        $this->worksheets[] = $worksheet;

        return $worksheet;
    }

    
    public function close($finalFilePointer)
    {
        
        $worksheets = $this->worksheets;

        foreach ($worksheets as $worksheet) {
            $worksheet->close();
        }

        $this->sharedStringsHelper->close();

                $this->fileSystemHelper
            ->createContentTypesFile($worksheets)
            ->createWorkbookFile($worksheets)
            ->createWorkbookRelsFile($worksheets)
            ->createStylesFile($this->styleHelper)
            ->zipRootFolderAndCopyToStream($finalFilePointer);

        $this->cleanupTempFolder();
    }

    
    protected function cleanupTempFolder()
    {
        $xlsxRootFolder = $this->fileSystemHelper->getRootFolder();
        $this->fileSystemHelper->deleteFolderRecursively($xlsxRootFolder);
    }
}
