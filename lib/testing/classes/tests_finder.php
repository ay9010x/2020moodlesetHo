<?php




class tests_finder {

    
    public static function get_components_with_tests($testtype) {

                $components = self::get_all_plugins_with_tests($testtype) + self::get_all_subsystems_with_tests($testtype);

                $directories = self::get_all_directories_with_tests($testtype);

                $remaining = array_diff($directories, $components);

                $components += $remaining;

        return $components;
    }

    
    private static function get_all_plugins_with_tests($testtype) {
        $pluginswithtests = array();

        $plugintypes = core_component::get_plugin_types();
        ksort($plugintypes);
        foreach ($plugintypes as $type => $unused) {
            $plugs = core_component::get_plugin_list($type);
            ksort($plugs);
            foreach ($plugs as $plug => $fullplug) {
                                if (self::directory_has_tests($fullplug, $testtype)) {
                    $pluginswithtests[$type . '_' . $plug] = $fullplug;
                }
            }
        }
        return $pluginswithtests;
    }

    
    private static function get_all_subsystems_with_tests($testtype) {
        global $CFG;

        $subsystemswithtests = array();

        $subsystems = core_component::get_core_subsystems();

                $subsystems['backup'] = $CFG->dirroot.'/backup';
        $subsystems['db-dml'] = $CFG->dirroot.'/lib/dml';
        $subsystems['db-ddl'] = $CFG->dirroot.'/lib/ddl';

        ksort($subsystems);
        foreach ($subsystems as $subsys => $fullsubsys) {
            if ($fullsubsys === null) {
                continue;
            }
            if (!is_dir($fullsubsys)) {
                continue;
            }
                        if (self::directory_has_tests($fullsubsys, $testtype)) {
                $subsystemswithtests['core_' . $subsys] = $fullsubsys;
            }
        }
        return $subsystemswithtests;
    }

    
    private static function get_all_directories_with_tests($testtype) {
        global $CFG;

                $excludedir = array('node_modules', 'vendor');

                $directoriestosearch = array();
        $alldirs = glob($CFG->dirroot . DIRECTORY_SEPARATOR . '*' , GLOB_ONLYDIR);
        foreach ($alldirs as $dir) {
            if (!in_array(basename($dir), $excludedir) && (filetype($dir) != 'link')) {
                $directoriestosearch[] = $dir;
            }
        }

                $dirs = array();
        foreach ($directoriestosearch as $dir) {
            $dirite = new RecursiveDirectoryIterator($dir);
            $iteite = new RecursiveIteratorIterator($dirite);
            $regexp = self::get_regexp($testtype);
            $regite = new RegexIterator($iteite, $regexp);
            foreach ($regite as $path => $element) {
                $key = dirname(dirname($path));
                $value = trim(str_replace(DIRECTORY_SEPARATOR, '_', str_replace($CFG->dirroot, '', $key)), '_');
                $dirs[$key] = $value;
            }
        }
        ksort($dirs);
        return array_flip($dirs);
    }

    
    private static function directory_has_tests($dir, $testtype) {
        if (!is_dir($dir)) {
            return false;
        }

        $dirite = new RecursiveDirectoryIterator($dir);
        $iteite = new RecursiveIteratorIterator($dirite);
        $regexp = self::get_regexp($testtype);
        $regite = new RegexIterator($iteite, $regexp);
        $regite->rewind();
        if ($regite->valid()) {
            return true;
        }
        return false;
    }


    
    private static function get_regexp($testtype) {

        $sep = preg_quote(DIRECTORY_SEPARATOR, '|');

        switch ($testtype) {
            case 'phpunit':
                $regexp = '|'.$sep.'tests'.$sep.'.*_test\.php$|';
                break;
            case 'features':
                $regexp = '|'.$sep.'tests'.$sep.'behat'.$sep.'.*\.feature$|';
                break;
            case 'stepsdefinitions':
                $regexp = '|'.$sep.'tests'.$sep.'behat'.$sep.'behat_.*\.php$|';
                break;
        }

        return $regexp;
    }
}
