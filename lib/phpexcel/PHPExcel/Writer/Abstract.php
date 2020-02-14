<?php


abstract class PHPExcel_Writer_Abstract implements PHPExcel_Writer_IWriter
{
    
    protected $includeCharts = false;

    
    protected $preCalculateFormulas = true;

    
    protected $_useDiskCaching = false;

    
    protected $_diskCachingDirectory    = './';

    
    public function getIncludeCharts()
    {
        return $this->includeCharts;
    }

    
    public function setIncludeCharts($pValue = false)
    {
        $this->includeCharts = (boolean) $pValue;
        return $this;
    }

    
    public function getPreCalculateFormulas()
    {
        return $this->preCalculateFormulas;
    }

    
    public function setPreCalculateFormulas($pValue = true)
    {
        $this->preCalculateFormulas = (boolean) $pValue;
        return $this;
    }

    
    public function getUseDiskCaching()
    {
        return $this->_useDiskCaching;
    }

    
    public function setUseDiskCaching($pValue = false, $pDirectory = null)
    {
        $this->_useDiskCaching = $pValue;

        if ($pDirectory !== null) {
            if (is_dir($pDirectory)) {
                $this->_diskCachingDirectory = $pDirectory;
            } else {
                throw new PHPExcel_Writer_Exception("Directory does not exist: $pDirectory");
            }
        }
        return $this;
    }

    
    public function getDiskCachingDirectory()
    {
        return $this->_diskCachingDirectory;
    }
}
