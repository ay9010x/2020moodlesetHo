<?php




abstract class Mustache_Template
{
    
    protected $mustache;

    
    protected $strictCallables = false;

    
    public function __construct(Mustache_Engine $mustache)
    {
        $this->mustache = $mustache;
    }

    
    public function __invoke($context = array())
    {
        return $this->render($context);
    }

    
    public function render($context = array())
    {
        return $this->renderInternal(
            $this->prepareContextStack($context)
        );
    }

    
    abstract public function renderInternal(Mustache_Context $context, $indent = '');

    
    protected function isIterable($value)
    {
        switch (gettype($value)) {
            case 'object':
                return $value instanceof Traversable;

            case 'array':
                $i = 0;
                foreach ($value as $k => $v) {
                    if ($k !== $i++) {
                        return false;
                    }
                }

                return true;

            default:
                return false;
        }
    }

    
    protected function prepareContextStack($context = null)
    {
        $stack = new Mustache_Context();

        $helpers = $this->mustache->getHelpers();
        if (!$helpers->isEmpty()) {
            $stack->push($helpers);
        }

        if (!empty($context)) {
            $stack->push($context);
        }

        return $stack;
    }

    
    protected function resolveValue($value, Mustache_Context $context)
    {
        if (($this->strictCallables ? is_object($value) : !is_string($value)) && is_callable($value)) {
            return $this->mustache
                ->loadLambda((string) call_user_func($value))
                ->renderInternal($context);
        }

        return $value;
    }
}
