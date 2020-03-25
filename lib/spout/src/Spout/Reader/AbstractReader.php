<?php

namespace Box\Spout\Reader;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;


abstract class AbstractReader implements ReaderInterface
{
    
    protected $isStreamOpened = false;

    
    protected $globalFunctionsHelper;

    
    abstract protected function doesSupportStreamWrapper();

    
    abstract protected function openReader($filePath);

    
    abstract public function getConcreteSheetIterator();

    
    abstract protected function closeReader();

    
    public function setGlobalFunctionsHelper($globalFunctionsHelper)
    {
        $this->globalFunctionsHelper = $globalFunctionsHelper;
        return $this;
    }

    
    public function open($filePath)
    {
        if ($this->isStreamWrapper($filePath) && (!$this->doesSupportStreamWrapper() || !$this->isSupportedStreamWrapper($filePath))) {
            throw new IOException("Could not open $filePath for reading! Stream wrapper used is not supported for this type of file.");
        }

        if (!$this->isPhpStream($filePath)) {
                        if (!$this->globalFunctionsHelper->file_exists($filePath)) {
                throw new IOException("Could not open $filePath for reading! File does not exist.");
            } else if (!$this->globalFunctionsHelper->is_readable($filePath)) {
                throw new IOException("Could not open $filePath for reading! File is not readable.");
            }
        }

        try {
            $fileRealPath = $this->getFileRealPath($filePath);
            $this->openReader($fileRealPath);
            $this->isStreamOpened = true;
        } catch (\Exception $exception) {
            throw new IOException("Could not open $filePath for reading! ({$exception->getMessage()})");
        }
    }

    
    protected function getFileRealPath($filePath)
    {
        if ($this->isSupportedStreamWrapper($filePath)) {
            return $filePath;
        }

                return realpath($filePath);
    }

    
    protected function getStreamWrapperScheme($filePath)
    {
        $streamScheme = null;
        if (preg_match('/^(\w+):\/\//', $filePath, $matches)) {
            $streamScheme = $matches[1];
        }
        return $streamScheme;
    }

    
    protected function isStreamWrapper($filePath)
    {
        return ($this->getStreamWrapperScheme($filePath) !== null);
    }

    
    protected function isSupportedStreamWrapper($filePath)
    {
        $streamScheme = $this->getStreamWrapperScheme($filePath);
        return ($streamScheme !== null) ?
            in_array($streamScheme, $this->globalFunctionsHelper->stream_get_wrappers()) :
            true;
    }

    
    protected function isPhpStream($filePath)
    {
        $streamScheme = $this->getStreamWrapperScheme($filePath);
        return ($streamScheme === 'php');
    }

    
    public function getSheetIterator()
    {
        if (!$this->isStreamOpened) {
            throw new ReaderNotOpenedException('Reader should be opened first.');
        }

        return $this->getConcreteSheetIterator();
    }

    
    public function close()
    {
        if ($this->isStreamOpened) {
            $this->closeReader();

            $sheetIterator = $this->getConcreteSheetIterator();
            if ($sheetIterator) {
                $sheetIterator->end();
            }

            $this->isStreamOpened = false;
        }
    }
}
