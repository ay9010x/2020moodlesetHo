<?php

class HTMLPurifier_HTMLModule_Tidy_XHTMLAndHTML4 extends HTMLPurifier_HTMLModule_Tidy
{

    
    public function makeFixes()
    {
        $r = array();

        
        $r['font'] = new HTMLPurifier_TagTransform_Font();
        $r['menu'] = new HTMLPurifier_TagTransform_Simple('ul');
        $r['dir'] = new HTMLPurifier_TagTransform_Simple('ul');
        $r['center'] = new HTMLPurifier_TagTransform_Simple('div', 'text-align:center;');
        $r['u'] = new HTMLPurifier_TagTransform_Simple('span', 'text-decoration:underline;');
        $r['s'] = new HTMLPurifier_TagTransform_Simple('span', 'text-decoration:line-through;');
        $r['strike'] = new HTMLPurifier_TagTransform_Simple('span', 'text-decoration:line-through;');

        
        $r['caption@align'] =
            new HTMLPurifier_AttrTransform_EnumToCSS(
                'align',
                array(
                                                                                                    'left' => 'text-align:left;',
                    'right' => 'text-align:right;',
                    'top' => 'caption-side:top;',
                    'bottom' => 'caption-side:bottom;'                 )
            );

                $r['img@align'] =
            new HTMLPurifier_AttrTransform_EnumToCSS(
                'align',
                array(
                    'left' => 'float:left;',
                    'right' => 'float:right;',
                    'top' => 'vertical-align:top;',
                    'middle' => 'vertical-align:middle;',
                    'bottom' => 'vertical-align:baseline;',
                )
            );

                $r['table@align'] =
            new HTMLPurifier_AttrTransform_EnumToCSS(
                'align',
                array(
                    'left' => 'float:left;',
                    'center' => 'margin-left:auto;margin-right:auto;',
                    'right' => 'float:right;'
                )
            );

                $r['hr@align'] =
            new HTMLPurifier_AttrTransform_EnumToCSS(
                'align',
                array(
                                                                                                    'left' => 'margin-left:0;margin-right:auto;text-align:left;',
                    'center' => 'margin-left:auto;margin-right:auto;text-align:center;',
                    'right' => 'margin-left:auto;margin-right:0;text-align:right;'
                )
            );

                        $align_lookup = array();
        $align_values = array('left', 'right', 'center', 'justify');
        foreach ($align_values as $v) {
            $align_lookup[$v] = "text-align:$v;";
        }
                $r['h1@align'] =
        $r['h2@align'] =
        $r['h3@align'] =
        $r['h4@align'] =
        $r['h5@align'] =
        $r['h6@align'] =
        $r['p@align'] =
        $r['div@align'] =
            new HTMLPurifier_AttrTransform_EnumToCSS('align', $align_lookup);

                $r['table@bgcolor'] =
        $r['td@bgcolor'] =
        $r['th@bgcolor'] =
            new HTMLPurifier_AttrTransform_BgColor();

                $r['img@border'] = new HTMLPurifier_AttrTransform_Border();

                $r['br@clear'] =
            new HTMLPurifier_AttrTransform_EnumToCSS(
                'clear',
                array(
                    'left' => 'clear:left;',
                    'right' => 'clear:right;',
                    'all' => 'clear:both;',
                    'none' => 'clear:none;',
                )
            );

                $r['td@height'] =
        $r['th@height'] =
            new HTMLPurifier_AttrTransform_Length('height');

                $r['img@hspace'] = new HTMLPurifier_AttrTransform_ImgSpace('hspace');

                                $r['hr@noshade'] =
            new HTMLPurifier_AttrTransform_BoolToCSS(
                'noshade',
                'color:#808080;background-color:#808080;border:0;'
            );

                $r['td@nowrap'] =
        $r['th@nowrap'] =
            new HTMLPurifier_AttrTransform_BoolToCSS(
                'nowrap',
                'white-space:nowrap;'
            );

                $r['hr@size'] = new HTMLPurifier_AttrTransform_Length('size', 'height');

                        $ul_types = array(
            'disc' => 'list-style-type:disc;',
            'square' => 'list-style-type:square;',
            'circle' => 'list-style-type:circle;'
        );
        $ol_types = array(
            '1' => 'list-style-type:decimal;',
            'i' => 'list-style-type:lower-roman;',
            'I' => 'list-style-type:upper-roman;',
            'a' => 'list-style-type:lower-alpha;',
            'A' => 'list-style-type:upper-alpha;'
        );
        $li_types = $ul_types + $ol_types;
        
        $r['ul@type'] = new HTMLPurifier_AttrTransform_EnumToCSS('type', $ul_types);
        $r['ol@type'] = new HTMLPurifier_AttrTransform_EnumToCSS('type', $ol_types, true);
        $r['li@type'] = new HTMLPurifier_AttrTransform_EnumToCSS('type', $li_types, true);

                $r['img@vspace'] = new HTMLPurifier_AttrTransform_ImgSpace('vspace');

                $r['td@width'] =
        $r['th@width'] =
        $r['hr@width'] = new HTMLPurifier_AttrTransform_Length('width');

        return $r;
    }
}

