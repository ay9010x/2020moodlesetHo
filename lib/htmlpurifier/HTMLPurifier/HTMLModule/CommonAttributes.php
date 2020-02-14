<?php

class HTMLPurifier_HTMLModule_CommonAttributes extends HTMLPurifier_HTMLModule
{
    
    public $name = 'CommonAttributes';

    
    public $attr_collections = array(
        'Core' => array(
            0 => array('Style'),
                        'class' => 'Class',
            'id' => 'ID',
            'title' => 'CDATA',
        ),
        'Lang' => array(),
        'I18N' => array(
            0 => array('Lang'),         ),
        'Common' => array(
            0 => array('Core', 'I18N')
        )
    );
}

