<?php


class HTMLPurifier_AttrTransform_Textarea extends HTMLPurifier_AttrTransform
{
    
    public function transform($attr, $config, $context)
    {
                if (!isset($attr['cols'])) {
            $attr['cols'] = '22';
        }
        if (!isset($attr['rows'])) {
            $attr['rows'] = '3';
        }
        return $attr;
    }
}

