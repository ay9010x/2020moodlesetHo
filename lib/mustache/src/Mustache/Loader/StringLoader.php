<?php




class Mustache_Loader_StringLoader implements Mustache_Loader
{
    
    public function load($name)
    {
        return $name;
    }
}
