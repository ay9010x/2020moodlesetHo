<?php


class HTMLPurifier_HTMLModule_List extends HTMLPurifier_HTMLModule
{
    
    public $name = 'List';

                                
    
    public $content_sets = array('Flow' => 'List');

    
    public function setup($config)
    {
        $ol = $this->addElement('ol', 'List', new HTMLPurifier_ChildDef_List(), 'Common');
        $ul = $this->addElement('ul', 'List', new HTMLPurifier_ChildDef_List(), 'Common');
                                                        $ol->wrap = 'li';
        $ul->wrap = 'li';
        $this->addElement('dl', 'List', 'Required: dt | dd', 'Common');

        $this->addElement('li', false, 'Flow', 'Common');

        $this->addElement('dd', false, 'Flow', 'Common');
        $this->addElement('dt', false, 'Inline', 'Common');
    }
}

