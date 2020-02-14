<?php




class Mustache_Context
{
    private $stack      = array();
    private $blockStack = array();

    
    public function __construct($context = null)
    {
        if ($context !== null) {
            $this->stack = array($context);
        }
    }

    
    public function push($value)
    {
        array_push($this->stack, $value);
    }

    
    public function pushBlockContext($value)
    {
        array_push($this->blockStack, $value);
    }

    
    public function pop()
    {
        return array_pop($this->stack);
    }

    
    public function popBlockContext()
    {
        return array_pop($this->blockStack);
    }

    
    public function last()
    {
        return end($this->stack);
    }

    
    public function find($id)
    {
        return $this->findVariableInStack($id, $this->stack);
    }

    
    public function findDot($id)
    {
        $chunks = explode('.', $id);
        $first  = array_shift($chunks);
        $value  = $this->findVariableInStack($first, $this->stack);

        foreach ($chunks as $chunk) {
            if ($value === '') {
                return $value;
            }

            $value = $this->findVariableInStack($chunk, array($value));
        }

        return $value;
    }

    
    public function findAnchoredDot($id)
    {
        $chunks = explode('.', $id);
        $first  = array_shift($chunks);
        if ($first !== '') {
            throw new Mustache_Exception_InvalidArgumentException(sprintf('Unexpected id for findAnchoredDot: %s', $id));
        }

        $value  = $this->last();

        foreach ($chunks as $chunk) {
            if ($value === '') {
                return $value;
            }

            $value = $this->findVariableInStack($chunk, array($value));
        }

        return $value;
    }

    
    public function findInBlock($id)
    {
        foreach ($this->blockStack as $context) {
            if (array_key_exists($id, $context)) {
                return $context[$id];
            }
        }

        return '';
    }

    
    private function findVariableInStack($id, array $stack)
    {
        for ($i = count($stack) - 1; $i >= 0; $i--) {
            $frame = &$stack[$i];

            switch (gettype($frame)) {
                case 'object':
                    if (!($frame instanceof Closure)) {
                                                                        if (method_exists($frame, $id)) {
                            return $frame->$id();
                        }

                        if (isset($frame->$id)) {
                            return $frame->$id;
                        }

                        if ($frame instanceof ArrayAccess && isset($frame[$id])) {
                            return $frame[$id];
                        }
                    }
                    break;

                case 'array':
                    if (array_key_exists($id, $frame)) {
                        return $frame[$id];
                    }
                    break;
            }
        }

        return '';
    }
}
