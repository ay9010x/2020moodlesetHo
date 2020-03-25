<?php



defined('MOODLE_INTERNAL') || die();


define('RENDERER_TARGET_GENERAL', 'general');


define('RENDERER_TARGET_MAINTENANCE', 'maintenance');


define('RENDERER_TARGET_CLI', 'cli');


define('RENDERER_TARGET_AJAX', 'ajax');


define('RENDERER_TARGET_TEXTEMAIL', 'textemail');


define('RENDERER_TARGET_HTMLEMAIL', 'htmlemail');




interface renderer_factory {

    
    public function get_renderer(moodle_page $page, $component, $subtype=null, $target=null);
}



abstract class renderer_factory_base implements renderer_factory {
    
    protected $theme;

    
    public function __construct(theme_config $theme) {
        $this->theme = $theme;
    }

    
    protected function get_target_suffix($target) {
        if (empty($target) || $target === RENDERER_TARGET_MAINTENANCE) {
                                                if (defined('PREFERRED_RENDERER_TARGET')) {
                $target = PREFERRED_RENDERER_TARGET;
            } else if (CLI_SCRIPT) {
                $target = RENDERER_TARGET_CLI;
            } else if (AJAX_SCRIPT) {
                $target = RENDERER_TARGET_AJAX;
            }
        }

        switch ($target) {
            case RENDERER_TARGET_CLI: $suffix = '_cli'; break;
            case RENDERER_TARGET_AJAX: $suffix = '_ajax'; break;
            case RENDERER_TARGET_TEXTEMAIL: $suffix = '_textemail'; break;
            case RENDERER_TARGET_HTMLEMAIL: $suffix = '_htmlemail'; break;
            case RENDERER_TARGET_MAINTENANCE: $suffix = '_maintenance'; break;
            default: $target = RENDERER_TARGET_GENERAL; $suffix = '';
        }

        return array($target, $suffix);
    }

    
    protected function standard_renderer_classnames($component, $subtype = null) {
        global $CFG;         $classnames = array();

                list($plugin, $type) = core_component::normalize_component($component);
        if ($type === null) {
            $component = $plugin;
        } else {
            $component = $plugin.'_'.$type;
        }

        if ($component !== 'core') {
                        if (!$compdirectory = core_component::get_component_directory($component)) {
                throw new coding_exception('Invalid component specified in renderer request', $component);
            }
            $rendererfile = $compdirectory . '/renderer.php';
            if (file_exists($rendererfile)) {
                include_once($rendererfile);
            }

        } else if (!empty($subtype)) {
            $coresubsystems = core_component::get_core_subsystems();
            if (!array_key_exists($subtype, $coresubsystems)) {                 throw new coding_exception('Invalid core subtype "' . $subtype . '" in renderer request', $subtype);
            }
            if ($coresubsystems[$subtype]) {
                $rendererfile = $coresubsystems[$subtype] . '/renderer.php';
                if (file_exists($rendererfile)) {
                    include_once($rendererfile);
                }
            }
        }

        if (empty($subtype)) {
                        $classnames[] = array(
                'validwithprefix' => true,
                'validwithoutprefix' => false,
                'autoloaded' => true,
                'classname' => '\\output\\' . $component . '_renderer'
            );

                        $classnames[] = array(
                'validwithprefix' => false,
                'validwithoutprefix' => true,
                'autoloaded' => true,
                'classname' => '\\' . $component . '\\output\\renderer'
            );
                        $classnames[] = array(
                'validwithprefix' => true,
                'validwithoutprefix' => true,
                'autoloaded' => false,
                'classname' => $component . '_renderer'
            );
        } else {
                        $classnames[] = array(
                'validwithprefix' => true,
                'validwithoutprefix' => false,
                'autoloaded' => true,
                'classname' => '\\output\\' . $component . '\\' . $subtype . '_renderer'
            );
                        $classnames[] = array(
                'validwithprefix' => true,
                'validwithoutprefix' => false,
                'autoloaded' => true,
                'classname' => '\\output\\' . $component . '\\' . $subtype . '\\renderer'
            );
                        $classnames[] = array(
                'validwithprefix' => false,
                'validwithoutprefix' => true,
                'autoloaded' => true,
                'classname' => '\\' . $component . '\\output\\' . $subtype . '_renderer'
            );
                        $classnames[] = array(
                'validwithprefix' => false,
                'validwithoutprefix' => true,
                'autoloaded' => true,
                'classname' => '\\' . $component . '\\output\\' . $subtype . '\\renderer'
            );
                        $classnames[] = array(
                'validwithprefix' => true,
                'validwithoutprefix' => true,
                'autoloaded' => false,
                'classname' => $component . '_' . $subtype . '_renderer'
            );
        }
        return $classnames;
    }
}


