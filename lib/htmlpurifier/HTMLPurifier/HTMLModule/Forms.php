<?php


class HTMLPurifier_HTMLModule_Forms extends HTMLPurifier_HTMLModule
{
    
    public $name = 'Forms';

    
    public $safe = false;

    
    public $content_sets = array(
        'Block' => 'Form',
        'Inline' => 'Formctrl',
    );

    
    public function setup($config)
    {
        $form = $this->addElement(
            'form',
            'Form',
            'Required: Heading | List | Block | fieldset',
            'Common',
            array(
                'accept' => 'ContentTypes',
                'accept-charset' => 'Charsets',
                'action*' => 'URI',
                'method' => 'Enum#get,post',
                                'enctype' => 'Enum#application/x-www-form-urlencoded,multipart/form-data',
            )
        );
        $form->excludes = array('form' => true);

        $input = $this->addElement(
            'input',
            'Formctrl',
            'Empty',
            'Common',
            array(
                'accept' => 'ContentTypes',
                'accesskey' => 'Character',
                'alt' => 'Text',
                'checked' => 'Bool#checked',
                'disabled' => 'Bool#disabled',
                'maxlength' => 'Number',
                'name' => 'CDATA',
                'readonly' => 'Bool#readonly',
                'size' => 'Number',
                'src' => 'URI#embedded',
                'tabindex' => 'Number',
                'type' => 'Enum#text,password,checkbox,button,radio,submit,reset,file,hidden,image',
                'value' => 'CDATA',
            )
        );
        $input->attr_transform_post[] = new HTMLPurifier_AttrTransform_Input();

        $this->addElement(
            'select',
            'Formctrl',
            'Required: optgroup | option',
            'Common',
            array(
                'disabled' => 'Bool#disabled',
                'multiple' => 'Bool#multiple',
                'name' => 'CDATA',
                'size' => 'Number',
                'tabindex' => 'Number',
            )
        );

        $this->addElement(
            'option',
            false,
            'Optional: #PCDATA',
            'Common',
            array(
                'disabled' => 'Bool#disabled',
                'label' => 'Text',
                'selected' => 'Bool#selected',
                'value' => 'CDATA',
            )
        );
                        
        $textarea = $this->addElement(
            'textarea',
            'Formctrl',
            'Optional: #PCDATA',
            'Common',
            array(
                'accesskey' => 'Character',
                'cols*' => 'Number',
                'disabled' => 'Bool#disabled',
                'name' => 'CDATA',
                'readonly' => 'Bool#readonly',
                'rows*' => 'Number',
                'tabindex' => 'Number',
            )
        );
        $textarea->attr_transform_pre[] = new HTMLPurifier_AttrTransform_Textarea();

        $button = $this->addElement(
            'button',
            'Formctrl',
            'Optional: #PCDATA | Heading | List | Block | Inline',
            'Common',
            array(
                'accesskey' => 'Character',
                'disabled' => 'Bool#disabled',
                'name' => 'CDATA',
                'tabindex' => 'Number',
                'type' => 'Enum#button,submit,reset',
                'value' => 'CDATA',
            )
        );

                $button->excludes = $this->makeLookup(
            'form',
            'fieldset',             'input',
            'select',
            'textarea',
            'label',
            'button',             'a',             'isindex',
            'iframe'         );

                        
                $this->addElement('fieldset', 'Form', 'Custom: (#WS?,legend,(Flow|#PCDATA)*)', 'Common');

        $label = $this->addElement(
            'label',
            'Formctrl',
            'Optional: #PCDATA | Inline',
            'Common',
            array(
                'accesskey' => 'Character',
                            )
        );
        $label->excludes = array('label' => true);

        $this->addElement(
            'legend',
            false,
            'Optional: #PCDATA | Inline',
            'Common',
            array(
                'accesskey' => 'Character',
            )
        );

        $this->addElement(
            'optgroup',
            false,
            'Required: option',
            'Common',
            array(
                'disabled' => 'Bool#disabled',
                'label*' => 'Text',
            )
        );
                    }
}

