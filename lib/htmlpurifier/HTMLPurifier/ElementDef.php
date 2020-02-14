<?php


class HTMLPurifier_ElementDef
{
    
    public $standalone = true;

    
    public $attr = array();

                                            
    
    public $attr_transform_pre = array();

    
    public $attr_transform_post = array();

    
    public $child;

    
    public $content_model;

    
    public $content_model_type;

    
    public $descendants_are_inline = false;

    
    public $required_attr = array();

    
    public $excludes = array();

    
    public $autoclose = array();

    
    public $wrap;

    
    public $formatting;

    
    public static function create($content_model, $content_model_type, $attr)
    {
        $def = new HTMLPurifier_ElementDef();
        $def->content_model = $content_model;
        $def->content_model_type = $content_model_type;
        $def->attr = $attr;
        return $def;
    }

    
    public function mergeIn($def)
    {
                foreach ($def->attr as $k => $v) {
            if ($k === 0) {
                                                foreach ($v as $v2) {
                    $this->attr[0][] = $v2;
                }
                continue;
            }
            if ($v === false) {
                if (isset($this->attr[$k])) {
                    unset($this->attr[$k]);
                }
                continue;
            }
            $this->attr[$k] = $v;
        }
        $this->_mergeAssocArray($this->excludes, $def->excludes);
        $this->attr_transform_pre = array_merge($this->attr_transform_pre, $def->attr_transform_pre);
        $this->attr_transform_post = array_merge($this->attr_transform_post, $def->attr_transform_post);

        if (!empty($def->content_model)) {
            $this->content_model =
                str_replace("#SUPER", $this->content_model, $def->content_model);
            $this->child = false;
        }
        if (!empty($def->content_model_type)) {
            $this->content_model_type = $def->content_model_type;
            $this->child = false;
        }
        if (!is_null($def->child)) {
            $this->child = $def->child;
        }
        if (!is_null($def->formatting)) {
            $this->formatting = $def->formatting;
        }
        if ($def->descendants_are_inline) {
            $this->descendants_are_inline = $def->descendants_are_inline;
        }
    }

    
    private function _mergeAssocArray(&$a1, $a2)
    {
        foreach ($a2 as $k => $v) {
            if ($v === false) {
                if (isset($a1[$k])) {
                    unset($a1[$k]);
                }
                continue;
            }
            $a1[$k] = $v;
        }
    }
}

