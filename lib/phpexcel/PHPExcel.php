<?php


if (!defined('PHPEXCEL_ROOT')) {
    define('PHPEXCEL_ROOT', dirname(__FILE__) . '/');
    require(PHPEXCEL_ROOT . 'PHPExcel/Autoloader.php');
}


class PHPExcel
{
    
    private $uniqueID;

    
    private $properties;

    
    private $security;

    
    private $workSheetCollection = array();

    
    private $calculationEngine;

    
    private $activeSheetIndex = 0;

    
    private $namedRanges = array();

    
    private $cellXfSupervisor;

    
    private $cellXfCollection = array();

    
    private $cellStyleXfCollection = array();

    
    private $hasMacros = false;

    
    private $macrosCode;
    
    private $macrosCertificate;

    
    private $ribbonXMLData;

    
    private $ribbonBinObjects;

    
    public function hasMacros()
    {
        return $this->hasMacros;
    }

    
    public function setHasMacros($hasMacros = false)
    {
        $this->hasMacros = (bool) $hasMacros;
    }

    
    public function setMacrosCode($MacrosCode = null)
    {
        $this->macrosCode=$MacrosCode;
        $this->setHasMacros(!is_null($MacrosCode));
    }

    
    public function getMacrosCode()
    {
        return $this->macrosCode;
    }

    
    public function setMacrosCertificate($Certificate = null)
    {
        $this->macrosCertificate=$Certificate;
    }

    
    public function hasMacrosCertificate()
    {
        return !is_null($this->macrosCertificate);
    }

    
    public function getMacrosCertificate()
    {
        return $this->macrosCertificate;
    }

    
    public function discardMacros()
    {
        $this->hasMacros=false;
        $this->macrosCode=null;
        $this->macrosCertificate=null;
    }

    
    public function setRibbonXMLData($Target = null, $XMLData = null)
    {
        if (!is_null($Target) && !is_null($XMLData)) {
            $this->ribbonXMLData = array('target' => $Target, 'data' => $XMLData);
        } else {
            $this->ribbonXMLData = null;
        }
    }

    
    public function getRibbonXMLData($What = 'all')     {
        $ReturnData = null;
        $What = strtolower($What);
        switch ($What){
            case 'all':
                $ReturnData = $this->ribbonXMLData;
                break;
            case 'target':
            case 'data':
                if (is_array($this->ribbonXMLData) && array_key_exists($What, $this->ribbonXMLData)) {
                    $ReturnData = $this->ribbonXMLData[$What];
                }
                break;
        }

        return $ReturnData;
    }

    
    public function setRibbonBinObjects($BinObjectsNames = null, $BinObjectsData = null)
    {
        if (!is_null($BinObjectsNames) && !is_null($BinObjectsData)) {
            $this->ribbonBinObjects = array('names' => $BinObjectsNames, 'data' => $BinObjectsData);
        } else {
            $this->ribbonBinObjects = null;
        }
    }
    
