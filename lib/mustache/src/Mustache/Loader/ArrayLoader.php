<?php




class Mustache_Loader_ArrayLoader implements Mustache_Loader, Mustache_Loader_MutableLoader
{
    private $templates;

    
    public function __construct(array $templates = array())
    {
        $this->templates = $templates;
    }

    
    public function load($name)
    {
        if (!isset($this->templates[$name])) {
            throw new Mustache_Exception_UnknownTemplateException($name);
        }

        return $this->templates[$name];
    }

    
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
    }

    
    public function setTemplate($name, $template)
    {
        $this->templates[$name] = $template;
    }
}
