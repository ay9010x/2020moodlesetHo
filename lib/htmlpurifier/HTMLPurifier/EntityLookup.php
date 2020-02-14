<?php


class HTMLPurifier_EntityLookup
{
    
    public $table;

    
    public function setup($file = false)
    {
        if (!$file) {
            $file = HTMLPURIFIER_PREFIX . '/HTMLPurifier/EntityLookup/entities.ser';
        }
        $this->table = unserialize(file_get_contents($file));
    }

    
    public static function instance($prototype = false)
    {
                static $instance = null;
        if ($prototype) {
            $instance = $prototype;
        } elseif (!$instance) {
            $instance = new HTMLPurifier_EntityLookup();
            $instance->setup();
        }
        return $instance;
    }
}

