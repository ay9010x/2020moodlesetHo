<?php




class Mustache_Cache_NoopCache extends Mustache_Cache_AbstractCache
{
    
    public function load($key)
    {
        return false;
    }

    
    public function cache($key, $value)
    {
        $this->log(
            Mustache_Logger::WARNING,
            'Template cache disabled, evaluating "{className}" class at runtime',
            array('className' => $key)
        );
        eval('?>' . $value);
    }
}
