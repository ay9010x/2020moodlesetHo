<?php




class HTMLPurifier_HTMLModule_Scripting extends HTMLPurifier_HTMLModule
{
    
    public $name = 'Scripting';

    
    public $elements = array('script', 'noscript');

    
    public $content_sets = array('Block' => 'script | noscript', 'Inline' => 'script | noscript');

    
    public $safe = false;

    
    public function setup($config)
    {
                                        
                
                        $this->info['noscript'] = new HTMLPurifier_ElementDef();
        $this->info['noscript']->attr = array(0 => array('Common'));
        $this->info['noscript']->content_model = 'Heading | List | Block';
        $this->info['noscript']->content_model_type = 'required';

        $this->info['script'] = new HTMLPurifier_ElementDef();
        $this->info['script']->attr = array(
            'defer' => new HTMLPurifier_AttrDef_Enum(array('defer')),
            'src' => new HTMLPurifier_AttrDef_URI(true),
            'type' => new HTMLPurifier_AttrDef_Enum(array('text/javascript'))
        );
        $this->info['script']->content_model = '#PCDATA';
        $this->info['script']->content_model_type = 'optional';
        $this->info['script']->attr_transform_pre[] =
        $this->info['script']->attr_transform_post[] =
            new HTMLPurifier_AttrTransform_ScriptRequired();
    }
}

