<?php



defined('MOODLE_INTERNAL') || die();



class core_string_manager_install implements core_string_manager {
    
    protected $installroot;

    
    public function __construct() {
        global $CFG;
        $this->installroot = "$CFG->dirroot/install/lang";
    }

    
    public function load_component_strings($component, $lang, $disablecache = false, $disablelocal = false) {
                return array();
    }

    
    public function string_exists($identifier, $component) {
                $str = get_string($identifier, $component);
        return (strpos($str, '[[') === false);
    }

    
    public function string_deprecated($identifier, $component) {
        return false;
    }

    
    public function get_string($identifier, $component = '', $a = null, $lang = null) {
        if (!$component) {
            $component = 'moodle';
        }

        if ($lang === null) {
            $lang = current_language();
        }

                $parent = '';
        if ($lang !== 'en' and $identifier !== 'parentlanguage' and $component !== 'langconfig') {
            if (file_exists("$this->installroot/$lang/langconfig.php")) {
                $string = array();
                include("$this->installroot/$lang/langconfig.php");
                if (isset($string['parentlanguage'])) {
                    $parent = $string['parentlanguage'];
                }
            }
        }

                if (!file_exists("$this->installroot/en/$component.php")) {
            return "[[$identifier]]";
        }
        $string = array();
        include("$this->installroot/en/$component.php");

                if ($parent and $parent !== 'en' and file_exists("$this->installroot/$parent/$component.php")) {
            include("$this->installroot/$parent/$component.php");
        }

                if ($lang !== 'en' and file_exists("$this->installroot/$lang/$component.php")) {
            include("$this->installroot/$lang/$component.php");
        }

        if (!isset($string[$identifier])) {
            return "[[$identifier]]";
        }

        $string = $string[$identifier];

        if ($a !== null) {
            if (is_object($a) or is_array($a)) {
                $a = (array)$a;
                $search = array();
                $replace = array();
                foreach ($a as $key => $value) {
                    if (is_int($key)) {
                                                continue;
                    }
                    $search[] = '{$a->' . $key . '}';
                    $replace[] = (string)$value;
                }
                if ($search) {
                    $string = str_replace($search, $replace, $string);
                }
            } else {
                $string = str_replace('{$a}', (string)$a, $string);
            }
        }

        return $string;
    }

    
    public function get_list_of_countries($returnall = false, $lang = null) {
                return array();
    }

    
    public function get_list_of_languages($lang = null, $standard = 'iso6392') {
                return array();
    }

    
    public function translation_exists($lang, $includeall = true) {
        return file_exists($this->installroot . '/' . $lang . '/langconfig.php');
    }

    
    public function get_list_of_translations($returnall = false) {
                $languages = array();
                $langdirs = get_list_of_plugins('install/lang');
        asort($langdirs);
                foreach ($langdirs as $lang) {
            if (file_exists($this->installroot . '/' . $lang . '/langconfig.php')) {
                $string = array();
                include($this->installroot . '/' . $lang . '/langconfig.php');
                if (!empty($string['thislanguage'])) {
                    $languages[$lang] = $string['thislanguage'] . ' (' . $lang . ')';
                }
            }
        }
                return $languages;
    }

    
    public function get_list_of_currencies($lang = null) {
                return array();
    }

    
    public function reset_caches($phpunitreset = false) {
            }

    
    public function get_revision() {
        return -1;
    }
}
