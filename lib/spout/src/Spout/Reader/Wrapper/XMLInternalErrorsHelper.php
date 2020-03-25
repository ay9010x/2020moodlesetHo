<?php

namespace Box\Spout\Reader\Wrapper;

use Box\Spout\Reader\Exception\XMLProcessingException;


trait XMLInternalErrorsHelper
{
    
    protected $initialUseInternalErrorsValue;

    
    protected function useXMLInternalErrors()
    {
        libxml_clear_errors();
        $this->initialUseInternalErrorsValue = libxml_use_internal_errors(true);
    }

    
    protected function resetXMLInternalErrorsSettingAndThrowIfXMLErrorOccured()
    {
        if ($this->hasXMLErrorOccured()) {
            $this->resetXMLInternalErrorsSetting();
            throw new XMLProcessingException($this->getLastXMLErrorMessage());
        }

        $this->resetXMLInternalErrorsSetting();
    }

    
    private function hasXMLErrorOccured()
    {
        return (libxml_get_last_error() !== false);
    }

    
    private function getLastXMLErrorMessage()
    {
        $errorMessage = null;
        $error = libxml_get_last_error();

        if ($error !== false) {
            $errorMessage = trim($error->message);
        }

        return $errorMessage;
    }

    
    protected function resetXMLInternalErrorsSetting()
    {
        libxml_use_internal_errors($this->initialUseInternalErrorsValue);
    }

}
