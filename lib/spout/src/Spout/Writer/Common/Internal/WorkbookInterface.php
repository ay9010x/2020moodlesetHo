<?php

namespace Box\Spout\Writer\Common\Internal;


interface WorkbookInterface
{
    
    public function addNewSheet();

    
    public function addNewSheetAndMakeItCurrent();

    
    public function getWorksheets();

    
    public function getCurrentWorksheet();

    
    public function setCurrentSheet($sheet);

    
    public function addRowToCurrentWorksheet($dataRow, $style);

    
    public function close($finalFilePointer);
}
