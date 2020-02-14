<?php




class Mustache_HelperCollection
{
    private $helpers = array();

    
    public function __construct($helpers = null)
    {
        if ($helpers === null) {
            return;
        }

        if (!is_array($helpers) && !$helpers instanceof Traversable) {
            throw new Mustache_Exception_InvalidArgumentException('HelperCollection constructor expects an array of helpers');
        }

        foreach ($helpers as $name => $helper) {
            $this->add($name, $helper);
        }
    }

    
    public function __set($name, $helper)
    {
        $this->add($name, $helper);
    }

    
    public function add($name, $helper)
    {
        $this->helpers[$name] = $helper;
    }

    
    public function __get($name)
    {
        return $this->get($name);
    }

    
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new Mustache_Exception_UnknownHelperException($name);
        }

        return $this->helpers[$name];
    }

    
    public function __isset($name)
    {
        return $this->has($name);
    }

    
    public function has($name)
    {
        return array_key_exists($name, $this->helpers);
    }

    
    public function __unset($name)
    {
        $this->remove($name);
    }

    
    public function remove($name)
    {
        if (!$this->has($name)) {
            throw new Mustache_Exception_UnknownHelperException($name);
        }

        unset($this->helpers[$name]);
    }

    
    public function clear()
    {
        $this->helpers = array();
    }

    
    public function isEmpty()
    {
        return empty($this->helpers);
    }
}
