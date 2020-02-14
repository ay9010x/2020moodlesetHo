<?php


class HTMLPurifier_HTMLDefinition extends HTMLPurifier_Definition
{

    
    
    public $info = array();

    
    public $info_global_attr = array();

    
    public $info_parent = 'div';

    
    public $info_parent_def;

    
    public $info_block_wrapper = 'p';

    
    public $info_tag_transform = array();

    
    public $info_attr_transform_pre = array();

    
    public $info_attr_transform_post = array();

    
    public $info_content_sets = array();

    
    public $info_injector = array();

    
    public $doctype;



    
    
    public function addAttribute($element_name, $attr_name, $def)
    {
        $module = $this->getAnonymousModule();
        if (!isset($module->info[$element_name])) {
            $element = $module->addBlankElement($element_name);
        } else {
            $element = $module->info[$element_name];
        }
        $element->attr[$attr_name] = $def;
    }

    
    public function addElement($element_name, $type, $contents, $attr_collections, $attributes = array())
    {
        $module = $this->getAnonymousModule();
                        $element = $module->addElement($element_name, $type, $contents, $attr_collections, $attributes);
        return $element;
    }

    
    public function addBlankElement($element_name)
    {
        $module  = $this->getAnonymousModule();
        $element = $module->addBlankElement($element_name);
        return $element;
    }

    
    public function getAnonymousModule()
    {
        if (!$this->_anonModule) {
            $this->_anonModule = new HTMLPurifier_HTMLModule();
            $this->_anonModule->name = 'Anonymous';
        }
        return $this->_anonModule;
    }

    private $_anonModule = null;

    
    
