<?php

namespace Box\Spout\Writer\CSV;

use Box\Spout\Writer\AbstractWriter;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Helper\EncodingHelper;


class Writer extends AbstractWriter
{
    
    const FLUSH_THRESHOLD = 500;

    
    protected static $headerContentType = 'text/csv; charset=UTF-8';

    
    protected $fieldDelimiter = ',';

    
    protected $fieldEnclosure = '"';

    
    protected $lastWrittenRowIndex = 0;

    
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

    
    protected function openWriter()
    {
                $this->globalFunctionsHelper->fputs($this->filePointer, EncodingHelper::BOM_UTF8);
    }

    
    protected function addRowToWriter(array $dataRow, $style)
    {
        $wasWriteSuccessful = $this->globalFunctionsHelper->fputcsv($this->filePointer, $dataRow, $this->fieldDelimiter, $this->fieldEnclosure);
        if ($wasWriteSuccessful === false) {
            throw new IOException('Unable to write data');
        }

        $this->lastWrittenRowIndex++;
        if ($this->lastWrittenRowIndex % self::FLUSH_THRESHOLD === 0) {
            $this->globalFunctionsHelper->fflush($this->filePointer);
        }
    }

    
    protected function closeWriter()
    {
        $this->lastWrittenRowIndex = 0;
    }
}
