<?php


class HTMLPurifier_HTMLModule_Tidy_Name extends HTMLPurifier_HTMLModule_Tidy
{
    
    public $name = 'Tidy_Name';

    
    public $defaultLevel = 'heavy';

    
    public function makeFixes()
    {
        $r = array();
                                $r['img@name'] =
        $r['a@name'] = new HTMLPurifier_AttrTransform_Name();
        return $r;
    }
}

