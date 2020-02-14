<?php


class HTMLPurifier_HTMLModule_Hypertext extends HTMLPurifier_HTMLModule
{

    
    public $name = 'Hypertext';

    
    public function setup($config)
    {
        $a = $this->addElement(
            'a',
            'Inline',
            'Inline',
            'Common',
            array(
                                                'href' => 'URI',
                                'rel' => new HTMLPurifier_AttrDef_HTML_LinkTypes('rel'),
                'rev' => new HTMLPurifier_AttrDef_HTML_LinkTypes('rev'),
                                            )
        );
        $a->formatting = true;
        $a->excludes = array('a' => true);
    }
}

