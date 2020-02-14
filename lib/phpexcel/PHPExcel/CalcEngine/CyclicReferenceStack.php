<?php


class PHPExcel_CalcEngine_CyclicReferenceStack
{
    
    private $stack = array();

    
    public function count()
    {
        return count($this->stack);
    }

    
    public function push($value)
    {
        $this->stack[$value] = $value;
    }

    
    public function pop()
    {
        return array_pop($this->stack);
    }

    
    public function onStack($value)
    {
        return isset($this->stack[$value]);
    }

    
    public function clear()
    {
        $this->stack = array();
    }

    
    public function showStack()
    {
        return $this->stack;
    }
}
