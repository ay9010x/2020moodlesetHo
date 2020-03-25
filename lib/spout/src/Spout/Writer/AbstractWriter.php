<?php

namespace Box\Spout\Writer;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Writer\Exception\WriterAlreadyOpenedException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Box\Spout\Writer\Style\StyleBuilder;


abstract class AbstractWriter implements WriterInterface
{
    
    protected $outputFilePath;

    
    protected $filePointer;

    
    protected $isWriterOpened = false;

    
    protected $globalFunctionsHelper;

    
    protected $rowStyle;

    
    protected $defaultRowStyle;

    
    protected static $headerContentType;

    
    abstract protected function openWriter();

    
    abstract protected function addRowToWriter(array $dataRow, $style);

    
    abstract protected function closeWriter();

    
    public function __construct()
    {
        $this->defaultRowStyle = $this->getDefaultRowStyle();
        $this->resetRowStyleToDefault();
    }

    
    public function setGlobalFunctionsHelper($globalFunctionsHelper)
    {
        $this->globalFunctionsHelper = $globalFunctionsHelper;
        return $this;
    }

    
    public function openToFile($outputFilePath)
    {
        $this->outputFilePath = $outputFilePath;

        $this->filePointer = $this->globalFunctionsHelper->fopen($this->outputFilePath, 'wb+');
        $this->throwIfFilePointerIsNotAvailable();

        $this->openWriter();
        $this->isWriterOpened = true;

        return $this;
    }

    
    public function openToBrowser($outputFileName)
    {
        $this->outputFilePath = $this->globalFunctionsHelper->basename($outputFileName);

        $this->filePointer = $this->globalFunctionsHelper->fopen('php://output', 'w');
        $this->throwIfFilePointerIsNotAvailable();

                $this->globalFunctionsHelper->header('Content-Type: ' . static::$headerContentType);
        $this->globalFunctionsHelper->header('Content-Disposition: attachment; filename="' . $this->outputFilePath . '"');

        
        $this->globalFunctionsHelper->header('Cache-Control: max-age=0');
        $this->globalFunctionsHelper->header('Pragma: public');

        $this->openWriter();
        $this->isWriterOpened = true;

        return $this;
    }

    
    protected function throwIfFilePointerIsNotAvailable()
    {
        if (!$this->filePointer) {
            throw new IOException('File pointer has not be opened');
        }
    }

    
    protected function throwIfWriterAlreadyOpened($message)
    {
        if ($this->isWriterOpened) {
            throw new WriterAlreadyOpenedException($message);
        }
    }

    
    public function addRow(array $dataRow)
    {
        if ($this->isWriterOpened) {
                        if (!empty($dataRow)) {
                $this->addRowToWriter($dataRow, $this->rowStyle);
            }
        } else {
            throw new WriterNotOpenedException('The writer needs to be opened before adding row.');
        }

        return $this;
    }

    
    public function addRowWithStyle(array $dataRow, $style)
    {
        if (!$style instanceof Style\Style) {
            throw new InvalidArgumentException('The "$style" argument must be a Style instance and cannot be NULL.');
        }

        $this->setRowStyle($style);
        $this->addRow($dataRow);
        $this->resetRowStyleToDefault();

        return $this;
    }

    
    public function addRows(array $dataRows)
    {
        if (!empty($dataRows)) {
            if (!is_array($dataRows[0])) {
                throw new InvalidArgumentException('The input should be an array of arrays');
            }

            foreach ($dataRows as $dataRow) {
                $this->addRow($dataRow);
            }
        }

        return $this;
    }

    
    public function addRowsWithStyle(array $dataRows, $style)
    {
        if (!$style instanceof Style\Style) {
            throw new InvalidArgumentException('The "$style" argument must be a Style instance and cannot be NULL.');
        }

        $this->setRowStyle($style);
        $this->addRows($dataRows);
        $this->resetRowStyleToDefault();

        return $this;
    }

    
    protected function getDefaultRowStyle()
    {
        return (new StyleBuilder())->build();
    }

    
    private function setRowStyle($style)
    {
                $this->rowStyle = $style->mergeWith($this->defaultRowStyle);
    }

    
    private function resetRowStyleToDefault()
    {
        $this->rowStyle = $this->defaultRowStyle;
    }

    
    public function close()
    {
        $this->closeWriter();

        if (is_resource($this->filePointer)) {
            $this->globalFunctionsHelper->fclose($this->filePointer);
        }

        $this->isWriterOpened = false;
    }
}

