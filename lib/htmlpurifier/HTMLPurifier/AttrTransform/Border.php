<?php


class HTMLPurifier_AttrTransform_Border extends HTMLPurifier_AttrTransform
{
    
    public function transform($attr, $config, $context)
    {
        if (!isset($attr['border'])) {
            return $attr;
        }
        $border_width = $this->confiscateAttr($attr, 'border');
                $this->prependCSS($attr, "border:{$border_width}px solid;");
        return $attr;
    }
}

