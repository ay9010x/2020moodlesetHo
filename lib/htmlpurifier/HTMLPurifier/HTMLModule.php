<?php



class HTMLPurifier_HTMLModule
{

    
    
    public $name;

    
    public $elements = array();

    
    public $info = array();

    
    public $content_sets = array();

    
    public $attr_collections = array();

    
    public $info_tag_transform = array();

    
    public $info_attr_transform_pre = array();

    
    public $info_attr_transform_post = array();

    
    public $info_injector = array();

    
    public $defines_child_def = false;

    
    public $safe = true;

    
    public function getChildDef($def)
    {
        return false;
    }

    
    
    public function addElement($element, $type, $contents, $attr_includes = array(), $attr = array())
    {
        $this->elements[] = $element;
                list($content_model_type, $content_model) = $this->parseContents($contents);
                $this->mergeInAttrIncludes($attr, $attr_includes);
                if ($type) {
            $this->addElementToContentSet($element, $type);
        }
                $this->info[$element] = HTMLPurifier_ElementDef::create(
            $content_model,
            $content_model_type,
            $attr
        );
                if (!is_string($contents)) {
            $this->info[$element]->child = $contents;
        }
        return $this->info[$element];
    }

    
    public function addBlankElement($element)
    {
        if (!isset($this->info[$element])) {
            $this->elements[] = $element;
            $this->info[$element] = new HTMLPurifier_ElementDef();
            $this->info[$element]->standalone = false;
        } else {
            trigger_error("Definition for $element already exists in module, cannot redefine");
        }
        return $this->info[$element];
    }

    
    public function addElementToContentSet($element, $type)
    {
        if (!isset($this->content_sets[$type])) {
            $this->content_sets[$type] = '';
        } else {
            $this->content_sets[$type] .= ' | ';
        }
        $this->content_sets[$type] .= $element;
    }

    
    public function parseContents($contents)
    {
        if (!is_string($contents)) {
            return array(null, null);
        }         switch ($contents) {
                        case 'Empty':
                return array('empty', '');
            case 'Inline':
                return array('optional', 'Inline | #PCDATA');
            case 'Flow':
                return array('optional', 'Flow | #PCDATA');
        }
        list($content_model_type, $content_model) = explode(':', $contents);
        $content_model_type = strtolower(trim($content_model_type));
        $content_model = trim($content_model);
        return array($content_model_type, $content_model);
    }

    
    public function mergeInAttrIncludes(&$attr, $attr_includes)
    {
        if (!is_array($attr_includes)) {
            if (empty($attr_includes)) {
                $attr_includes = array();
            } else {
                $attr_includes = array($attr_includes);
            }
        }
        $attr[0] = $attr_includes;
    }

    
    public function makeLookup($list)
    {
        if (is_string($list)) {
            $list = func_get_args();
        }
        $ret = array();
        foreach ($list as $value) {
            if (is_null($value)) {
                continue;
            }
            $ret[$value] = true;
        }
        return $ret;
    }

    
    public function setup($config)
    {
    }
}

