<?php


class PHPExcel_Writer_Excel2007 extends PHPExcel_Writer_Abstract implements PHPExcel_Writer_IWriter
{
    
    protected $preCalculateFormulas = false;

    
    private $office2003compatibility = false;

    
    private $writerParts    = array();

    
    private $spreadSheet;

    
    private $stringTable    = array();

    
    private $stylesConditionalHashTable;

    
    private $styleHashTable;

    
    private $fillHashTable;

    
    private $fontHashTable;

    
    private $bordersHashTable ;

    
    private $numFmtHashTable;

    
    private $drawingHashTable;

    
    public function __construct(PHPExcel $pPHPExcel = null)
    {
                $this->setPHPExcel($pPHPExcel);

        $writerPartsArray = array(  'stringtable'       => 'PHPExcel_Writer_Excel2007_StringTable',
                                    'contenttypes'      => 'PHPExcel_Writer_Excel2007_ContentTypes',
                                    'docprops'          => 'PHPExcel_Writer_Excel2007_DocProps',
                                    'rels'              => 'PHPExcel_Writer_Excel2007_Rels',
                                    'theme'             => 'PHPExcel_Writer_Excel2007_Theme',
                                    'style'             => 'PHPExcel_Writer_Excel2007_Style',
                                    'workbook'          => 'PHPExcel_Writer_Excel2007_Workbook',
                                    'worksheet'         => 'PHPExcel_Writer_Excel2007_Worksheet',
                                    'drawing'           => 'PHPExcel_Writer_Excel2007_Drawing',
                                    'comments'          => 'PHPExcel_Writer_Excel2007_Comments',
                                    'chart'             => 'PHPExcel_Writer_Excel2007_Chart',
                                    'relsvba'           => 'PHPExcel_Writer_Excel2007_RelsVBA',
                                    'relsribbonobjects' => 'PHPExcel_Writer_Excel2007_RelsRibbon'
                                 );

                        foreach ($writerPartsArray as $writer => $class) {
            $this->writerParts[$writer] = new $class($this);
        }

        $hashTablesArray = array( 'stylesConditionalHashTable',    'fillHashTable',        'fontHashTable',
                                  'bordersHashTable',                'numFmtHashTable',        'drawingHashTable',
                                  'styleHashTable'
                                );

                foreach ($hashTablesArray as $tableName) {
            $this->$tableName     = new PHPExcel_HashTable();
        }
    }

    
    public function getWriterPart($pPartName = '')
    {
        if ($pPartName != '' && isset($this->writerParts[strtolower($pPartName)])) {
            return $this->writerParts[strtolower($pPartName)];
        } else {
            return null;
        }
    }

    
    public function save($pFilename = null)
    {
        if ($this->spreadSheet !== null) {
                        $this->spreadSheet->garbageCollect();

                        $originalFilename = $pFilename;
            if (strtolower($pFilename) == 'php://output' || strtolower($pFilename) == 'php://stdout') {
                $pFilename = @tempnam(PHPExcel_Shared_File::sys_get_temp_dir(), 'phpxltmp');
                if ($pFilename == '') {
                    $pFilename = $originalFilename;
                }
            }

            $saveDebugLog = PHPExcel_Calculation::getInstance($this->spreadSheet)->getDebugLog()->getWriteDebugLog();
            PHPExcel_Calculation::getInstance($this->spreadSheet)->getDebugLog()->setWriteDebugLog(false);
            $saveDateReturnType = PHPExcel_Calculation_Functions::getReturnDateType();
            PHPExcel_Calculation_Functions::setReturnDateType(PHPExcel_Calculation_Functions::RETURNDATE_EXCEL);

                        $this->stringTable = array();
            for ($i = 0; $i < $this->spreadSheet->getSheetCount(); ++$i) {
                $this->stringTable = $this->getWriterPart('StringTable')->createStringTable($this->spreadSheet->getSheet($i), $this->stringTable);
            }

                        $this->styleHashTable->addFromSource($this->getWriterPart('Style')->allStyles($this->spreadSheet));
            $this->stylesConditionalHashTable->addFromSource($this->getWriterPart('Style')->allConditionalStyles($this->spreadSheet));
            $this->fillHashTable->addFromSource($this->getWriterPart('Style')->allFills($this->spreadSheet));
            $this->fontHashTable->addFromSource($this->getWriterPart('Style')->allFonts($this->spreadSheet));
            $this->bordersHashTable->addFromSource($this->getWriterPart('Style')->allBorders($this->spreadSheet));
            $this->numFmtHashTable->addFromSource($this->getWriterPart('Style')->allNumberFormats($this->spreadSheet));

                        $this->drawingHashTable->addFromSource($this->getWriterPart('Drawing')->allDrawings($this->spreadSheet));

                        $zipClass = PHPExcel_Settings::getZipClass();
            $objZip = new $zipClass();

                                    $ro = new ReflectionObject($objZip);
            $zipOverWrite = $ro->getConstant('OVERWRITE');
            $zipCreate = $ro->getConstant('CREATE');

            if (file_exists($pFilename)) {
                unlink($pFilename);
            }
                        if ($objZip->open($pFilename, $zipOverWrite) !== true) {
                if ($objZip->open($pFilename, $zipCreate) !== true) {
                    throw new PHPExcel_Writer_Exception("Could not open " . $pFilename . " for writing.");
                }
            }

                        $objZip->addFromString('[Content_Types].xml', $this->getWriterPart('ContentTypes')->writeContentTypes($this->spreadSheet, $this->includeCharts));

                        if ($this->spreadSheet->hasMacros()) {
                $macrosCode=$this->spreadSheet->getMacrosCode();
                if (!is_null($macrosCode)) {                    $objZip->addFromString('xl/vbaProject.bin', $macrosCode);                    if ($this->spreadSheet->hasMacrosCertificate()) {                                                $objZip->addFromString('xl/vbaProjectSignature.bin', $this->spreadSheet->getMacrosCertificate());
                        $objZip->addFromString('xl/_rels/vbaProject.bin.rels', $this->getWriterPart('RelsVBA')->writeVBARelationships($this->spreadSheet));
                    }
                }
            }
                        if ($this->spreadSheet->hasRibbon()) {
                $tmpRibbonTarget=$this->spreadSheet->getRibbonXMLData('target');
                $objZip->addFromString($tmpRibbonTarget, $this->spreadSheet->getRibbonXMLData('data'));
                if ($this->spreadSheet->hasRibbonBinObjects()) {
                    $tmpRootPath=dirname($tmpRibbonTarget).'/';
                    $ribbonBinObjects=$this->spreadSheet->getRibbonBinObjects('data');                    foreach ($ribbonBinObjects as $aPath => $aContent) {
                        $objZip->addFromString($tmpRootPath.$aPath, $aContent);
                    }
                                        $objZip->addFromString($tmpRootPath.'_rels/'.basename($tmpRibbonTarget).'.rels', $this->getWriterPart('RelsRibbonObjects')->writeRibbonRelationships($this->spreadSheet));
                }
            }
            
                        $objZip->addFromString('_rels/.rels', $this->getWriterPart('Rels')->writeRelationships($this->spreadSheet));
            $objZip->addFromString('xl/_rels/workbook.xml.rels', $this->getWriterPart('Rels')->writeWorkbookRelationships($this->spreadSheet));

                        $objZip->addFromString('docProps/app.xml', $this->getWriterPart('DocProps')->writeDocPropsApp($this->spreadSheet));
            $objZip->addFromString('docProps/core.xml', $this->getWriterPart('DocProps')->writeDocPropsCore($this->spreadSheet));
            $customPropertiesPart = $this->getWriterPart('DocProps')->writeDocPropsCustom($this->spreadSheet);
            if ($customPropertiesPart !== null) {
                $objZip->addFromString('docProps/custom.xml', $customPropertiesPart);
            }

                        $objZip->addFromString('xl/theme/theme1.xml', $this->getWriterPart('Theme')->writeTheme($this->spreadSheet));

                        $objZip->addFromString('xl/sharedStrings.xml', $this->getWriterPart('StringTable')->writeStringTable($this->stringTable));

                        $objZip->addFromString('xl/styles.xml', $this->getWriterPart('Style')->writeStyles($this->spreadSheet));

                        $objZip->addFromString('xl/workbook.xml', $this->getWriterPart('Workbook')->writeWorkbook($this->spreadSheet, $this->preCalculateFormulas));

            $chartCount = 0;
                        for ($i = 0; $i < $this->spreadSheet->getSheetCount(); ++$i) {
                $objZip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $this->getWriterPart('Worksheet')->writeWorksheet($this->spreadSheet->getSheet($i), $this->stringTable, $this->includeCharts));
                if ($this->includeCharts) {
                    $charts = $this->spreadSheet->getSheet($i)->getChartCollection();
                    if (count($charts) > 0) {
                        foreach ($charts as $chart) {
                            $objZip->addFromString('xl/charts/chart' . ($chartCount + 1) . '.xml', $this->getWriterPart('Chart')->writeChart($chart));
                            $chartCount++;
                        }
                    }
                }
            }

            $chartRef1 = $chartRef2 = 0;
                        for ($i = 0; $i < $this->spreadSheet->getSheetCount(); ++$i) {
                                $objZip->addFromString('xl/worksheets/_rels/sheet' . ($i + 1) . '.xml.rels', $this->getWriterPart('Rels')->writeWorksheetRelationships($this->spreadSheet->getSheet($i), ($i + 1), $this->includeCharts));

                $drawings = $this->spreadSheet->getSheet($i)->getDrawingCollection();
                $drawingCount = count($drawings);
                if ($this->includeCharts) {
                    $chartCount = $this->spreadSheet->getSheet($i)->getChartCount();
                }

                                if (($drawingCount > 0) || ($chartCount > 0)) {
                                        $objZip->addFromString('xl/drawings/_rels/drawing' . ($i + 1) . '.xml.rels', $this->getWriterPart('Rels')->writeDrawingRelationships($this->spreadSheet->getSheet($i), $chartRef1, $this->includeCharts));

                                        $objZip->addFromString('xl/drawings/drawing' . ($i + 1) . '.xml', $this->getWriterPart('Drawing')->writeDrawings($this->spreadSheet->getSheet($i), $chartRef2, $this->includeCharts));
                }

                                if (count($this->spreadSheet->getSheet($i)->getComments()) > 0) {
                                        $objZip->addFromString('xl/drawings/vmlDrawing' . ($i + 1) . '.vml', $this->getWriterPart('Comments')->writeVMLComments($this->spreadSheet->getSheet($i)));

                                        $objZip->addFromString('xl/comments' . ($i + 1) . '.xml', $this->getWriterPart('Comments')->writeComments($this->spreadSheet->getSheet($i)));
                }

                                if (count($this->spreadSheet->getSheet($i)->getHeaderFooter()->getImages()) > 0) {
                                        $objZip->addFromString('xl/drawings/vmlDrawingHF' . ($i + 1) . '.vml', $this->getWriterPart('Drawing')->writeVMLHeaderFooterImages($this->spreadSheet->getSheet($i)));

                                        $objZip->addFromString('xl/drawings/_rels/vmlDrawingHF' . ($i + 1) . '.vml.rels', $this->getWriterPart('Rels')->writeHeaderFooterDrawingRelationships($this->spreadSheet->getSheet($i)));

                                        foreach ($this->spreadSheet->getSheet($i)->getHeaderFooter()->getImages() as $image) {
                        $objZip->addFromString('xl/media/' . $image->getIndexedFilename(), file_get_contents($image->getPath()));
                    }
                }
            }

                        for ($i = 0; $i < $this->getDrawingHashTable()->count(); ++$i) {
                if ($this->getDrawingHashTable()->getByIndex($i) instanceof PHPExcel_Worksheet_Drawing) {
                    $imageContents = null;
                    $imagePath = $this->getDrawingHashTable()->getByIndex($i)->getPath();
                    if (strpos($imagePath, 'zip://') !== false) {
                        $imagePath = substr($imagePath, 6);
                        $imagePathSplitted = explode('#', $imagePath);

                        $imageZip = new ZipArchive();
                        $imageZip->open($imagePathSplitted[0]);
                        $imageContents = $imageZip->getFromName($imagePathSplitted[1]);
                        $imageZip->close();
                        unset($imageZip);
                    } else {
                        $imageContents = file_get_contents($imagePath);
                    }

                    $objZip->addFromString('xl/media/' . str_replace(' ', '_', $this->getDrawingHashTable()->getByIndex($i)->getIndexedFilename()), $imageContents);
                } elseif ($this->getDrawingHashTable()->getByIndex($i) instanceof PHPExcel_Worksheet_MemoryDrawing) {
                    ob_start();
                    call_user_func(
                        $this->getDrawingHashTable()->getByIndex($i)->getRenderingFunction(),
                        $this->getDrawingHashTable()->getByIndex($i)->getImageResource()
                    );
                    $imageContents = ob_get_contents();
                    ob_end_clean();

                    $objZip->addFromString('xl/media/' . str_replace(' ', '_', $this->getDrawingHashTable()->getByIndex($i)->getIndexedFilename()), $imageContents);
                }
            }

            PHPExcel_Calculation_Functions::setReturnDateType($saveDateReturnType);
            PHPExcel_Calculation::getInstance($this->spreadSheet)->getDebugLog()->setWriteDebugLog($saveDebugLog);

                        if ($objZip->close() === false) {
                throw new PHPExcel_Writer_Exception("Could not close zip file $pFilename.");
            }

                        if ($originalFilename != $pFilename) {
                if (copy($pFilename, $originalFilename) === false) {
                    throw new PHPExcel_Writer_Exception("Could not copy temporary zip file $pFilename to $originalFilename.");
                }
                @unlink($pFilename);
            }
        } else {
            throw new PHPExcel_Writer_Exception("PHPExcel object unassigned.");
        }
    }

    
    public function getPHPExcel()
    {
        if ($this->spreadSheet !== null) {
            return $this->spreadSheet;
        } else {
            throw new PHPExcel_Writer_Exception("No PHPExcel object assigned.");
        }
    }

    
    public function setPHPExcel(PHPExcel $pPHPExcel = null)
    {
        $this->spreadSheet = $pPHPExcel;
        return $this;
    }

    
    public function getStringTable()
    {
        return $this->stringTable;
    }

    
    public function getStyleHashTable()
    {
        return $this->styleHashTable;
    }

    
    public function getStylesConditionalHashTable()
    {
        return $this->stylesConditionalHashTable;
    }

    
    public function getFillHashTable()
    {
        return $this->fillHashTable;
    }

    
    public function getFontHashTable()
    {
        return $this->fontHashTable;
    }

    
    public function getBordersHashTable()
    {
        return $this->bordersHashTable;
    }

    
    public function getNumFmtHashTable()
    {
        return $this->numFmtHashTable;
    }

    
    public function getDrawingHashTable()
    {
        return $this->drawingHashTable;
    }

    
    public function getOffice2003Compatibility()
    {
        return $this->office2003compatibility;
    }

    
    public function setOffice2003Compatibility($pValue = false)
    {
        $this->office2003compatibility = $pValue;
        return $this;
    }
}
