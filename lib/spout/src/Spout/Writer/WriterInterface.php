<?php

namespace Box\Spout\Writer;


interface WriterInterface
{
    
    public function openToFile($outputFilePath);

    
    public function openToBrowser($outputFileName);

    
    public function addRow(array $dataRow);

    
    public function addRowWithStyle(array $dataRow, $style);

    
    public function addRows(array $dataRows);

    
    public function addRowsWithStyle(array $dataRows, $style);

    
    public function close();
}
