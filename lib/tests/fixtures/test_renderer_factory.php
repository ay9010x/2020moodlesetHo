<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/outputfactories.php');


class test_output_factory extends renderer_factory_base {

    
    public function __construct() {
        $this->prefixes = array('theme_child', 'theme_parent');
    }

    
    public function get_renderer(moodle_page $page, $component, $subtype = null, $target = null) {
        throw new coding_exception('Do not call this function, this class is for testing only.');
    }

    
    public function get_standard_renderer_factory_search_paths($component, $subtype = null, $target = null) {
        $classnames = $this->standard_renderer_classnames($component, $subtype);
        $searchtargets = array();

        list($target, $suffix) = $this->get_target_suffix($target);
                foreach ($classnames as $classnamedetails) {
            if ($classnamedetails['validwithoutprefix']) {
                $newclassname = $classnamedetails['classname'] . $suffix;
                $searchtargets[] = $newclassname;
            }
        }
                foreach ($classnames as $classnamedetails) {
            if ($classnamedetails['validwithoutprefix']) {
                $newclassname = $classnamedetails['classname'];
                $searchtargets[] = $newclassname;
            }
        }

        return $searchtargets;
    }

    
    public function get_theme_overridden_renderer_factory_search_paths($component, $subtype = null, $target = null) {
        $searchtargets = array();
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
                    $searchtargets[] = $newclassname;
                }
            }
        }
        foreach ($classnames as $classnamedetails) {
            if ($classnamedetails['validwithoutprefix']) {
                $newclassname = $classnamedetails['classname'] . $suffix;
                $searchtargets[] = $newclassname;
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
                    $searchtargets[] = $newclassname;
                }
            }
        }

                foreach ($classnames as $classnamedetails) {
            if ($classnamedetails['validwithoutprefix']) {
                $newclassname = $classnamedetails['classname'];
                $searchtargets[] = $newclassname;
            }
        }
        return $searchtargets;
    }
}
