<?php



defined('MOODLE_INTERNAL') || die();



class HTMLPurifier_URIScheme_rtsp extends HTMLPurifier_URIScheme {

    public $browsable = true;
    public $hierarchical = true;

    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        return true;
    }

}



class HTMLPurifier_URIScheme_rtmp extends HTMLPurifier_URIScheme {

    public $browsable = false;
    public $hierarchical = true;

    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        return true;
    }

}



class HTMLPurifier_URIScheme_irc extends HTMLPurifier_URIScheme {

    public $browsable = true;
    public $hierarchical = true;

    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        return true;
    }

}



class HTMLPurifier_URIScheme_mms extends HTMLPurifier_URIScheme {

    public $browsable = true;
    public $hierarchical = true;

    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        return true;
    }

}



class HTMLPurifier_URIScheme_gopher extends HTMLPurifier_URIScheme {

    public $browsable = true;
    public $hierarchical = true;

    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        return true;
    }

}



class HTMLPurifier_URIScheme_teamspeak extends HTMLPurifier_URIScheme {

    public $browsable = true;
    public $hierarchical = true;

    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        return true;
    }

}


class HTMLPurifier_AttrTransform_Noreferrer extends HTMLPurifier_AttrTransform {
    
    private $parser;

    
    public function __construct() {
        $this->parser = new HTMLPurifier_URIParser();
    }

    
    public function transform($attr, $config, $context) {
                if (!empty($attr['rel']) && substr($attr['rel'], 'noreferrer') !== false) {
            return $attr;
        }

                if (!empty($attr['target']) && $attr['target'] == '_blank') {
            $attr['rel'] = !empty($attr['rel']) ? $attr['rel'] . ' noreferrer' : 'noreferrer';
        }

        return $attr;
    }
}


class HTMLPurifier_HTMLModule_Noreferrer extends HTMLPurifier_HTMLModule {
    
    public $name = 'Noreferrer';

    
    public function setup($config) {
        $a = $this->addBlankElement('a');
        $a->attr_transform_post[] = new HTMLPurifier_AttrTransform_Noreferrer();
    }
}
