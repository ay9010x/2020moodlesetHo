<?php


class HTMLPurifier_HTMLModule_TargetBlank extends HTMLPurifier_HTMLModule
{
    
    public $name = 'TargetBlank';

    
    public function setup($config)
    {
        $a = $this->addBlankElement('a');
        $a->attr_transform_post[] = new HTMLPurifier_AttrTransform_TargetBlank();
    }
}

