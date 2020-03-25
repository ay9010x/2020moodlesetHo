<?php


class PHPExcel_Writer_Excel2007_ContentTypes extends PHPExcel_Writer_Excel2007_WriterPart
{
    
    public function writeContentTypes(PHPExcel $pPHPExcel = null, $includeCharts = false)
    {
                $objWriter = null;
        if ($this->getParentWriter()->getUseDiskCaching()) {
            $objWriter = new PHPExcel_Shared_XMLWriter(PHPExcel_Shared_XMLWriter::STORAGE_DISK, $this->getParentWriter()->getDiskCachingDirectory());
        } else {
            $objWriter = new PHPExcel_Shared_XMLWriter(PHPExcel_Shared_XMLWriter::STORAGE_MEMORY);
        }

                $objWriter->startDocument('1.0', 'UTF-8', 'yes');

                $objWriter->startElement('Types');
        $objWriter->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/package/2006/content-types');

                $this->writeOverrideContentType($objWriter, '/xl/theme/theme1.xml', 'application/vnd.openxmlformats-officedocument.theme+xml');

                $this->writeOverrideContentType($objWriter, '/xl/styles.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml');

                $this->writeDefaultContentType($objWriter, 'rels', 'application/vnd.openxmlformats-package.relationships+xml');

                $this->writeDefaultContentType($objWriter, 'xml', 'application/xml');

                $this->writeDefaultContentType($objWriter, 'vml', 'application/vnd.openxmlformats-officedocument.vmlDrawing');

                if ($pPHPExcel->hasMacros()) {                         $this->writeOverrideContentType($objWriter, '/xl/workbook.xml', 'application/vnd.ms-excel.sheet.macroEnabled.main+xml');
                        $this->writeDefaultContentType($objWriter, 'bin', 'application/vnd.ms-office.vbaProject');
            if ($pPHPExcel->hasMacrosCertificate()) {                                $this->writeOverrideContentType($objWriter, '/xl/vbaProjectSignature.bin', 'application/vnd.ms-office.vbaProjectSignature');
            }
        } else {            $this->writeOverrideContentType($objWriter, '/xl/workbook.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml');
        }

                $this->writeOverrideContentType($objWriter, '/docProps/app.xml', 'application/vnd.openxmlformats-officedocument.extended-properties+xml');

        $this->writeOverrideContentType($objWriter, '/docProps/core.xml', 'application/vnd.openxmlformats-package.core-properties+xml');

        $customPropertyList = $pPHPExcel->getProperties()->getCustomProperties();
        if (!empty($customPropertyList)) {
            $this->writeOverrideContentType($objWriter, '/docProps/custom.xml', 'application/vnd.openxmlformats-officedocument.custom-properties+xml');
        }

                $sheetCount = $pPHPExcel->getSheetCount();
        for ($i = 0; $i < $sheetCount; ++$i) {
            $this->writeOverrideContentType($objWriter, '/xl/worksheets/sheet' . ($i + 1) . '.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml');
        }

                $this->writeOverrideContentType($objWriter, '/xl/sharedStrings.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml');

                $chart = 1;
        for ($i = 0; $i < $sheetCount; ++$i) {
            $drawings = $pPHPExcel->getSheet($i)->getDrawingCollection();
            $drawingCount = count($drawings);
            $chartCount = ($includeCharts) ? $pPHPExcel->getSheet($i)->getChartCount() : 0;

                        if (($drawingCount > 0) || ($chartCount > 0)) {
                $this->writeOverrideContentType($objWriter, '/xl/drawings/drawing' . ($i + 1) . '.xml', 'application/vnd.openxmlformats-officedocument.drawing+xml');
            }

                        if ($chartCount > 0) {
                for ($c = 0; $c < $chartCount; ++$c) {
                    $this->writeOverrideContentType($objWriter, '/xl/charts/chart' . $chart++ . '.xml', 'application/vnd.openxmlformats-officedocument.drawingml.chart+xml');
                }
            }
        }

                for ($i = 0; $i < $sheetCount; ++$i) {
            if (count($pPHPExcel->getSheet($i)->getComments()) > 0) {
                $this->writeOverrideContentType($objWriter, '/xl/comments' . ($i + 1) . '.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.comments+xml');
            }
        }

                $aMediaContentTypes = array();
        $mediaCount = $this->getParentWriter()->getDrawingHashTable()->count();
        for ($i = 0; $i < $mediaCount; ++$i) {
            $extension     = '';
            $mimeType     = '';

            if ($this->getParentWriter()->getDrawingHashTable()->getByIndex($i) instanceof PHPExcel_Worksheet_Drawing) {
                $extension = strtolower($this->getParentWriter()->getDrawingHashTable()->getByIndex($i)->getExtension());
                $mimeType = $this->getImageMimeType($this->getParentWriter()->getDrawingHashTable()->getByIndex($i)->getPath());
            } elseif ($this->getParentWriter()->getDrawingHashTable()->getByIndex($i) instanceof PHPExcel_Worksheet_MemoryDrawing) {
                $extension = strtolower($this->getParentWriter()->getDrawingHashTable()->getByIndex($i)->getMimeType());
                $extension = explode('/', $extension);
                $extension = $extension[1];

                $mimeType = $this->getParentWriter()->getDrawingHashTable()->getByIndex($i)->getMimeType();
            }

            if (!isset( $aMediaContentTypes[$extension])) {
                $aMediaContentTypes[$extension] = $mimeType;

                $this->writeDefaultContentType($objWriter, $extension, $mimeType);
            }
        }
        if ($pPHPExcel->hasRibbonBinObjects()) {
                                    $tabRibbonTypes=array_diff($pPHPExcel->getRibbonBinObjects('types'), array_keys($aMediaContentTypes));
            foreach ($tabRibbonTypes as $aRibbonType) {
                $mimeType='image/.'.$aRibbonType;                $this->writeDefaultContentType($objWriter, $aRibbonType, $mimeType);
            }
        }
        $sheetCount = $pPHPExcel->getSheetCount();
        for ($i = 0; $i < $sheetCount; ++$i) {
            if (count($pPHPExcel->getSheet()->getHeaderFooter()->getImages()) > 0) {
                foreach ($pPHPExcel->getSheet()->getHeaderFooter()->getImages() as $image) {
                    if (!isset( $aMediaContentTypes[strtolower($image->getExtension())])) {
                        $aMediaContentTypes[strtolower($image->getExtension())] = $this->getImageMimeType($image->getPath());

                        $this->writeDefaultContentType($objWriter, strtolower($image->getExtension()), $aMediaContentTypes[strtolower($image->getExtension())]);
                    }
                }
            }
        }

        $objWriter->endElement();

                return $objWriter->getData();
    }

    
    private function getImageMimeType($pFile = '')
    {
        if (PHPExcel_Shared_File::file_exists($pFile)) {
            $image = getimagesize($pFile);
            return image_type_to_mime_type($image[2]);
        } else {
            throw new PHPExcel_Writer_Exception("File $pFile does not exist");
        }
    }

    
    private function writeDefaultContentType(PHPExcel_Shared_XMLWriter $objWriter = null, $pPartname = '', $pContentType = '')
    {
        if ($pPartname != '' && $pContentType != '') {
                        $objWriter->startElement('Default');
            $objWriter->writeAttribute('Extension', $pPartname);
            $objWriter->writeAttribute('ContentType', $pContentType);
            $objWriter->endElement();
        } else {
            throw new PHPExcel_Writer_Exception("Invalid parameters passed.");
        }
    }

    
    private function writeOverrideContentType(PHPExcel_Shared_XMLWriter $objWriter = null, $pPartname = '', $pContentType = '')
    {
        if ($pPartname != '' && $pContentType != '') {
                        $objWriter->startElement('Override');
            $objWriter->writeAttribute('PartName', $pPartname);
            $objWriter->writeAttribute('ContentType', $pContentType);
            $objWriter->endElement();
        } else {
            throw new PHPExcel_Writer_Exception("Invalid parameters passed.");
        }
    }
}
