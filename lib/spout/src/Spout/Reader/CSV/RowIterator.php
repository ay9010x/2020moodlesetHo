<?php

namespace Box\Spout\Reader\CSV;

use Box\Spout\Reader\IteratorInterface;
use Box\Spout\Common\Helper\EncodingHelper;


class RowIterator implements IteratorInterface
{
    
    const MAX_READ_BYTES_PER_LINE = 32768;

    
    protected $filePointer;

    
    protected $numReadRows = 0;

    
    protected $rowDataBuffer = null;

    
    protected $hasReachedEndOfFile = false;

    
    protected $fieldDelimiter;

    
    protected $fieldEnclosure;

    
    protected $encoding;

    
    protected $globalFunctionsHelper;

    
    protected $encodingHelper;

    
    protected $encodedEOLDelimiter;

    
    protected $inputEOLDelimiter;

    
    public function __construct($filePointer, $fieldDelimiter, $fieldEnclosure, $encoding, $endOfLineDelimiter, $globalFunctionsHelper)
    {
        $this->filePointer = $filePointer;
        $this->fieldDelimiter = $fieldDelimiter;
        $this->fieldEnclosure = $fieldEnclosure;
        $this->encoding = $encoding;
        $this->inputEOLDelimiter = $endOfLineDelimiter;
        $this->globalFunctionsHelper = $globalFunctionsHelper;

        $this->encodingHelper = new EncodingHelper($globalFunctionsHelper);
    }

    
    public function rewind()
    {
        $this->rewindAndSkipBom();

        $this->numReadRows = 0;
        $this->rowDataBuffer = null;

        $this->next();
    }

    
    protected function rewindAndSkipBom()
    {
        $byteOffsetToSkipBom = $this->encodingHelper->getBytesOffsetToSkipBOM($this->filePointer, $this->encoding);

                $this->globalFunctionsHelper->fseek($this->filePointer, $byteOffsetToSkipBom);
    }

    
    public function valid()
    {
        return ($this->filePointer && !$this->hasReachedEndOfFile);
    }

    
    public function next()
    {
        $this->hasReachedEndOfFile = $this->globalFunctionsHelper->feof($this->filePointer);

        if ($this->hasReachedEndOfFile) {
            return;
        }

        do {
            $rowData = $this->getNextUTF8EncodedRow();
            $hasNowReachedEndOfFile = $this->globalFunctionsHelper->feof($this->filePointer);
        } while (($rowData === false && !$hasNowReachedEndOfFile) || $this->isEmptyLine($rowData));

        if ($rowData !== false) {
            $this->rowDataBuffer = $rowData;
            $this->numReadRows++;
        } else {
                                    $this->hasReachedEndOfFile = $hasNowReachedEndOfFile;
        }
    }

    
    protected function getNextUTF8EncodedRow()
    {
        $encodedRowData = fgetcsv($this->filePointer, self::MAX_READ_BYTES_PER_LINE, $this->fieldDelimiter, $this->fieldEnclosure);
        if (false === $encodedRowData) {
            return false;
        }

        foreach ($encodedRowData as $cellIndex => $cellValue) {
            switch($this->encoding) {
                case EncodingHelper::ENCODING_UTF16_LE:
                case EncodingHelper::ENCODING_UTF32_LE:
                                        $cellValue = ltrim($cellValue);
                    break;

                case EncodingHelper::ENCODING_UTF16_BE:
                case EncodingHelper::ENCODING_UTF32_BE:
                                        $cellValue = rtrim($cellValue);
                    break;
            }

            $encodedRowData[$cellIndex] = $this->encodingHelper->attemptConversionToUTF8($cellValue, $this->encoding);
        }

        return $encodedRowData;
    }

    
    protected function getEncodedEOLDelimiter()
    {
        if (!isset($this->encodedEOLDelimiter)) {
            $this->encodedEOLDelimiter = $this->encodingHelper->attemptConversionFromUTF8($this->inputEOLDelimiter, $this->encoding);
        }

        return $this->encodedEOLDelimiter;
    }

    
    protected function isEmptyLine($lineData)
    {
        return (is_array($lineData) && count($lineData) === 1 && $lineData[0] === null);
    }

    
    public function current()
    {
        return $this->rowDataBuffer;
    }

    
    public function key()
    {
        return $this->numReadRows;
    }

    
    public function end()
    {
            }
}
