<?php


class HTMLPurifier_TagTransform_Font extends HTMLPurifier_TagTransform
{
    
    public $transform_to = 'span';

    
    protected $_size_lookup = array(
        '0' => 'xx-small',
        '1' => 'xx-small',
        '2' => 'small',
        '3' => 'medium',
        '4' => 'large',
        '5' => 'x-large',
        '6' => 'xx-large',
        '7' => '300%',
        '-1' => 'smaller',
        '-2' => '60%',
        '+1' => 'larger',
        '+2' => '150%',
        '+3' => '200%',
        '+4' => '300%'
    );

    
    public function transform($tag, $config, $context)
    {
        if ($tag instanceof HTMLPurifier_Token_End) {
            $new_tag = clone $tag;
            $new_tag->name = $this->transform_to;
            return $new_tag;
        }

        $attr = $tag->attr;
        $prepend_style = '';

                if (isset($attr['color'])) {
            $prepend_style .= 'color:' . $attr['color'] . ';';
            unset($attr['color']);
        }

                if (isset($attr['face'])) {
            $prepend_style .= 'font-family:' . $attr['face'] . ';';
            unset($attr['face']);
        }

                if (isset($attr['size'])) {
                        if ($attr['size'] !== '') {
                if ($attr['size']{0} == '+' || $attr['size']{0} == '-') {
                    $size = (int)$attr['size'];
                    if ($size < -2) {
                        $attr['size'] = '-2';
                    }
                    if ($size > 4) {
                        $attr['size'] = '+4';
                    }
                } else {
                    $size = (int)$attr['size'];
                    if ($size > 7) {
                        $attr['size'] = '7';
                    }
                }
            }
            if (isset($this->_size_lookup[$attr['size']])) {
                $prepend_style .= 'font-size:' .
                    $this->_size_lookup[$attr['size']] . ';';
            }
            unset($attr['size']);
        }

        if ($prepend_style) {
            $attr['style'] = isset($attr['style']) ?
                $prepend_style . $attr['style'] :
                $prepend_style;
        }

        $new_tag = clone $tag;
        $new_tag->name = $this->transform_to;
        $new_tag->attr = $attr;

        return $new_tag;
    }
}

