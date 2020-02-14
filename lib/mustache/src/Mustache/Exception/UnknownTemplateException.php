<?php




class Mustache_Exception_UnknownTemplateException extends InvalidArgumentException implements Mustache_Exception
{
    protected $templateName;

    
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
        parent::__construct(sprintf('Unknown template: %s', $templateName));
    }

    public function getTemplateName()
    {
        return $this->templateName;
    }
}
