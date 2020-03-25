<?php


class HTMLPurifier_AttrTransform_SafeObject extends HTMLPurifier_AttrTransform
{
    
    public $name = "SafeObject";

    
    public function transform($attr, $config, $context)
    {
        if (!isset($attr['type'])) {
            $attr['type'] = 'application/x-shockwave-flash';
        }
        return $attr;
    }
}

