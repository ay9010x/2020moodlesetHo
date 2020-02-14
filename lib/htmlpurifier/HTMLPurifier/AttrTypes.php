<?php


class HTMLPurifier_AttrTypes
{
    
    protected $info = array();

    
    public function __construct()
    {
                                                
                $this->info['Enum']    = new HTMLPurifier_AttrDef_Enum();
        $this->info['Bool']    = new HTMLPurifier_AttrDef_HTML_Bool();

        $this->info['CDATA']    = new HTMLPurifier_AttrDef_Text();
        $this->info['ID']       = new HTMLPurifier_AttrDef_HTML_ID();
        $this->info['Length']   = new HTMLPurifier_AttrDef_HTML_Length();
        $this->info['MultiLength'] = new HTMLPurifier_AttrDef_HTML_MultiLength();
        $this->info['NMTOKENS'] = new HTMLPurifier_AttrDef_HTML_Nmtokens();
        $this->info['Pixels']   = new HTMLPurifier_AttrDef_HTML_Pixels();
        $this->info['Text']     = new HTMLPurifier_AttrDef_Text();
        $this->info['URI']      = new HTMLPurifier_AttrDef_URI();
        $this->info['LanguageCode'] = new HTMLPurifier_AttrDef_Lang();
        $this->info['Color']    = new HTMLPurifier_AttrDef_HTML_Color();
        $this->info['IAlign']   = self::makeEnum('top,middle,bottom,left,right');
        $this->info['LAlign']   = self::makeEnum('top,bottom,left,right');
        $this->info['FrameTarget'] = new HTMLPurifier_AttrDef_HTML_FrameTarget();

                $this->info['ContentType'] = new HTMLPurifier_AttrDef_Text();
        $this->info['ContentTypes'] = new HTMLPurifier_AttrDef_Text();
        $this->info['Charsets'] = new HTMLPurifier_AttrDef_Text();
        $this->info['Character'] = new HTMLPurifier_AttrDef_Text();

                $this->info['Class'] = new HTMLPurifier_AttrDef_HTML_Class();

                        $this->info['Number']   = new HTMLPurifier_AttrDef_Integer(false, false, true);
    }

    private static function makeEnum($in)
    {
        return new HTMLPurifier_AttrDef_Clone(new HTMLPurifier_AttrDef_Enum(explode(',', $in)));
    }

    
    public function get($type)
    {
                if (strpos($type, '#') !== false) {
            list($type, $string) = explode('#', $type, 2);
        } else {
            $string = '';
        }

        if (!isset($this->info[$type])) {
            trigger_error('Cannot retrieve undefined attribute type ' . $type, E_USER_ERROR);
            return;
        }
        return $this->info[$type]->make($string);
    }

    
    public function set($type, $impl)
    {
        $this->info[$type] = $impl;
    }
}

