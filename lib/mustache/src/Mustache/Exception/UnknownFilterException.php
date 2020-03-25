<?php




class Mustache_Exception_UnknownFilterException extends UnexpectedValueException implements Mustache_Exception
{
    protected $filterName;

    
    public function __construct($filterName)
    {
        $this->filterName = $filterName;
        parent::__construct(sprintf('Unknown filter: %s', $filterName));
    }

    public function getFilterName()
    {
        return $this->filterName;
    }
}
