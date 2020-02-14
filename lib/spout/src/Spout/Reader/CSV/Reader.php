<?php

namespace Box\Spout\Reader\CSV;

use Box\Spout\Reader\AbstractReader;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Helper\EncodingHelper;


class Reader extends AbstractReader
{
    
    protected $filePointer;

    
    protected $sheetIterator;

    
    protected $fieldDelimiter = ',';

    
    protected $fieldEnclosure = '"';

    
    protected $encoding = EncodingHelper::ENCODING_UTF8;

    
    protected $endOfLineCharacter = "\n";

    
    protected $autoDetectLineEndings;

    
    public function setFieldDelimiter($fieldDelimiter)
    {
        $this->fieldDelimiter = $fieldDelimiter;
        return $this;
    }

    
    public function setFieldEnclosure($fieldEnclosure)
    {
        $this->fieldEnclosure = $fieldEnclosure;
        return $this;
    }

    
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
        return $this;
    }

    
    public function setEndOfLineCharacter($endOfLineCharacter)
    {
        $this->endOfLineCharacter = $endOfLineCharacter;
        return $this;
    }

    
    protected function doesSupportStreamWrapper()
    {
        return true;
    }

    
    protected function openReader($filePath)
    {
        $this->autoDetectLineEndings = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', '1');

        $this->filePointer = $this->globalFunctionsHelper->fopen($filePath, 'r');
        if (!$this->filePointer) {
            throw new IOException("Could not open file $filePath for reading.");
        }

        $this->sheetIterator = new SheetIterator(
            $this->filePointer,
            $this->fieldDelimiter,
            $this->fieldEnclosure,
            $this->encoding,
            $this->endOfLineCharacter,
            $this->globalFunctionsHelper
        );
    }

    
    public function getConcreteSheetIterator()
    {
        return $this->sheetIterator;
    }


    
    protected function closeReader()
    {
        if ($this->filePointer) {
            $this->globalFunctionsHelper->fclose($this->filePointer);
        }

        ini_set('auto_detect_line_endings', $this->autoDetectLineEndings);
    }
}
