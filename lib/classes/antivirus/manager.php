<?php



namespace core\antivirus;

defined('MOODLE_INTERNAL') || die();


class manager {
    
    private static function get_enabled() {
        global $CFG;

        $active = array();
        if (empty($CFG->antiviruses)) {
            return $active;
        }

        foreach (explode(',', $CFG->antiviruses) as $e) {
            if ($antivirus = self::get_antivirus($e)) {
                if ($antivirus->is_configured()) {
                    $active[$e] = $antivirus;
                }
            }
        }
        return $active;
    }

    
    public static function scan_file($file, $filename, $deleteinfected) {
        $antiviruses = self::get_enabled();
        foreach ($antiviruses as $antivirus) {
            $antivirus->scan_file($file, $filename, $deleteinfected);
        }
    }

    
    public static function get_antivirus($antivirusname) {
        global $CFG;

        $classname = '\\antivirus_' . $antivirusname . '\\scanner';
        if (!class_exists($classname)) {
            return false;
        }
        return new $classname();
    }

    
    public static function get_available() {
        $antiviruses = array();
        foreach (\core_component::get_plugin_list('antivirus') as $antivirusname => $dir) {
            $antiviruses[$antivirusname] = get_string('pluginname', 'antivirus_'.$antivirusname);
        }
        return $antiviruses;
    }
}
