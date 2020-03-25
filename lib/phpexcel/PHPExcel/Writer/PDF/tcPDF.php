<?php


$pdfRendererClassFile = PHPExcel_Settings::getPdfRendererPath() . '/tcpdf.php';
if (file_exists($pdfRendererClassFile)) {
    $k_path_url = PHPExcel_Settings::getPdfRendererPath();
    require_once $pdfRendererClassFile;
} else {
    throw new PHPExcel_Writer_Exception('Unable to load PDF Rendering library');
}


class PHPExcel_Writer_PDF_tcPDF extends PHPExcel_Writer_PDF_Core implements PHPExcel_Writer_IWriter
{
    
    public function __construct(PHPExcel $phpExcel)
    {
        parent::__construct($phpExcel);
    }

    
    public function save($pFilename = null)
    {
        $fileHandle = parent::prepareForSave($pFilename);

                $paperSize = 'LETTER';    
                if (is_null($this->getSheetIndex())) {
            $orientation = ($this->phpExcel->getSheet(0)->getPageSetup()->getOrientation()
                == PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE) ? 'L' : 'P';
            $printPaperSize = $this->phpExcel->getSheet(0)->getPageSetup()->getPaperSize();
            $printMargins = $this->phpExcel->getSheet(0)->getPageMargins();
        } else {
            $orientation = ($this->phpExcel->getSheet($this->getSheetIndex())->getPageSetup()->getOrientation()
                == PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE) ? 'L' : 'P';
            $printPaperSize = $this->phpExcel->getSheet($this->getSheetIndex())->getPageSetup()->getPaperSize();
            $printMargins = $this->phpExcel->getSheet($this->getSheetIndex())->getPageMargins();
        }

                if (!is_null($this->getOrientation())) {
            $orientation = ($this->getOrientation() == PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE)
                ? 'L'
                : 'P';
        }
                if (!is_null($this->getPaperSize())) {
            $printPaperSize = $this->getPaperSize();
        }

        if (isset(self::$paperSizes[$printPaperSize])) {
            $paperSize = self::$paperSizes[$printPaperSize];
        }


                $pdf = new TCPDF($orientation, 'pt', $paperSize);
        $pdf->setFontSubsetting(false);
                $pdf->SetMargins($printMargins->getLeft() * 72, $printMargins->getTop() * 72, $printMargins->getRight() * 72);
        $pdf->SetAutoPageBreak(true, $printMargins->getBottom() * 72);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage();

                $pdf->SetFont($this->getFont());
        $pdf->writeHTML(
            $this->generateHTMLHeader(false) .
            $this->generateSheetData() .
            $this->generateHTMLFooter()
        );

                $pdf->SetTitle($this->phpExcel->getProperties()->getTitle());
        $pdf->SetAuthor($this->phpExcel->getProperties()->getCreator());
        $pdf->SetSubject($this->phpExcel->getProperties()->getSubject());
        $pdf->SetKeywords($this->phpExcel->getProperties()->getKeywords());
        $pdf->SetCreator($this->phpExcel->getProperties()->getCreator());

                fwrite($fileHandle, $pdf->output($pFilename, 'S'));

        parent::restoreStateAfterSave($fileHandle);
    }
}