    public $type = 'HTML';

    
    public $manager;

    
    public function __construct()
    {
        $this->manager = new HTMLPurifier_HTMLModuleManager();
    }

    
    protected function doSetup($config)
    {
        $this->processModules($config);
        $this->setupConfigStuff($config);
        unset($this->manager);

                foreach ($this->info as $k => $v) {
            unset($this->info[$k]->content_model);
            unset($this->info[$k]->content_model_type);
        }
    }

    
    protected function processModules($config)
    {
        if ($this->_anonModule) {
                                                $this->manager->addModule($this->_anonModule);
            unset($this->_anonModule);
        }

        $this->manager->setup($config);
        $this->doctype = $this->manager->doctype;

        foreach ($this->manager->modules as $module) {
            foreach ($module->info_tag_transform as $k => $v) {
                if ($v === false) {
                    unset($this->info_tag_transform[$k]);
                } else {
                    $this->info_tag_transform[$k] = $v;
                }
            }
            foreach ($module->info_attr_transform_pre as $k => $v) {
                if ($v === false) {
                    unset($this->info_attr_transform_pre[$k]);
                } else {
                    $this->info_attr_transform_pre[$k] = $v;
                }
            }
            foreach ($module->info_attr_transform_post as $k => $v) {
                if ($v === false) {
                    unset($this->info_attr_transform_post[$k]);
                } else {
                    $this->info_attr_transform_post[$k] = $v;
                }
            }
            foreach ($module->info_injector as $k => $v) {
                if ($v === false) {
                    unset($this->info_injector[$k]);
                } else {
                    $this->info_injector[$k] = $v;
                }
            }
        }
        $this->info = $this->manager->getElements();
        $this->info_content_sets = $this->manager->contentSets->lookup;
    }

    
    protected function setupConfigStuff($config)
    {
        $block_wrapper = $config->get('HTML.BlockWrapper');
        if (isset($this->info_content_sets['Block'][$block_wrapper])) {
            $this->info_block_wrapper = $block_wrapper;
        } else {
            trigger_error(
                'Cannot use non-block element as block wrapper',
                E_USER_ERROR
            );
        }

        $parent = $config->get('HTML.Parent');
        $def = $this->manager->getElement($parent, true);
        if ($def) {
            $this->info_parent = $parent;
            $this->info_parent_def = $def;
        } else {
            trigger_error(
                'Cannot use unrecognized element as parent',
                E_USER_ERROR
            );
            $this->info_parent_def = $this->manager->getElement($this->info_parent, true);
        }

                $support = "(for information on implementing this, see the support forums) ";

        
        $allowed_elements = $config->get('HTML.AllowedElements');
        $allowed_attributes = $config->get('HTML.AllowedAttributes'); 
        if (!is_array($allowed_elements) && !is_array($allowed_attributes)) {
            $allowed = $config->get('HTML.Allowed');
            if (is_string($allowed)) {
                list($allowed_elements, $allowed_attributes) = $this->parseTinyMCEAllowedList($allowed);
            }
        }

        if (is_array($allowed_elements)) {
            foreach ($this->info as $name => $d) {
                if (!isset($allowed_elements[$name])) {
                    unset($this->info[$name]);
                }
                unset($allowed_elements[$name]);
            }
                        foreach ($allowed_elements as $element => $d) {
                $element = htmlspecialchars($element);                 trigger_error("Element '$element' is not supported $support", E_USER_WARNING);
            }
        }

        
        $allowed_attributes_mutable = $allowed_attributes;         if (is_array($allowed_attributes)) {
                                                foreach ($this->info_global_attr as $attr => $x) {
                $keys = array($attr, "*@$attr", "*.$attr");
                $delete = true;
                foreach ($keys as $key) {
                    if ($delete && isset($allowed_attributes[$key])) {
                        $delete = false;
                    }
                    if (isset($allowed_attributes_mutable[$key])) {
                        unset($allowed_attributes_mutable[$key]);
                    }
                }
                if ($delete) {
                    unset($this->info_global_attr[$attr]);
                }
            }

            foreach ($this->info as $tag => $info) {
                foreach ($info->attr as $attr => $x) {
                    $keys = array("$tag@$attr", $attr, "*@$attr", "$tag.$attr", "*.$attr");
                    $delete = true;
                    foreach ($keys as $key) {
                        if ($delete && isset($allowed_attributes[$key])) {
                            $delete = false;
                        }
                        if (isset($allowed_attributes_mutable[$key])) {
                            unset($allowed_attributes_mutable[$key]);
                        }
                    }
                    if ($delete) {
                        if ($this->info[$tag]->attr[$attr]->required) {
                            trigger_error(
                                "Required attribute '$attr' in element '$tag' " .
                                "was not allowed, which means '$tag' will not be allowed either",
                                E_USER_WARNING
                            );
                        }
                        unset($this->info[$tag]->attr[$attr]);
                    }
                }
            }
                        foreach ($allowed_attributes_mutable as $elattr => $d) {
                $bits = preg_split('/[.@]/', $elattr, 2);
                $c = count($bits);
                switch ($c) {
                    case 2:
                        if ($bits[0] !== '*') {
                            $element = htmlspecialchars($bits[0]);
                            $attribute = htmlspecialchars($bits[1]);
                            if (!isset($this->info[$element])) {
                                trigger_error(
                                    "Cannot allow attribute '$attribute' if element " .
                                    "'$element' is not allowed/supported $support"
                                );
                            } else {
                                trigger_error(
                                    "Attribute '$attribute' in element '$element' not supported $support",
                                    E_USER_WARNING
                                );
                            }
                            break;
                        }
                                            case 1:
                        $attribute = htmlspecialchars($bits[0]);
                        trigger_error(
                            "Global attribute '$attribute' is not ".
                            "supported in any elements $support",
                            E_USER_WARNING
                        );
                        break;
                }
            }
        }

        
        $forbidden_elements   = $config->get('HTML.ForbiddenElements');
        $forbidden_attributes = $config->get('HTML.ForbiddenAttributes');

        foreach ($this->info as $tag => $info) {
            if (isset($forbidden_elements[$tag])) {
                unset($this->info[$tag]);
                continue;
            }
            foreach ($info->attr as $attr => $x) {
                if (isset($forbidden_attributes["$tag@$attr"]) ||
                    isset($forbidden_attributes["*@$attr"]) ||
                    isset($forbidden_attributes[$attr])
                ) {
                    unset($this->info[$tag]->attr[$attr]);
                    continue;
                } elseif (isset($forbidden_attributes["$tag.$attr"])) {                                         trigger_error(
                        "Error with $tag.$attr: tag.attr syntax not supported for " .
                        "HTML.ForbiddenAttributes; use tag@attr instead",
                        E_USER_WARNING
                    );
                }
            }
        }
        foreach ($forbidden_attributes as $key => $v) {
            if (strlen($key) < 2) {
                continue;
            }
            if ($key[0] != '*') {
                continue;
            }
            if ($key[1] == '.') {
                trigger_error(
                    "Error with $key: *.attr syntax not supported for HTML.ForbiddenAttributes; use attr instead",
                    E_USER_WARNING
                );
            }
        }

                foreach ($this->info_injector as $i => $injector) {
            if ($injector->checkNeeded($config) !== false) {
                                                unset($this->info_injector[$i]);
            }
        }
    }

    
    public function parseTinyMCEAllowedList($list)
    {
        $list = str_replace(array(' ', "\t"), '', $list);

        $elements = array();
        $attributes = array();

        $chunks = preg_split('/(,|[\n\r]+)/', $list);
        foreach ($chunks as $chunk) {
            if (empty($chunk)) {
                continue;
            }
                        if (!strpos($chunk, '[')) {
                $element = $chunk;
                $attr = false;
            } else {
                list($element, $attr) = explode('[', $chunk);
            }
            if ($element !== '*') {
                $elements[$element] = true;
            }
            if (!$attr) {
                continue;
            }
            $attr = substr($attr, 0, strlen($attr) - 1);             $attr = explode('|', $attr);
            foreach ($attr as $key) {
                $attributes["$element.$key"] = true;
            }
        }
        return array($elements, $attributes);
    }
}

