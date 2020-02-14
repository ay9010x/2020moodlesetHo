<?php



defined('MOODLE_INTERNAL') || die();


class core_requirejs {

    
    public static function find_one_amd_module($component, $jsfilename, $debug = false) {
        $jsfileroot = core_component::get_component_directory($component);
        if (!$jsfileroot) {
            return array();
        }

        $module = str_replace('.js', '', $jsfilename);

        $srcdir = $jsfileroot . '/amd/build';
        $minpart = '.min';
        if ($debug) {
            $srcdir = $jsfileroot . '/amd/src';
            $minpart = '';
        }

        $filename = $srcdir . '/' . $module . $minpart . '.js';
        if (!file_exists($filename)) {
            return array();
        }

        $fullmodulename = $component . '/' . $module;
        return array($fullmodulename => $filename);
    }

    
    public static function find_all_amd_modules($debug = false) {
        global $CFG;

        $jsdirs = array();
        $jsfiles = array();

        $dir = $CFG->libdir . '/amd';
        if (!empty($dir) && is_dir($dir)) {
            $jsdirs['core'] = $dir;
        }
        $subsystems = core_component::get_core_subsystems();
        foreach ($subsystems as $subsystem => $dir) {
            if (!empty($dir) && is_dir($dir . '/amd')) {
                $jsdirs['core_' . $subsystem] = $dir . '/amd';
            }
        }
        $plugintypes = core_component::get_plugin_types();
        foreach ($plugintypes as $type => $dir) {
            $plugins = core_component::get_plugin_list_with_file($type, 'amd', false);
            foreach ($plugins as $plugin => $dir) {
                if (!empty($dir) && is_dir($dir)) {
                    $jsdirs[$type . '_' . $plugin] = $dir;
                }
            }
        }

        foreach ($jsdirs as $component => $dir) {
            $srcdir = $dir . '/build';
            if ($debug) {
                $srcdir = $dir . '/src';
            }
            if (!is_dir($srcdir) || !is_readable($srcdir)) {
                                                continue;
            }
            $items = new RecursiveDirectoryIterator($srcdir);
            foreach ($items as $item) {
                $extension = $item->getExtension();
                if ($extension === 'js') {
                    $filename = str_replace('.min', '', $item->getBaseName('.js'));
                                        if (strpos($filename, '-lazy') === false) {
                        $modulename = $component . '/' . $filename;
                        $jsfiles[$modulename] = $item->getRealPath();
                    }
                }
                unset($item);
            }
            unset($items);
        }

        return $jsfiles;
    }

}
