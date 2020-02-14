<?php


class HTMLPurifier_AttrTransform_SafeParam extends HTMLPurifier_AttrTransform
{
    
    public $name = "SafeParam";

    
    private $uri;

    public function __construct()
    {
        $this->uri = new HTMLPurifier_AttrDef_URI(true);         $this->wmode = new HTMLPurifier_AttrDef_Enum(array('window', 'opaque', 'transparent'));
    }

    
    public function transform($attr, $config, $context)
    {
                        switch ($attr['name']) {
                                    case 'allowScriptAccess':
                $attr['value'] = 'never';
                break;
            case 'allowNetworking':
                $attr['value'] = 'internal';
                break;
            case 'allowFullScreen':
                if ($config->get('HTML.FlashAllowFullScreen')) {
                    $attr['value'] = ($attr['value'] == 'true') ? 'true' : 'false';
                } else {
                    $attr['value'] = 'false';
                }
                break;
            case 'wmode':
                $attr['value'] = $this->wmode->validate($attr['value'], $config, $context);
                break;
            case 'movie':
            case 'src':
                $attr['name'] = "movie";
                $attr['value'] = $this->uri->validate($attr['value'], $config, $context);
                break;
            case 'flashvars':
                                                break;
                        default:
                $attr['name'] = $attr['value'] = null;
        }
        return $attr;
    }
}

