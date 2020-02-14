<?php


class PHPExcel_Writer_OpenDocument_Settings extends PHPExcel_Writer_OpenDocument_WriterPart
{
    
    public function write(PHPExcel $pPHPExcel = null)
    {
        if (!$pPHPExcel) {
            $pPHPExcel = $this->getParentWriter()->getPHPExcel();
        }

        $objWriter = null;
        if ($this->getParentWriter()->getUseDiskCaching()) {
            $objWriter = new PHPExcel_Shared_XMLWriter(PHPExcel_Shared_XMLWriter::STORAGE_DISK, $this->getParentWriter()->getDiskCachingDirectory());
        } else {
            $objWriter = new PHPExcel_Shared_XMLWriter(PHPExcel_Shared_XMLWriter::STORAGE_MEMORY);
        }

                $objWriter->startDocument('1.0', 'UTF-8');

                $objWriter->startElement('office:document-settings');
            $objWriter->writeAttribute('xmlns:office', 'urn:oasis:names:tc:opendocument:xmlns:office:1.0');
            $objWriter->writeAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
            $objWriter->writeAttribute('xmlns:config', 'urn:oasis:names:tc:opendocument:xmlns:config:1.0');
            $objWriter->writeAttribute('xmlns:ooo', 'http://openoffice.org/2004/office');
            $objWriter->writeAttribute('office:version', '1.2');

            $objWriter->startElement('office:settings');
                $objWriter->startElement('config:config-item-set');
                    $objWriter->writeAttribute('config:name', 'ooo:view-settings');
                    $objWriter->startElement('config:config-item-map-indexed');
                        $objWriter->writeAttribute('config:name', 'Views');
                    $objWriter->endElement();
                $objWriter->endElement();
                $objWriter->startElement('config:config-item-set');
                    $objWriter->writeAttribute('config:name', 'ooo:configuration-settings');
                $objWriter->endElement();
            $objWriter->endElement();
        $objWriter->endElement();

        return $objWriter->getData();
    }
}
