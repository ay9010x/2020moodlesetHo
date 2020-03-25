<?php


class HTMLPurifier_AttrTransform_BgColor extends HTMLPurifier_AttrTransform
{
    
    public function transform($attr, $config, $context)
    {
        if (!isset($attr['bgcolor'])) {
            return $attr;
        }

        $bgcolor = $this->confiscateAttr($attr, 'bgcolor');
        
        $this->prependCSS($attr, "background-color:$bgcolor;");
        return $attr;
    }
}

