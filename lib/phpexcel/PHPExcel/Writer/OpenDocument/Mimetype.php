<?php


class PHPExcel_Writer_OpenDocument_Mimetype extends PHPExcel_Writer_OpenDocument_WriterPart
{
    
    public function write(PHPExcel $pPHPExcel = null)
    {
        return 'application/vnd.oasis.opendocument.spreadsheet';
    }
}