    private function getExtensionOnly($ThePath)
    {
        return pathinfo($ThePath, PATHINFO_EXTENSION);
    }

    
    public function getRibbonBinObjects($What = 'all')
    {
        $ReturnData = null;
        $What = strtolower($What);
        switch($What) {
            case 'all':
                return $this->ribbonBinObjects;
                break;
            case 'names':
            case 'data':
                if (is_array($this->ribbonBinObjects) && array_key_exists($What, $this->ribbonBinObjects)) {
                    $ReturnData=$this->ribbonBinObjects[$What];
                }
                break;
            case 'types':
                if (is_array($this->ribbonBinObjects) &&
                    array_key_exists('data', $this->ribbonBinObjects) && is_array($this->ribbonBinObjects['data'])) {
                    $tmpTypes=array_keys($this->ribbonBinObjects['data']);
                    $ReturnData = array_unique(array_map(array($this, 'getExtensionOnly'), $tmpTypes));
                } else {
                    $ReturnData=array();                 }
                break;
        }
        return $ReturnData;
    }

    
    public function hasRibbon()
    {
        return !is_null($this->ribbonXMLData);
    }

    
    public function hasRibbonBinObjects()
    {
        return !is_null($this->ribbonBinObjects);
    }

    
    public function sheetCodeNameExists($pSheetCodeName)
    {
        return ($this->getSheetByCodeName($pSheetCodeName) !== null);
    }

    
    public function getSheetByCodeName($pName = '')
    {
        $worksheetCount = count($this->workSheetCollection);
        for ($i = 0; $i < $worksheetCount; ++$i) {
            if ($this->workSheetCollection[$i]->getCodeName() == $pName) {
                return $this->workSheetCollection[$i];
            }
        }

        return null;
    }

     
    public function __construct()
    {
        $this->uniqueID = uniqid();
        $this->calculationEngine = PHPExcel_Calculation::getInstance($this);

                $this->workSheetCollection = array();
        $this->workSheetCollection[] = new PHPExcel_Worksheet($this);
        $this->activeSheetIndex = 0;

                $this->properties = new PHPExcel_DocumentProperties();

                $this->security = new PHPExcel_DocumentSecurity();

                $this->namedRanges = array();

                $this->cellXfSupervisor = new PHPExcel_Style(true);
        $this->cellXfSupervisor->bindParent($this);

                $this->addCellXf(new PHPExcel_Style);
        $this->addCellStyleXf(new PHPExcel_Style);
    }

    
    public function __destruct()
    {
        PHPExcel_Calculation::unsetInstance($this);
        $this->disconnectWorksheets();
    }

    
    public function disconnectWorksheets()
    {
        $worksheet = null;
        foreach ($this->workSheetCollection as $k => &$worksheet) {
            $worksheet->disconnectCells();
            $this->workSheetCollection[$k] = null;
        }
        unset($worksheet);
        $this->workSheetCollection = array();
    }

    
    public function getCalculationEngine()
    {
        return $this->calculationEngine;
    }    
    
