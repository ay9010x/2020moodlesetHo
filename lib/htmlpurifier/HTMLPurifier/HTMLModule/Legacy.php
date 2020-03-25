<?php



class HTMLPurifier_HTMLModule_Legacy extends HTMLPurifier_HTMLModule
{
    
    public $name = 'Legacy';

    
    public function setup($config)
    {
        $this->addElement(
            'basefont',
            'Inline',
            'Empty',
            null,
            array(
                'color' => 'Color',
                'face' => 'Text',                 'size' => 'Text',                 'id' => 'ID'
            )
        );
        $this->addElement('center', 'Block', 'Flow', 'Common');
        $this->addElement(
            'dir',
            'Block',
            'Required: li',
            'Common',
            array(
                'compact' => 'Bool#compact'
            )
        );
        $this->addElement(
            'font',
            'Inline',
            'Inline',
            array('Core', 'I18N'),
            array(
                'color' => 'Color',
                'face' => 'Text',                 'size' => 'Text',             )
        );
        $this->addElement(
            'menu',
            'Block',
            'Required: li',
            'Common',
            array(
                'compact' => 'Bool#compact'
            )
        );

        $s = $this->addElement('s', 'Inline', 'Inline', 'Common');
        $s->formatting = true;

        $strike = $this->addElement('strike', 'Inline', 'Inline', 'Common');
        $strike->formatting = true;

        $u = $this->addElement('u', 'Inline', 'Inline', 'Common');
        $u->formatting = true;

        
        $align = 'Enum#left,right,center,justify';

        $address = $this->addBlankElement('address');
        $address->content_model = 'Inline | #PCDATA | p';
        $address->content_model_type = 'optional';
        $address->child = false;

        $blockquote = $this->addBlankElement('blockquote');
        $blockquote->content_model = 'Flow | #PCDATA';
        $blockquote->content_model_type = 'optional';
        $blockquote->child = false;

        $br = $this->addBlankElement('br');
        $br->attr['clear'] = 'Enum#left,all,right,none';

        $caption = $this->addBlankElement('caption');
        $caption->attr['align'] = 'Enum#top,bottom,left,right';

        $div = $this->addBlankElement('div');
        $div->attr['align'] = $align;

        $dl = $this->addBlankElement('dl');
        $dl->attr['compact'] = 'Bool#compact';

        for ($i = 1; $i <= 6; $i++) {
            $h = $this->addBlankElement("h$i");
            $h->attr['align'] = $align;
        }

        $hr = $this->addBlankElement('hr');
        $hr->attr['align'] = $align;
        $hr->attr['noshade'] = 'Bool#noshade';
        $hr->attr['size'] = 'Pixels';
        $hr->attr['width'] = 'Length';

        $img = $this->addBlankElement('img');
        $img->attr['align'] = 'IAlign';
        $img->attr['border'] = 'Pixels';
        $img->attr['hspace'] = 'Pixels';
        $img->attr['vspace'] = 'Pixels';

        
        $li = $this->addBlankElement('li');
        $li->attr['value'] = new HTMLPurifier_AttrDef_Integer();
        $li->attr['type'] = 'Enum#s:1,i,I,a,A,disc,square,circle';

        $ol = $this->addBlankElement('ol');
        $ol->attr['compact'] = 'Bool#compact';
        $ol->attr['start'] = new HTMLPurifier_AttrDef_Integer();
        $ol->attr['type'] = 'Enum#s:1,i,I,a,A';

        $p = $this->addBlankElement('p');
        $p->attr['align'] = $align;

        $pre = $this->addBlankElement('pre');
        $pre->attr['width'] = 'Number';

        
        $table = $this->addBlankElement('table');
        $table->attr['align'] = 'Enum#left,center,right';
        $table->attr['bgcolor'] = 'Color';

        $tr = $this->addBlankElement('tr');
        $tr->attr['bgcolor'] = 'Color';

        $th = $this->addBlankElement('th');
        $th->attr['bgcolor'] = 'Color';
        $th->attr['height'] = 'Length';
        $th->attr['nowrap'] = 'Bool#nowrap';
        $th->attr['width'] = 'Length';

        $td = $this->addBlankElement('td');
        $td->attr['bgcolor'] = 'Color';
        $td->attr['height'] = 'Length';
        $td->attr['nowrap'] = 'Bool#nowrap';
        $td->attr['width'] = 'Length';

        $ul = $this->addBlankElement('ul');
        $ul->attr['compact'] = 'Bool#compact';
        $ul->attr['type'] = 'Enum#square,disc,circle';

                                
        $form = $this->addBlankElement('form');
        $form->content_model = 'Flow | #PCDATA';
        $form->content_model_type = 'optional';
        $form->attr['target'] = 'FrameTarget';

        $input = $this->addBlankElement('input');
        $input->attr['align'] = 'IAlign';

        $legend = $this->addBlankElement('legend');
        $legend->attr['align'] = 'LAlign';
    }
}

