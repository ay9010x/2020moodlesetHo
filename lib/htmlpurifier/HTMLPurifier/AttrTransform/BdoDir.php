<?php



class HTMLPurifier_AttrTransform_BdoDir extends HTMLPurifier_AttrTransform
{

    
    public function transform($attr, $config, $context)
    {
        if (isset($attr['dir'])) {
            return $attr;
        }
        $attr['dir'] = $config->get('Attr.DefaultTextDir');
        return $attr;
    }
}

