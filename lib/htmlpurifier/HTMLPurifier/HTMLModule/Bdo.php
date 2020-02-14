<?php


class HTMLPurifier_HTMLModule_Bdo extends HTMLPurifier_HTMLModule
{

    
    public $name = 'Bdo';

    
    public $attr_collections = array(
        'I18N' => array('dir' => false)
    );

    
    public function setup($config)
    {
        $bdo = $this->addElement(
            'bdo',
            'Inline',
            'Inline',
            array('Core', 'Lang'),
            array(
                'dir' => 'Enum#ltr,rtl',                                             )
        );
        $bdo->attr_transform_post[] = new HTMLPurifier_AttrTransform_BdoDir();

        $this->attr_collections['I18N']['dir'] = 'Enum#ltr,rtl';
    }
}

