<?php


class HTMLPurifier_HTMLModule_StyleAttribute extends HTMLPurifier_HTMLModule
{
    
    public $name = 'StyleAttribute';

    
    public $attr_collections = array(
                        'Style' => array('style' => false),         'Core' => array(0 => array('Style'))
    );

    
    public function setup($config)
    {
        $this->attr_collections['Style']['style'] = new HTMLPurifier_AttrDef_CSS();
    }
}

