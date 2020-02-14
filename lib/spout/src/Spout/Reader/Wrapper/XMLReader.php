<?php

namespace Box\Spout\Reader\Wrapper;



class XMLReader extends \XMLReader
{
    use XMLInternalErrorsHelper;

    
    public function open($URI, $encoding = null, $options = 0)
    {
        $wasOpenSuccessful = false;
        $realPathURI = $this->convertURIToUseRealPath($URI);

                        if ($this->isRunningHHVM() && $this->isZipStream($realPathURI)) {
            if ($this->fileExistsWithinZip($realPathURI)) {
                $wasOpenSuccessful = parent::open($realPathURI, $encoding, $options|LIBXML_NONET);
            }
        } else {
            $wasOpenSuccessful = parent::open($realPathURI, $encoding, $options|LIBXML_NONET);
        }

        return $wasOpenSuccessful;
    }

    
    protected function convertURIToUseRealPath($URI)
    {
        $realPathURI = $URI;

        if ($this->isZipStream($URI)) {
            if (preg_match('/zip:\/\/(.*)#(.*)/', $URI, $matches)) {
                $documentPath = $matches[1];
                $documentInsideZipPath = $matches[2];
                $realPathURI = 'zip://' . realpath($documentPath) . '#' . $documentInsideZipPath;
            }
        } else {
            $realPathURI = realpath($URI);
        }

        return $realPathURI;
    }

    
    protected function isZipStream($URI)
    {
        return (strpos($URI, 'zip://') === 0);
    }

    
    protected function isRunningHHVM()
    {
        return defined('HHVM_VERSION');
    }

    
    protected function fileExistsWithinZip($zipStreamURI)
    {
        $doesFileExists = false;

        $pattern = '/zip:\/\/([^#]+)#(.*)/';
        if (preg_match($pattern, $zipStreamURI, $matches)) {
            $zipFilePath = $matches[1];
            $innerFilePath = $matches[2];

            $zip = new \ZipArchive();
            if ($zip->open($zipFilePath) === true) {
                $doesFileExists = ($zip->locateName($innerFilePath) !== false);
                $zip->close();
            }
        }

        return $doesFileExists;
    }

    
    public function read()
    {
        $this->useXMLInternalErrors();

        $wasReadSuccessful = parent::read();

        $this->resetXMLInternalErrorsSettingAndThrowIfXMLErrorOccured();

        return $wasReadSuccessful;
    }

    
    public function readUntilNodeFound($nodeName)
    {
        while (($wasReadSuccessful = $this->read()) && ($this->nodeType !== \XMLReader::ELEMENT || $this->name !== $nodeName)) {
                    }

        return $wasReadSuccessful;
    }

    
    public function next($localName = null)
    {
        $this->useXMLInternalErrors();

        $wasNextSuccessful = parent::next($localName);

        $this->resetXMLInternalErrorsSettingAndThrowIfXMLErrorOccured();

        return $wasNextSuccessful;
    }

    
    public function isPositionedOnStartingNode($nodeName)
    {
        return ($this->nodeType === XMLReader::ELEMENT && $this->name === $nodeName);
    }

    
    public function isPositionedOnEndingNode($nodeName)
    {
        return ($this->nodeType === XMLReader::END_ELEMENT && $this->name === $nodeName);
    }
}
