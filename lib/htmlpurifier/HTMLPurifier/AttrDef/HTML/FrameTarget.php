<?php


class HTMLPurifier_AttrDef_HTML_FrameTarget extends HTMLPurifier_AttrDef_Enum
{

    
    public $valid_values = false; 
    
    protected $case_sensitive = false;

    public function __construct()
    {
    }

    
    public function validate($string, $config, $context)
    {
        if ($this->valid_values === false) {
            $this->valid_values = $config->get('Attr.AllowedFrameTargets');
        }
        return parent::validate($string, $config, $context);
    }
}

