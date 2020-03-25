<?php




interface Mustache_Loader_MutableLoader
{
    
    public function setTemplates(array $templates);

    
    public function setTemplate($name, $template);
}
