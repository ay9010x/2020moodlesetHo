<?php

namespace Box\Spout\Reader\ODS;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\Exception\XMLProcessingException;
use Box\Spout\Reader\IteratorInterface;
use Box\Spout\Reader\Wrapper\XMLReader;


class SheetIterator implements IteratorInterface
{
    
    const XML_NODE_TABLE = 'table:table';
    const XML_ATTRIBUTE_TABLE_NAME = 'table:name';

    
    protected $filePath;

    
    protected $xmlReader;

    
    protected $escaper;

    
    protected $hasFoundSheet;

    
    protected $currentSheetIndex;

    
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
        $this->xmlReader = new XMLReader();

        
        $this->escaper = new \Box\Spout\Common\Escaper\ODS();
    }

    
    public function rewind()
    {
        $this->xmlReader->close();

        $contentXmlFilePath = $this->filePath . '#content.xml';
        if ($this->xmlReader->open('zip://' . $contentXmlFilePath) === false) {
            throw new IOException("Could not open \"{$contentXmlFilePath}\".");
        }

        try {
            $this->hasFoundSheet = $this->xmlReader->readUntilNodeFound(self::XML_NODE_TABLE);
        } catch (XMLProcessingException $exception) {
           throw new IOException("The content.xml file is invalid and cannot be read. [{$exception->getMessage()}]");
       }

        $this->currentSheetIndex = 0;
    }

    
    public function valid()
    {
        return $this->hasFoundSheet;
    }

    
    public function next()
    {
        $this->hasFoundSheet = $this->xmlReader->readUntilNodeFound(self::XML_NODE_TABLE);

        if ($this->hasFoundSheet) {
            $this->currentSheetIndex++;
        }
    }

    
    public function current()
    {
        $escapedSheetName = $this->xmlReader->getAttribute(self::XML_ATTRIBUTE_TABLE_NAME);
        $sheetName = $this->escaper->unescape($escapedSheetName);

        return new Sheet($this->xmlReader, $sheetName, $this->currentSheetIndex);
    }

    
    public function key()
    {
        return $this->currentSheetIndex + 1;
    }

    
    public function end()
    {
        $this->xmlReader->close();
    }
}
