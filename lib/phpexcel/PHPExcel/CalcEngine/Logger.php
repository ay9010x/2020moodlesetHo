<?php


class PHPExcel_CalcEngine_Logger
{
    
    private $writeDebugLog = false;

    
    private $echoDebugLog = false;

    
    private $debugLog = array();

    
    private $cellStack;

    
    public function __construct(PHPExcel_CalcEngine_CyclicReferenceStack $stack)
    {
        $this->cellStack = $stack;
    }

    
    public function setWriteDebugLog($pValue = false)
    {
        $this->writeDebugLog = $pValue;
    }

    
    public function getWriteDebugLog()
    {
        return $this->writeDebugLog;
    }

    
    public function setEchoDebugLog($pValue = false)
    {
        $this->echoDebugLog = $pValue;
    }

    
    public function getEchoDebugLog()
    {
        return $this->echoDebugLog;
    }

    
    public function writeDebugLog()
    {
                if ($this->writeDebugLog) {
            $message = implode(func_get_args());
            $cellReference = implode(' -> ', $this->cellStack->showStack());
            if ($this->echoDebugLog) {
                echo $cellReference,
                    ($this->cellStack->count() > 0 ? ' => ' : ''),
                    $message,
                    PHP_EOL;
            }
            $this->debugLog[] = $cellReference .
                ($this->cellStack->count() > 0 ? ' => ' : '') .
                $message;
        }
    }

    
    public function clearLog()
    {
        $this->debugLog = array();
    }

    
    public function getLog()
    {
        return $this->debugLog;
    }
}
