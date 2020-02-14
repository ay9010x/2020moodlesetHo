<?php

class Horde_Support_Backtrace
{
    
    public $backtrace;

    
    public function __construct($backtrace = null)
    {
        if ($backtrace instanceof Exception) {
            $this->createFromException($backtrace);
        } elseif ($backtrace) {
            $this->createFromDebugBacktrace($backtrace);
        } else {
            $this->createFromDebugBacktrace(debug_backtrace(), 1);
        }
    }

    
    public function createFromDebugBacktrace($backtrace, $nestingLevel = 0)
    {
        while ($nestingLevel > 0) {
            array_shift($backtrace);
            --$nestingLevel;
        }

        $this->backtrace = $backtrace;
    }

    
    public function createFromException(Exception $e)
    {
        $this->backtrace = $e->getTrace();
        if ($previous = $e->getPrevious()) {
            $backtrace = new self($previous);
            $this->backtrace = array_merge($backtrace->backtrace,
                                           $this->backtrace);
        }
    }

    
    public function getNestingLevel()
    {
        return count($this->backtrace);
    }

    
    public function getContext($nestingLevel)
    {
        if (!isset($this->backtrace[$nestingLevel])) {
            throw new Horde_Exception('Unknown nesting level');
        }
        return $this->backtrace[$nestingLevel];
    }

    
    public function getCurrentContext()
    {
        return $this->getContext(0);
    }

    
    public function getCallingContext()
    {
        return $this->getContext(1);
    }

    
    public function __toString()
    {
        $count = count($this->backtrace);
        $pad = strlen($count);
        $map = '';
        for ($i = $count - 1; $i >= 0; $i--) {
            $map .= str_pad($count - $i, $pad, ' ', STR_PAD_LEFT) . '. ';
            if (isset($this->backtrace[$i]['class'])) {
                $map .= $this->backtrace[$i]['class']
                    . $this->backtrace[$i]['type'];
            }
            $map .= $this->backtrace[$i]['function'] . '()';
            if (isset($this->backtrace[$i]['file'])) {
                $map .= ' ' . $this->backtrace[$i]['file']
                    . ':' . $this->backtrace[$i]['line'];
            }
            $map .= "\n";
        }
        return $map;
    }
}
