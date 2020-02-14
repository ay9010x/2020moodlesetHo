<?php




class Mustache_Loader_CascadingLoader implements Mustache_Loader
{
    private $loaders;

    
    public function __construct(array $loaders = array())
    {
        $this->loaders = array();
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    
    public function addLoader(Mustache_Loader $loader)
    {
        $this->loaders[] = $loader;
    }

    
    public function load($name)
    {
        foreach ($this->loaders as $loader) {
            try {
                return $loader->load($name);
            } catch (Mustache_Exception_UnknownTemplateException $e) {
                            }
        }

        throw new Mustache_Exception_UnknownTemplateException($name);
    }
}
