<?php


class HTMLPurifier_HTMLModule_Proprietary extends HTMLPurifier_HTMLModule
{
    
    public $name = 'Proprietary';

    
    public function setup($config)
    {
        $this->addElement(
            'marquee',
            'Inline',
            'Flow',
            'Common',
            array(
                'direction' => 'Enum#left,right,up,down',
                'behavior' => 'Enum#alternate',
                'width' => 'Length',
                'height' => 'Length',
                'scrolldelay' => 'Number',
                'scrollamount' => 'Number',
                'loop' => 'Number',
                'bgcolor' => 'Color',
                'hspace' => 'Pixels',
                'vspace' => 'Pixels',
            )
        );
    }
}