    public function getProperties()
    {
        return $this->properties;
    }

    
    public function setProperties(PHPExcel_DocumentProperties $pValue)
    {
        $this->properties = $pValue;
    }

    
    public function getSecurity()
    {
        return $this->security;
    }

    
    public function setSecurity(PHPExcel_DocumentSecurity $pValue)
    {
        $this->security = $pValue;
    }

    
    public function getActiveSheet()
    {
        return $this->getSheet($this->activeSheetIndex);
    }

    
    public function createSheet($iSheetIndex = null)
    {
        $newSheet = new PHPExcel_Worksheet($this);
        $this->addSheet($newSheet, $iSheetIndex);
        return $newSheet;
    }

    
    public function sheetNameExists($pSheetName)
    {
        return ($this->getSheetByName($pSheetName) !== null);
    }

    
    public function addSheet(PHPExcel_Worksheet $pSheet, $iSheetIndex = null)
    {
        if ($this->sheetNameExists($pSheet->getTitle())) {
            throw new PHPExcel_Exception(
                "Workbook already contains a worksheet named '{$pSheet->getTitle()}'. Rename this worksheet first."
            );
        }

        if ($iSheetIndex === null) {
            if ($this->activeSheetIndex < 0) {
                $this->activeSheetIndex = 0;
            }
            $this->workSheetCollection[] = $pSheet;
        } else {
                        array_splice(
                $this->workSheetCollection,
                $iSheetIndex,
                0,
                array($pSheet)
            );

                        if ($this->activeSheetIndex >= $iSheetIndex) {
                ++$this->activeSheetIndex;
            }
        }

        if ($pSheet->getParent() === null) {
            $pSheet->rebindParent($this);
        }

        return $pSheet;
    }

    
    public function removeSheetByIndex($pIndex = 0)
    {

        $numSheets = count($this->workSheetCollection);
        if ($pIndex > $numSheets - 1) {
            throw new PHPExcel_Exception(
                "You tried to remove a sheet by the out of bounds index: {$pIndex}. The actual number of sheets is {$numSheets}."
            );
        } else {
            array_splice($this->workSheetCollection, $pIndex, 1);
        }
                if (($this->activeSheetIndex >= $pIndex) &&
            ($pIndex > count($this->workSheetCollection) - 1)) {
            --$this->activeSheetIndex;
        }

    }

    
    public function getSheet($pIndex = 0)
    {
        if (!isset($this->workSheetCollection[$pIndex])) {
            $numSheets = $this->getSheetCount();
            throw new PHPExcel_Exception(
                "Your requested sheet index: {$pIndex} is out of bounds. The actual number of sheets is {$numSheets}."
            );
        }

        return $this->workSheetCollection[$pIndex];
    }

    
    public function getAllSheets()
    {
        return $this->workSheetCollection;
    }

    
    public function getSheetByName($pName = '')
    {
        $worksheetCount = count($this->workSheetCollection);
        for ($i = 0; $i < $worksheetCount; ++$i) {
            if ($this->workSheetCollection[$i]->getTitle() === $pName) {
                return $this->workSheetCollection[$i];
            }
        }

        return null;
    }

    
    public function getIndex(PHPExcel_Worksheet $pSheet)
    {
        foreach ($this->workSheetCollection as $key => $value) {
            if ($value->getHashCode() == $pSheet->getHashCode()) {
                return $key;
            }
        }

        throw new PHPExcel_Exception("Sheet does not exist.");
    }

    
    public function setIndexByName($sheetName, $newIndex)
    {
        $oldIndex = $this->getIndex($this->getSheetByName($sheetName));
        $pSheet = array_splice(
            $this->workSheetCollection,
            $oldIndex,
            1
        );
        array_splice(
            $this->workSheetCollection,
            $newIndex,
            0,
            $pSheet
        );
        return $newIndex;
    }

    
    public function getSheetCount()
    {
        return count($this->workSheetCollection);
    }

    
    public function getActiveSheetIndex()
    {
        return $this->activeSheetIndex;
    }

    
    public function setActiveSheetIndex($pIndex = 0)
    {
        $numSheets = count($this->workSheetCollection);

        if ($pIndex > $numSheets - 1) {
            throw new PHPExcel_Exception(
                "You tried to set a sheet active by the out of bounds index: {$pIndex}. The actual number of sheets is {$numSheets}."
            );
        } else {
            $this->activeSheetIndex = $pIndex;
        }
        return $this->getActiveSheet();
    }

    
    public function setActiveSheetIndexByName($pValue = '')
    {
        if (($worksheet = $this->getSheetByName($pValue)) instanceof PHPExcel_Worksheet) {
            $this->setActiveSheetIndex($this->getIndex($worksheet));
            return $worksheet;
        }

        throw new PHPExcel_Exception('Workbook does not contain sheet:' . $pValue);
    }

    
    public function getSheetNames()
    {
        $returnValue = array();
        $worksheetCount = $this->getSheetCount();
        for ($i = 0; $i < $worksheetCount; ++$i) {
            $returnValue[] = $this->getSheet($i)->getTitle();
        }

        return $returnValue;
    }

    
    public function addExternalSheet(PHPExcel_Worksheet $pSheet, $iSheetIndex = null)
    {
        if ($this->sheetNameExists($pSheet->getTitle())) {
            throw new PHPExcel_Exception("Workbook already contains a worksheet named '{$pSheet->getTitle()}'. Rename the external sheet first.");
        }

                $countCellXfs = count($this->cellXfCollection);

                foreach ($pSheet->getParent()->getCellXfCollection() as $cellXf) {
            $this->addCellXf(clone $cellXf);
        }

                $pSheet->rebindParent($this);

                foreach ($pSheet->getCellCollection(false) as $cellID) {
            $cell = $pSheet->getCell($cellID);
            $cell->setXfIndex($cell->getXfIndex() + $countCellXfs);
        }

        return $this->addSheet($pSheet, $iSheetIndex);
    }

    
    public function getNamedRanges()
    {
        return $this->namedRanges;
    }

    
    public function addNamedRange(PHPExcel_NamedRange $namedRange)
    {
        if ($namedRange->getScope() == null) {
                        $this->namedRanges[$namedRange->getName()] = $namedRange;
        } else {
                        $this->namedRanges[$namedRange->getScope()->getTitle().'!'.$namedRange->getName()] = $namedRange;
        }
        return true;
    }

    
    public function getNamedRange($namedRange, PHPExcel_Worksheet $pSheet = null)
    {
        $returnValue = null;

        if ($namedRange != '' && ($namedRange !== null)) {
                        if (isset($this->namedRanges[$namedRange])) {
                $returnValue = $this->namedRanges[$namedRange];
            }

                        if (($pSheet !== null) && isset($this->namedRanges[$pSheet->getTitle() . '!' . $namedRange])) {
                $returnValue = $this->namedRanges[$pSheet->getTitle() . '!' . $namedRange];
            }
        }

        return $returnValue;
    }

    
    public function removeNamedRange($namedRange, PHPExcel_Worksheet $pSheet = null)
    {
        if ($pSheet === null) {
            if (isset($this->namedRanges[$namedRange])) {
                unset($this->namedRanges[$namedRange]);
            }
        } else {
            if (isset($this->namedRanges[$pSheet->getTitle() . '!' . $namedRange])) {
                unset($this->namedRanges[$pSheet->getTitle() . '!' . $namedRange]);
            }
        }
        return $this;
    }

    
    public function getWorksheetIterator()
    {
        return new PHPExcel_WorksheetIterator($this);
    }

    
    public function copy()
    {
        $copied = clone $this;

        $worksheetCount = count($this->workSheetCollection);
        for ($i = 0; $i < $worksheetCount; ++$i) {
            $this->workSheetCollection[$i] = $this->workSheetCollection[$i]->copy();
            $this->workSheetCollection[$i]->rebindParent($this);
        }

        return $copied;
    }

    
    public function __clone()
    {
        foreach ($this as $key => $val) {
            if (is_object($val) || (is_array($val))) {
                $this->{$key} = unserialize(serialize($val));
            }
        }
    }

    
    public function getCellXfCollection()
    {
        return $this->cellXfCollection;
    }

    
    public function getCellXfByIndex($pIndex = 0)
    {
        return $this->cellXfCollection[$pIndex];
    }

    
    public function getCellXfByHashCode($pValue = '')
    {
        foreach ($this->cellXfCollection as $cellXf) {
            if ($cellXf->getHashCode() == $pValue) {
                return $cellXf;
            }
        }
        return false;
    }

    
    public function cellXfExists($pCellStyle = null)
    {
        return in_array($pCellStyle, $this->cellXfCollection, true);
    }

    
    public function getDefaultStyle()
    {
        if (isset($this->cellXfCollection[0])) {
            return $this->cellXfCollection[0];
        }
        throw new PHPExcel_Exception('No default style found for this workbook');
    }

    
    public function addCellXf(PHPExcel_Style $style)
    {
        $this->cellXfCollection[] = $style;
        $style->setIndex(count($this->cellXfCollection) - 1);
    }

    
    public function removeCellXfByIndex($pIndex = 0)
    {
        if ($pIndex > count($this->cellXfCollection) - 1) {
            throw new PHPExcel_Exception("CellXf index is out of bounds.");
        } else {
                        array_splice($this->cellXfCollection, $pIndex, 1);

                        foreach ($this->workSheetCollection as $worksheet) {
                foreach ($worksheet->getCellCollection(false) as $cellID) {
                    $cell = $worksheet->getCell($cellID);
                    $xfIndex = $cell->getXfIndex();
                    if ($xfIndex > $pIndex) {
                                                $cell->setXfIndex($xfIndex - 1);
                    } elseif ($xfIndex == $pIndex) {
                                                $cell->setXfIndex(0);
                    }
                }
            }
        }
    }

    
    public function getCellXfSupervisor()
    {
        return $this->cellXfSupervisor;
    }

    
    public function getCellStyleXfCollection()
    {
        return $this->cellStyleXfCollection;
    }

    
    public function getCellStyleXfByIndex($pIndex = 0)
    {
        return $this->cellStyleXfCollection[$pIndex];
    }

    
    public function getCellStyleXfByHashCode($pValue = '')
    {
        foreach ($this->cellStyleXfCollection as $cellStyleXf) {
            if ($cellStyleXf->getHashCode() == $pValue) {
                return $cellStyleXf;
            }
        }
        return false;
    }

    
    public function addCellStyleXf(PHPExcel_Style $pStyle)
    {
        $this->cellStyleXfCollection[] = $pStyle;
        $pStyle->setIndex(count($this->cellStyleXfCollection) - 1);
    }

    
    public function removeCellStyleXfByIndex($pIndex = 0)
    {
        if ($pIndex > count($this->cellStyleXfCollection) - 1) {
            throw new PHPExcel_Exception("CellStyleXf index is out of bounds.");
        } else {
            array_splice($this->cellStyleXfCollection, $pIndex, 1);
        }
    }

    
    public function garbageCollect()
    {
                $countReferencesCellXf = array();
        foreach ($this->cellXfCollection as $index => $cellXf) {
            $countReferencesCellXf[$index] = 0;
        }

        foreach ($this->getWorksheetIterator() as $sheet) {
                        foreach ($sheet->getCellCollection(false) as $cellID) {
                $cell = $sheet->getCell($cellID);
                ++$countReferencesCellXf[$cell->getXfIndex()];
            }

                        foreach ($sheet->getRowDimensions() as $rowDimension) {
                if ($rowDimension->getXfIndex() !== null) {
                    ++$countReferencesCellXf[$rowDimension->getXfIndex()];
                }
            }

                        foreach ($sheet->getColumnDimensions() as $columnDimension) {
                ++$countReferencesCellXf[$columnDimension->getXfIndex()];
            }
        }

                        $countNeededCellXfs = 0;
        foreach ($this->cellXfCollection as $index => $cellXf) {
            if ($countReferencesCellXf[$index] > 0 || $index == 0) {                 ++$countNeededCellXfs;
            } else {
                unset($this->cellXfCollection[$index]);
            }
            $map[$index] = $countNeededCellXfs - 1;
        }
        $this->cellXfCollection = array_values($this->cellXfCollection);

                foreach ($this->cellXfCollection as $i => $cellXf) {
            $cellXf->setIndex($i);
        }

                if (empty($this->cellXfCollection)) {
            $this->cellXfCollection[] = new PHPExcel_Style();
        }

                foreach ($this->getWorksheetIterator() as $sheet) {
                        foreach ($sheet->getCellCollection(false) as $cellID) {
                $cell = $sheet->getCell($cellID);
                $cell->setXfIndex($map[$cell->getXfIndex()]);
            }

                        foreach ($sheet->getRowDimensions() as $rowDimension) {
                if ($rowDimension->getXfIndex() !== null) {
                    $rowDimension->setXfIndex($map[$rowDimension->getXfIndex()]);
                }
            }

                        foreach ($sheet->getColumnDimensions() as $columnDimension) {
                $columnDimension->setXfIndex($map[$columnDimension->getXfIndex()]);
            }

                        $sheet->garbageCollect();
        }
    }

    
    public function getID()
    {
        return $this->uniqueID;
    }
}
