<?php


abstract class PHPExcel_Reader_Abstract implements PHPExcel_Reader_IReader
{
    
    protected $readDataOnly = false;

    
    protected $includeCharts = false;

    
    protected $loadSheetsOnly;

    
    protected $readFilter;

    protected $fileHandle = null;


    
    public function getReadDataOnly()
    {
        return $this->readDataOnly;
    }

    
    public function setReadDataOnly($pValue = false)
    {
        $this->readDataOnly = $pValue;
        return $this;
    }

    
    public function getIncludeCharts()
    {
        return $this->includeCharts;
    }

    
    public function setIncludeCharts($pValue = false)
    {
        $this->includeCharts = (boolean) $pValue;
        return $this;
    }

    
    public function getLoadSheetsOnly()
    {
        return $this->loadSheetsOnly;
    }

    
    public function setLoadSheetsOnly($value = null)
    {
        if ($value === null) {
            return $this->setLoadAllSheets();
        }

        $this->loadSheetsOnly = is_array($value) ? $value : array($value);
        return $this;
    }

    
    public function setLoadAllSheets()
    {
        $this->loadSheetsOnly = null;
        return $this;
    }

    
    public function getReadFilter()
    {
        return $this->readFilter;
    }

    
    public function setReadFilter(PHPExcel_Reader_IReadFilter $pValue)
    {
        $this->readFilter = $pValue;
        return $this;
    }

    
    protected function openFile($pFilename)
    {
                if (!file_exists($pFilename) || !is_readable($pFilename)) {
            throw new PHPExcel_Reader_Exception("Could not open " . $pFilename . " for reading! File does not exist.");
        }

                $this->fileHandle = fopen($pFilename, 'r');
        if ($this->fileHandle === false) {
            throw new PHPExcel_Reader_Exception("Could not open file " . $pFilename . " for reading.");
        }
    }

    
    public function canRead($pFilename)
    {
                try {
            $this->openFile($pFilename);
        } catch (Exception $e) {
            return false;
        }

        $readable = $this->isValidFormat();
        fclose($this->fileHandle);
        return $readable;
    }

    
    public function securityScan($xml)
    {
        $pattern = '/\\0?' . implode('\\0?', str_split('<!DOCTYPE')) . '\\0?/';
        if (preg_match($pattern, $xml)) {
            throw new PHPExcel_Reader_Exception('Detected use of ENTITY in XML, spreadsheet file load() aborted to prevent XXE/XEE attacks');
        }
        return $xml;
    }

    
    public function securityScanFile($filestream)
    {
        return $this->securityScan(file_get_contents($filestream));
    }
}
