<?php

namespace Box\Spout\Writer\Common\Internal;


interface WorksheetInterface
{
    
    public function getExternalSheet();

    
    public function getLastWrittenRowIndex();

    
    public function addRow($dataRow, $style);

    
    public function close();
}
