<?php

namespace Box\Spout\Reader;


interface ReaderInterface
{
    
    public function open($filePath);

    
    public function getSheetIterator();

    
    public function close();
}
