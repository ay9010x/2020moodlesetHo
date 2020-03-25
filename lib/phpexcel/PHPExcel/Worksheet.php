<?php


class PHPExcel_Worksheet implements PHPExcel_IComparable
{
    
    const BREAK_NONE   = 0;
    const BREAK_ROW    = 1;
    const BREAK_COLUMN = 2;

    
    const SHEETSTATE_VISIBLE    = 'visible';
    const SHEETSTATE_HIDDEN     = 'hidden';
    const SHEETSTATE_VERYHIDDEN = 'veryHidden';

    
    private static $invalidCharacters = array('*', ':', '/', '\\', '?', '[', ']');

    
    private $parent;

    
    private $cellCollection;

    
    private $rowDimensions = array();

    
    private $defaultRowDimension;

    
    private $columnDimensions = array();

    
    private $defaultColumnDimension = null;

    
    private $drawingCollection = null;

    
    private $chartCollection = array();

    
    private $title;

    
    private $sheetState;

    
    private $pageSetup;

    
    private $pageMargins;

    
    private $headerFooter;

    
    private $sheetView;

    
    private $protection;

    
    private $styles = array();

    
    private $conditionalStylesCollection = array();

    
    private $cellCollectionIsSorted = false;

    
    private $breaks = array();

    
    private $mergeCells = array();

    
    private $protectedCells = array();

    
    private $autoFilter;

    
    private $freezePane = '';

    
    private $showGridlines = true;

    
    private $printGridlines = false;

    
    private $showRowColHeaders = true;

    
    private $showSummaryBelow = true;

    
    private $showSummaryRight = true;

    
    private $comments = array();

    
    private $activeCell = 'A1';

    
    private $selectedCells = 'A1';

    
    private $cachedHighestColumn = 'A';

    
    private $cachedHighestRow = 1;

    
    private $rightToLeft = false;

    
    private $hyperlinkCollection = array();

    
    private $dataValidationCollection = array();

    
    private $tabColor;

    
    private $dirty = true;

    
    private $hash;

    
    private $codeName = null;

    
    public function __construct(PHPExcel $pParent = null, $pTitle = 'Worksheet')
    {
                $this->parent = $pParent;
        $this->setTitle($pTitle, false);
                $this->setCodeName($this->getTitle());
        $this->setSheetState(PHPExcel_Worksheet::SHEETSTATE_VISIBLE);

        $this->cellCollection         = PHPExcel_CachedObjectStorageFactory::getInstance($this);
                $this->pageSetup              = new PHPExcel_Worksheet_PageSetup();
                $this->pageMargins            = new PHPExcel_Worksheet_PageMargins();
                $this->headerFooter           = new PHPExcel_Worksheet_HeaderFooter();
                $this->sheetView              = new PHPExcel_Worksheet_SheetView();
                $this->drawingCollection      = new ArrayObject();
                $this->chartCollection        = new ArrayObject();
                $this->protection             = new PHPExcel_Worksheet_Protection();
                $this->defaultRowDimension    = new PHPExcel_Worksheet_RowDimension(null);
                $this->defaultColumnDimension = new PHPExcel_Worksheet_ColumnDimension(null);
        $this->autoFilter             = new PHPExcel_Worksheet_AutoFilter(null, $this);
    }


    
    public function disconnectCells()
    {
        if ($this->cellCollection !== null) {
            $this->cellCollection->unsetWorksheetCells();
            $this->cellCollection = null;
        }
                $this->parent = null;
    }

    
    public function __destruct()
    {
        PHPExcel_Calculation::getInstance($this->parent)->clearCalculationCacheForWorksheet($this->title);

        $this->disconnectCells();
    }

   
    public function getCellCacheController()
    {
        return $this->cellCollection;
    }    

    
    public static function getInvalidCharacters()
    {
        return self::$invalidCharacters;
    }

    
    private static function checkSheetCodeName($pValue)
    {
        $CharCount = PHPExcel_Shared_String::CountCharacters($pValue);
        if ($CharCount == 0) {
            throw new PHPExcel_Exception('Sheet code name cannot be empty.');
        }
                if ((str_replace(self::$invalidCharacters, '', $pValue) !== $pValue) ||
            (PHPExcel_Shared_String::Substring($pValue, -1, 1)=='\'') ||
            (PHPExcel_Shared_String::Substring($pValue, 0, 1)=='\'')) {
            throw new PHPExcel_Exception('Invalid character found in sheet code name');
        }

                if ($CharCount > 31) {
            throw new PHPExcel_Exception('Maximum 31 characters allowed in sheet code name.');
        }

        return $pValue;
    }

   
    private static function checkSheetTitle($pValue)
    {
                if (str_replace(self::$invalidCharacters, '', $pValue) !== $pValue) {
            throw new PHPExcel_Exception('Invalid character found in sheet title');
        }

                if (PHPExcel_Shared_String::CountCharacters($pValue) > 31) {
            throw new PHPExcel_Exception('Maximum 31 characters allowed in sheet title.');
        }

        return $pValue;
    }

    
    public function getCellCollection($pSorted = true)
    {
        if ($pSorted) {
                        return $this->sortCellCollection();
        }
        if ($this->cellCollection !== null) {
            return $this->cellCollection->getCellList();
        }
        return array();
    }

    
    public function sortCellCollection()
    {
        if ($this->cellCollection !== null) {
            return $this->cellCollection->getSortedCellList();
        }
        return array();
    }

    
    public function getRowDimensions()
    {
        return $this->rowDimensions;
    }

    
    public function getDefaultRowDimension()
    {
        return $this->defaultRowDimension;
    }

    
    public function getColumnDimensions()
    {
        return $this->columnDimensions;
    }

    
    public function getDefaultColumnDimension()
    {
        return $this->defaultColumnDimension;
    }

    
    public function getDrawingCollection()
    {
        return $this->drawingCollection;
    }

    
    public function getChartCollection()
    {
        return $this->chartCollection;
    }

    
    public function addChart(PHPExcel_Chart $pChart = null, $iChartIndex = null)
    {
        $pChart->setWorksheet($this);
        if (is_null($iChartIndex)) {
            $this->chartCollection[] = $pChart;
        } else {
                        array_splice($this->chartCollection, $iChartIndex, 0, array($pChart));
        }

        return $pChart;
    }

    
    public function getChartCount()
    {
        return count($this->chartCollection);
    }

    
    public function getChartByIndex($index = null)
    {
        $chartCount = count($this->chartCollection);
        if ($chartCount == 0) {
            return false;
        }
        if (is_null($index)) {
            $index = --$chartCount;
        }
        if (!isset($this->chartCollection[$index])) {
            return false;
        }

        return $this->chartCollection[$index];
    }

    
    public function getChartNames()
    {
        $chartNames = array();
        foreach ($this->chartCollection as $chart) {
            $chartNames[] = $chart->getName();
        }
        return $chartNames;
    }

    
    public function getChartByName($chartName = '')
    {
        $chartCount = count($this->chartCollection);
        if ($chartCount == 0) {
            return false;
        }
        foreach ($this->chartCollection as $index => $chart) {
            if ($chart->getName() == $chartName) {
                return $this->chartCollection[$index];
            }
        }
        return false;
    }

    
    public function refreshColumnDimensions()
    {
        $currentColumnDimensions = $this->getColumnDimensions();
        $newColumnDimensions = array();

        foreach ($currentColumnDimensions as $objColumnDimension) {
            $newColumnDimensions[$objColumnDimension->getColumnIndex()] = $objColumnDimension;
        }

        $this->columnDimensions = $newColumnDimensions;

        return $this;
    }

    
    public function refreshRowDimensions()
    {
        $currentRowDimensions = $this->getRowDimensions();
        $newRowDimensions = array();

        foreach ($currentRowDimensions as $objRowDimension) {
            $newRowDimensions[$objRowDimension->getRowIndex()] = $objRowDimension;
        }

        $this->rowDimensions = $newRowDimensions;

        return $this;
    }

    
    public function calculateWorksheetDimension()
    {
                return 'A1' . ':' .  $this->getHighestColumn() . $this->getHighestRow();
    }

    
    public function calculateWorksheetDataDimension()
    {
                return 'A1' . ':' .  $this->getHighestDataColumn() . $this->getHighestDataRow();
    }

    
    public function calculateColumnWidths($calculateMergeCells = false)
    {
                $autoSizes = array();
        foreach ($this->getColumnDimensions() as $colDimension) {
            if ($colDimension->getAutoSize()) {
                $autoSizes[$colDimension->getColumnIndex()] = -1;
            }
        }

                if (!empty($autoSizes)) {
                        $isMergeCell = array();
            foreach ($this->getMergeCells() as $cells) {
                foreach (PHPExcel_Cell::extractAllCellReferencesInRange($cells) as $cellReference) {
                    $isMergeCell[$cellReference] = true;
                }
            }

                        foreach ($this->getCellCollection(false) as $cellID) {
                $cell = $this->getCell($cellID);
                if (isset($autoSizes[$this->cellCollection->getCurrentColumn()])) {
                                        if (!isset($isMergeCell[$this->cellCollection->getCurrentAddress()])) {
                                                                        $cellValue = PHPExcel_Style_NumberFormat::toFormattedString(
                            $cell->getCalculatedValue(),
                            $this->getParent()->getCellXfByIndex($cell->getXfIndex())->getNumberFormat()->getFormatCode()
                        );

                        $autoSizes[$this->cellCollection->getCurrentColumn()] = max(
                            (float) $autoSizes[$this->cellCollection->getCurrentColumn()],
                            (float)PHPExcel_Shared_Font::calculateColumnWidth(
                                $this->getParent()->getCellXfByIndex($cell->getXfIndex())->getFont(),
                                $cellValue,
                                $this->getParent()->getCellXfByIndex($cell->getXfIndex())->getAlignment()->getTextRotation(),
                                $this->getDefaultStyle()->getFont()
                            )
                        );
                    }
                }
            }

                        foreach ($autoSizes as $columnIndex => $width) {
                if ($width == -1) {
                    $width = $this->getDefaultColumnDimension()->getWidth();
                }
                $this->getColumnDimension($columnIndex)->setWidth($width);
            }
        }

        return $this;
    }

    
    public function getParent()
    {
        return $this->parent;
    }

    
    public function rebindParent(PHPExcel $parent)
    {
        if ($this->parent !== null) {
            $namedRanges = $this->parent->getNamedRanges();
            foreach ($namedRanges as $namedRange) {
                $parent->addNamedRange($namedRange);
            }

            $this->parent->removeSheetByIndex(
                $this->parent->getIndex($this)
            );
        }
        $this->parent = $parent;

        return $this;
    }

    
    public function getTitle()
    {
        return $this->title;
    }

    
    public function setTitle($pValue = 'Worksheet', $updateFormulaCellReferences = true)
    {
                if ($this->getTitle() == $pValue) {
            return $this;
        }

                self::checkSheetTitle($pValue);

                $oldTitle = $this->getTitle();

        if ($this->parent) {
                        if ($this->parent->sheetNameExists($pValue)) {
                
                if (PHPExcel_Shared_String::CountCharacters($pValue) > 29) {
                    $pValue = PHPExcel_Shared_String::Substring($pValue, 0, 29);
                }
                $i = 1;
                while ($this->parent->sheetNameExists($pValue . ' ' . $i)) {
                    ++$i;
                    if ($i == 10) {
                        if (PHPExcel_Shared_String::CountCharacters($pValue) > 28) {
                            $pValue = PHPExcel_Shared_String::Substring($pValue, 0, 28);
                        }
                    } elseif ($i == 100) {
                        if (PHPExcel_Shared_String::CountCharacters($pValue) > 27) {
                            $pValue = PHPExcel_Shared_String::Substring($pValue, 0, 27);
                        }
                    }
                }

                $altTitle = $pValue . ' ' . $i;
                return $this->setTitle($altTitle, $updateFormulaCellReferences);
            }
        }

                $this->title = $pValue;
        $this->dirty = true;

        if ($this->parent) {
                        $newTitle = $this->getTitle();
            PHPExcel_Calculation::getInstance($this->parent)
                ->renameCalculationCacheForWorksheet($oldTitle, $newTitle);
            if ($updateFormulaCellReferences) {
                PHPExcel_ReferenceHelper::getInstance()->updateNamedFormulas($this->parent, $oldTitle, $newTitle);
            }
        }

        return $this;
    }

    
    public function getSheetState()
    {
        return $this->sheetState;
    }

    
    public function setSheetState($value = PHPExcel_Worksheet::SHEETSTATE_VISIBLE)
    {
        $this->sheetState = $value;
        return $this;
    }

    
    public function getPageSetup()
    {
        return $this->pageSetup;
    }

    
    public function setPageSetup(PHPExcel_Worksheet_PageSetup $pValue)
    {
        $this->pageSetup = $pValue;
        return $this;
    }

    
    public function getPageMargins()
    {
        return $this->pageMargins;
    }

    
    public function setPageMargins(PHPExcel_Worksheet_PageMargins $pValue)
    {
        $this->pageMargins = $pValue;
        return $this;
    }

    
    public function getHeaderFooter()
    {
        return $this->headerFooter;
    }

    
    public function setHeaderFooter(PHPExcel_Worksheet_HeaderFooter $pValue)
    {
        $this->headerFooter = $pValue;
        return $this;
    }

    
    public function getSheetView()
    {
        return $this->sheetView;
    }

    
    public function setSheetView(PHPExcel_Worksheet_SheetView $pValue)
    {
        $this->sheetView = $pValue;
        return $this;
    }

    
    public function getProtection()
    {
        return $this->protection;
    }

    
    public function setProtection(PHPExcel_Worksheet_Protection $pValue)
    {
        $this->protection = $pValue;
        $this->dirty = true;

        return $this;
    }

    
    public function getHighestColumn($row = null)
    {
        if ($row == null) {
            return $this->cachedHighestColumn;
        }
        return $this->getHighestDataColumn($row);
    }

    
    public function getHighestDataColumn($row = null)
    {
        return $this->cellCollection->getHighestColumn($row);
    }

    
    public function getHighestRow($column = null)
    {
        if ($column == null) {
            return $this->cachedHighestRow;
        }
        return $this->getHighestDataRow($column);
    }

    
    public function getHighestDataRow($column = null)
    {
        return $this->cellCollection->getHighestRow($column);
    }

    
    public function getHighestRowAndColumn()
    {
        return $this->cellCollection->getHighestRowAndColumn();
    }

    
    public function setCellValue($pCoordinate = 'A1', $pValue = null, $returnCell = false)
    {
        $cell = $this->getCell(strtoupper($pCoordinate))->setValue($pValue);
        return ($returnCell) ? $cell : $this;
    }

    
    public function setCellValueByColumnAndRow($pColumn = 0, $pRow = 1, $pValue = null, $returnCell = false)
    {
        $cell = $this->getCellByColumnAndRow($pColumn, $pRow)->setValue($pValue);
        return ($returnCell) ? $cell : $this;
    }

    
    public function setCellValueExplicit($pCoordinate = 'A1', $pValue = null, $pDataType = PHPExcel_Cell_DataType::TYPE_STRING, $returnCell = false)
    {
                $cell = $this->getCell(strtoupper($pCoordinate))->setValueExplicit($pValue, $pDataType);
        return ($returnCell) ? $cell : $this;
    }

    
    public function setCellValueExplicitByColumnAndRow($pColumn = 0, $pRow = 1, $pValue = null, $pDataType = PHPExcel_Cell_DataType::TYPE_STRING, $returnCell = false)
    {
        $cell = $this->getCellByColumnAndRow($pColumn, $pRow)->setValueExplicit($pValue, $pDataType);
        return ($returnCell) ? $cell : $this;
    }

    
    public function getCell($pCoordinate = 'A1')
    {
                if ($this->cellCollection->isDataSet(strtoupper($pCoordinate))) {
            return $this->cellCollection->getCacheData($pCoordinate);
        }

                if (strpos($pCoordinate, '!') !== false) {
            $worksheetReference = PHPExcel_Worksheet::extractSheetTitle($pCoordinate, true);
            return $this->parent->getSheetByName($worksheetReference[0])->getCell(strtoupper($worksheetReference[1]));
        }

                if ((!preg_match('/^'.PHPExcel_Calculation::CALCULATION_REGEXP_CELLREF.'$/i', $pCoordinate, $matches)) &&
            (preg_match('/^'.PHPExcel_Calculation::CALCULATION_REGEXP_NAMEDRANGE.'$/i', $pCoordinate, $matches))) {
            $namedRange = PHPExcel_NamedRange::resolveRange($pCoordinate, $this);
            if ($namedRange !== null) {
                $pCoordinate = $namedRange->getRange();
                return $namedRange->getWorksheet()->getCell($pCoordinate);
            }
        }

                $pCoordinate = strtoupper($pCoordinate);

        if (strpos($pCoordinate, ':') !== false || strpos($pCoordinate, ',') !== false) {
            throw new PHPExcel_Exception('Cell coordinate can not be a range of cells.');
        } elseif (strpos($pCoordinate, '$') !== false) {
            throw new PHPExcel_Exception('Cell coordinate must not be absolute.');
        }

                return $this->createNewCell($pCoordinate);
    }

    
    public function getCellByColumnAndRow($pColumn = 0, $pRow = 1)
    {
        $columnLetter = PHPExcel_Cell::stringFromColumnIndex($pColumn);
        $coordinate = $columnLetter . $pRow;

        if ($this->cellCollection->isDataSet($coordinate)) {
            return $this->cellCollection->getCacheData($coordinate);
        }

        return $this->createNewCell($coordinate);
    }

    
    private function createNewCell($pCoordinate)
    {
        $cell = $this->cellCollection->addCacheData(
            $pCoordinate,
            new PHPExcel_Cell(null, PHPExcel_Cell_DataType::TYPE_NULL, $this)
        );
        $this->cellCollectionIsSorted = false;

                $aCoordinates = PHPExcel_Cell::coordinateFromString($pCoordinate);
        if (PHPExcel_Cell::columnIndexFromString($this->cachedHighestColumn) < PHPExcel_Cell::columnIndexFromString($aCoordinates[0])) {
            $this->cachedHighestColumn = $aCoordinates[0];
        }
        $this->cachedHighestRow = max($this->cachedHighestRow, $aCoordinates[1]);

                        $rowDimension    = $this->getRowDimension($aCoordinates[1], false);
        $columnDimension = $this->getColumnDimension($aCoordinates[0], false);

        if ($rowDimension !== null && $rowDimension->getXfIndex() > 0) {
                        $cell->setXfIndex($rowDimension->getXfIndex());
        } elseif ($columnDimension !== null && $columnDimension->getXfIndex() > 0) {
                        $cell->setXfIndex($columnDimension->getXfIndex());
        }

        return $cell;
    }

    
    public function cellExists($pCoordinate = 'A1')
    {
               if (strpos($pCoordinate, '!') !== false) {
            $worksheetReference = PHPExcel_Worksheet::extractSheetTitle($pCoordinate, true);
            return $this->parent->getSheetByName($worksheetReference[0])->cellExists(strtoupper($worksheetReference[1]));
        }

                if ((!preg_match('/^'.PHPExcel_Calculation::CALCULATION_REGEXP_CELLREF.'$/i', $pCoordinate, $matches)) &&
            (preg_match('/^'.PHPExcel_Calculation::CALCULATION_REGEXP_NAMEDRANGE.'$/i', $pCoordinate, $matches))) {
            $namedRange = PHPExcel_NamedRange::resolveRange($pCoordinate, $this);
            if ($namedRange !== null) {
                $pCoordinate = $namedRange->getRange();
                if ($this->getHashCode() != $namedRange->getWorksheet()->getHashCode()) {
                    if (!$namedRange->getLocalOnly()) {
                        return $namedRange->getWorksheet()->cellExists($pCoordinate);
                    } else {
                        throw new PHPExcel_Exception('Named range ' . $namedRange->getName() . ' is not accessible from within sheet ' . $this->getTitle());
                    }
                }
            } else {
                return false;
            }
        }

                $pCoordinate = strtoupper($pCoordinate);

        if (strpos($pCoordinate, ':') !== false || strpos($pCoordinate, ',') !== false) {
            throw new PHPExcel_Exception('Cell coordinate can not be a range of cells.');
        } elseif (strpos($pCoordinate, '$') !== false) {
            throw new PHPExcel_Exception('Cell coordinate must not be absolute.');
        } else {
                        $aCoordinates = PHPExcel_Cell::coordinateFromString($pCoordinate);

                        return $this->cellCollection->isDataSet($pCoordinate);
        }
    }

    
    public function cellExistsByColumnAndRow($pColumn = 0, $pRow = 1)
    {
        return $this->cellExists(PHPExcel_Cell::stringFromColumnIndex($pColumn) . $pRow);
    }

    
    public function getRowDimension($pRow = 1, $create = true)
    {
                $found = null;

                if (!isset($this->rowDimensions[$pRow])) {
            if (!$create) {
                return null;
            }
            $this->rowDimensions[$pRow] = new PHPExcel_Worksheet_RowDimension($pRow);

            $this->cachedHighestRow = max($this->cachedHighestRow, $pRow);
        }
        return $this->rowDimensions[$pRow];
    }

    
    public function getColumnDimension($pColumn = 'A', $create = true)
    {
                $pColumn = strtoupper($pColumn);

                if (!isset($this->columnDimensions[$pColumn])) {
            if (!$create) {
                return null;
            }
            $this->columnDimensions[$pColumn] = new PHPExcel_Worksheet_ColumnDimension($pColumn);

            if (PHPExcel_Cell::columnIndexFromString($this->cachedHighestColumn) < PHPExcel_Cell::columnIndexFromString($pColumn)) {
                $this->cachedHighestColumn = $pColumn;
            }
        }
        return $this->columnDimensions[$pColumn];
    }

    
    public function getColumnDimensionByColumn($pColumn = 0)
    {
        return $this->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($pColumn));
    }

    
    public function getStyles()
    {
        return $this->styles;
    }

    
    public function getDefaultStyle()
    {
        return $this->parent->getDefaultStyle();
    }

    
    public function setDefaultStyle(PHPExcel_Style $pValue)
    {
        $this->parent->getDefaultStyle()->applyFromArray(array(
            'font' => array(
                'name' => $pValue->getFont()->getName(),
                'size' => $pValue->getFont()->getSize(),
            ),
        ));
        return $this;
    }

    
    public function getStyle($pCellCoordinate = 'A1')
    {
                $this->parent->setActiveSheetIndex($this->parent->getIndex($this));

                $this->setSelectedCells(strtoupper($pCellCoordinate));

        return $this->parent->getCellXfSupervisor();
    }

    
    public function getConditionalStyles($pCoordinate = 'A1')
    {
        $pCoordinate = strtoupper($pCoordinate);
        if (!isset($this->conditionalStylesCollection[$pCoordinate])) {
            $this->conditionalStylesCollection[$pCoordinate] = array();
        }
        return $this->conditionalStylesCollection[$pCoordinate];
    }

    
    public function conditionalStylesExists($pCoordinate = 'A1')
    {
        if (isset($this->conditionalStylesCollection[strtoupper($pCoordinate)])) {
            return true;
        }
        return false;
    }

    
    public function removeConditionalStyles($pCoordinate = 'A1')
    {
        unset($this->conditionalStylesCollection[strtoupper($pCoordinate)]);
        return $this;
    }

    
    public function getConditionalStylesCollection()
    {
        return $this->conditionalStylesCollection;
    }

    
    public function setConditionalStyles($pCoordinate = 'A1', $pValue)
    {
        $this->conditionalStylesCollection[strtoupper($pCoordinate)] = $pValue;
        return $this;
    }

    
    public function getStyleByColumnAndRow($pColumn = 0, $pRow = 1, $pColumn2 = null, $pRow2 = null)
    {
        if (!is_null($pColumn2) && !is_null($pRow2)) {
            $cellRange = PHPExcel_Cell::stringFromColumnIndex($pColumn) . $pRow . ':' . PHPExcel_Cell::stringFromColumnIndex($pColumn2) . $pRow2;
            return $this->getStyle($cellRange);
        }

        return $this->getStyle(PHPExcel_Cell::stringFromColumnIndex($pColumn) . $pRow);
    }

    
    public function setSharedStyle(PHPExcel_Style $pSharedCellStyle = null, $pRange = '')
    {
        $this->duplicateStyle($pSharedCellStyle, $pRange);
        return $this;
    }

    
    public function duplicateStyle(PHPExcel_Style $pCellStyle = null, $pRange = '')
    {
                $style = $pCellStyle->getIsSupervisor() ? $pCellStyle->getSharedComponent() : $pCellStyle;

                $workbook = $this->parent;
        if ($existingStyle = $this->parent->getCellXfByHashCode($pCellStyle->getHashCode())) {
                        $xfIndex = $existingStyle->getIndex();
        } else {
                        $workbook->addCellXf($pCellStyle);
            $xfIndex = $pCellStyle->getIndex();
        }

                list($rangeStart, $rangeEnd) = PHPExcel_Cell::rangeBoundaries($pRange . ':' . $pRange);

                if ($rangeStart[0] > $rangeEnd[0] && $rangeStart[1] > $rangeEnd[1]) {
            $tmp = $rangeStart;
            $rangeStart = $rangeEnd;
            $rangeEnd = $tmp;
        }

                for ($col = $rangeStart[0]; $col <= $rangeEnd[0]; ++$col) {
            for ($row = $rangeStart[1]; $row <= $rangeEnd[1]; ++$row) {
                $this->getCell(PHPExcel_Cell::stringFromColumnIndex($col - 1) . $row)->setXfIndex($xfIndex);
            }
        }

        return $this;
    }

    
    public function duplicateConditionalStyle(array $pCellStyle = null, $pRange = '')
    {
        foreach ($pCellStyle as $cellStyle) {
            if (!($cellStyle instanceof PHPExcel_Style_Conditional)) {
                throw new PHPExcel_Exception('Style is not a conditional style');
            }
        }

                list($rangeStart, $rangeEnd) = PHPExcel_Cell::rangeBoundaries($pRange . ':' . $pRange);

                if ($rangeStart[0] > $rangeEnd[0] && $rangeStart[1] > $rangeEnd[1]) {
            $tmp = $rangeStart;
            $rangeStart = $rangeEnd;
            $rangeEnd = $tmp;
        }

                for ($col = $rangeStart[0]; $col <= $rangeEnd[0]; ++$col) {
            for ($row = $rangeStart[1]; $row <= $rangeEnd[1]; ++$row) {
                $this->setConditionalStyles(PHPExcel_Cell::stringFromColumnIndex($col - 1) . $row, $pCellStyle);
            }
        }

        return $this;
    }

    
    public function duplicateStyleArray($pStyles = null, $pRange = '', $pAdvanced = true)
    {
        $this->getStyle($pRange)->applyFromArray($pStyles, $pAdvanced);
        return $this;
    }

    
    public function setBreak($pCell = 'A1', $pBreak = PHPExcel_Worksheet::BREAK_NONE)
    {
                $pCell = strtoupper($pCell);

        if ($pCell != '') {
            if ($pBreak == PHPExcel_Worksheet::BREAK_NONE) {
                if (isset($this->breaks[$pCell])) {
                    unset($this->breaks[$pCell]);
                }
            } else {
                $this->breaks[$pCell] = $pBreak;
            }
        } else {
            throw new PHPExcel_Exception('No cell coordinate specified.');
        }

        return $this;
    }

    
    public function setBreakByColumnAndRow($pColumn = 0, $pRow = 1, $pBreak = PHPExcel_Worksheet::BREAK_NONE)
    {
        return $this->setBreak(PHPExcel_Cell::stringFromColumnIndex($pColumn) . $pRow, $pBreak);
    }

    
    public function getBreaks()
    {
        return $this->breaks;
    }

    
    public function mergeCells($pRange = 'A1:A1')
    {
                $pRange = strtoupper($pRange);

        if (strpos($pRange, ':') !== false) {
            $this->mergeCells[$pRange] = $pRange;

            
                        $aReferences = PHPExcel_Cell::extractAllCellReferencesInRange($pRange);

                        $upperLeft = $aReferences[0];
            if (!$this->cellExists($upperLeft)) {
                $this->getCell($upperLeft)->setValueExplicit(null, PHPExcel_Cell_DataType::TYPE_NULL);
            }

                        $count = count($aReferences);
            for ($i = 1; $i < $count; $i++) {
                $this->getCell($aReferences[$i])->setValueExplicit(null, PHPExcel_Cell_DataType::TYPE_NULL);
            }
        } else {
            throw new PHPExcel_Exception('Merge must be set on a range of cells.');
        }

        return $this;
    }

    
    public function mergeCellsByColumnAndRow($pColumn1 = 0, $pRow1 = 1, $pColumn2 = 0, $pRow2 = 1)
    {
        $cellRange = PHPExcel_Cell::stringFromColumnIndex($pColumn1) . $pRow1 . ':' . PHPExcel_Cell::stringFromColumnIndex($pColumn2) . $pRow2;
        return $this->mergeCells($cellRange);
    }

    
    public function unmergeCells($pRange = 'A1:A1')
    {
                $pRange = strtoupper($pRange);

        if (strpos($pRange, ':') !== false) {
            if (isset($this->mergeCells[$pRange])) {
                unset($this->mergeCells[$pRange]);
            } else {
                throw new PHPExcel_Exception('Cell range ' . $pRange . ' not known as merged.');
            }
        } else {
            throw new PHPExcel_Exception('Merge can only be removed from a range of cells.');
        }

        return $this;
    }

    
    public function unmergeCellsByColumnAndRow($pColumn1 = 0, $pRow1 = 1, $pColumn2 = 0, $pRow2 = 1)
    {
        $cellRange = PHPExcel_Cell::stringFromColumnIndex($pColumn1) . $pRow1 . ':' . PHPExcel_Cell::stringFromColumnIndex($pColumn2) . $pRow2;
        return $this->unmergeCells($cellRange);
    }

    
    public function getMergeCells()
    {
        return $this->mergeCells;
    }

    
    public function setMergeCells($pValue = array())
    {
        $this->mergeCells = $pValue;
        return $this;
    }

    
    public function protectCells($pRange = 'A1', $pPassword = '', $pAlreadyHashed = false)
    {
                $pRange = strtoupper($pRange);

        if (!$pAlreadyHashed) {
            $pPassword = PHPExcel_Shared_PasswordHasher::hashPassword($pPassword);
        }
        $this->protectedCells[$pRange] = $pPassword;

        return $this;
    }

    
    public function protectCellsByColumnAndRow($pColumn1 = 0, $pRow1 = 1, $pColumn2 = 0, $pRow2 = 1, $pPassword = '', $pAlreadyHashed = false)
    {
        $cellRange = PHPExcel_Cell::stringFromColumnIndex($pColumn1) . $pRow1 . ':' . PHPExcel_Cell::stringFromColumnIndex($pColumn2) . $pRow2;
        return $this->protectCells($cellRange, $pPassword, $pAlreadyHashed);
    }

    
    public function unprotectCells($pRange = 'A1')
    {
                $pRange = strtoupper($pRange);

        if (isset($this->protectedCells[$pRange])) {
            unset($this->protectedCells[$pRange]);
        } else {
            throw new PHPExcel_Exception('Cell range ' . $pRange . ' not known as protected.');
        }
        return $this;
    }

    
    public function unprotectCellsByColumnAndRow($pColumn1 = 0, $pRow1 = 1, $pColumn2 = 0, $pRow2 = 1, $pPassword = '', $pAlreadyHashed = false)
    {
        $cellRange = PHPExcel_Cell::stringFromColumnIndex($pColumn1) . $pRow1 . ':' . PHPExcel_Cell::stringFromColumnIndex($pColumn2) . $pRow2;
        return $this->unprotectCells($cellRange, $pPassword, $pAlreadyHashed);
    }

    
    public function getProtectedCells()
    {
        return $this->protectedCells;
    }

    
    public function getAutoFilter()
    {
        return $this->autoFilter;
    }

    
    public function setAutoFilter($pValue)
    {
        $pRange = strtoupper($pValue);
        if (is_string($pValue)) {
            $this->autoFilter->setRange($pValue);
        } elseif (is_object($pValue) && ($pValue instanceof PHPExcel_Worksheet_AutoFilter)) {
            $this->autoFilter = $pValue;
        }
        return $this;
    }

    
    public function setAutoFilterByColumnAndRow($pColumn1 = 0, $pRow1 = 1, $pColumn2 = 0, $pRow2 = 1)
    {
        return $this->setAutoFilter(
            PHPExcel_Cell::stringFromColumnIndex($pColumn1) . $pRow1
            . ':' .
            PHPExcel_Cell::stringFromColumnIndex($pColumn2) . $pRow2
        );
    }

    
    public function removeAutoFilter()
    {
        $this->autoFilter->setRange(null);
        return $this;
    }

    
    public function getFreezePane()
    {
        return $this->freezePane;
    }

    
    public function freezePane($pCell = '')
    {
                $pCell = strtoupper($pCell);
        if (strpos($pCell, ':') === false && strpos($pCell, ',') === false) {
            $this->freezePane = $pCell;
        } else {
            throw new PHPExcel_Exception('Freeze pane can not be set on a range of cells.');
        }
        return $this;
    }

    
    public function freezePaneByColumnAndRow($pColumn = 0, $pRow = 1)
    {
        return $this->freezePane(PHPExcel_Cell::stringFromColumnIndex($pColumn) . $pRow);
    }

    
    public function unfreezePane()
    {
        return $this->freezePane('');
    }

    
    public function insertNewRowBefore($pBefore = 1, $pNumRows = 1)
    {
        if ($pBefore >= 1) {
            $objReferenceHelper = PHPExcel_ReferenceHelper::getInstance();
            $objReferenceHelper->insertNewBefore('A' . $pBefore, 0, $pNumRows, $this);
        } else {
            throw new PHPExcel_Exception("Rows can only be inserted before at least row 1.");
        }
        return $this;
    }

    
    public function insertNewColumnBefore($pBefore = 'A', $pNumCols = 1)
    {
        if (!is_numeric($pBefore)) {
            $objReferenceHelper = PHPExcel_ReferenceHelper::getInstance();
            $objReferenceHelper->insertNewBefore($pBefore . '1', $pNumCols, 0, $this);
        } else {
            throw new PHPExcel_Exception("Column references should not be numeric.");
        }
        return $this;
    }

    
    public function insertNewColumnBeforeByIndex($pBefore = 0, $pNumCols = 1)
    {
        if ($pBefore >= 0) {
            return $this->insertNewColumnBefore(PHPExcel_Cell::stringFromColumnIndex($pBefore), $pNumCols);
        } else {
            throw new PHPExcel_Exception("Columns can only be inserted before at least column A (0).");
        }
    }

    
    public function removeRow($pRow = 1, $pNumRows = 1)
    {
        if ($pRow >= 1) {
            $highestRow = $this->getHighestDataRow();
            $objReferenceHelper = PHPExcel_ReferenceHelper::getInstance();
            $objReferenceHelper->insertNewBefore('A' . ($pRow + $pNumRows), 0, -$pNumRows, $this);
            for ($r = 0; $r < $pNumRows; ++$r) {
                $this->getCellCacheController()->removeRow($highestRow);
                --$highestRow;
            }
        } else {
            throw new PHPExcel_Exception("Rows to be deleted should at least start from row 1.");
        }
        return $this;
    }

    
    public function removeColumn($pColumn = 'A', $pNumCols = 1)
    {
        if (!is_numeric($pColumn)) {
            $highestColumn = $this->getHighestDataColumn();
            $pColumn = PHPExcel_Cell::stringFromColumnIndex(PHPExcel_Cell::columnIndexFromString($pColumn) - 1 + $pNumCols);
            $objReferenceHelper = PHPExcel_ReferenceHelper::getInstance();
            $objReferenceHelper->insertNewBefore($pColumn . '1', -$pNumCols, 0, $this);
            for ($c = 0; $c < $pNumCols; ++$c) {
                $this->getCellCacheController()->removeColumn($highestColumn);
                $highestColumn = PHPExcel_Cell::stringFromColumnIndex(PHPExcel_Cell::columnIndexFromString($highestColumn) - 2);
            }
        } else {
            throw new PHPExcel_Exception("Column references should not be numeric.");
        }
        return $this;
    }

    
    public function removeColumnByIndex($pColumn = 0, $pNumCols = 1)
    {
        if ($pColumn >= 0) {
            return $this->removeColumn(PHPExcel_Cell::stringFromColumnIndex($pColumn), $pNumCols);
        } else {
            throw new PHPExcel_Exception("Columns to be deleted should at least start from column 0");
        }
    }

    
    public function getShowGridlines()
    {
        return $this->showGridlines;
    }

    
    public function setShowGridlines($pValue = false)
    {
        $this->showGridlines = $pValue;
        return $this;
    }

    
    public function getPrintGridlines()
    {
        return $this->printGridlines;
    }

    
    public function setPrintGridlines($pValue = false)
    {
        $this->printGridlines = $pValue;
        return $this;
    }

    
    public function getShowRowColHeaders()
    {
        return $this->showRowColHeaders;
    }

    
    public function setShowRowColHeaders($pValue = false)
    {
        $this->showRowColHeaders = $pValue;
        return $this;
    }

    
    public function getShowSummaryBelow()
    {
        return $this->showSummaryBelow;
    }

    
    public function setShowSummaryBelow($pValue = true)
    {
        $this->showSummaryBelow = $pValue;
        return $this;
    }

    
    public function getShowSummaryRight()
    {
        return $this->showSummaryRight;
    }

    
    public function setShowSummaryRight($pValue = true)
    {
        $this->showSummaryRight = $pValue;
        return $this;
    }

    
    public function getComments()
    {
        return $this->comments;
    }

    
    public function setComments($pValue = array())
    {
        $this->comments = $pValue;

        return $this;
    }

    
    public function getComment($pCellCoordinate = 'A1')
    {
                $pCellCoordinate = strtoupper($pCellCoordinate);

        if (strpos($pCellCoordinate, ':') !== false || strpos($pCellCoordinate, ',') !== false) {
            throw new PHPExcel_Exception('Cell coordinate string can not be a range of cells.');
        } elseif (strpos($pCellCoordinate, '$') !== false) {
            throw new PHPExcel_Exception('Cell coordinate string must not be absolute.');
        } elseif ($pCellCoordinate == '') {
            throw new PHPExcel_Exception('Cell coordinate can not be zero-length string.');
        } else {
                                    if (isset($this->comments[$pCellCoordinate])) {
                return $this->comments[$pCellCoordinate];
            } else {
                $newComment = new PHPExcel_Comment();
                $this->comments[$pCellCoordinate] = $newComment;
                return $newComment;
            }
        }
    }

    
    public function getCommentByColumnAndRow($pColumn = 0, $pRow = 1)
    {
        return $this->getComment(PHPExcel_Cell::stringFromColumnIndex($pColumn) . $pRow);
    }

    
    public function getSelectedCell()
    {
        return $this->getSelectedCells();
    }

    
    public function getActiveCell()
    {
        return $this->activeCell;
    }

    
    public function getSelectedCells()
    {
        return $this->selectedCells;
    }

    
    public function setSelectedCell($pCoordinate = 'A1')
    {
        return $this->setSelectedCells($pCoordinate);
    }

    
    public function setSelectedCells($pCoordinate = 'A1')
    {
                $pCoordinate = strtoupper($pCoordinate);

                $pCoordinate = preg_replace('/^([A-Z]+)$/', '${1}:${1}', $pCoordinate);

                $pCoordinate = preg_replace('/^([0-9]+)$/', '${1}:${1}', $pCoordinate);

                $pCoordinate = preg_replace('/^([A-Z]+):([A-Z]+)$/', '${1}1:${2}1048576', $pCoordinate);

                $pCoordinate = preg_replace('/^([0-9]+):([0-9]+)$/', 'A${1}:XFD${2}', $pCoordinate);

        if (strpos($pCoordinate, ':') !== false || strpos($pCoordinate, ',') !== false) {
            list($first, ) = PHPExcel_Cell::splitRange($pCoordinate);
            $this->activeCell = $first[0];
        } else {
            $this->activeCell = $pCoordinate;
        }
        $this->selectedCells = $pCoordinate;
        return $this;
    }

    
    public function setSelectedCellByColumnAndRow($pColumn = 0, $pRow = 1)
    {
        return $this->setSelectedCells(PHPExcel_Cell::stringFromColumnIndex($pColumn) . $pRow);
    }

    
    public function getRightToLeft()
    {
        return $this->rightToLeft;
    }

    
    public function setRightToLeft($value = false)
    {
        $this->rightToLeft = $value;
        return $this;
    }

    
    public function fromArray($source = null, $nullValue = null, $startCell = 'A1', $strictNullComparison = false)
    {
        if (is_array($source)) {
                        if (!is_array(end($source))) {
                $source = array($source);
            }

                        list ($startColumn, $startRow) = PHPExcel_Cell::coordinateFromString($startCell);

                        foreach ($source as $rowData) {
                $currentColumn = $startColumn;
                foreach ($rowData as $cellValue) {
                    if ($strictNullComparison) {
                        if ($cellValue !== $nullValue) {
                                                        $this->getCell($currentColumn . $startRow)->setValue($cellValue);
                        }
                    } else {
                        if ($cellValue != $nullValue) {
                                                        $this->getCell($currentColumn . $startRow)->setValue($cellValue);
                        }
                    }
                    ++$currentColumn;
                }
                ++$startRow;
            }
        } else {
            throw new PHPExcel_Exception("Parameter \$source should be an array.");
        }
        return $this;
    }

    
    public function rangeToArray($pRange = 'A1', $nullValue = null, $calculateFormulas = true, $formatData = true, $returnCellRef = false)
    {
                $returnValue = array();
                list($rangeStart, $rangeEnd) = PHPExcel_Cell::rangeBoundaries($pRange);
        $minCol = PHPExcel_Cell::stringFromColumnIndex($rangeStart[0] -1);
        $minRow = $rangeStart[1];
        $maxCol = PHPExcel_Cell::stringFromColumnIndex($rangeEnd[0] -1);
        $maxRow = $rangeEnd[1];

        $maxCol++;
                $r = -1;
        for ($row = $minRow; $row <= $maxRow; ++$row) {
            $rRef = ($returnCellRef) ? $row : ++$r;
            $c = -1;
                        for ($col = $minCol; $col != $maxCol; ++$col) {
                $cRef = ($returnCellRef) ? $col : ++$c;
                                                if ($this->cellCollection->isDataSet($col.$row)) {
                                        $cell = $this->cellCollection->getCacheData($col.$row);
                    if ($cell->getValue() !== null) {
                        if ($cell->getValue() instanceof PHPExcel_RichText) {
                            $returnValue[$rRef][$cRef] = $cell->getValue()->getPlainText();
                        } else {
                            if ($calculateFormulas) {
                                $returnValue[$rRef][$cRef] = $cell->getCalculatedValue();
                            } else {
                                $returnValue[$rRef][$cRef] = $cell->getValue();
                            }
                        }

                        if ($formatData) {
                            $style = $this->parent->getCellXfByIndex($cell->getXfIndex());
                            $returnValue[$rRef][$cRef] = PHPExcel_Style_NumberFormat::toFormattedString(
                                $returnValue[$rRef][$cRef],
                                ($style && $style->getNumberFormat()) ? $style->getNumberFormat()->getFormatCode() : PHPExcel_Style_NumberFormat::FORMAT_GENERAL
                            );
                        }
                    } else {
                                                $returnValue[$rRef][$cRef] = $nullValue;
                    }
                } else {
                                        $returnValue[$rRef][$cRef] = $nullValue;
                }
            }
        }

                return $returnValue;
    }


    
    public function namedRangeToArray($pNamedRange = '', $nullValue = null, $calculateFormulas = true, $formatData = true, $returnCellRef = false)
    {
        $namedRange = PHPExcel_NamedRange::resolveRange($pNamedRange, $this);
        if ($namedRange !== null) {
            $pWorkSheet = $namedRange->getWorksheet();
            $pCellRange = $namedRange->getRange();

            return $pWorkSheet->rangeToArray($pCellRange, $nullValue, $calculateFormulas, $formatData, $returnCellRef);
        }

        throw new PHPExcel_Exception('Named Range '.$pNamedRange.' does not exist.');
    }


    
    public function toArray($nullValue = null, $calculateFormulas = true, $formatData = true, $returnCellRef = false)
    {
                $this->garbageCollect();

                $maxCol = $this->getHighestColumn();
        $maxRow = $this->getHighestRow();
                return $this->rangeToArray('A1:'.$maxCol.$maxRow, $nullValue, $calculateFormulas, $formatData, $returnCellRef);
    }

    
    public function getRowIterator($startRow = 1, $endRow = null)
    {
        return new PHPExcel_Worksheet_RowIterator($this, $startRow, $endRow);
    }

    
    public function getColumnIterator($startColumn = 'A', $endColumn = null)
    {
        return new PHPExcel_Worksheet_ColumnIterator($this, $startColumn, $endColumn);
    }

    
    public function garbageCollect()
    {
                $this->cellCollection->getCacheData('A1');
                        $colRow = $this->cellCollection->getHighestRowAndColumn();
        $highestRow = $colRow['row'];
        $highestColumn = PHPExcel_Cell::columnIndexFromString($colRow['column']);

                foreach ($this->columnDimensions as $dimension) {
            $highestColumn = max($highestColumn, PHPExcel_Cell::columnIndexFromString($dimension->getColumnIndex()));
        }

                foreach ($this->rowDimensions as $dimension) {
            $highestRow = max($highestRow, $dimension->getRowIndex());
        }

                if ($highestColumn < 0) {
            $this->cachedHighestColumn = 'A';
        } else {
            $this->cachedHighestColumn = PHPExcel_Cell::stringFromColumnIndex(--$highestColumn);
        }
        $this->cachedHighestRow = $highestRow;

                return $this;
    }

    
    public function getHashCode()
    {
        if ($this->dirty) {
            $this->hash = md5($this->title . $this->autoFilter . ($this->protection->isProtectionEnabled() ? 't' : 'f') . __CLASS__);
            $this->dirty = false;
        }
        return $this->hash;
    }

    
    public static function extractSheetTitle($pRange, $returnRange = false)
    {
                if (($sep = strpos($pRange, '!')) === false) {
            return '';
        }

        if ($returnRange) {
            return array(trim(substr($pRange, 0, $sep), "'"), substr($pRange, $sep + 1));
        }

        return substr($pRange, $sep + 1);
    }

    
    public function getHyperlink($pCellCoordinate = 'A1')
    {
                if (isset($this->hyperlinkCollection[$pCellCoordinate])) {
            return $this->hyperlinkCollection[$pCellCoordinate];
        }

                $this->hyperlinkCollection[$pCellCoordinate] = new PHPExcel_Cell_Hyperlink();
        return $this->hyperlinkCollection[$pCellCoordinate];
    }

    
    public function setHyperlink($pCellCoordinate = 'A1', PHPExcel_Cell_Hyperlink $pHyperlink = null)
    {
        if ($pHyperlink === null) {
            unset($this->hyperlinkCollection[$pCellCoordinate]);
        } else {
            $this->hyperlinkCollection[$pCellCoordinate] = $pHyperlink;
        }
        return $this;
    }

    
    public function hyperlinkExists($pCoordinate = 'A1')
    {
        return isset($this->hyperlinkCollection[$pCoordinate]);
    }

    
    public function getHyperlinkCollection()
    {
        return $this->hyperlinkCollection;
    }

    
    public function getDataValidation($pCellCoordinate = 'A1')
    {
                if (isset($this->dataValidationCollection[$pCellCoordinate])) {
            return $this->dataValidationCollection[$pCellCoordinate];
        }

                $this->dataValidationCollection[$pCellCoordinate] = new PHPExcel_Cell_DataValidation();
        return $this->dataValidationCollection[$pCellCoordinate];
    }

    
    public function setDataValidation($pCellCoordinate = 'A1', PHPExcel_Cell_DataValidation $pDataValidation = null)
    {
        if ($pDataValidation === null) {
            unset($this->dataValidationCollection[$pCellCoordinate]);
        } else {
            $this->dataValidationCollection[$pCellCoordinate] = $pDataValidation;
        }
        return $this;
    }

    
    public function dataValidationExists($pCoordinate = 'A1')
    {
        return isset($this->dataValidationCollection[$pCoordinate]);
    }

    
    public function getDataValidationCollection()
    {
        return $this->dataValidationCollection;
    }

    
    public function shrinkRangeToFit($range)
    {
        $maxCol = $this->getHighestColumn();
        $maxRow = $this->getHighestRow();
        $maxCol = PHPExcel_Cell::columnIndexFromString($maxCol);

        $rangeBlocks = explode(' ', $range);
        foreach ($rangeBlocks as &$rangeSet) {
            $rangeBoundaries = PHPExcel_Cell::getRangeBoundaries($rangeSet);

            if (PHPExcel_Cell::columnIndexFromString($rangeBoundaries[0][0]) > $maxCol) {
                $rangeBoundaries[0][0] = PHPExcel_Cell::stringFromColumnIndex($maxCol);
            }
            if ($rangeBoundaries[0][1] > $maxRow) {
                $rangeBoundaries[0][1] = $maxRow;
            }
            if (PHPExcel_Cell::columnIndexFromString($rangeBoundaries[1][0]) > $maxCol) {
                $rangeBoundaries[1][0] = PHPExcel_Cell::stringFromColumnIndex($maxCol);
            }
            if ($rangeBoundaries[1][1] > $maxRow) {
                $rangeBoundaries[1][1] = $maxRow;
            }
            $rangeSet = $rangeBoundaries[0][0].$rangeBoundaries[0][1].':'.$rangeBoundaries[1][0].$rangeBoundaries[1][1];
        }
        unset($rangeSet);
        $stRange = implode(' ', $rangeBlocks);

        return $stRange;
    }

    
    public function getTabColor()
    {
        if ($this->tabColor === null) {
            $this->tabColor = new PHPExcel_Style_Color();
        }
        return $this->tabColor;
    }

    
    public function resetTabColor()
    {
        $this->tabColor = null;
        unset($this->tabColor);

        return $this;
    }

    
    public function isTabColorSet()
    {
        return ($this->tabColor !== null);
    }

    
    public function copy()
    {
        $copied = clone $this;

        return $copied;
    }

    
    public function __clone()
    {
        foreach ($this as $key => $val) {
            if ($key == 'parent') {
                continue;
            }

            if (is_object($val) || (is_array($val))) {
                if ($key == 'cellCollection') {
                    $newCollection = clone $this->cellCollection;
                    $newCollection->copyCellCollection($this);
                    $this->cellCollection = $newCollection;
                } elseif ($key == 'drawingCollection') {
                    $newCollection = clone $this->drawingCollection;
                    $this->drawingCollection = $newCollection;
                } elseif (($key == 'autoFilter') && ($this->autoFilter instanceof PHPExcel_Worksheet_AutoFilter)) {
                    $newAutoFilter = clone $this->autoFilter;
                    $this->autoFilter = $newAutoFilter;
                    $this->autoFilter->setParent($this);
                } else {
                    $this->{$key} = unserialize(serialize($val));
                }
            }
        }
    }

    public function setCodeName($pValue = null)
    {
                if ($this->getCodeName() == $pValue) {
            return $this;
        }
        $pValue = str_replace(' ', '_', $pValue);                        self::checkSheetCodeName($pValue);

        
        if ($this->getParent()) {
                        if ($this->getParent()->sheetCodeNameExists($pValue)) {
                
                if (PHPExcel_Shared_String::CountCharacters($pValue) > 29) {
                    $pValue = PHPExcel_Shared_String::Substring($pValue, 0, 29);
                }
                $i = 1;
                while ($this->getParent()->sheetCodeNameExists($pValue . '_' . $i)) {
                    ++$i;
                    if ($i == 10) {
                        if (PHPExcel_Shared_String::CountCharacters($pValue) > 28) {
                            $pValue = PHPExcel_Shared_String::Substring($pValue, 0, 28);
                        }
                    } elseif ($i == 100) {
                        if (PHPExcel_Shared_String::CountCharacters($pValue) > 27) {
                            $pValue = PHPExcel_Shared_String::Substring($pValue, 0, 27);
                        }
                    }
                }

                $pValue = $pValue . '_' . $i;                                            }
        }

        $this->codeName=$pValue;
        return $this;
    }
    
    public function getCodeName()
    {
        return $this->codeName;
    }
    
    public function hasCodeName()
    {
        return !(is_null($this->codeName));
    }
}
