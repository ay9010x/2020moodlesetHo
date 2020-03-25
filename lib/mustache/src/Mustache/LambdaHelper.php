<?php




class Mustache_LambdaHelper
{
    private $mustache;
    private $context;
    private $delims;

    
    public function __construct(Mustache_Engine $mustache, Mustache_Context $context, $delims = null)
    {
        $this->mustache = $mustache;
        $this->context  = $context;
        $this->delims   = $delims;
    }

    
    public function render($string)
    {
        return $this->mustache
            ->loadLambda((string) $string, $this->delims)
            ->renderInternal($this->context);
    }

    
    public function __invoke($string)
    {
        return $this->render($string);
    }

    
    public function withDelimiters($delims)
    {
        return new self($this->mustache, $this->context, $delims);
    }
}