class standard_renderer_factory extends renderer_factory_base {

    
    public function get_renderer(moodle_page $page, $component, $subtype = null, $target = null) {
        $classnames = $this->standard_renderer_classnames($component, $subtype);
        $classname = '';

        list($target, $suffix) = $this->get_target_suffix($target);
                foreach ($classnames as $classnamedetails) {
            if ($classnamedetails['validwithoutprefix']) {
                $newclassname = $classnamedetails['classname'] . $suffix;
                if (class_exists($newclassname)) {
                    $classname = $newclassname;
                    break;
                } else {
                    $newclassname = $classnamedetails['classname'];
                    if (class_exists($newclassname)) {
                        $classname = $newclassname;
                        break;
                    }
                }
            }
        }
                if (empty($classname)) {
            foreach ($classnames as $classnamedetails) {
                if ($classnamedetails['validwithoutprefix']) {
                    $newclassname = $classnamedetails['classname'];
                    if (class_exists($newclassname)) {
                        $classname = $newclassname;
                        break;
                    }
                }
            }
        }

        if (empty($classname)) {
                        throw new coding_exception('Request for an unknown renderer class. Searched for: ' . var_export($classnames, true));
        }

        return new $classname($page, $target);
    }
}



class theme_overridden_renderer_factory extends renderer_factory_base {

    
    protected $prefixes = array();

    
    public function __construct(theme_config $theme) {
        parent::__construct($theme);
                $this->prefixes = $theme->renderer_prefixes();
    }

    
    public function get_renderer(moodle_page $page, $component, $subtype = null, $target = null) {
        $classnames = $this->standard_renderer_classnames($component, $subtype);

        list($target, $suffix) = $this->get_target_suffix($target);

                
                foreach ($this->prefixes as $prefix) {
            foreach ($classnames as $classnamedetails) {
                if ($classnamedetails['validwithprefix']) {
                    if ($classnamedetails['autoloaded']) {
                        $newclassname = $prefix . $classnamedetails['classname'] . $suffix;
                    } else {
                        $newclassname = $prefix . '_' . $classnamedetails['classname'] . $suffix;
                    }
                    if (class_exists($newclassname)) {
                        return new $newclassname($page, $target);
                    }
                }
            }
        }
        foreach ($classnames as $classnamedetails) {
            if ($classnamedetails['validwithoutprefix']) {
                $newclassname = $classnamedetails['classname'] . $suffix;
                if (class_exists($newclassname)) {
                                                            return new $newclassname($page, $target);
                }
            }
        }

                foreach ($this->prefixes as $prefix) {
            foreach ($classnames as $classnamedetails) {
                if ($classnamedetails['validwithprefix']) {
                    if ($classnamedetails['autoloaded']) {
                        $newclassname = $prefix . $classnamedetails['classname'];
                    } else {
                        $newclassname = $prefix . '_' . $classnamedetails['classname'];
                    }
                    if (class_exists($newclassname)) {
                        return new $newclassname($page, $target);
                    }
                }
            }
        }

                foreach ($classnames as $classnamedetails) {
            if ($classnamedetails['validwithoutprefix']) {
                $newclassname = $classnamedetails['classname'];
                if (class_exists($newclassname)) {
                    return new $newclassname($page, $target);
                }
            }
        }
        throw new coding_exception('Request for an unknown renderer ' . $component . ', ' . $subtype . ', ' . $target);
    }
}
