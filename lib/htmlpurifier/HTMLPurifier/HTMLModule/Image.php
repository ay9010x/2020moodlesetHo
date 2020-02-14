<?php


class HTMLPurifier_HTMLModule_Image extends HTMLPurifier_HTMLModule
{

    
    public $name = 'Image';

    
    public function setup($config)
    {
        $max = $config->get('HTML.MaxImgLength');
        $img = $this->addElement(
            'img',
            'Inline',
            'Empty',
            'Common',
            array(
                'alt*' => 'Text',
                                                'height' => 'Pixels#' . $max,
                'width' => 'Pixels#' . $max,
                'longdesc' => 'URI',
                'src*' => new HTMLPurifier_AttrDef_URI(true),             )
        );
        if ($max === null || $config->get('HTML.Trusted')) {
            $img->attr['height'] =
            $img->attr['width'] = 'Length';
        }

                $img->attr_transform_pre[] =
        $img->attr_transform_post[] =
            new HTMLPurifier_AttrTransform_ImgRequired();
    }
}

