<?php


class PHPExcel_Calculation_Token_Stack
{
    
    private $stack = array();

    
    private $count = 0;

    
    public function count()
    {
        return $this->count;
    }

    
    public function push($type, $value, $reference = null)
    {
        $this->stack[$this->count++] = array(
            'type'      => $type,
            'value'     => $value,
            'reference' => $reference
        );
        if ($type == 'Function') {
            $localeFunction = PHPExcel_Calculation::localeFunc($value);
            if ($localeFunction != $value) {
                $this->stack[($this->count - 1)]['localeValue'] = $localeFunction;
            }
        }
    }

    
    public function pop()
    {
        if ($this->count > 0) {
            return $this->stack[--$this->count];
        }
        return null;
    }

    
    public function last($n = 1)
    {
        if ($this->count - $n < 0) {
            return null;
        }
        return $this->stack[$this->count - $n];
    }

    
    public function clear()
    {
        $this->stack = array();
        $this->count = 0;
    }
}
