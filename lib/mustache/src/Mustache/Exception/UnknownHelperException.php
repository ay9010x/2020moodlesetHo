<?php




class Mustache_Exception_UnknownHelperException extends InvalidArgumentException implements Mustache_Exception
{
    protected $helperName;

    
    public function __construct($helperName)
    {
        $this->helperName = $helperName;
        parent::__construct(sprintf('Unknown helper: %s', $helperName));
    }

    public function getHelperName()
    {
        return $this->helperName;
    }
}
